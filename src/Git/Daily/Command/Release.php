<?php
/**
 *
 */

class Git_Daily_Command_Release
    extends Git_Daily_CommandAbstract
{
    const DESCRIPTION = 'Operation daily release';

    protected $load_config = true;

    protected $option = array(
    );

    public function runCommand()
    {
        $is_clean = Git_Daily_GitUtil::isClean();
        if (!$is_clean) {
            throw new Git_Daily_Exception(
                'git status is not clean',
                Git_Daily::E_GIT_STATUS_NOT_CLEAN,
                null, true
            );
        }

        $args = $this->opt->getArgs();
        if (empty($args)) {
            throw new Git_Daily_Exception(
                'please specify release subcommand',
                Git_Daily::E_INVALID_ARGS,
                null, true, 'release'
            );
        }

        $arg = reset($args);
        $method = '_do' . ucfirst(strtolower($arg));
        if (!is_callable(array($this, $method))) {
            throw new Git_Daily_Exception(
                "no such release subcommand: $arg",
                Git_Daily::E_SUBCOMMAND_NOT_FOUND,
                null, true, 'release'
            );
        }

        return call_user_func(array($this, $method));
    }

    private function _doOpen()
    {
        // check if current release process opened
        $release_branches = self::cmd(Git_Daily::$git, array('branch'), array('grep', array("release")));
        if (!empty($release_branches)) {
            $release_branches = implode(',', $release_branches);

            throw new Git_Daily_Exception(
                "release process (on local) is not closed, so cannot open release\n    release branches: $release_branches",
                Git_Daily::E_RELEASE_CANNOT_OPEN,
                null, true
            );
        }

        // check if remote has release process
        if (isset($this->config['remote'])) {
            self::info('first, fetch remotes');
            self::cmd(Git_Daily::$git, array('fetch', '--all'));

            $remote = $this->config['remote'];
            $release_branches = self::cmd(Git_Daily::$git, array('branch', '-a'), array('grep', array("remotes/$remote/release")));

            if (!empty($release_branches)) {
                $release_branches = implode(',', $release_branches);

                throw new Git_Daily_Exception(
                    "release process (on remote) is not closed, so cannot open release\n    release branches: $release_branches",
                    Git_Daily::E_RELEASE_CANNOT_OPEN,
                    null, true
                );
            }
        }
    }

    private function _doSync()
    {
    }

    private function _doClose()
    {
    }

    public static function usage()
    {
        fwrite(STDERR, <<<E
Usage: git daily release open        : Open daily-release process
   or: git daily release sync        : Sync current opened release process
   or: git daily release close       : Close to daily-release process

E
        );
    }
}


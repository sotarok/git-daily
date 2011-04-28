<?php
/**
 *
 */

class Git_Daily_Command_Push
    extends Git_Daily_CommandAbstract
{
    const DESCRIPTION = 'Pull remote to local (for only same branch)';

    protected $option = array(
    );

    protected $load_config = true;

    public function runCommand()
    {
        if (!isset($this->config['remote'])) {
            throw new Git_Daily_Exception('no remote setting');
        }

        // current branch to origin
        $current_branch = Git_Daily_GitUtil::currentBranch();
        if ($current_branch === null) {
            throw new Git_Daily_Exception('not on any branches');
        }

        $remote = $this->config['remote'];
        $remote_branch = Git_Daily_GitUtil::remoteBranch($remote, $current_branch);
        if ($remote_branch === null) {
            throw new Git_Daily_Exception("not remote branch named: $current_branch");
        }

        self::info("run git push $remote $current_branch");
        list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('push', $remote, $current_branch));

        self::outLn($res);
        if ($retval != 0) {
            self::warn("git push failed:");
            throw new Git_Daily_Exception(
                "git push failed"
            );
        }

        return 'push completed';
    }

    public static function usage()
    {
        fwrite(STDERR, <<<E

usage: git daily push

E
        );
    }
}

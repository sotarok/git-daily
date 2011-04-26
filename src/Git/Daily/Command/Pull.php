<?php
/**
 *
 */

class Git_Daily_Command_Pull
    extends Git_Daily_CommandAbstract
{
    const DESCRIPTION = 'Pull remote to local (for only same branch)';

    protected $option = array(
        'rebase' => array(null, 'rebase', Git_Daily_OptionParser::ACT_STORE_TRUE),
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

        $is_rebase = $this->opt->getOptVar('rebase');

        self::info("run git pull $remote $current_branch" . ($is_rebase ? ' (rebase)' : ''));
        if ($is_rebase) {
            list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('pull', '--rebase', $remote, $current_branch));
        } else {
            list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('pull', $remote, $current_branch));
        }

        if ($retval != 0) {
            self::warn("git pull failed:");
            self::out($res);
            throw new Git_Daily_Exception(
                "git pull failed"
            );
        }

        return 'pull completed';
    }

    public static function usage()
    {
        fwrite(STDERR, <<<E
usage: git daily pull
   or: git daily pull --rebase

E
        );
    }
}

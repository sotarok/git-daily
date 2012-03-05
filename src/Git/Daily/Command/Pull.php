<?php
/**
 *
 */

class Git_Daily_Command_Pull
    extends Git_Daily_CommandAbstract
{
    // TODO
    protected $load_config = true;

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return array(
            'rebase' => array(null, 'rebase', Git_Daily_OptionParser::ACT_STORE_TRUE),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return 'Pull remote to local (for only same branch)';
    }


    public function execute()
    {
        if (null === $this->config->get('remote')) {
            throw new Git_Daily_Exception('No remote setting');
        }

        // current branch to origin
        $current_branch = $this->git->currentBranch();
        if ($current_branch === null) {
            throw new Git_Daily_Exception('Not on any branches');
        }

        $remote = $this->config->get('remote');
        $remote_branch = $this->git->hasRemoteBranch($remote, $current_branch);
        if ($remote_branch === null) {
            throw new Git_Daily_Exception("No remote branch named '$current_branch' exists");
        }

        $is_rebase = $this->opt->getOptVar('rebase');

        $this->output->info("[INFO] run: git pull $remote $current_branch" . ($is_rebase ? ' (rebase)' : ''));
        if ($is_rebase) {
            list($res, $retval) = $this->cmd->run(Git_Daily::$git, array('pull', '--rebase', $remote, $current_branch));
        } else {
            list($res, $retval) = $this->cmd->run(Git_Daily::$git, array('pull', $remote, $current_branch));
        }

        $this->output->writeLn($res);
        if ($retval != 0) {
            throw new Git_Daily_Exception("git pull failed");
        }

        return 'pull completed';
    }

    public function usage()
    {
        return <<<E

Usage:
    git daily pull [--rebase]

Options:

    --rebase
        Rebase remote branch instead of merge.

E;
    }
}

<?php
/**
 *
 */

class Git_Daily_Command_Push
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

        $this->output->info("[INFO] run: git push $remote $current_branch");
        list($res, $retval) = $this->cmd->run(Git_Daily::$git, array('push', $remote, $current_branch));

        $this->output->writeLn($res);
        if ($retval != 0) {
            throw new Git_Daily_Exception("git push failed");
        }

        return 'push completed';
    }

    public function usage()
    {
        return <<<E

Usage:
    git daily push

E;
    }
}

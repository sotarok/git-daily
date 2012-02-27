<?php
/**
 *
 */


class Git_Daily_Command_Init
    extends Git_Daily_CommandAbstract
{
    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return 'Initialize git daily';
    }

    public function execute()
    {
        $is_init = $this->cmd->run(Git_Daily::$git, array('config', '--bool', 'gitdaily.init'));
        if (count($is_init) > 0 && (bool)$is_init[0]) {
            throw new Git_Daily_Exception(sprintf('%s already initialized.', Git_Daily::COMMAND));
        }

        $remotes = $this->cmd->run(Git_Daily::$git, array('config', '--list'), array('grep', array('remote')));
        $remote_url = array();
        foreach ($remotes as $remote) {
            //remote.origin.url
            if (preg_match('/remote\.([^\.]+)\.url/', $remote, $m)) {
                $remote_url[] = $m[1];
            }
        }

        $remote = null;
        if (!empty($remote_url)) {
            if (count($remote_url) >= 2) {
                do {
                    $this->output->outLn('Choose your remote:');
                    foreach ($remote_url as $key => $url) {
                        $this->output->outLn('    %d: %s', $key, $url);
                    }
                    self::out('    > ');
                    $num = trim(Git_Daily_CommandUtil::get());

                } while(!isset($remote_url[$num]));
                $first_choise = $remote_url[$num];
            }
            else {
                $first_choise = reset($remote_url);
            }
            $this->cmd->run(Git_Daily::$git, array('config', 'gitdaily.remote', $first_choise));
            $this->output->outLn('Your remote is [%s]', $first_choise);

            $remote =  $first_choise;
        }

        // master branch
        self::out('Name master branch [master]: ');
        $master_branch = trim(Git_Daily_CommandUtil::get());
        if (empty($master_branch)) {
            $master_branch = 'master';
        }
        $this->cmd->run(Git_Daily::$git, array('config', 'gitdaily.master', $master_branch));

        // develop branch
        self::out('Name develop branch [develop]: ');
        $develop_branch = trim(Git_Daily_CommandUtil::get());
        if (empty($develop_branch)) {
            $develop_branch = 'develop';
        }
        $this->cmd->run(Git_Daily::$git, array('config', 'gitdaily.develop', $develop_branch));
        if (!Git_Daily_GitUtil::hasBranch($develop_branch)) {
            if ($remote !== null && Git_Daily_GitUtil::hasRemoteBranch($remote, $develop_branch)) {
                $this->cmd->run(Git_Daily::$git, array('checkout', $develop_branch));
            } else {
                $this->cmd->run(Git_Daily::$git, array('checkout', '-b', $develop_branch));
                if (!empty($remote)) {
                    $this->cmd->run(Git_Daily::$git, array('push', $remote, $develop_branch));
                }
            }
        }

        // initialized
        $this->cmd->run(Git_Daily::$git, array('config', 'gitdaily.init', 'true'));

        $this->output->outLn();
        $this->output->outLn('%s completed to initialize.', Git_Daily::COMMAND);
        return null;
    }
}

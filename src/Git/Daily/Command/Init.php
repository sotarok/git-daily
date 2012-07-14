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
        return array(
            'master'  => array(null, 'master',  Git_Daily_OptionParser::ACT_STORE_VAR),
            'develop' => array(null, 'develop', Git_Daily_OptionParser::ACT_STORE_VAR),
            'remote'  => array(null, 'remote',  Git_Daily_OptionParser::ACT_STORE_VAR),
        );
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
        if (!$this->context->isInGitRepository()) {
            throw new Git_Daily_Exception('Not a git repository (or any of the parent directories): .git',
                Git_Daily::E_NOT_IN_REPO
            );
        }

        $is_init = $this->config->get('init');
        if ($is_init) {
            throw new Git_Daily_Exception(sprintf('%s already initialized.', Git_Daily::COMMAND));
        }

        // store local var and set up after all options are decided.
        $chose_options = array();

        $remote = $this->opt->getOptVar('remote');
        $remotes = $this->git->remotes();

        do {
            if (null !== $remote) {
                if (empty($remotes)) {
                    throw new Git_Daily_Exception(sprintf('Remote "%s" is not exists', $remote));
                }
            } else {
                if (!empty($remotes)) {
                    $this->output->writeLn('Choose your remote (choose from the following):');
                    foreach ($remotes as $key => $url) {
                        if ($remote === null) {
                            $this->output->writeLn('    - %s (default)', $key);
                            $remote = $key;
                        } else {
                            $this->output->writeLn('    - %s', $key);
                        }
                    }
                    $this->output->write('    > ');
                    $input_remote = trim($this->cmd->get());
                    if (!empty($input_remote)) {
                        $remote = $input_remote;
                    }
                }
            }

            if (null === $remote) {
                break;
            }

            // has remote
            if (isset($remotes[$remote])) {
                $this->output->writeLn('Your remote is [%s]', $remote);
                $chose_options['remote'] = $remote;
                break;
            } else {
                $this->output->warn('No remote named "%s".', $remote);
            }

            $remote = null;
        } while(1);

        // master branch
        if (null === ($master_branch = $this->opt->getOptVar('master'))) {
            $this->output->write('Name master branch [master]: ');
            $master_branch = trim($this->cmd->get());
            if (empty($master_branch)) {
                $master_branch = 'master';
            }
        }
        // TODO: branch name check
        $chose_options['master'] = $master_branch;

        // develop branch
        if (null === ($develop_branch = $this->opt->getOptVar('develop'))) {
            $this->output->write('Name develop branch [develop]: ');
            $develop_branch = trim($this->cmd->get());
            if (empty($develop_branch)) {
                $develop_branch = 'develop';
            }
        }
        // TODO: branch name check
        $chose_options['develop'] = $develop_branch;


        $this->doInitialize($chose_options);

        if (!$this->git->hasBranch($chose_options['develop'])) {
            list($res, $retval) = $this->cmd->run(Git_Daily::$git, array('checkout', '-b', $chose_options['develop']));
            $this->output->info("[INFO] Create and checkout {$chose_options['develop']} branch.");
        } else {
            list($res, $retval) = $this->cmd->run(Git_Daily::$git, array('checkout', $chose_options['develop']));
            $this->output->info("[INFO] Checkout {$chose_options['develop']} branch.");
        }
        $this->output->info($res);

        if (isset($chose_options['remote']) && !$this->git->hasRemoteBranch($chose_options['remote'], $chose_options['develop'])) {
            list($res, $retval) = $this->cmd->run(Git_Daily::$git, array('push', $chose_options['remote'], $chose_options['develop']));
            $this->output->info("[INFO] The remote has no {$chose_options['develop']} branch, push to initialize.");
            $this->output->info($res);
        }

        return array("\n%s completed to initialize.", Git_Daily::COMMAND);
    }

    protected function doInitialize($options)
    {
        if (isset($options['remote'])) {
            $this->config->set('remote', $options['remote']);
        }
        $this->config->set('master', $options['master']);
        $this->config->set('develop', $options['develop']);
        $this->config->set('init', true);
    }

    public function usage()
    {
        return <<<E
Initialize git-daily repository. Set up git-daily branching model (like gitflow).
If any options are not given, setup interactive.

Usage:
    git daily init [--master <master_name>] [--develop <develp_name>] [--remote <remote_name>]

Options:

    --master
        Branch name to use as master. Master branch is always use as a released branch.

    --develop
        Branch name to use as develop. Develop branch is use to development.

    --remote
        Collaborate your development with some remote repository, set your remote name.

E;
    }
}

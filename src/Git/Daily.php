<?php
/**
 *
 */


class Git_Daily
{
    const VERSION = '0.9.0-dev';
    const COMMAND = 'git-daily';

    const SUPPORTED_MIN_GIT_VERSION = '1.7.0';

    const USAGE_SPACE = 4;

    const E_GIT_NOT_FOUND           = 1;
    const E_GIT_STATUS_NOT_CLEAN    = 2;
    const E_GIT_PUSH_FAILED         = 3;
    const E_GIT_PULL_FAILED         = 4;
    const E_GIT_VERSION_COMPAT      = 5;

    const E_NOT_IN_REPO             = 101;
    const E_NOT_INITIALIZED         = 102;

    const E_SUBCOMMAND_NOT_FOUND    = 200;
    const E_NO_SUCH_CONIFIG         = 201;
    const E_INVALID_CONIFIG_VALUE   = 202;
    const E_INVALID_ARGS            = 203;
    const E_RELEASE_CANNOT_OPEN     = 204;

    const E_ERROR                   = 255;

    public static $git = null;

    private $git_dir = null;

    public function __construct(Git_Daily_CommandUtil $cmd)
    {
        if (getenv('GIT_DAILY_GITBIN')) {
            self::$git = getenv('GIT_DAILY_GITBIN');
        } else {
            list($git_cmd,) = $cmd->run('which', array('git'));
            if (empty($git_cmd) || !is_executable($git_cmd = array_shift($git_cmd))) {
                throw new Git_Daily_Exception("git command not found",
                    self::E_GIT_NOT_FOUND, null, true
                );
            }
            self::$git = $git_cmd;
        }

        $this->cmd = $cmd;

        $this->checkGitVersion();
        $this->findGitDir();
        $this->registerCommands();

        $this->config = new Git_Daily_Config($this);
    }

    /**
     * @return object Git_Daily_Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function getCommandUtil()
    {
        return $this->cmd;
    }

    private function findGitDir()
    {
        list($git_dir, $retval) = $this->cmd->run(self::$git, array('rev-parse', '--git-dir'));
        if ($retval == 0) {
            $this->git_dir = $git_dir;
        }
    }

    /**
     * @return mixed
     */
    public function getGitDir()
    {
        return $this->git_dir;
    }

    /**
     * @return boolan
     */
    public function isInGitRepository()
    {
        return !is_null($this->git_dir);
    }

    /**
     * @throws Git_Daily_Exception
     * @return boolan
     */
    private function checkGitVersion()
    {
        // git version check
        list($git_version, ) = $this->cmd->run(self::$git, array('version'));
        if (preg_match('/((\d\.)+\d)/', trim($git_version[0]), $matches)) {
            $git_version = $matches[1];
        } else {
            $git_version = 0;
        }

        if (version_compare($git_version, self::SUPPORTED_MIN_GIT_VERSION) < 0) {
            throw new Git_Daily_Exception(
                sprintf("git daily now supported at least version %s\nyour git version: %s", self::SUPPORTED_MIN_GIT_VERSION, $git_version),
                self::E_GIT_VERSION_COMPAT, null, true
            );
        }

        return true;
    }

    public function registerCommand($name, $class)
    {
        $this->commands[$name] = $class;
    }

    public function registerCommands()
    {
        $this->commands = array(
            'version' => 'Git_Daily_Command_Version',
            'init'    => 'Git_Daily_Command_Init',
            'push'    => 'Git_Daily_Command_Push',
            'pull'    => 'Git_Daily_Command_Pull',
            'release' => 'Git_Daily_Command_Release',
            'config'  => 'Git_Daily_Command_Config',
            'help'    => 'Git_Daily_Command_Help',
        );
    }

    public function getCommands()
    {
        return $this->commands;
    }

    public function getCommand($name)
    {
        if (isset($this->commands[$name])) {
            return $this->commands[$name];
        }

        return false;
    }

    public function createDummyCommandClass($cmd_name)
    {
        $cmd_class = $this->getCommand($cmd_name);
        if ($cmd_class) {
            return new $cmd_class($cmd_name, $this, array(), new Git_Daily_ConsoleOutput(), new Git_Daily_CommandUtil());
        }

        // TODO
    }

    public function run($args, Git_Daily_OutputInterface $output)
    {
        try {
            $file = array_shift($args);
            if (count($args) < 1) {
                throw new Git_Daily_Exception("No subcommand specified",
                    self::E_SUBCOMMAND_NOT_FOUND, null, true
                );
            }

            if (!($cmd_class = $this->getCommand($subcommand = array_shift($args)))) {
                throw new Git_Daily_Exception(
                    "No such subcommand: $subcommand",
                    self::E_SUBCOMMAND_NOT_FOUND, null, true
                );
            }

            $cmd = new $cmd_class($subcommand, $this, $args, $output, $this->cmd);

            if (!$this->getGitDir() && !$cmd->isAllowedOutOfRepo()) {
                throw new Git_Daily_Exception("not in git repository",
                    self::E_NOT_IN_REPO, null, true
                );
            }

            $result = $cmd->execute();
            if ($result !== null) {
                if (!is_array($result)) {
                    $result = array($result);
                }
                call_user_func_array(array($output, 'writeLn'), $result);
            }

            return 0;
        } catch (Git_Daily_Exception $e) {
            $output->warn("Fatal: %s", $e->getMessage());
            $output->writeLn('');
            if ($e->isShowUsage()) {
                $subcommand = $e->getSubCommand();
                $output->writeLn($this->usage($subcommand));
            }

            return $e->getCode();
        }
    }

    public function usage($subcommand = null, $only_subcommand = false)
    {
        $usage_str = '';

        if ($subcommand === null && !$only_subcommand) {
            $usage_str .= <<<E
git-daily:

Usage:

E;

            $lists = $this->getCommands();
            $max = 0;
            foreach ($lists as $cmd_name => $cmd_class) {
                if (strlen($cmd_name) > $max) {
                    $max = strlen($cmd_name);
                }
            }
            foreach ($lists as $cmd_name => $cmd_class) {
                $usage_str .= "    ";
                $usage_str .= str_pad($cmd_name, $max + self::USAGE_SPACE, ' ');

                $cmd = $this->createDummyCommandClass($cmd_name);
                $usage_str .= $cmd->getDescription();
                $usage_str .= PHP_EOL;
            }
        }

        if ($subcommand !== null) {
            try {
                $cmd = $this->createDummyCommandClass($subcommand);
                $usage_str .= $cmd->usage();
            } catch (Git_Daily_Exception $e) {
                // through
            }
        }

        return $usage_str;
    }
}

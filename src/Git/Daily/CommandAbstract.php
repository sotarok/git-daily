<?php
/**
 *
 */

abstract class Git_Daily_CommandAbstract
    implements Git_Daily_CommandInterface
{
    public static $last_command_result;
    public static $last_command_retval;

    protected $command_name = '';

    protected $context = null;
    protected $cmd = null;
    protected $output = null;
    protected $opt = null;

    public function __construct(
        $command_name,
        Git_Daily $context,
        $args,
        Git_Daily_OutputInterface $output,
        Git_Daily_CommandUtil $cmd
    ) {
        $this->command_name = $command_name;
        $this->context = $context;
        $this->output = $output;
        $this->cmd = $cmd;
        $this->git = new Git_Daily_GitUtil($cmd);

        try {
            $this->opt = new Git_Daily_OptionParser($args, $this->getOptions());
        } catch (Git_Daily_Exception $e) {
            throw new Git_Daily_Exception(
                $e->getMessage(),
                $e->getCode(),
                $e,
                true,
                $command_name
            );
        }
        $this->config = $this->context->getConfig();
    }

    public function createCommand($cmd_class, $args = array())
    {
        return new $cmd_class($this->context, $args, $this->output, $this->cmd);
    }

    public function cmd($cmd, $options, array $pipe = array())
    {
        list($ret, $retval) = $this->cmd->run($cmd, $options, $pipe);
        self::$last_command_result = $ret;
        self::$last_command_retval = $retval;

        return $ret;
    }

    public function isAllowedOutOfRepo()
    {
        return false;
    }

    /**
     * @return array    sub-command list
     * @throws Git_Daily_Exception
     */
    public function getSubCommands()
    {
        throw new Git_Daily_Exception(
            "Command '{$this->command_name}' has no subcommand ",
            Git_Daily::E_INVALID_ARGS,
            null, true, $this->command_name
        );
    }

    public function getSubCommand($name, $args)
    {
        $commands = $this->getSubCommands();
        if (!isset($commands[$name])) {
            throw new Git_Daily_Exception(
                "No such subcommand: $name",
                Git_Daily::E_SUBCOMMAND_NOT_FOUND,
                null, true, $this->command_name
            );
        }

        $cmd_class = $commands[$name];
        $cmd = new $cmd_class($name, $this->context, $args, $this->output, $this->cmd);
        return $cmd;
    }

    public function usage()
    {
        return '';
    }
}

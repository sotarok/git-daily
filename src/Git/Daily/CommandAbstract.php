<?php
/**
 *
 */

abstract class Git_Daily_CommandAbstract
    implements Git_Daily_CommandInterface
{
    public static $last_command_result;
    public static $last_command_retval;

    protected $context = null;
    protected $cmd = null;
    protected $output = null;
    protected $opt = null;

    protected $load_config = false;

    public function __construct(
        Git_Daily $context,
        $args,
        Git_Daily_OutputInterface $output,
        Git_Daily_CommandUtil $cmd
    ) {
        $this->context = $context;
        $this->output = $output;
        $this->cmd = $cmd;
        $this->git = new Git_Daily_GitUtil($cmd);
        $this->opt = new Git_Daily_OptionParser($args, $this->getOptions());
        $this->config = $this->context->getConfig();
    }

    public function createCommand($class_name, $args = array())
    {
        return new $class_name($this->context, $args, $this->output, $this->cmd);
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

    public function getSubCommands()
    {
        return array();
    }

    public function usage()
    {
        return '';
    }
}

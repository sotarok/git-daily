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

        if ($this->load_config) {
            $config = new Git_Daily_Command_Config(array(), $output, $cmd);
            $config_vars = $config->execute();
            foreach ($config_vars as $config_var) {
                $config_line = explode('=', $config_var);
                $key = str_replace('gitdaily.', '', array_shift($config_line));
                $this->config[$key] =  implode('=', $config_line);
            }
        }
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

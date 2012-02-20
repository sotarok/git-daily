<?php
/**
 *
 */

abstract class Git_Daily_CommandAbstract
{
    //protected $args = array();
    protected $option = array();

    protected $opt = null;

    public static $last_command_result;
    public static $last_command_retval;

    protected $load_config = false;
    protected $config = array();

    public function __construct($args)
    {
        $this->opt = new Git_Daily_OptionParser($args, $this->option);

        if ($this->load_config) {
            $config = new Git_Daily_Command_Config(array());
            $config_vars = $config->runCommand();
            foreach ($config_vars as $config_var) {
                $config_line = explode('=', $config_var);
                $key = str_replace('gitdaily.', '', array_shift($config_line));
                $this->config[$key] =  implode('=', $config_line);
            }
        }
    }

    public static function warn($msg)
    {
        self::out("[41;37m");
        self::out('[WARNING] ' . $msg);
        self::out("[0m");
        self::out(PHP_EOL);
    }

    public static function info($msg)
    {
        self::out("[36m");
        self::out('[INFO] ' . $msg);
        self::out("[0m");
        self::out(PHP_EOL);
    }

    public static function outLn()
    {
        $args = func_get_args();
        call_user_func_array(array('self', 'out'), $args);
        self::out(PHP_EOL);
    }

    public static function out()
    {
        if (func_num_args() < 1) {
            return;
        }
        elseif (func_num_args() == 1) {
            $args = func_get_args();
            $arg = $args[0];
            if (is_array($arg)) {
                $string = '';
                foreach ($arg as $str) {
                    $string .= $str . PHP_EOL;
                }
                $string = trim($string);
            }
            else {
                $string = $args[0];
            }
        }
        else {
            $args = func_get_args();
            if (count($args) - 1 != preg_match_all('/%/', $args[0], $m)) {
                $string = '';
                foreach ($args as $str) {
                    $string .= $str . PHP_EOL;
                }
                $string = trim($string);
            }
            else {
                $format = array_shift($args);
                $string = vsprintf($format, $args);
            }
        }
        fwrite(STDOUT, $string);
    }

    public static function cmd($cmd, $options, array $pipe = array())
    {
        list($ret, $retval) = Git_Daily_CommandUtil::cmd($cmd, $options, $pipe);
        self::$last_command_result = $ret;
        self::$last_command_retval = $retval;

        return $ret;
    }

    public static function runSubCommand($subcommand, $argv = array())
    {
        $command = Git_Daily::getSubCommand($subcommand);
        $command_class = new $command($argv);
        return $command_class->runCommand();
    }

    abstract public function runCommand();

}

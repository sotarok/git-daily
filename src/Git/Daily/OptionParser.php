<?php
/**
 *
 */


class Git_Daily_OptionParser
{
    const ACT_STORE_VAR     = 1;
    const ACT_STORE_TRUE    = 2;
    const ACT_STORE_FALSE   = 3;

    protected $var = array();

    protected $args = array();
    protected $options = array();

    public function __construct($argv, $options)
    {
        $this->options = $options;
        $this->parse($argv, $options);
    }

    public function parse($argv, $options)
    {
        $orig_options = $options;
        $normalize_argv = array();
        foreach ($argv as $arg) {
            if (strpos($arg, '=') && strpos($arg, '--') === 0) {
                $arg_line = explode('=', $arg);
                $normalize_argv[] = array_shift($arg_line);
                $normalize_argv[] = implode('=', $arg_line);
            }
            else {
                $normalize_argv[] = $arg;
            }
        }
        $argv = $normalize_argv;

        for ($i = 0, $end = count($argv); $i < $end ; ++$i) {
            $arg = $argv[$i];
            if ($arg{0} == '-') {
                $optdef_key = $this->_findDef($arg);
                if (!$optdef_key) {
                    throw new Git_Daily_Exception(
                        "invalid option: {$arg}"
                    );
                }
                $optdef = $this->options[$optdef_key];
                if (!isset($optdef[2])) {
                    $optdef[2] = self::ACT_STORE_VAR;
                }
                switch ($optdef[2]) {
                case self::ACT_STORE_VAR:
                    // get next key
                    $key = $argv[$i];
                    unset($argv[$i]);
                    $i++;
                    if (!isset($argv[$i])) {
                        throw new Git_Daily_Exception(
                            "argument {$key} required a value"
                        );
                    }
                    $value = $argv[$i];
                    $this->var[$optdef_key] = $value;
                    unset($argv[$i]);
                    break;
                case self::ACT_STORE_TRUE:
                    $key = $argv[$i];
                    $this->var[$optdef_key] = true;
                    unset($argv[$i]);
                    break;
                case self::ACT_STORE_FALSE:
                    $key = $argv[$i];
                    $this->var[$optdef_key] = false;
                    unset($argv[$i]);
                    break;
                default:
                    break;
                }
            }
        }

        // register remain argv as args
        $this->args = array_merge(array(), $argv);
    }

    private function _findDef($arg)
    {
        // longopt
        if (strlen($arg) > 3 && $arg{0} == '-' && $arg{1} == '-') {
            foreach ($this->options as $key => $opt) {
                $def_opt = $opt[1];
                $real_arg = substr($arg, 2);
                if ($real_arg == $def_opt) {
                    return $key;
                }
            }

            // not found
            return false;
        }
        if (strlen($arg) == 2 && $arg{0} == '-') {
            foreach ($this->options as $key => $opt) {
                $def_opt = $opt[0];
                $real_arg = substr($arg, 1);
                if ($real_arg == $def_opt) {
                    return $key;
                }
            }

            // not found
            return false;
        }
        // $arg is not opt
        return false;
    }

    public function getOptVar($key)
    {
        if (isset($this->var[$key])) {
            return $this->var[$key];
        }
        return null;
    }

    public function getArgs()
    {
        return $this->args;
    }
}

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

    public function __construct($argv, $options)
    {
        $this->parse($argv, $options);
    }

    public function parse($argv, $options)
    {
        foreach ($argv as $arg) {
            if ($arg{0} == '-') {
            }
        }

        // register remain argv as args
        $this->args = $argv;
    }

    public function getOptVar($key)
    {
        if (isset($this->var[$key])) {
            return $this->var[$key];
        }
    }

    public function getArgs()
    {
        return $this->args;
    }
}

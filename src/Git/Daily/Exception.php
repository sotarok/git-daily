<?php
/**
 *
 */


class Git_Daily_Exception
    extends Exception
{
    public function __construct($message = "", $code = 0, $prev = null, $show_usage = false, $subcommand = null)
    {
        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
            parent::__construct($message, $code);
        }
        else {
            parent::__construct($message, $code, $prev);
        }

        $this->_show_usage = $show_usage;
        $this->_subcommand = $subcommand;

        if ($show_usage) {
            if (!empty($message)) {
                fwrite(STDERR, "[41;37m$message[0m" . PHP_EOL);
            }
            if ($subcommand !== null) {
                Git_Daily::usage($subcommand);
            }
        }
    }

    public function isShowUsage()
    {
        return (bool)$this->_show_usage;
    }

    public function getSubCommand()
    {
        return $this->_subcommand;
    }
}

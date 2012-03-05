<?php
/**
 *
 */


class Git_Daily_Exception
    extends Exception
{
    private $subcommand = null;
    private $show_usagea = null;

    public function __construct($message = "", $code = 255, $prev = null, $show_usage = false, $subcommand = null)
    {
        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
            parent::__construct($message, $code);
        }
        else {
            parent::__construct($message, $code, $prev);
        }

        $this->show_usage = $show_usage;
        $this->subcommand = $subcommand;
    }

    public function isShowUsage()
    {
        return (bool)$this->show_usage;
    }

    public function getSubCommand()
    {
        return $this->subcommand;
    }
}

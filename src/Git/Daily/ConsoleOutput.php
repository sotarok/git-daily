<?php
/**
 *
 */


class Git_Daily_ConsoleOutput
    implements Git_Daily_OutputInterface
{
    public function printString($string)
    {
        echo $string;
    }

    public function info()
    {
        $string = call_user_func_array(array($this, 'formatString'), func_get_args());
        $this->printString("[36m" . $string . "[0m" . PHP_EOL);
    }

    public function warn()
    {
        $string = call_user_func_array(array($this, 'formatString'), func_get_args());
        $this->printString("[41;37m" . $string . "[0m" . PHP_EOL);
    }

    public function write()
    {
        $string = call_user_func_array(array($this, 'formatString'), func_get_args());
        $this->printString($string);
    }

    public function writeLn()
    {
        $string = call_user_func_array(array($this, 'formatString'), func_get_args());
        $this->printString($string . PHP_EOL);
    }

    public function formatString()
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

        return $string;
    }
}

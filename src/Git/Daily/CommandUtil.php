<?php
/**
 *
 */

class Git_Daily_CommandUtil
{
    const YESNO_YES = 1;
    const YESNO_NO  = 2;
    public static function get()
    {
        return fgets(STDIN);
    }

    public static function yesNo($message = "", $default = self::YESNO_YES)
    {
        $default_yes = ($default == self::YESNO_YES) ? true : false;
        fwrite(STDOUT, $message);
        if ($default_yes) {
            fwrite(STDOUT, ' [Yn] : ');
        } else {
            fwrite(STDOUT, ' [yN] : ');
        }

        $answer = trim(self::get());
        if (empty($answer)) {
            $answer = ($default_yes) ? 'y' : 'n';
        }

        if (strcasecmp($answer, 'y') == 0) {
            return true;
        }
        elseif (strcasecmp($answer, 'n') == 0) {
            return false;
        }
    }

    public static function cmd($cmd, $options, array $pipe = array())
    {
        $cmd_string = self::buildCmdString($cmd, $options, $pipe);
        exec($cmd_string, $ret, $retval);

        return array($ret, $retval);
    }

    public static function buildCmdString($cmd, $options, array $pipe = array())
    {
        $options = array_map('escapeshellarg', $options);
        $cmd_string = $cmd . ' ' . implode(' ', $options) . ' 2>&1';

        if (!empty($pipe)) {
            $cmd_string .= ' | ' . call_user_func_array(array('self', 'buildCmdString'), $pipe);
        }

        return $cmd_string;
    }
}

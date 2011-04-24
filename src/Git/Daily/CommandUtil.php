<?php
/**
 *
 */

class Git_Daily_CommandUtil
{
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

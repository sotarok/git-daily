<?php
/**
 *
 */


class Git_Daily_GitUtil
{
    public static function isClean()
    {
        list($res, ) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('status', '-uno', '-s'));
        return empty($res);
    }
}


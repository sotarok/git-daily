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

    public static function currentBranch()
    {
        // git branch --no-color | grep '^\* ' | grep -v 'no branch' | sed 's/^* //g'
        list($res, ) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('branch', '--no-color'),
            array(
                'grep', array('^\*'),
                array(
                    'grep', array('-v', 'no branch'),
                    array(
                        'sed', array('s/^* //g')
                    )
                )
            )
        );

        if (empty($res)) {
            return null;
        }
        return array_shift($res);
    }

    public static function remoteBranch($remote, $branch)
    {
        list($res, ) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('branch', '--no-color', '-a'),
            array(
                'grep', array("remotes/$remote/$branch"),
                array(
                    'sed', array('s/^\s\+//g')
                )
            )
        );
        return array_shift($res);
    }
}


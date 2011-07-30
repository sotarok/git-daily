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

    public static function branches()
    {
        list($res, ) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('branch', '--no-color'),
            array(
                'sed', array('s/^[^a-zA-Z0-9]*//g'),
            )
        );
        return $res;
    }

    public static function mergedBranches()
    {
        list($res, ) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('branch', '--no-color', '--merged'),
            array(
                'sed', array('s/^[^a-zA-Z0-9]*//g'),
            )
        );
        return $res;
    }

    public static function releaseBranches()
    {
        list($release_branch, ) = Git_Daily_CommandUtil::cmd(
            Git_Daily::$git, array('branch'),
            array('grep', array("release/"),
                array(
                    'sed', array('s/^[^a-zA-Z0-9]*//g'),
                )
            )
        );
        return $release_branch;
    }

    public static function hasBranch($branch)
    {
        $branches = self::branches();
        return in_array($branch, $branches);
    }

    public static function hasRemoteBranch($remote, $branch)
    {
        $remote_branch = self::remoteBranch($remote, $branch);
        if ($remote != null) {
            return true;
        }
        return false;
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
        if (!empty($res)) {
            return array_shift($res);
        } else {
            return null;
        }
    }

}


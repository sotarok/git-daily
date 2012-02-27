<?php
/**
 *
 */


class Git_Daily_GitUtil
{

    public function __construct(Git_Daily_CommandUtil $cmd)
    {
        $this->cmd = $cmd;
    }

    public function isClean()
    {
        list($res, ) = $this->cmd->run(Git_Daily::$git, array('status', '-uno', '-s'));
        return empty($res);
    }

    public function branches()
    {
        list($res, ) = $this->cmd->run(Git_Daily::$git, array('branch', '--no-color'),
            array(
                'sed', array('s/^[^a-zA-Z0-9]*//g'),
            )
        );
        return $res;
    }

    public function mergedBranches()
    {
        list($res, ) = $this->cmd->run(Git_Daily::$git, array('branch', '--no-color', '--merged'),
            array(
                'sed', array('s/^[^a-zA-Z0-9]*//g'),
            )
        );
        return $res;
    }

    public function releaseBranches($branch)
    {
        list($release_branch, ) = $this->cmd->run(
            Git_Daily::$git, array('branch'),
            array('grep', array("{$branch}/"),
                array(
                    'sed', array('s/^[^a-zA-Z0-9]*//g'),
                )
            )
        );
        return $release_branch;
    }

    public function hasBranch($branch)
    {
        $branches = self::branches();
        return in_array($branch, $branches);
    }

    public function hasRemoteBranch($remote, $branch)
    {
        $remote_branch = self::remoteBranch($remote, $branch);
        if ($remote_branch != null) {
            return true;
        }
        return false;
    }

    public function currentBranch()
    {
        // git branch --no-color | grep '^\* ' | grep -v 'no branch' | sed 's/^* //g'
        list($res, ) = $this->cmd->run(Git_Daily::$git, array('branch', '--no-color'),
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

    public function remoteBranch($remote, $branch)
    {
        list($res, ) = $this->cmd->run(Git_Daily::$git, array('branch', '--no-color', '-a'),
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


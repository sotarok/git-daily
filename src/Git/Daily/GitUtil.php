<?php
/**
 *
 */


class Git_Daily_GitUtil
{
    protected $cmd = null;

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
        list($res, ) = $this->cmd->run(Git_Daily::$git, array('branch', '--no-color'));
        return $this->normalizeBranches($res);
    }

    public function mergedBranches()
    {
        list($res, ) = $this->cmd->run(Git_Daily::$git, array('branch', '--no-color', '--merged'));
        return $this->normalizeBranches($res);
    }

    public function releaseBranches($branch)
    {
        list($release_branch, ) = $this->cmd->run(
            Git_Daily::$git, array('branch'),
            array('grep', array("{$branch}/"))
        );
        return $this->normalizeBranches($release_branch);
    }

    public function hasBranch($branch)
    {
        $branches = $this->branches();
        return in_array($branch, $branches);
    }

    public function hasRemoteBranch($remote, $branch)
    {
        $remote_branch_name = "$remote/$branch";
        $remote_branches = $this->remoteBranches($remote);
        foreach ($remote_branches as $remote_branch) {
            if ($remote_branch_name == $remote_branch) {
                return true;
            }
        }
        return false;
    }

    public function currentBranch()
    {
        list($res, ) = $this->cmd->run(Git_Daily::$git, array('branch', '--no-color'));

        $branch = null;
        foreach ($res as $branch_name) {
            if ($branch_name != '* (no branch)'
                && ($pos = strpos($branch_name, '*')) === 0) {
                $branch = substr($branch_name, 2);
            }
        }

        return $branch;
    }

    protected function normalizeBranches(array $res) {
        $branches = array();
        foreach ($res as $branch_name) {
            $branch_name = str_replace('* ', '', $branch_name);
            $branches[] = trim($branch_name);
        }
        return $branches;
    }

    public function remotes()
    {
        list($remote_url_list, $retval) = $this->cmd->run(Git_Daily::$git, array('config', '--get-regexp', '^remote.*url$'));

        $remotes = array();
        foreach ($remote_url_list as $remote_url) {
            if (preg_match("/^remote\.([^\.]+)\.url (.+)$/", $remote_url, $m)) {
                $remotes[$m[1]] = $m[2];
            }
        }

        return $remotes;
    }

    public function hasRemote($remote_name)
    {
        $remotes = $this->remotes();
        return isset($remotes[$remote_name]);
    }

    /**
     * @param string $remote    remote name
     * @return array            branch list of the remote
     */
    public function remoteBranches($remote)
    {
        list($res, ) = $this->cmd->run(Git_Daily::$git, array('branch', '--no-color', '-r'));
        $branches = $this->normalizeBranches($res);

        $r_branches = array();
        foreach ($branches as $branch) {
            if (strpos($branch, "$remote/") === 0) {
                $r_branches[] = $branch;
            }
        }

        return $r_branches;
    }

}


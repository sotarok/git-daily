<?php
/**
 *  @package    Git
 *  @author     Sotaro Karasawa <sotaro.k@gmail.com>
 */

/**
 *  Git_Daily_Command_Release
 *
 *  @package    Git
 */
class Git_Daily_Command_Release
    extends Git_Daily_CommandAbstract
{
    const DESCRIPTION = 'Operation daily release';

    protected $load_config = true;

    protected $option = array(
        'yes' => array('y', null, Git_Daily_OptionParser::ACT_STORE_TRUE),
    );

    protected $base_branch = 'develop';

    protected $merge_to = array('master', 'develop');

    protected $branch_prefix = 'release';

    /**
     *  runCommand
     */
    public function runCommand()
    {
        $is_clean = Git_Daily_GitUtil::isClean();
        if (!$is_clean) {
            throw new Git_Daily_Exception(
                'git status is not clean',
                Git_Daily::E_GIT_STATUS_NOT_CLEAN,
                null, true
            );
        }

        $args = $this->opt->getArgs();
        if (empty($args)) {
            throw new Git_Daily_Exception(
                'please specify release subcommand',
                Git_Daily::E_INVALID_ARGS,
                null, true, 'release'
            );
        }

        $arg = reset($args);
        $method = '_do' . ucfirst(strtolower($arg));
        if (!is_callable(array($this, $method))) {
            throw new Git_Daily_Exception(
                "no such release subcommand: $arg",
                Git_Daily::E_SUBCOMMAND_NOT_FOUND,
                null, true, 'release'
            );
        }

        return call_user_func(array($this, $method));
    }

    /**
     * getMergedBranches
     */
    protected function getMergeBranches()
    {
        return $this->merge_to;
    }

    /**
     *  Open and start release process
     *
     *  @throws Git_Daily_Exception
     *  @return string
     */
    private function _doOpen()
    {
        // get current branch
        $current_branch = Git_Daily_GitUtil::currentBranch();
        if ($current_branch != $this->config[$this->base_branch]) {
            throw new Git_Daily_Exception(
                "currently not on {$this->config[$this->base_branch]} but on $current_branch"
            );
        }

        // check if current release process opened
        $release_branches = Git_Daily_GitUtil::releaseBranches($this->branch_prefix);
        if (!empty($release_branches)) {
            $release_branches = implode(',', $release_branches);

            throw new Git_Daily_Exception(
                "release process (on local) is not closed, so cannot open release\n    release branches: $release_branches",
                Git_Daily::E_RELEASE_CANNOT_OPEN,
                null, true
            );
        }

        // check if remote has release process
        if (isset($this->config['remote'])) {
            self::info('first, fetch remotes');
            self::cmd(Git_Daily::$git, array('fetch', '--all'));

            $remote = $this->config['remote'];
            $release_branches = self::cmd(Git_Daily::$git, array('branch', '-a'),
                array('grep', array("remotes/$remote/" . $this->branch_prefix))
            );

            if (!empty($release_branches)) {
                $release_branches = implode(',', $release_branches);

                throw new Git_Daily_Exception(
                    "release process (on remote) is not closed, so cannot open release\n    release branches: $release_branches",
                    Git_Daily::E_RELEASE_CANNOT_OPEN,
                    null, true
                );
            }
        }

        $y = $this->opt->getOptVar('yes');
        $new_release_branch = $this->branch_prefix . '/' . date('Ymd-Hi');
        // confirmation
        if (!$y
            && !Git_Daily_CommandUtil::yesNo(
                "Confirm: create branch $new_release_branch from $current_branch ?", Git_Daily_CommandUtil::YESNO_NO
            )
        ) {
            throw new Git_Daily_Exception('abort');
        }

        // merge current branch
        if (isset($this->config['remote'])) {
            //
            // Fetch --all is already done. Just git merge.
            //
            $remote = $this->config['remote'];
            self::info("merge $current_branch branch from remote");
            $res = self::cmd(Git_Daily::$git, array('merge', "$remote/$current_branch"));
            if (self::$last_command_retval != 0) {
                self::warn('merge failed');
                self::outLn($res);
                throw new Git_Daily_Exception('abort');
            }
        }

        // create release branch
        self::info("create release branch: $new_release_branch");
        $res = self::cmd(Git_Daily::$git, array('branch', $new_release_branch));
        if (isset($this->config['remote'])) {
            $remote = $this->config['remote'];
            self::info("push to remote: $remote");
            list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('push', $remote, $new_release_branch));
            self::outLn($res);
            if ($retval != 0) {
                self::warn('push failed');
                $res = self::cmd(Git_Daily::$git, array('branch', '-d', $new_release_branch));
                self::outLn($res);
                self::info('rollback (delete branch)');

                throw new Git_Daily_Exception('abort');
            }
        }

        self::cmd(Git_Daily::$git, array('checkout', $new_release_branch));
        return 'release opened';
    }

    /**
     *  _doSync
     *
     *  Sync release process:
     *  1. If remote release process opened and local have no release branch, then checkout it.
     *  2. If remote release process opened and also local have a release branch, then pull/push.
     *  3. If remote release process closed and still local have a release branch, clean up it.
     *  4. If remote release process closed and also local have no release branch, do nothing.
     *
     *  @throws Git_Daily_Exception
     *  @return string
     */
    private function _doSync()
    {
        $current_branch = Git_Daily_GitUtil::currentBranch();

        if (!isset($this->config['remote'])) {
            throw new Git_Daily_Exception('remote not setted, cannot sync');
        }
        $remote = $this->config['remote'];
        $develop_branch = $this->config[$this->base_branch];
        self::info('first, fetch remotes');
        self::cmd(Git_Daily::$git, array('fetch', '--all'));
        self::info('cleanup remote');
        self::cmd(Git_Daily::$git, array('remote', 'prune', $remote));

        // if has local branch, try to checkout
        $release_branch = false;
        $release_branches = Git_Daily_GitUtil::releaseBranches($this->branch_prefix);
        if (!empty($release_branches)) {
            if (count($release_branches) == 1) {
                $release_branch = array_shift($release_branches);
            } else {
                throw new Git_Daily_Exception('there are a number of local release branches, please delete local release branch manually');
            }
        }

        // remote branch still exists ?
        $remote_closed = false;
        $remote_release_branch = null;
        $remote_release_branches = self::cmd(Git_Daily::$git, array('branch', '-a'),
            array('grep', array("remotes/$remote/{$this->branch_prefix}"),
                array(
                    'sed', array('s/^[^a-zA-Z0-9]*//g'),
                )
            )
        );
        if (!empty($remote_release_branches) && count($remote_release_branches) == 1) {
            $remote_release_branch = array_shift($remote_release_branches);
            $remote_release_branch = str_replace("remotes/$remote/", '', $remote_release_branch);
        } else {
            if (empty($remote_release_branches)) {
                // to clean up
                $remote_closed = true;
            }
            else {
                throw new Git_Daily_Exception('there are a number of remote release branches');
            }
        }

        // release branch already checkouted, try push, pull
        if ($release_branch) {
            // checkout
            // remote release branch opened, but local has different (old ?) release branch, then clean up
            if ($remote_release_branch != $release_branch) {
                $remote_closed = true;
            }

            // if remote closed, local still have release branch, then cleanup
            if ($remote_closed) {
                self::info('release closed! so cleanup local release branch');
                self::info("checkout $develop_branch");
                Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('checkout', $develop_branch));
                list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('daily', 'pull'));
                self::outLn($res);
                self::info("delete $release_branch");
                list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('branch', '-d', $release_branch));
                self::outLn($res);
                if ($retval != 0) {
                    self::warn('branch delete failed');
                    self::outLn("\n     git branch delete failed, please manually\n");
                }

                if ($remote_release_branch != $release_branch) {
                    self::outLn(PHP_EOL, 'Closed old release branch', '  Please retry "release sync"', PHP_EOL);
                }
                return "sync to release close";
            }

            if ($current_branch != $release_branch) {
                self::info("checkout $release_branch");
                self::cmd(Git_Daily::$git, array('checkout', $release_branch));
                $current_branch = Git_Daily_GitUtil::currentBranch();
            }

            // first, pull
            self::info("git pull");
            list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('daily', 'pull'));
            self::outLn($res);
            if ($retval != 0) {
                throw new Git_Daily_Exception('abort');
            }

            // push
            self::info("git push");
            list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('daily', 'push'));
            self::outLn($res);
            if ($retval != 0) {
                self::warn('failed to push to remote');
                throw new Git_Daily_Exception('abort');
            }
        }
        else {
            // first time checkout release branch
            if ($remote_closed) {
                // do nothing
                return 'sync completed (nothing to do)';
            }

            self::info("checkout and tracking $remote_release_branch");
            list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('checkout', $remote_release_branch));
            self::outLn($res);
            if ($retval != 0) {
                self::warn('failed to checkout');
                throw new Git_Daily_Exception('abort');
            }
            return 'start to tracking release branch';
        }
    }

    /**
     *  Close release process
     *
     *  @throws Git_Daily_Exception
     *  @return string
     */
    private function _doClose()
    {
        $release_branches = Git_Daily_GitUtil::releaseBranches($this->branch_prefix);
        if (empty($release_branches)) {
            throw new Git_Daily_Exception("{$this->branch_prefix} branch not found. abort.");
        }
        $release_branch = array_shift($release_branches);

        //
        // Start merging diffs
        //
        foreach ($this->getMergeBranches() as $branch_name) {
            $merge_branches = self::cmd(Git_Daily::$git, array('branch'),
                array(
                    'grep', array($branch_name),
                    array('sed', array('s/^[^a-zA-Z0-9]*//g')
                    )
                )
            );
            $merge_branch = array_shift($merge_branches);
            $remote = $this->config['remote'];
            
            if (empty($merge_branch)) {
                continue;
            }

            if (!empty($remote)) {
                self::info('first, fetch remotes');
                self::cmd(Git_Daily::$git, array('fetch', '--all'));
                self::info('diff check');
                
                $diff_branch_str1 = "{$release_branch}..{$remote}/{$release_branch}";
                $diff_branch_str2 = "{$remote}/{$release_branch}..{$release_branch}";
                $res1 = self::cmd(Git_Daily::$git, array('diff', $diff_branch_str1));
                $res2 = self::cmd(Git_Daily::$git, array('diff', $diff_branch_str2));
                if (!empty($res1) || !empty($res2)) {
                    self::warn("There are some diff between local and $remote, run release sync first.");
                    throw new Git_Daily_Exception('abort');
                }
            }

            self::info("checkout {$merge_branch} and merge $release_branch to {$merge_branch}");
            self::cmd(Git_Daily::$git, array('checkout', $merge_branch));
            // pull
            if (!empty($remote)) {
                list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('daily', 'pull'));
                self::outLn($res);
                if ($retval != 0) {
                    self::warn("{$merge_branch} pull failed");
                    throw new Git_Daily_Exception('abort');
                }
            }
           
            // merged check
            $res = Git_Daily_GitUtil::mergedBranches();
            if (!in_array($release_branch, $res)) {
                // merge to master
                list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('merge', '--no-ff', $release_branch));
                self::outLn($res);
                if ($retval != 0) {
                    self::warn('merge failed');
                    throw new Git_Daily_Exception('abort');
                }
            }

            // push the merged branch to remote
            self::info("push $merge_branch to $remote");
            self::cmd(Git_Daily::$git, array('checkout', $merge_branch));
            list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('push', $remote, $merge_branch));
            self::outLn($res);
            if ($retval != 0) {
                self::warn('push failed');
                throw new Git_Daily_Exception('abort');
            }

        }

        // delere release branch
        self::info("delete branch: $release_branch");
        list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('branch', '-d', $release_branch));
        self::outLn($res);
        if ($retval != 0) {
            self::warn("failed to delete local $release_branch");
            throw new Git_Daily_Exception('abort');
        }
        if (!empty($remote)) {
            // delete remote release branch
            list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('push', $remote, ':' . $release_branch));
            self::outLn($res);
            if ($retval != 0) {
                self::warn("failed to delete {$remote}'s $release_branch");
                throw new Git_Daily_Exception('abort');
            }
        }


        // return to develop
        self::cmd(Git_Daily::$git, array('checkout', $this->config[$this->base_branch]));
        return "{$this->branch_prefix} closed";
    }

    private function _doList()
    {
        $release_branch = Git_Daily_GitUtil::releaseBranches($this->branch_prefix);
        if (empty($release_branch)) {
            throw new Git_Daily_Exception("{$this->branch_prefix} branch not found. abort.");
        }
        $current_branch = Git_Daily_GitUtil::currentBranch();
        $master_branch = $this->config['master'];

        //
        // check if remote has release process
        //
        if (isset($this->config['remote'])) {
            self::info('first, fetch remotes');
            self::cmd(Git_Daily::$git, array('fetch', '--all'));
        }
        
        //
        // Get revision list using git rev-list.
        //
        $revision_list = array();
        $revision_id_list = self::cmd(Git_Daily::$git, array('rev-list', "$master_branch..$current_branch"));
        foreach ($revision_id_list as $rev_id) {
            //
            // Get the detail of a revision using git show.
            //
            $logs = self::cmd(Git_Daily::$git, array('show', $rev_id));

            $revision = array();
            $revision['id'] = $rev_id;
            $revision['files'] = array();
            $revision['files']['added'] = array();
            $revision['files']['modified'] = array();

            //
            // Parse output of git show.
            //
            $merge = false;
            foreach ($logs as $line) {
                if (preg_match("/^Merge: /", $line)) {
                    $merge = true;
                } elseif (preg_match("/^Author: .+\<([^@]+)@([^>]+)>/", $line, $matches)) {
                    $revision['author'] = $matches[1];
                } elseif (preg_match("/^diff --git a\/([^ ]+) /", $line, $matches)) {
                    $file = $matches[1];
                } elseif (preg_match("/^new file mode/", $line)) {
                    $revision['files']['added'][] = $file;
                } elseif (preg_match("/^index/", $line)) {
                    $revision['files']['modified'][] = $file;
                }
            }

            //
            // Skip a merge log.
            //
            if (!$merge) {
                $revision_list[] = $revision;
            }
        }

        //
        // Merge file list
        //
        $modlist = array();
        $addlist = array();
        foreach ($revision_list as $revision) {
            $modlist = array_merge($modlist, $revision['files']['modified']);
            $addlist = array_merge($addlist, $revision['files']['added']);
        }
        sort($modlist);
        sort($addlist);
        $modlist = array_unique($modlist);
        $addlist = array_unique($addlist);

        //
        // Print commit list
        //
        if (count($revision_list) > 0) {
            self::outLn("Commit list:");
            foreach ($revision_list as $revision) {
                self::outLn("\t" . $revision['id'] . " = " . $revision['author']);
            }
            self::outLn("");
        }

        //
        // Print added files
        //
        if (count($addlist) > 0) {
            self::outLn("Added files:");
            foreach ($addlist as $file) {
                self::outLn("\t$file");
            }
            self::outLn("");
        }

        //
        // Print modified files
        //
        if (count($modlist) > 0) {
            self::outLn("Modified files:");
            foreach ($modlist as $file) {
                self::outLn("\t$file");
            }
            self::outLn("");
        }

        //
        // Print URL list by author when gitdaily.logurl is define.
        //
        if (isset($this->config['logurl']) && count($revision_list) > 0) {
            // Merge commit list by author
            $author_list = array();
            foreach ($revision_list as $revision) {
                if (!isset($author_list[$revision['author']])) {
                    $author_list[$revision['author']] = array();
                }

                // Add commit list
                $author_list[$revision['author']][] = $revision['id'];
            }

            self::outLn("Author list:");
            foreach ($author_list as $author => $id_list) {
                self::outLn("\t@$author:");
                foreach ($id_list as $id) {
                    $url = sprintf("\t{$this->config['logurl']}", $id);
                    self::outLn($url);
                }
                self::outLn("");
            }
        }
    }

    public static function usage()
    {
        fwrite(STDERR, <<<E
Usage: git daily release open        : Open daily-release process
   or: git daily release list        : Show release list
   or: git daily release sync        : Sync current opened release process
   or: git daily release close       : Close to daily-release process

E
        );
    }
}


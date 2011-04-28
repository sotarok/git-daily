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
    );

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
     *  Open and start release process
     *
     *  @throws Git_Daily_Exception
     *  @return string
     */
    private function _doOpen()
    {
        // get current branch
        $current_branch = Git_Daily_GitUtil::currentBranch();
        if ($current_branch != $this->config['develop']) {
            throw new Git_Daily_Exception(
                "currently not on {$this->config['develop']} but on $current_branch"
            );
        }

        // check if current release process opened
        $release_branches = self::cmd(Git_Daily::$git, array('branch'), array('grep', array("release"), array('sed', array('s/*\?\s\+//g'))));
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
                array('grep', array("remotes/$remote/release"))
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

        $new_release_branch = 'release/' . date('Ymd-Hi');
        // confirmation
        if (!Git_Daily_CommandUtil::yesNo("Confirm: create branch $new_release_branch from $current_branch ?",
            Git_Daily_CommandUtil::YESNO_NO)) {
            throw new Git_Daily_Exception('abort');
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
        $develop_branch = $this->config['develop'];
        self::info('first, fetch remotes');
        self::cmd(Git_Daily::$git, array('fetch', '--all'));
        self::info('cleanup remote');
        self::cmd(Git_Daily::$git, array('remote', 'prune', $remote));

        // if has local branch, try to checkout
        $release_branch = false;
        $release_branches = self::cmd(Git_Daily::$git, array('branch'), array('grep', array("release"),
            array(
                'sed', array('s/*\?\s\+//g'),
            )
        ));
        if (!empty($release_branches)) {
            if (count($release_branches) == 1) {
                $release_branch = array_shift($release_branches);
            } else {
                throw new Git_Daily_Exception('there are a number of local release branches, please delete local release branch manually');
            }
        }

        // remote branch still exists ?
        $remote_closed = false;
        $remote_release_branches = self::cmd(Git_Daily::$git, array('branch', '-a'),
            array('grep', array("remotes/$remote/release"),
                array(
                    'sed', array('s/*\?\s\+//g'),
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
        $release_branch = self::cmd(Git_Daily::$git, array('branch'), array('grep', array("release"),
            array(
                'sed', array('s/*\?\s\+//g'),
            )
        ));
        if (empty($release_branch)) {
            throw new Git_Daily_Exception('release branch not found. abort.');
        }
        $release_branch = array_shift($release_branch);

        $master_branch = $this->config['master'];
        $develop_branch = $this->config['develop'];
        $remote = $this->config['remote'];

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

        self::info("checkout $master_branch and merge $release_branch to $master_branch");
        self::cmd(Git_Daily::$git, array('checkout', $master_branch));
        // pull
        if (!empty($remote)) {
            list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('daily', 'pull'));
            self::outLn($res);
            if ($retval != 0) {
                self::warn("$master_branch pull failed");
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

        self::info("checkout $develop_branch and merge $release_branch to $develop_branch");
        self::cmd(Git_Daily::$git, array('checkout', $develop_branch));
        // pull
        if (!empty($remote)) {
            list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('daily', 'pull'));
            if ($retval != 0) {
                self::outLn($res);
                self::warn("$develop_branch pull failed");
                throw new Git_Daily_Exception('abort');
            }
        }

        $res = Git_Daily_GitUtil::mergedBranches();
        if (!in_array($release_branch, $res)) {
            // merge to develop
            list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('merge', '--no-ff', $release_branch));
            self::outLn($res);
            if ($retval != 0) {
                self::warn('merge failed');
                throw new Git_Daily_Exception('abort');
            }
        }

        if (!empty($remote)) {
            // push
            self::info("push $master_branch to $remote");
            self::cmd(Git_Daily::$git, array('checkout', $master_branch));
            list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('push', $remote, $master_branch));
            self::outLn($res);
            if ($retval != 0) {
                self::warn('push failed');
                throw new Git_Daily_Exception('abort');
            }

            // push develop
            self::info("push $develop_branch to $remote");
            self::cmd(Git_Daily::$git, array('checkout', $develop_branch));
            list($res, $retval) = Git_Daily_CommandUtil::cmd(Git_Daily::$git, array('push', $remote, $develop_branch));
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
        self::cmd(Git_Daily::$git, array('checkout', $master_branch));
        return 'release closed';
    }

    public static function usage()
    {
        fwrite(STDERR, <<<E
Usage: git daily release open        : Open daily-release process
   or: git daily release sync        : Sync current opened release process
   or: git daily release close       : Close to daily-release process

E
        );
    }
}


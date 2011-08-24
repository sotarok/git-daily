<?php
/**
 *  @package    Git
 *  @author     Sotaro Karasawa <sotaro.k@gmail.com>
 */

require 'Git/Daily/Command/Release.php';

/**
 *  Git_Daily_Command_Release
 *
 *  @package    Git
 */
class Git_Daily_Command_Hotfix
    extends Git_Daily_Command_Release
{
    const DESCRIPTION = 'Operation hotfix release';

    protected $load_config = true;

    protected $option = array(
    );

    protected $base_branch = 'master';

    protected $merge_to = array('master', 'develop', 'release');

    protected $branch_prefix = 'hotfix';

    protected function getMergeBranches()
    {
        // Check if a release branch is open
        $release_branches = Git_Daily_GitUtil::releaseBranches('release');
        if (!empty($release_branches)) {
            //
            // Merge diffs into master and release branches.
            //
            return array('master', 'release');
        } else {
            //
            // Merge diffs into master and develop branches.
            //
            return array('master', 'develop');
        }
    }

    public static function usage()
    {
        fwrite(STDERR, <<<E
Usage: git daily hotfix open        : Open hotfix-release process
   or: git daily hotfix list        : Show hotfix list
   or: git daily hotfix sync        : Sync current opened hotfix process
   or: git daily hotfix close       : Close to hotfix-release process

E
        );
    }
}

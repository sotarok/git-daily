<?php
/**
 *
 */

require_once GIT_DAILY_SRC_DIR . '/Git/Daily.php';
require_once GIT_DAILY_SRC_DIR . '/Git/Daily/CommandAbstract.php';
require_once GIT_DAILY_SRC_DIR . '/Git/Daily/Command/Help.php';

/**
 * @backupGlobals
 */
class Git_Daily_Command_HelpTest
    extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    /**
     * @outputBuffering enabled
     */
    public function testRunCommand()
    {
        $cmd = new Git_Daily_Command_Help(array());
        $cmd->runCommand();
    }

    public function tearDown()
    {
    }
}

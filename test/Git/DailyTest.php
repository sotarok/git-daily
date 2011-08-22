<?php
/**
 *
 */

require_once GIT_DAILY_SRC_DIR . '/Git/Daily.php';

class Git_DailyTest
    extends PHPUnit_Framework_TestCase
{
    public function testGitDaily()
    {
        $this->assertEquals('git-daily', Git_Daily::COMMAND);
    }
}

<?php
/**
 *
 */

class Git_Daily_Command_VersionTest
    extends PHPUnit_Framework_TestCase
{
    protected $cmd;
    public function setUp()
    {
        $this->cmd = $this->create();
    }

    public function testGetOptions()
    {
        $this->assertEmpty($this->cmd->getOptions());
    }

    public function testGetDescription()
    {
        $this->assertEquals('Show git-daily version', $this->cmd->getDescription());
    }

    public function testIsAllowedOutOfRepo()
    {
        $this->assertTrue($this->cmd->isAllowedOutOfRepo());
    }

    public function testVersionCommand()
    {
        $str = call_user_func_array('sprintf', $this->cmd->execute());
        $this->assertRegExp('/version \d+\.\d+\.\d+/', $str);
    }

    private function create()
    {
        $cmd_util = new Git_Daily_CommandUtil();
        return new Git_Daily_Command_Version(
            'version',
            new Git_Daily($cmd_util),
            array(),
            new Git_Daily_ConsoleOutput(),
            $cmd_util
        );
    }
}

<?php
/**
 *
 */

require_once GIT_DAILY_SRC_DIR . '/Git/Daily/GitUtil.php';

class Git_Daily_GitUtilTest
    extends Git_Daily_GitTestCase
{

    private $orig_dir;
    private $dir;

    private $git;

    public function setUp()
    {
        parent::setUp();
        $this->orig_dir = getcwd();

        Git_Daily::$git = trim(`which git`);
        $this->git = new Git_Daily_GitUtil(new Git_Daily_CommandUtil());
    }

    public function tearDown()
    {
        parent::tearDown();

        Git_Daily::$git = null;
        chdir($this->orig_dir);
    }

    public function testIsClean()
    {
        chdir($this->getTmpRepositoryDir());

        $tmpfile = $this->getTmpRepositoryDir() . '/README';
        $this->assertTrue($this->git->isClean());

        file_put_contents($tmpfile, 'hoge');
        $this->assertFalse($this->git->isClean());

        `git checkout $tmpfile`;
        $this->assertTrue($this->git->isClean());
    }

    public function testBranches()
    {
        chdir($this->getTmpRepositoryDir());

        $this->assertEquals(array('master'), $this->git->branches());

        `git branch hoge`;
        $this->assertContains('master', $this->git->branches());
        $this->assertContains('hoge', $this->git->branches());

        `git branch -d hoge`;
        $this->assertEquals(array('master'), $this->git->branches());
    }

    public function testMergedBranches()
    {
        chdir($this->getTmpRepositoryDir());

        $this->assertEquals(array('master'), $this->git->mergedBranches());

        `git checkout -b fuga 2>&1 && touch FUGA && git add FUGA && git commit -m "add FUGA" && git checkout master 2>&1 && git merge fuga`;
        $this->assertContains('master', $this->git->branches());
        $this->assertContains('fuga', $this->git->branches());

        `git branch -d fuga`;
    }

    public function testReleaseBranches()
    {
        chdir($this->getTmpRepositoryDir());
        $this->assertEquals(array(), $this->git->releaseBranches('release'));
    }

    public function testHasBranch()
    {
        chdir($this->getTmpRepositoryDir());
        $this->assertTrue($this->git->hasBranch('master'));
        $this->assertFalse($this->git->hasBranch('hoge'));
    }

    public function testCurrentBranch()
    {
        chdir($this->getTmpRepositoryDir());
        $this->assertEquals('master', $this->git->currentBranch());
    }

}

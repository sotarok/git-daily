<?php
/**
 *
 */

require_once GIT_DAILY_SRC_DIR . '/Git/Daily/GitUtil.php';

class Git_Daily_GitUtilTest
    extends PHPUnit_Framework_TestCase
{
    const TMP_REPO = 'tmp_gitutil_test';

    private $orig_dir;
    private $dir;

    public function setUp()
    {
        $this->orig_dir = getcwd();
        Git_Daily::$git = trim(`which git`);

        $tmpdir = GIT_DAILY_TMP_DIR . DIRECTORY_SEPARATOR . self::TMP_REPO;
        if (is_dir($tmpdir)) {
            `rm -rf $tmpdir`;
        }
        `mkdir $tmpdir && cd $tmpdir && git init && touch README && git add README && git commit -m "initial"`;

        $this->dir = $tmpdir;
    }

    public function testIsClean()
    {
        chdir($this->dir);

        $tmpfile = $this->dir . '/README';
        $this->assertTrue(Git_Daily_GitUtil::isClean());

        file_put_contents($tmpfile, 'hoge');
        $this->assertFalse(Git_Daily_GitUtil::isClean());

        `git checkout $tmpfile`;
        $this->assertTrue(Git_Daily_GitUtil::isClean());
    }

    public function testBranches()
    {
        chdir($this->dir);

        $this->assertEquals(array('master'), Git_Daily_GitUtil::branches());

        `git branch hoge`;
        $this->assertContains('master', Git_Daily_GitUtil::branches());
        $this->assertContains('hoge', Git_Daily_GitUtil::branches());

        `git branch -d hoge`;
        $this->assertEquals(array('master'), Git_Daily_GitUtil::branches());
    }

    public function testMergedBranches()
    {
        chdir($this->dir);

        $this->assertEquals(array('master'), Git_Daily_GitUtil::mergedBranches());

        `git checkout -b fuga 2>&1 && touch FUGA && git add FUGA && git commit -m "add FUGA" && git checkout master 2>&1 && git merge fuga`;
        $this->assertContains('master', Git_Daily_GitUtil::branches());
        $this->assertContains('fuga', Git_Daily_GitUtil::branches());

        `git branch -d fuga`;
    }

    public function testReleaseBranches()
    {
        chdir($this->dir);
        $this->assertEquals(array(), Git_Daily_GitUtil::releaseBranches());
    }

    public function testHasBranch()
    {
        chdir($this->dir);
        $this->assertTrue(Git_Daily_GitUtil::hasBranch('master'));
        $this->assertFalse(Git_Daily_GitUtil::hasBranch('hoge'));
    }

    public function testCurrentBranch()
    {
        chdir($this->dir);
        $this->assertEquals('master', Git_Daily_GitUtil::currentBranch());
    }

    public function tearDown()
    {
        `rm -rf {$this->dir}`;
        Git_Daily::$git = null;
        chdir($this->orig_dir);
    }
}

<?php
/**
 *
 */

class Git_Daily_GitTestCase
    extends PHPUnit_Framework_TestCase
{
    const TMP_REPO = 'tmp_gitutil_test';

    public function setUp()
    {
        if (!defined('GIT_DAILY_TMP_DIR')) {
            throw new \RuntimeException('Constant GIT_DAILY_TMP_DIR is not defined');
        }

        $this->createTmpRepositoy(self::TMP_REPO);
    }

    public function tearDown()
    {
        $this->removeTmpRepositoy(self::TMP_REPO);
    }

    public function getTmpRepositoryDir($dir = null)
    {
        return GIT_DAILY_TMP_DIR . DIRECTORY_SEPARATOR . (is_null($dir) ? self::TMP_REPO : $dir);
    }

    public function removeTmpRepositoy($dir)
    {
        $tmpdir = $this->getTmpRepositoryDir($dir);
        exec("rm -rf $tmpdir", $ret, $retval);
        if ($retval != 0) {
            throw new \RuntimeException(sprintf('Failed to remove tmp repository dir. output="%s"', implode(PHP_EOL, $ret)));
        }
    }

    public function createTmpRepositoy($dir)
    {
        $tmpdir = $this->getTmpRepositoryDir($dir);
        if (is_dir($tmpdir)) {
            $this->removeTmpRepositoy($dir);
        }

        exec("mkdir $tmpdir && cd $tmpdir && git init && touch README && git add README && git commit -m 'initial'", $ret, $retval);

        if ($retval != 0) {
            throw new \RuntimeException('Failed to create tmp repository');
        }

    }
}

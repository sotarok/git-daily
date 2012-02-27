<?php
/**
 *
 */

require_once GIT_DAILY_SRC_DIR . '/Git/Daily/ClassLoader.php';

class Git_Daily_ClassLoaderTest
    extends PHPUnit_Framework_TestCase
{
    /**
     */
    public function testGetSrcPath()
    {
        $this->assertEquals(realpath(GIT_DAILY_SRC_DIR), realpath(Git_Daily_ClassLoader::getSrcPath()));
    }

    /**
     */
    public function testClassLoader()
    {
        $prefix = 'Git';
        $include_path = '/usr/share/php5';
        $cl = new Git_Daily_ClassLoader($prefix, $include_path);
        $this->assertEquals($include_path, $cl->getIncludePath());

        $include_path = '/usr/share/php';
        $cl->setIncludePath($include_path);
        $this->assertEquals($include_path, $cl->getIncludePath());

        $fn = 'Git.php';
        $this->assertEquals($include_path . DIRECTORY_SEPARATOR . $fn, $cl->getFilePath('Git'));
    }

    /**
     */
    public function testClassLoaderRegister()
    {
        $prefix = 'Git';
        $include_path = '/usr/share/php5';
        $cl = new Git_Daily_ClassLoader($prefix, $include_path);

        $this->assertNotContains(array($cl, 'loadClass'), spl_autoload_functions());
        $cl->register();
        $this->assertContains(array($cl, 'loadClass'), spl_autoload_functions());
        $cl->unregister();
        $this->assertNotContains(array($cl, 'loadClass'), spl_autoload_functions());
    }

    /**
     * @dataProvider classNameProvider
     */
    public function testFilePath($prefix, $include_path, $class_name, $expected_path)
    {
        $cl = new Git_Daily_ClassLoader($prefix, $include_path);
        $this->assertEquals($expected_path, $cl->getFilePath($class_name));
    }

    public function classNameProvider()
    {
        return array(
            array(
                'Git',
                '/usr/share/php5',
                'Git_Daily',
                '/usr/share/php5/Git/Daily.php',
            ),
            array(
                'Git',
                '/usr/share/php5',
                'Git_Daily_OptionParser',
                '/usr/share/php5/Git/Daily/OptionParser.php',
            ),
            array(
                'Git_Daily',
                '/usr/share/php5',
                'Git_Daily_OptionParser',
                '/usr/share/php5/Git/Daily/OptionParser.php',
            ),
        );
    }
}

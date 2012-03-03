<?php
/**
 *
 */


class Git_Daily_ConfigTest
    extends Git_Daily_GitTestCase
{
    protected $output;

    public function setUp()
    {
        $this->orig_dir = getcwd();
        Git_Daily::$git = trim(`which git`);
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();

        Git_Daily::$git = null;
        chdir($this->orig_dir);
    }

    public function testConstruct()
    {
        chdir(GIT_DAILY_TEST_DIR);
        $config = $this->createConfigInstance();
        $this->assertInstanceOf('Git_Daily_Config', $config);
    }

    /**
     * @dataProvider provideParseLines
     */
    public function testParseConfigLines($expected, $config_lines)
    {
        $config = $this->createConfigInstance();
        $ref = new ReflectionMethod('Git_Daily_Config', 'parseConfigLines');
        $ref->setAccessible(true);
        $this->assertEquals($expected, $ref->invokeArgs($config, array($config_lines)));
    }

    public function provideParseLines()
    {
        return array(
            array(
                array('test' => 1),
                array('gitdaily.test 1'),
            ),
            array(
                array('test' => 1, 'test2' => false, 'test3' => true),
                array(
                    'gitdaily.test 1',
                    'gitdaily.test2 false',
                    'gitdaily.test3 true',
                    '',
                ),
            ),
        );
    }

    public function testGet()
    {
        chdir($this->getTmpRepositoryDir());
        `git config gitdaily.test 1`;
        `git config gitdaily.test2 true`;

        $config = $this->createConfigInstance();
        $this->assertEquals(1, $config->get('test'));
        $this->assertEquals(true, $config->get('test2'));
        $this->assertEquals(null, $config->get('test3'), 'non exists key');
    }

    public function testGetGlobal()
    {
        chdir($this->getTmpRepositoryDir());
        `git config --global gitdaily.testglobal 2`;

        $config = $this->createConfigInstance();
        $this->assertEquals(2, $config->get('testglobal', true));
        $this->assertEquals(null, $config->get('testglobal2', true), 'non exists key');

        `git config --global --unset gitdaily.testglobal`;
    }

    public function testGetAll()
    {
        chdir($this->getTmpRepositoryDir());
        `git config gitdaily.testmiyazaki aoi`;
        `git config --global gitdaily.testaoi yu`;

        $config = $this->createConfigInstance();
        $expected = array('testmiyazaki' => 'aoi', 'testaoi' => 'yu');
        $this->assertEquals(
            $expected,
            array_intersect($expected, $config->getAll())
        );

        $expected = array('testaoi' => 'yu');
        $this->assertEquals(
            $expected,
            array_intersect($expected, $config->getAll(true))
        );

        `git config --unset gitdaily.testmiyazaki`;
        `git config --global --unset gitdaily.testaoi`;
    }

    public function testSet()
    {
        chdir($this->getTmpRepositoryDir());

        $config = $this->createConfigInstance();
        $this->assertTrue($config->set('test', 1));

        $res = str_replace('gitdaily.test=', '', trim(`git config gitdaily.test`));
        $this->assertEquals(1, $res);

        `git config --unset gitdaily.test`;
    }

    /**
     * @expectedException Git_Daily_Exception
     */
    public function testSetFailed()
    {
        chdir($this->getTmpRepositoryDir());

        $config = $this->createConfigInstance();
        $config->set('test_hoge', 1);
    }

    /**
     * @expectedException Git_Daily_Exception
     */
    public function testSetFailed2()
    {
        // not on git directory
        chdir('/tmp');
        $config = $this->createConfigInstance();
        $config->set('test', 1);
    }

    /**
     * @expectedException Git_Daily_Exception
     */
    public function testSetFailed3()
    {
        // invalid git config key
        chdir($this->getTmpRepositoryDir());

        $config = $this->createConfigInstance();
        $config->set('test_config', 1);
    }

    public function testSetGlobal()
    {
        chdir('/tmp');

        $config = $this->createConfigInstance();
        $this->assertTrue($config->set('test', 1, true));

        $res = str_replace('gitdaily.test=', '', trim(`git config --global gitdaily.test`));
        $this->assertEquals(1, $res);

        `git config --global --unset gitdaily.test`;
    }

    /**
     * @expectedException Git_Daily_Exception
     */
    public function testSetGlobalFailed()
    {
        // invalid git config key
        chdir('/tmp');
        $config = $this->createConfigInstance();
        $config->set('test_config', 1, true);
    }

    private function createConfigInstance()
    {
        $context = new Git_Daily(new Git_Daily_CommandUtil());
        return new Git_Daily_Config($context);
    }
}


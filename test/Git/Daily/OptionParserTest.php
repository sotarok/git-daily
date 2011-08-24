<?php
/**
 *
 */

require_once GIT_DAILY_SRC_DIR . '/Git/Daily/OptionParser.php';

class Git_Daily_OptionParserTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider optVarProvider
     */
    public function testOptVar($arg_str, $option, $key, $expected, $args)
    {
        $argv = explode(' ', $arg_str);
        //var_dump($argv);
        $opt = new Git_Daily_OptionParser($argv, $option);
        $this->assertEquals($expected, $opt->getOptVar($key));
        $this->assertEquals($args, $opt->getArgs());
    }

    public function optVarProvider()
    {
        //'rebase' => array(null, 'rebase', Git_Daily_OptionParser::ACT_STORE_TRUE),
        return array(
            array( // longopt store true
                "--rebase",
                array('rebase' => array(null, 'rebase', Git_Daily_OptionParser::ACT_STORE_TRUE)),
                'rebase', true, array()
            ),
            array( // longopt store false
                "--no-commit",
                array('no_commit' => array(null, 'no-commit', Git_Daily_OptionParser::ACT_STORE_FALSE)),
                'no_commit', false, array()
            ),
            array( // longopt store var
                "--branch test",
                array('branch' => array(null, 'branch', Git_Daily_OptionParser::ACT_STORE_VAR)),
                'branch', 'test', array()
            ),
            array( // longopt store var with args
                "--branch test fuga piyo",
                array('branch' => array(null, 'branch', Git_Daily_OptionParser::ACT_STORE_VAR)),
                'branch', 'test', array('fuga', 'piyo')
            ),
            array( // short opt
                "-b test",
                array('branch' => array('b', 'branch', Git_Daily_OptionParser::ACT_STORE_VAR)),
                'branch', 'test', array()
            ),
            array( // short opt with long opt
                "-b hoge --test",
                array(
                    'base' => array('b', 'base', Git_Daily_OptionParser::ACT_STORE_VAR),
                    'test' => array(null,'test', Git_Daily_OptionParser::ACT_STORE_TRUE)
                ),
                'test', true, array()
            ),
            array( // short opt with long opt
                "-b hoge --test",
                array(
                    'base' => array('b', 'base', Git_Daily_OptionParser::ACT_STORE_VAR),
                    'test' => array(null,'test', Git_Daily_OptionParser::ACT_STORE_TRUE)
                ),
                'base', 'hoge', array()
            ),
            array( // short opt with long opt, args
                "-b hoge --test arg1",
                array(
                    'base' => array('b', 'base', Git_Daily_OptionParser::ACT_STORE_VAR),
                    'test' => array(null,'test', Git_Daily_OptionParser::ACT_STORE_TRUE)
                ),
                'base', 'hoge', array('arg1')
            ),
            array( // complex
                "piyo -b hoge fuga --test arg1",
                array(
                    'base' => array('b', 'base', Git_Daily_OptionParser::ACT_STORE_VAR),
                    'test' => array(null,'test', Git_Daily_OptionParser::ACT_STORE_TRUE)
                ),
                'base', 'hoge', array('piyo', 'fuga', 'arg1')
            ),
            array( // complex
                "-b hoge --test=arg1 aoi",
                array(
                    'base' => array('b', 'base', Git_Daily_OptionParser::ACT_STORE_VAR),
                    'test' => array(null,'test') // default is ACT_STORE_VAR
                ),
                'test', 'arg1', array('aoi')
            ),
            array( // option is not a type of OptionParser
                "-b hoge",
                array(
                    'base' => array('b', 'base', 999),
                ),
                'base', '', array('-b', 'hoge')
            ),
        );
    }

    /**
     * @expectedException Git_Daily_Exception
     * @expectedExceptionMessage invalid option
     */
    public function testInvalidOptions()
    {
        $argv = array('-b', 'hoge');
        $option = array(
            'test' => array(null,'test', Git_Daily_OptionParser::ACT_STORE_VAR)
        );

        $opt = new Git_Daily_OptionParser($argv, $option);
    }

    /**
     * @expectedException Git_Daily_Exception
     * @expectedExceptionMessage invalid option
     */
    public function testInvalidLongOptions()
    {
        $argv = array('--base', 'hoge');
        $option = array(
            'test' => array(null,'test', Git_Daily_OptionParser::ACT_STORE_VAR)
        );

        $opt = new Git_Daily_OptionParser($argv, $option);
    }

    /**
     * @expectedException Git_Daily_Exception
     * @expectedExceptionMessage invalid option
     */
    public function testInvalidOptionFormats()
    {
        $argv = array('-', 'hoge');
        $option = array(
            'test' => array(null,'test', Git_Daily_OptionParser::ACT_STORE_VAR)
        );

        $opt = new Git_Daily_OptionParser($argv, $option);
    }

    /**
     * @expectedException Git_Daily_Exception
     * @expectedExceptionMessage argument -b required a value
     */
    public function testInvalidArgs()
    {
        $argv = array('-b',);
        $option = array(
            'base' => array('b', 'base', Git_Daily_OptionParser::ACT_STORE_VAR)
        );

        $opt = new Git_Daily_OptionParser($argv, $option);
    }
}

<?php
/**
 *
 */


class Git_Daily_ConsoleOutputTest
    extends PHPUnit_Framework_TestCase
{
    protected $output;

    public function setUp()
    {
        $this->output = new Git_Daily_ConsoleOutput();
    }

    /**
     */
    public function testInfoWarn()
    {
        $expected = "[41;37mError[0m" . PHP_EOL
            . "[36mInfo[0m" . PHP_EOL
            ;
        $this->expectOutputString($expected);
        $this->output->warn("Error");
        $this->output->info("Info");

    }

    /**
     * @dataProvider outProvider
     */
    public function testOut($args, $expected)
    {
        $this->expectOutputString($expected);
        call_user_func_array(array($this->output, 'out'), $args);
    }

    public function outProvider()
    {
        return array(
            array(
                array('Aoi Miyazaki'),
                "Aoi Miyazaki",
            ),
            array(
                array('Aoi %s', 'Miyazaki'),
                "Aoi Miyazaki",
            ),
        );
    }

    /**
     * @dataProvider outLnProvider
     */
    public function testOutLn($args, $expected)
    {
        $output = new Git_Daily_ConsoleOutput();
        $this->expectOutputString($expected);
        call_user_func_array(array($this->output, 'outLn'), $args);
    }

    public function outLnProvider()
    {
        return array(
            array(
                array('Aoi Miyazaki'),
                "Aoi Miyazaki\n",
            ),
        );
    }


    /**
     * @dataProvider formatProvider
     */
    public function testFormatString($args, $expected)
    {
        $output = new Git_Daily_ConsoleOutput();
        if (!is_array($args)) {
            $this->assertEquals($expected, $this->output->formatString());
        } else {
            $this->assertEquals($expected, call_user_func_array(array($this->output, 'formatString'), $args));
        }
    }

    public function formatProvider()
    {
        return array(
            array(
                null,
                null,
            ),
            array(
                array("Aoi Miyazaki"),
                "Aoi Miyazaki",
            ),
            array(
                array("Aoi %s", 'Yu'),
                "Aoi Yu",
            ),
            array(
                array("Aoi No.%d", 1),
                "Aoi No.1",
            ),
            array(
                array("Aoi %s"),
                "Aoi %s",
            ),
            array(
                array("Aoi %s", 'Sora', 'Yu'),
                "Aoi %s\nSora\nYu",
            ),
            array(
                array(array("Aoi Miyazaki")),
                "Aoi Miyazaki",
            ),
        );
    }
}

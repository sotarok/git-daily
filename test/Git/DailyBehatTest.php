<?php
/**
 *
 *  @forked https://gist.github.com/1298503
 */


use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Behat\Behat\Console\BehatApplication;

class Git_DailyBehatTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @group behat
     */
    public function testBehat()
    {
        $composer_autoloader = __DIR__ . '/../../vendor/.composer/autoload.php';
        if (!file_exists($composer_autoloader)) {
            $this->markTestSkipped("behat.phar is not installed.");
        }

        require_once $composer_autoloader;

        try {
            $input = new ArrayInput(array(
                '--lang' => 'en',
                '--format' => 'progress',
            ));
            $output = new ConsoleOutput();
            $app = new BehatApplication('unknown');
            $app->setAutoExit(false);
            $result = $app->run($input, $output);
            $this->assertEquals(0, $result);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}

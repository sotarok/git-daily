<?php

use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;
use Behat\Behat\Event\SuiteEvent;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

if (!defined('GIT_DAILY_BASEDIR')) {
    define('GIT_DAILY_BASEDIR', dirname(dirname(__DIR__)));
}
if (!defined('GIT_DAILY_BIN_PATH')) {
    if ($git_daily_bin = getenv('GIT_DAILY_BIN_PATH')) {
        define('GIT_DAILY_BIN_PATH', $git_daily_bin);
    } else {
        define('GIT_DAILY_BIN_PATH', GIT_DAILY_BASEDIR . '/bin/git-daily');
    }
}
require_once GIT_DAILY_BASEDIR . '/src/Git/Daily/ClassLoader.php';


/**
 * Git context.
 */
class GitContext extends BehatContext
{
    const LOCAL_GIT_DIR = '/tmp/local';
    const LOCAL_GIT_DIR1 = self::LOCAL_GIT_DIR;
    const LOCAL_GIT_DIR2 = '/tmp/local-others';
    const SERVER_GIT_DIR = '/tmp/server';

    private static $cl = null;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
    }

    /**
     * @BeforeSuite
     */
    public static function setUp(SuiteEvent $event) // {{{
    {
        $cl = new Git_Daily_ClassLoader('Git_Daily', GIT_DAILY_BASEDIR . '/src');
        $cl->register();
        self::$cl = $cl;

        // initialize git directory
        self::moveToNewPath(GIT_DAILY_BASEDIR . self::LOCAL_GIT_DIR);

        exec("git init", $out, $retvar);
        assertEquals(0, $retvar, 'Git init for local test.');

        file_put_contents(GIT_DAILY_BASEDIR . self::LOCAL_GIT_DIR . '/README', 'This is test');

        exec("git add README", $out, $retvar);
        assertEquals(0, $retvar, 'Git add README.');
        exec("git commit -m 'initial commit'", $out, $retvar);
        assertEquals(0, $retvar, 'Git first commit.');

        // add server git dir
        self::moveToNewPath(GIT_DAILY_BASEDIR . self::SERVER_GIT_DIR);
        exec("git init --bare --shared", $out, $retvar);
        assertEquals(0, $retvar, 'Git init --bare --shared for server test.');
    } // }}}

    /**
     * @AfterSuite
     */
    public static function tearDown(SuiteEvent $event) // {{{
    {
        // cleanup
        if (is_dir($dir = GIT_DAILY_BASEDIR . self::LOCAL_GIT_DIR)) {
            self::rmdirRecursive($dir);
        }
        if (is_dir($dir = GIT_DAILY_BASEDIR . self::LOCAL_GIT_DIR2)) {
            self::rmdirRecursive($dir);
        }
        if (is_dir($dir = GIT_DAILY_BASEDIR . self::SERVER_GIT_DIR)) {
            self::rmdirRecursive($dir);
        }

        self::$cl->unregister();
    } // }}}

    /**
     * @Given /^I am in a directory "([^"]*)"$/
     */
    public function iAmInADirectory($arg1) // {{{
    {
        $dir = GIT_DAILY_BASEDIR . DIRECTORY_SEPARATOR . $arg1;
        chdir($dir);
        assertTrue(getcwd(), $dir);
    } // }}}

    /**
     * @Given /^I am in a git repository "([^"]*)"$/
     */
    public function iAmInAGitRepository($arg1) // {{{
    {
        $dir = GIT_DAILY_BASEDIR . DIRECTORY_SEPARATOR . $arg1;
        chdir($dir);
        assertEquals(getcwd(), $dir);

        exec("git rev-parse --git-dir", $out, $retvar);
        assertEquals(0, $retvar);
        $gitdir = array_shift($out);
        assertContains('.git', $gitdir);
    } // }}}


    /**
     * @Given /^I am on the git branch "([^"]*)"$/
     */
    public function iAmOnTheGitBranch($branch)
    {
        exec("git checkout $branch 2>&1", $out, $retvar);
        assertEquals(0, $retvar);

        exec("git branch 2>&1", $out, $retvar);
        $git_branch = array_shift($out);
        assertContains($branch, $git_branch);
    }

    /**
     * @When /^I run the command "([^"]*)"$/
     */
    public function iRunTheCommand($command) // {{{
    {
        $command = escapeshellcmd($command);

        if ('/' === DIRECTORY_SEPARATOR) {
            $command .= ' 2>&1';
        }

        exec($command, $output, $return);
        $this->command = $command;
        $this->output  = trim(implode("\n", $output));
        $this->return  = $return;
    } // }}}

    /**
     * @When /^I run "git daily(?: ([^"]*))?"$/
     *
     * @param   string  $args
     */
    public function iRunGitDaily($args = '') // {{{
    {

        list($argc, $argv) = $this->getGitDailyCommand($args);

        ob_start();
        $git_daily = new Git_Daily(new Git_Daily_CommandUtil());
        $output = new Git_Daily_ConsoleOutput();
        $retval = $git_daily->run($argv, $output);
        $out = ob_get_clean();

        //var_dump($retval);
        //var_dump($out);

        //exec($command, $output, $return);

        $this->command = 'git daily ' . $args;
        //$this->output  = trim(implode("\n", $output));
        $this->output  = $out;
        $this->retval  = $retval;
    } // }}}

    /**
     * @When /^I run "git daily(?: ([^"]*))?" with:$/
     *
     * @param   string  $args
     */
    public function iRunGitDailyWith($args = '', PyStringNode $string) // {{{
    {
        list($argc, $argv) = $this->getGitDailyCommand($args);
        ob_start();
        $return = Git_Daily::run($argc, $argv);
        $output = ob_get_clean();
        /*
        $echo = 'echo "';
        foreach ($string->getLines() as $line) {
            $echo .= $line . "\n";
        }
        $echo .= '"';
        $command = $this->getGitDailyCommand($args);
        exec($echo . ' | ' . $command, $output, $return);
         */


        $this->command = 'git daily ' . $args;
        $this->output  = trim(implode("\n", $output));
        $this->return  = $return;
    } // }}}


    /**
     * @Then /^I should get:$/
     */
    public function iShouldGet(PyStringNode $string) // {{{
    {
        assertEquals((string)$string, $this->output);
    } // }}}

    /**
     * @Then /^It should contains:$/
     */
    public function itShouldContains(PyStringNode $string) // {{{
    {
        assertContains((string)$string, $this->output);
    } // }}}

    /**
     * @Then /^It should (fails|passes) and contains:$/
     */
    public function itShouldFailsOrPassesPassAndContains($success, PyStringNode $string)
    {
        if ('fails' === $success) {
            assertNotEquals(0, $this->return);
        } else {
            assertEquals(0, $this->return);
        }
        assertContains((string)$string, $this->output);
    }

    /**
     * Checks whether previously runned command failed|passed.
     *
     * @Then /^It should (fail|pass)$/
     *
     * @param   string  $success    "fail" or "pass"
     */
    public function itShouldFailOrPass($success) // {{{
    {
        if ('fail' === $success) {
            assertNotEquals(0, $this->return);
        } else {
            assertEquals(0, $this->return);
        }
    } // }}}

    private function getGitDailyCommand($args = '')
    {
        $argv = array('git-daily');
        foreach (explode(' ', $args) as $arg) {
            $argv[] = $arg;
        }
        return array(count($argv), $argv);
        /*
        $command = sprintf('php %s %s',
            escapeshellarg(GIT_DAILY_BIN_PATH), $args
        );
        return $command;
         */
    }

    private static function moveToNewPath($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        chdir($path);
    }

    /**
     * Removes files and folders recursively at provided path.
     *
     * @param   string  $path
     */
    private static function rmdirRecursive($path) {
        $files = scandir($path);
        array_shift($files);
        array_shift($files);

        foreach ($files as $file) {
            $file = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                self::rmdirRecursive($file);
            } else {
                unlink($file);
            }
        }

        rmdir($path);
    }
}

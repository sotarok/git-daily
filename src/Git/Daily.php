<?php
/**
 *
 */

require_once 'Git/Daily/Exception.php';
require_once 'Git/Daily/CommandUtil.php';
require_once 'Git/Daily/CommandAbstract.php';
require_once 'Git/Daily/OptionParser.php';

class Git_Daily
{
    const VERSION = '0.1.0';
    const COMMAND = 'git-daily';

    const USAGE_SPACE = 4;

    const E_GIT_NOT_FOUND        = 100;
    const E_NOT_IN_REPO          = 101;
    const E_NOT_INITIALIZED      = 102;
    const E_SUBCOMMAND_NOT_FOUND = 200;

    public static $git = null;

    public static $allow_out_of_repo = array(
        'help',
        'version',
    );

    public static function getSubCommand($name)
    {
        $name = strtolower($name);
        $name_case = ucfirst(strtolower($name));
        $file = dirname(__FILE__) . '/Daily/Command/' . $name_case . '.php';
        if (file_exists($file)) {
            require_once $file;
            $class = __CLASS__ . '_Command_' . $name_case;
            if (class_exists($class, false)) {
                return $class;
            }
        }

        throw new Git_Daily_Exception(
            "no such subcommand: $name",
            self::E_SUBCOMMAND_NOT_FOUND
        );
    }

    public static function getSubCommandList()
    {
        $file_list = dirname(__FILE__) . '/Daily/Command/*.php';

        $command_list = array();
        foreach (glob($file_list) as $file) {
            if (preg_match(sprintf('@^%s/Daily/Command/(\w+)\.php$@', dirname(__FILE__)), $file , $m)) {
                $command_list[] = strtolower($m[1]);
            }
        }
        return $command_list;
    }

    public static function run($argc, $argv)
    {
        list($git_cmd,) = Git_Daily_CommandUtil::cmd('which', array('git'));
        $git_cmd = array_shift($git_cmd);
        if (!is_executable($git_cmd)) {
            throw new Git_Daily_Exception("git command not found",
                self::E_GIT_NOT_FOUND, null, true
            );
        }
        self::$git = $git_cmd;

        if ($argc < 2) {
            throw new Git_Daily_Exception("no subcommand specified.",
                self::E_SUBCOMMAND_NOT_FOUND, null, true, true
            );

        }
        $file = array_shift($argv);
        $subcommand = array_shift($argv);

        if (!in_array($subcommand, self::$allow_out_of_repo)) {
            list($git_dir, $retval) = Git_Daily_CommandUtil::cmd(self::$git, array('rev-parse', '--git-dir'));
            if ($retval != 0) {
                throw new Git_Daily_Exception("not in git repository",
                    self::E_NOT_IN_REPO, null, true
                );
            }
        }

        try {
            $result = Git_Daily_CommandAbstract::runSubCommand($subcommand, $argv);
            call_user_func_array('Git_Daily_CommandAbstract::outLn', $result);
        } catch (Git_Daily_Exception $e) {
            if (!$e->isShowUsage()) {
                fwrite(STDERR, self::COMMAND . ': fatal: ' .  $e->getMessage() . PHP_EOL);
            }
        }
    }

    public static function usage($subcommand = null)
    {
        fwrite(STDERR, <<<E
git-daily:

Usage:

E
);
        $max = 0;
        $lists = self::getSubCommandList();
        foreach ($lists as $list) {
            if (strlen($list) > $max) {
                $max = strlen($list);
            }
        }
        foreach ($lists as $list) {
            fwrite(STDERR, "    ");
            fwrite(STDERR, str_pad($list, $max + self::USAGE_SPACE, ' '));
            $command = self::getSubCommand($list);
            fwrite(STDERR, constant("$command::DESCRIPTION"));
            fwrite(STDERR, PHP_EOL);
        }

        if ($subcommand !== null) {
            try {
                $command = self::getSubCommand($subcommand);
                if (is_callable(array($command, 'usage'))) {
                    call_user_func(array($command, 'usage'));
                }
            } catch (Git_Daily_Exception $e) {
                // through
            }
        }
    }
}

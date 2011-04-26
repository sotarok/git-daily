<?php
/**
 *
 */

class Git_Daily_Command_Config
    extends Git_Daily_CommandAbstract
{
    const DESCRIPTION = 'Set or show config';

    protected $option = array(
        'list' => array('l', 'list'),
    );

    private $_config_key_list = array(
        'remote',
        'develop',
        'master',
    );

    public function runCommand()
    {
        $args = $this->opt->getArgs();
        if (count($args) == 2) {
            $key = $args[0];
            $value = $args[1];
            return $this->_setConfig($key, $value);
        }

        // else show config
        $option = array('config', '--list',);
        $pipe = array('grep', array('gitdaily'));
        $return = self::cmd(Git_Daily::$git, $option, $pipe);

        if (empty($return)) {
            //return "git-daily: not initialized. please run:\n   git daily init";
            throw new Git_Daily_Exception(
                "git-daily: not initialized. please run: git daily init",
                Git_Daily::E_NOT_INITIALIZED, null, true
            );
        }
        return $return;
    }

    private function _setConfig($key, $value)
    {
        switch ($key) {
        case 'remote':
            $this->_setConfigRemote($value);
            break;
        case 'develop':
            $this->_setConfigDevelop($value);
            break;
        case 'master':
            $this->_setConfigMaster($value);
            break;
        default:
            throw new Git_Daily_Exception(
                sprintf("invalid config key, allowed key is: %s", implode(',', $this->_config_key_list)),
                Git_Daily::E_NO_SUCH_CONIFIG
            );
        }
    }

    private function _setConfigRemote($value)
    {
        $remote_url_list = self::cmd(
            Git_Daily::$git,
            array('config', '--list'),
            array('grep', array('remote'),
                array('grep', array('url'))
            )
        );
        foreach ($remote_url_list as $remote_url) {
            if (preg_match("/^remote\.$value\.url=/", $remote_url)) {
                self::cmd(Git_Daily::$git, array('config', 'gitdaily.remote', $value));
                self::outLn('config setted');
                return true;
            }
        }

        throw new Git_Daily_Exception(
            "no such remote url $value",
            Git_Daily::E_INVALID_CONIFIG_VALUE
        );
    }

    private function _setConfigDevelop($value)
    {
        // branch check
        $branches = Git_Daily_GitUtil::branches();
        if (!in_array($value, $branches)) {
            throw new Git_Daily_Exception("no such branch: $value");
        }

        self::cmd(Git_Daily::$git, array('config', 'gitdaily.develop', $value));
        self::outLn('config setted');
    }

    private function _setConfigMaster($value)
    {
        // branch check
        $branches = Git_Daily_GitUtil::branches();
        if (!in_array($value, $branches)) {
            throw new Git_Daily_Exception("no such branch: $value");
        }

        self::cmd(Git_Daily::$git, array('config', 'gitdaily.master', $value));
        self::outLn('config setted');
    }

    public static function usage()
    {
        fwrite(STDERR, <<<E
Usage: git daily config <key> <value>

Example:
       git daily config remote origin

E
        );
    }
}


<?php
/**
 *
 */

class Git_Daily_Command_Config
    extends Git_Daily_CommandAbstract
{
    private $_config_key_list = array(
        'remote',
        'develop',
        'master',
        'logurl',
    );

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return 'Set or show config';
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return array();
    }

    public function execute()
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
        case 'logurl':
            $this->_setConfigLogurl($value);
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

    private function _setConfigLogurl($value)
    {
        // TODO: something validation?
        self::cmd(Git_Daily::$git, array('config', 'gitdaily.logurl', $value));
        self::outLn('config setted');
    }

    public function usage()
    {
        return <<<E
Usage: git daily config <key> <value>

Example:

    Remote name :
        git daily config remote origin

    Branch name of develop :
        git daily config develop develop

    Branch name of master :
        git daily config master master

    URL template for dump list (will dump commit hash instead of "%s") :
        GitWeb :  git daily config logurl "http://example.com/?p=repositories/example.git;a=commit;h=%s"
        GitHub :  git daily config logurl "https://github.com/sotarok/git-daily/commit/%s"

E;
    }
}


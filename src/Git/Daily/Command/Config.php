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

    /**
     * @TODO support --global ?
     */
    public function execute()
    {
        $config = $this->context->getConfig();
        $args = $this->opt->getArgs();
        if (count($args) == 2) {
            return $this->setConfig($key = $args[0], $value = $args[1]);
        }

        if (count($args) == 1) {
            return $config->get($args[0]);
        }

        $config_list = array();
        foreach ($config->getAll() as $key => $value) {
            $config_list[] = "$key = $value";
        }
        return $config_list;
    }

    private function setConfig($key, $value)
    {
        switch ($key) {
        case 'remote':
            $this->setConfigRemote($value);
            break;
        case 'develop':
            $this->setConfigDevelop($value);
            break;
        case 'master':
            $this->setConfigMaster($value);
            break;
        case 'logurl':
            $this->setConfigLogurl($value);
            break;
        default:
            throw new Git_Daily_Exception(
                sprintf("invalid config key, allowed key is: %s", implode(',', $this->_config_key_list)),
                Git_Daily::E_NO_SUCH_CONIFIG
            );
        }
    }

    private function setConfigRemote($value)
    {
        // TODO : use gitutil
        $remote_url_list = $this->cmd(Git_Daily::$git, array('config', '--get-regexp', '^remote.*url$'));
        foreach ($remote_url_list as $remote_url) {
            if (preg_match("/^remote\.$value\.url /", $remote_url)) {
                $this->context->getConfig()->set('remote', $value);
                $this->output->outLn('config set');
                return true;
            }
        }

        throw new Git_Daily_Exception(
            "no such remote url $value",
            Git_Daily::E_INVALID_CONIFIG_VALUE
        );
    }

    private function setConfigDevelop($value)
    {
        // branch check
        $branches = $this->git->branches();
        if (!in_array($value, $branches)) {
            throw new Git_Daily_Exception("no such branch: $value");
        }

        $this->context->getConfig()->set('develop', $value);
        $this->output->outLn('config set');
    }

    private function setConfigMaster($value)
    {
        // branch check
        $branches = $this->git->branches();
        if (!in_array($value, $branches)) {
            throw new Git_Daily_Exception("no such branch: $value");
        }

        $this->context->getConfig()->set('master', $value);
        $this->output->outLn('config set');
    }

    private function setConfigLogurl($value)
    {
        // TODO: something validation?
        $this->context->getConfig()->set('logurl', $value);
        $this->output->outLn('config set');
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


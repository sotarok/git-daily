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
            $value = $config->get($args[0]);
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            } else {
                return $value;
            }
        }

        $config_list = array();
        foreach ($config->getAll() as $key => $value) {
            if (is_bool($value)) {
                $config_list[] = "$key = " . ($value ? 'true' : 'false');
            } else {
                $config_list[] = "$key = $value";
            }
        }
        return $config_list;
    }

    /**
     * @return mixed output
     */
    private function setConfig($key, $value)
    {
        switch ($key) {
        case 'remote':
            return $this->setConfigRemote($value);
            break;
        case 'develop':
            return $this->setConfigDevelop($value);
            break;
        case 'master':
            return $this->setConfigMaster($value);
            break;
        case 'logurl':
            return $this->setConfigLogurl($value);
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
        $remotes = $this->git->remotes();
        foreach ($remotes as $remote_name => $remote_url) {
            if ($remote_name == $value) {
                $this->context->getConfig()->set('remote', $value);
                return 'config set';
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
        return 'config set';
    }

    private function setConfigMaster($value)
    {
        // branch check
        $branches = $this->git->branches();
        if (!in_array($value, $branches)) {
            throw new Git_Daily_Exception("no such branch: $value");
        }

        $this->context->getConfig()->set('master', $value);
        return 'config set';
    }

    private function setConfigLogurl($value)
    {
        // TODO: something validation?
        $this->context->getConfig()->set('logurl', $value);
        return 'config set';
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


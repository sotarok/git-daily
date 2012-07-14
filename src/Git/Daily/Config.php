<?php
/**
 *
 * @TODO support unset
 */


class Git_Daily_Config
{
    protected $context = null;
    protected $cmd = null;

    protected $config = array();
    protected $config_global = array();

    public function __construct(Git_Daily $context)
    {
        $this->context = $context;
        $this->cmd = $context->getCommandUtil();

        $this->config_global = $this->getAllGlobalConfig();
        if ($this->context->isInGitRepository()) {
            $this->config = $this->getAllLocalConfig();
        }
    }

    public function get($key, $global = false)
    {
        if ($global) {
            if (isset($this->config_global[$key])) {
                return $this->config_global[$key];
            }
        } else {
            if (isset($this->config[$key])) {
                return $this->config[$key];
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getAll($global = false)
    {
        if ($global) {
            return $this->config_global;
        }
        return $this->config;
    }

    public function set($key, $value, $global = false)
    {
        if (!$global && !$this->context->isInGitRepository()) {
            throw new Git_Daily_Exception('Not a git repository (or any of the parent directories): .git');
        }

        if ($global) {
            $this->setGlobalConfig($key, $value);
            $this->config_global[$key] = $value;
            return true;
        }

        $this->setLocalConfig($key, $value);
        $this->config[$key] = $value;
        return true;
    }

    protected function getAllLocalConfig()
    {
        list($result, $retval) = $this->cmd->run(Git_Daily::$git, array('config', '--get-regexp', '^gitdaily'));

        return $this->parseConfigLines($result);
    }

    protected function getAllGlobalConfig()
    {
        list($result, $retval) = $this->cmd->run(Git_Daily::$git, array('config', '--global', '--get-regexp', '^gitdaily'));
        return $this->parseConfigLines($result);
    }

    /**
     * @param array $config
     * @return array
     */
    protected function parseConfigLines(array $config)
    {
        $ret_config = array();
        foreach ($config as $confline) {
            if (empty($confline)) {
                continue;
            }
            $sep = strpos($confline, ' ');
            if (false === $sep) {
                $key = str_replace('gitdaily.', '', $confline);
                $value = null;
            } else {
                $key = str_replace('gitdaily.', '', substr($confline, 0, $sep));
                $value = substr($confline, $sep + 1);
            }

            if ($value === 'true') {
                $value = true;
            }
            if ($value === 'false') {
                $value = false;
            }
            $ret_config[$key] = $value;
        }

        return $ret_config;
    }

    /**
     * @param string $key
     * @param string $value
     * @return boolean
     */
    protected function setLocalConfig($key, $value)
    {
        if (is_bool($value)) {
            list($result, $retval) = $this->cmd->run(Git_Daily::$git, array('config', 'gitdaily.' . $key, $value ? 'true' : 'false'));
        } else {
            list($result, $retval) = $this->cmd->run(Git_Daily::$git, array('config', 'gitdaily.' . $key, $value));
        }
        if ($retval != 0) {
            throw new Git_Daily_Exception(sprintf("Failed to set git config, git returns error: output=%s", implode(PHP_EOL, $result)));
        }
        return true;
    }

    /**
     * @param string $key
     * @param string $value
     * @return boolean
     */
    protected function setGlobalConfig($key, $value)
    {
        if (is_bool($value)) {
            list($result, $retval) = $this->cmd->run(Git_Daily::$git, array('config', '--global', 'gitdaily.' . $key, $value ? 'true' : 'false'));
        } else {
            list($result, $retval) = $this->cmd->run(Git_Daily::$git, array('config', '--global', 'gitdaily.' . $key, $value));
        }
        if ($retval != 0) {
            throw new Git_Daily_Exception(sprintf("Failed to set global git config, git returns error: output=%s", implode(PHP_EOL, $result)));
        }
        return true;
    }
}

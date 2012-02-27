<?php
/**
 *
 */


class Git_Daily_Command_Version
    extends Git_Daily_CommandAbstract
{
    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return 'Show git-daily version';
    }

    /**
     */
    public function isAllowedOutOfRepo()
    {
        return true;
    }

    public function execute()
    {
        return array("%s: version %s", Git_Daily::COMMAND, Git_Daily::VERSION);
    }
}

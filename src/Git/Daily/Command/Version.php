<?php
/**
 *
 */


class Git_Daily_Command_Version
    extends Git_Daily_CommandAbstract
{
    const DESCRIPTION = 'Show version';

    public function runCommand()
    {
        return array("%s: version %s", Git_Daily::COMMAND, Git_Daily::VERSION);
    }
}

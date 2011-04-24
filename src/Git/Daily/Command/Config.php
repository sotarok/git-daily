<?php
/**
 *
 */

class Git_Daily_Command_Config
    extends Git_Daily_CommandAbstract
{
    const DESCRIPTION = 'Set or show config';

    protected $option = array(
        array('l', 'list'),
    );

    public function runCommand()
    {
        $option = array(
            'config',
            '--list',
        );
        $pipe = array(
            'grep',
            array(
                'gitdaily'
            ),
        );
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

    public static function usage()
    {
    }
}


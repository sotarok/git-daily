<?php
/**
 *
 */


class Git_Daily_Command_Init
    extends Git_Daily_CommandAbstract
{
    const DESCRIPTION = 'Initialize git daily';

    public function runCommand()
    {
        $is_init = self::cmd(Git_Daily::$git, array('config', '--bool', 'gitdaily.init'));
        if (count($is_init) > 0 && (bool)$is_init[0]) {
            self::outLn('%s already initialized.', Git_Daily::COMMAND);
            return null;
        }

        $remotes = self::cmd(Git_Daily::$git, array('config', '--list'), array('grep', array('remote')));
        $remote_url = array();
        foreach ($remotes as $remote) {
            //remote.origin.url
            if (preg_match('/remote\.([^\.]+)\.url/', $remote, $m)) {
                $remote_url[] = $m[1];
            }
        }

        if (!empty($remote_url)) {
            if (count($remote_url) >= 2) {
                do {
                    self::outLn('Choose your remote:');
                    foreach ($remote_url as $key => $url) {
                        self::outLn('    %d: %s', $key, $url);
                    }
                    self::out('    > ');
                    $num = trim(Git_Daily_CommandUtil::get());

                } while(!isset($remote_url[$num]));
                $first_choise = $remote_url[$num];
            }
            else {
                $first_choise = reset($remote_url);
            }
            self::cmd(Git_Daily::$git, array('config', 'gitdaily.remote', $first_choise));
            self::outLn('Your remote is [%s]', $first_choise);
        }

        // initialized
        self::cmd(Git_Daily::$git, array('config', 'gitdaily.init', 'true'));

        self::outLn();
        self::outLn('%s completed to initialize.', Git_Daily::COMMAND);
        return null;
    }
}

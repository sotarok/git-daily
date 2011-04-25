<?php
/**
 *
 */


class Git_Daily_Command_Help
    extends Git_Daily_CommandAbstract
{
    const DESCRIPTION = 'Show help (run "help {subcommand}" to get more help)';
    public function runCommand()
    {
        $args = $this->opt->getArgs();
        $subcommand = reset($args);
        if (!empty($subcommand)) {
            Git_Daily::usage($subcommand, $only_subcommand = true);
        }
        else {
            Git_Daily::usage();
        }
    }
}

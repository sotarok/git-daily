<?php
/**
 *
 */


class Git_Daily_Command_Help
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
        return 'Show help (run "help {subcommand}" to get more help)';
    }

    /**
     */
    public function isAllowedOutOfRepo()
    {
        return true;
    }

    public function execute()
    {
        $args = $this->opt->getArgs();
        $name = reset($args);


        if (!empty($name)) {
            $cmd_class = $this->context->findCommand($name);
            if (!$cmd_class) {
                throw new Git_Daily_Exception(
                    "no such subcommand: $name",
                    Git_Daily::E_SUBCOMMAND_NOT_FOUND
                );
            }
            $cmd = $this->createCommand($cmd_class);
            return $cmd->usage();
        }
        else {
            // TODO
            $this->context->findCommand($name);
        }
    }
}

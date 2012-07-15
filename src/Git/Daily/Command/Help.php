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
            $cmd_class = $this->context->getCommand($name);
            if (!$cmd_class) {
                throw new Git_Daily_Exception(
                    "no such subcommand: $name",
                    Git_Daily::E_SUBCOMMAND_NOT_FOUND
                );
            }

            return $this->context->usage($name);
        }
        else {
            return $this->context->usage();
        }
        return null;
    }
}

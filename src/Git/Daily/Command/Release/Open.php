<?php
/**
 *  @package    Git
 *  @author     Sotaro Karasawa <sotaro.k@gmail.com>
 */

/**
 *  Git_Daily_Command_Release_Open
 *
 *  @package    Git
 */
class Git_Daily_Command_Release_Open
    extends Git_Daily_CommandAbstract
{
    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return array(
            'yes' => array('y', null, Git_Daily_OptionParser::ACT_STORE_TRUE),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return 'Operation daily release';
    }

    /**
     *  runCommand
     */
    public function execute()
    {
        return 1;
    }

    public function usage()
    {
        return<<<E
Usage:
    git daily release open        : Open daily-release process

E;
    }
}


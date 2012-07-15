<?php
/**
 *
 */

abstract class Git_Daily_Flow
{
    protected $context = null;
    protected $config = null;
    public function __construct(
        Git_Daily $context
    )
    {
        $this->context = $context;
        $this->config = $context->getConfig();
    }

    /**
     * getMergingBranch
     *
     * branch name to merge
     */
    abstract public function getMergingBranch();

    /**
     * getWorkingBranch
     *
     * Branch name to working.
     */
    abstract public function getWorkingBranch();

    abstract public function isOpenable();
    abstract public function isSyncable();
    abstract public function isClosable();
}

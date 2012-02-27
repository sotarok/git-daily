<?php
/**
 *
 */

interface Git_Daily_CommandInterface
{
    /**
     * @return mixed output
     */
    public function execute();

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @return string
     */
    public function getDescription();
}

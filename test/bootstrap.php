<?php
/**
 *
 */

define('GIT_DAILY_SRC_DIR', dirname(__FILE__) . '/../src');
define('GIT_DAILY_TEST_DIR', dirname(__FILE__));

PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(dirname(__FILE__));

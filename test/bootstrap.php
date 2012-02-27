<?php
/**
 *
 */

define('GIT_DAILY_SRC_DIR' , dirname(__FILE__) . '/../src');
define('GIT_DAILY_TEST_DIR', dirname(__FILE__));
define('GIT_DAILY_TMP_DIR' , dirname(__FILE__) . '/../tmp');

require_once GIT_DAILY_SRC_DIR . '/Git/Daily/ClassLoader.php';

$cl = new Git_Daily_ClassLoader('Git_Daily', Git_Daily_ClassLoader::getSrcPath());
$cl->register();


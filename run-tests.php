<?php

header('Content-Type: text/html; charset=utf-8');

include('DebugBatchTest.class.php');

/* App/tests defs */
define('ALIB_DIR', __DIR__.'/../lib');
define('TEST_CLASSES_DIR', __DIR__.'/test-classes');
define('CACHE_DIR', __DIR__.'/cache/');
define('DEBUG', true);

define('TEST_DB_HOSTNAME', 'localhost');
define('TEST_DB_NAME', 'alib_0_0_6_test');
define('TEST_DB_USERNAME', 'root');
define('TEST_DB_PASSWORD', '');
/* end defs */


new DebugBatchTest(__DIR__.'/sample-tests', 'DebugDbTest');

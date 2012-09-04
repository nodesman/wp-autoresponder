<?php
// Load the test environment
// https://github.com/nb/wordpress-tests

$path = '/usr/lib/wordpress-tests/bootstrap.php';

if (file_exists($path)) {
        $GLOBALS['wp_tests_options'] = array(
                'active_plugins' => array('plugin-name/main-plugin-file.php')
        );
        require_once $path;
} else {
        exit("Couldn't find wordpress-tests/bootstrap.php\n");
}

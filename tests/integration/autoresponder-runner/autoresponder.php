<?php
//assuming that this file is in wp-content/plugins/wp-responder-..../tests
include "../../../../wp-load.php";
include "creator.php";
require "cronrunner.php";

echo "Testing whether test script has privileges to change the time...\r\n";
$time = date("mdHiY",time()+19800);

@exec("date $time", $output, $returnVal);
if (0 != $returnVal)
{
    echo "Unable to change the time, unable to test the autoresponder settings. Please run this script as an administrator\r\n";
	exit(1);
}

$plugins = wp_get_active_and_valid_plugins();
$foundPlugin = false;
foreach ($plugins as $plugin)
{
    if (preg_match("@wpresponder.php$@",$plugin))
    {
          $foundPlugin = true;
    }
}

if (!$foundPlugin)
{
     echo "WPR not found. Please run test in a blog that has WPR activated";
    exit(1);
}


echo "Creating sample newsletters, autoresponders and autoresponder messages...\r\n";

$nid = createTestBaseData();
$startDate = time();
runCronJobs($nid,$startDate);


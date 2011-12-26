<?php
function _wpr_firstrun()
{
	do_action("_wpr_firstrun");	
	$db_checker = $GLOBALS['db_checker'];
	$db_checker->perform_check();
	add_option("_wpr_firstrun","done");
	
}
<?php
function _wpr_firstrun()
{
	do_action("_wpr_firstrun");	
	$db_checker = $GLOBALS['db_checker'];
	$db_checker->perform_check();
	add_option("_wpr_firstrun","done");
	
}


function _wpr_firstrunv525()
{
	$role = get_role( 'administrator' );
	$role->add_cap( 'manage_newsletters' );
	add_option("_wpr_firstrun","done");
	
}

/*
 * Apparently you can't always rely on the activation_hook to fire on upgrades
 * This caused major issues in <5.2.6 wherein the new table structure didn't
 * get created in many users tables.
 */

function _wpr_firstrunv526()
{
        _wpr_firstrunv525();
        _wpr_firstrun();
}
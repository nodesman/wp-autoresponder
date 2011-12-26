<?php

function _wpr_subscriber_get($id)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers where id=$id";
	$result = $wpdb->get_results($query);
	if (count($result)>0)
	{
		return $result[0];
	}
	else
	{
		return false;
	}
}



function _wpr_subscription_status($active, $confirmed)
{
    if ($active == 1 && $confirmed== 1)
        return __("Subscribed");
    if ($active == 1 && $confirmed== 0)
        return __("Subscribed and Unconfirmed");
    if ($active == 0 && $confirmed== 1)
        return __("Unsubscribed");
    if ($active == 2 && $confirmed==1)
        return __("Transfered");
    if ($active == 3)
        return __("Disabled Due To Delivery Problems");
}




/*
	nid,
	name,
	email,
	fid,
	date,
	hash,
     
*/

function _wpr_subsciber_add_confirmed($params)
{
	global $wpdb;
	
	$nid = $params['nid'];
	$name = $params['name'];
	$email = $params['email'];
	$fid = ($params['fid'])?$params['fid']:0;
	$date = ($params['date'])?$params['date']:time();
	$hash = ($params['hash'])?$params['hash']:generateSubscriberHash();
	$query = "INSERT INTO ".$wpdb->prefix."wpr_subscribers (nid, name, email, fid, date, hash,active, confirmed) values ('$nid','$name','$email','$fid','$date','$hash',1,1);";		
	$wpdb->query($query);
	
	return $wpdb->insert_id;
	
}


function generateSubscriberHash()
{
	for ($i=0;$i<6;$i++)
	{
		$a[] = rand(65,90);
		$a[] = rand(97,123);
		$a[] = rand(48,57);
		
		$whichone = rand(0,2);
		$currentCharacter = chr($a[$whichone]);
		
		$hash .= $currentCharacter;
		unset($a);
		
	}
     $hash .= time();
	 return $hash;
}
<?php
function _wpr_subscriptionform_get($id)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscription_form where id=$id";
        
	$result = $wpdb->get_results($query);
        
        if (count($result)>0)
            return $result[0];
        else
            return false;
}
function _wpr_subscriptionforms_get()
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscription_form";
	$result = $wpdb->get_results($query);
	return $result;

}
function _wpr_subscriptionform_update($info)
{
	global $wpdb;
	$info = (object) $info;
	$query = "UPDATE  ".$wpdb->prefix."wpr_subscription_form SET name='$info->name', nid='$info->nid',return_url='$info->return_url', followup_type='$info->followup_type',followup_id='$info->followup_id',blogsubscription_type='$info->blogsubscription_type',blogsubscription_id='$info->blogsubscription_id',custom_fields='$info->custom_fields', confirm_subject='$info->confirm_subject', confirm_body='$info->confirm_body',confirmed_subject='$info->confirmed_subject',confirmed_body='$info->confirmed_body', submit_button='$info->submit_button' where id='$info->id';";	
	$result = $wpdb->query($query);
}

function _wpr_subscriptionform_create($info)
{
	global $wpdb;
	$info = (object) $info;
	$query = "INSERT INTO ".$wpdb->prefix."wpr_subscription_form (nid,name,return_url,followup_type,followup_id,blogsubscription_type,blogsubscription_id,custom_fields, confirm_subject,confirm_body,confirmed_subject,confirmed_body,submit_button) values ('$info->nid','$info->name','$info->return_url','$info->followup_type','$info->followup_id','$info->blogsubscription_type','$info->blogsubscription_id','$info->custom_fields','$info->confirm_subject','$info->confirm_body','$info->confirmed_subject','$info->confirmed_body','$info->submit_button');";
	$wpdb->query($query);
}

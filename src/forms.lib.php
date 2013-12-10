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
	$updateSubscriptionFormQuery = $wpdb->prepare("UPDATE  {$wpdb->prefix}wpr_subscription_form SET name=%s,
	                                                                                                nid={$info->nid},
	                                                                                                return_url=%s,
	                                                                                                followup_type='{$info->followup_type}',
	                                                                                                followup_id=%d,
	                                                                                                blogsubscription_type='{$info->blogsubscription_type}',
	                                                                                                blogsubscription_id='{$info->blogsubscription_id}',
	                                                                                                custom_fields='{$info->custom_fields}',
	                                                                                                confirm_subject=%s,
	                                                                                                confirm_body=%s,
	                                                                                                confirmed_subject=%s,
	                                                                                                confirmed_body=%s,
	                                                                                                submit_button=%s
	                                                                                                where id='$info->id';",$info->name,$info->return_url,intval($info->followup_id), $info->confirm_subject,$info->confirm_body,$info->confirmed_subject,$info->confirmed_body,$info->submit_button);
	$result = $wpdb->query($updateSubscriptionFormQuery);
}

function _wpr_subscriptionform_create($info)
{
	global $wpdb;
	$info = (object) $info;
	$createSubscriptionFormQuery = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpr_subscription_form (nid,name,return_url,followup_type,followup_id,blogsubscription_type,blogsubscription_id,custom_fields, confirm_subject,confirm_body,confirmed_subject,confirmed_body,submit_button) values (%d,%s,%s,'{$info->followup_type}',%d,'{$info->blogsubscription_type}',%d,'{$info->custom_fields}',%s,%s,%s,%s,%s);",$info->nid,$info->name,$info->return_url,$info->followup_id,$info->blogsubscription_id,$info->confirm_subject,$info->confirm_body,$info->confirmed_subject,$info->confirmed_body,$info->submit_button);
	$wpdb->query($createSubscriptionFormQuery);
}

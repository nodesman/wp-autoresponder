<?php


function _wpr_settings_handler()
{
	_wpr_set("canspam_address",get_option("wpr_address")); 
	$notification_custom_email_is_admin_email = (get_option('wpr_notification_custom_email')=="admin_email");
	_wpr_set("notification_custom_email_is_admin_email",$notification_custom_email_is_admin_email);
	
	$admin_email = get_bloginfo('admin_email');
	_wpr_set("admin_email",$admin_email);
	
	 $notificationEmail = get_option("wpr_notification_custom_email");
	 if ($notificationEmail != "admin_email")
	 {
		  $notification_email_address= get_option("wpr_notification_custom_email");
	 }
	 
	 _wpr_set("notification_email_address",$notification_email_address);
	 
	 $tutorial_on = (get_option('wpr_tutorial_active') == "on");
	 _wpr_set("tutorial_on",$tutorial_on);
	 
	 $updates_on = (get_option('wpr_updates_active') == "on");
	 _wpr_set("updates_on",$updates_on);
	 
	 $hourly_limit = get_option("wpr_hourlylimit");
	 _wpr_set("hourly_limit",$hourly_limit);
	 
	 
	 $smtp_enabled = (get_option("wpr_smtpenabled") == 1);
	 _wpr_set("smtp_enabled",$smtp_enabled);
	 
	 
	 $smtp_hostname = get_option("wpr_smtphostname");
	 _wpr_set("smtp_hostname",$smtp_hostname);
	 
	 $smtp_port = get_option("wpr_smtpport");
	 _wpr_set("smtp_port",$smtp_port);
	 
	 
	 $smtp_username = get_option("wpr_smtpusername");
	 _wpr_set("smtp_username",$smtp_username);
	 
	 
	 $smtp_password = get_option("wpr_smtppassword");
	 _wpr_set("smtp_password",$smtp_password);
	 
	 $is_smtp_secure_ssl = (get_option("wpr_smtpsecure") == 'ssl');
	 _wpr_set("is_smtp_secure_ssl",$is_smtp_secure_ssl);

	 $is_smtp_secure_tls = (get_option("wpr_smtpsecure") == 'tls');
	 _wpr_set("is_smtp_secure_tls",$is_smtp_secure_tls);

	  $is_smtp_secure_none = (get_option("wpr_smtpsecure") == 'none');
	 _wpr_set("is_smtp_secure_none",$is_smtp_secure_none);
	
	
}


function _wpr_settings_post_handler()
{
	if (check_admin_referer("_wpr_settings"))
	{	
		update_option("wpr_address",$_POST['address']);
		update_option("wpr_hourlylimit",$_POST['hourly']);
		delete_option("wpr_smtpenabled");
		add_option("wpr_smtpenabled",(isset($_POST['enablesmtp']))?1:0);
	
	
		delete_option("wpr_smtphostname");
		add_option("wpr_smtphostname",$_POST['smtphostname']);
		delete_option("wpr_smtpport");
		add_option("wpr_smtpport",$_POST['smtpport']);
	
		delete_option("wpr_smtprequireauth");
		add_option("wpr_smtprequireauth",($_POST['smtprequireauth']==1)?1:0);
	
		delete_option("wpr_smtpusername");
		add_option("wpr_smtpusername",$_POST['smtpusername']);
		delete_option("wpr_smtppassword");
		add_option("wpr_smtppassword",$_POST['smtppassword']);
	
		delete_option("wpr_smtpsecure");
		if ($_POST['securesmtp']!='ssl')
		{
			$securesmtp = ($_POST['securesmtp']=='tls')?"tls":"none";
		}
		else
			$securesmtp = "ssl";		
		add_option("wpr_smtpsecure",$securesmtp);
	
				
				//notification settings
		$currentNotificationValue = get_option("wpr_notification_custom_email");
		switch($_POST['notification_email'])
		{				
			case 'customemail':
				$theNotificationEmail = $_POST['notification_custom_email'];
				delete_option('wpr_notification_custom_email');
				add_option('wpr_notification_custom_email',$theNotificationEmail);
			break;
			
			case 'adminemail':
				delete_option('wpr_notification_custom_email');
				add_option('wpr_notification_custom_email','admin_email');						
				break;
		}
					
					
	
				
				
		if ($_POST['tutorialenable']=='enabled' && get_option('wpr_tutorial_active') == 'off')
		{
			wpr_enable_tutorial();
		}
		else if ($_POST['tutorialenable']=='disabled' && get_option('wpr_tutorial_active') == 'on')
		{
			wpr_disable_tutorial();
		}
	
		if ($_POST['updatesenable'] == 'enabled'&& get_option('wpr_updates_active') == 'off')
		{
			wpr_enable_updates();
		}
		else if ($_POST['updatesenable'] == 'disabled' && get_option('wpr_updates_active') == 'on')
		{
			wpr_disable_updates();
		}
	
	
		$settings_url = _wpr_admin_url("settings");
		wp_redirect($settings_url);
		exit;
	}
}
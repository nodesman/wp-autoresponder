<?php
function wpresponder_install()
{
	global $wpdb;
    global $db_checker;
    $WPR_PLUGIN_DIR = $GLOBALS['WPR_PLUGIN_DIR'];
    
    //add new capacity
	$role = get_role( 'administrator' );
	$role->add_cap( 'manage_newsletters' );

    //1. set up the necessary database tables
    $db_checker->perform_check(); 

    //2. set wpr_last_post_date to the last post published prior to this activation. 
	delete_option("wpr_last_post_date");
	$args = array('orderby'=> 'date','order'=>'DESC','numberposts'=>1,'post_type'=>'post');
	$posts = get_posts($args);
	if (count($posts) >0 ) 
        // //if there are any posts at all, set the last post date to the last post since activation.
	{
		$post = $posts[0];
		$last_post_date = $post->post_date_gmt;
	}
	else //if there are absolutely no posts in the blog then use the current time.
	{
		$last_post_date = date("Y-m-d H:i:s",time());
	}
	add_option("wpr_last_post_date",$last_post_date);


        //the confirm email, confirmation email and confirmed subject templates.
	$confirm_subject = file_get_contents($WPR_PLUGIN_DIR."/templates/confirm_subject.txt");
	$confirm_body = file_get_contents($WPR_PLUGIN_DIR.'/templates/confirm_body.txt');
	$confirmed_subject = file_get_contents($WPR_PLUGIN_DIR."/templates/confirmed_subject.txt");
	$confirmed_body = file_get_contents($WPR_PLUGIN_DIR."/templates/confirmed_body.txt");

	if (!get_option("wpr_confirm_subject"))
		add_option("wpr_confirm_subject",$confirm_subject);
        else
            update_option("wpr_confirm_subject",$confirm_subject);

	if (!get_option("wpr_confirm_body"))
		add_option("wpr_confirm_body",$confirm_body);
        else
            update_option("wpr_confirm_body",$confirm_body);

	if (!get_option("wpr_confirmed_subject"))
		add_option("wpr_confirmed_subject",$confirmed_subject);
        else
            update_option("wpr_confirmed_subject",$confirmed_subject);

	if (!get_option("wpr_confirmed_body"))
		add_option("wpr_confirmed_body",$confirmed_body);
        else
            update_option("wpr_confirmed_body",$confirmed_body);
		//the cron variable.
	if (!get_option("wpr_next_cron"))
	 	add_option("wpr_next_cron",time()+300);
		

		
	//initialize options
	_wpr_initialize_options();
         
	createNotificationEmail();
	wpr_enable_tutorial();
	wpr_enable_updates();
	_wpr_schedule_crons_initial();

}



function _wpr_initialize_options()
{
	$options = $GLOBALS['initial_wpr_options'];
	
	foreach ($options as $option_name=>$option_value)
	{
		$current_value = get_option($option_name);
		if (empty($current_value))
		{
			add_option($option_name,$option_value);
		}
	}
	
}

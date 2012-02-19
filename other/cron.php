<?php

function wpr_cronschedules()
{
    $schedules = $GLOBALS['schedules'];
    return  $schedules;
}


function _wpr_schedule_crons_initial()
{
    $cron_schedules = $GLOBALS['wpr_cron_schedules'];
    foreach ($cron_schedules as $cron)
    {
	if (false == wp_get_schedule($cron['action'],$cron['arguments']))
	{
            if (count($cron['arguments']) >0 )
            {
	        //check if the cron has already been scheduled
                wp_schedule_event(time(),$cron['schedule'], $cron['action'], $cron['arguments']);
	    }
            else
	    {
                wp_schedule_event(time(), $cron['schedule'],$cron['action']);
            }
        }
    }
}

function _wpr_unschedule_crons()
{
	$cron_schedules = $GLOBALS['wpr_cron_schedules'];
	foreach ($cron_schedules as $cron)
	{
		if (count($cron['arguments']))
		{
			$next_scheduled = wp_next_scheduled($cron['action'],$cron['arguments']);
			wp_unschedule_event(time(), $cron['action'], $cron['schedule'],$cron['arguments']);
		}
		else
		{
			$next_scheduled = wp_next_scheduled($cron['action']);
			wp_unschedule_event(time(), $cron['action'], $cron['schedule']);
		}
	}
	
}


function _wpr_attach_cron_actions_to_functions()
{	
	add_action('_wpr_ensure_single_instances_of_crons','_wpr_ensure_single_cron_instances');
	//the cron that delivers email. 		
	//the tutorial series
	add_action('wpr_tutorial_cron','wpr_process_tutorial');
	//the cron that delivers plugin updates
	add_action('wpr_updates_cron','wpr_process_updates');
	
	add_action('_wpr_queue_management_cron','_wpr_queue_management_cron');
	
	add_action("_wpr_autoresponder_process","_wpr_autoresponder_process");
	add_action("_wpr_postseries_process","_wpr_postseries_process");
	add_action("_wpr_process_blog_subscriptions","_wpr_process_blog_subscriptions");
	add_action("_wpr_process_broadcasts","_wpr_process_broadcasts");
	add_action("_wpr_process_queue","_wpr_process_queue");
        add_action("_wpr_process_blog_category_subscriptions","_wpr_process_blog_category_subscriptions");
        
        
}

function is_wpr_cron($action)
{
    $wpr_crons = $GLOBALS['_wpr_crons'];
    return in_array($action,$wpr_crons);
}


function _wpr_ensure_single_cron_instances()
{
	$scheduled_crons = _get_cron_array();
	
	$scheduled_cron_list = array();
	foreach ($scheduled_crons as $next_scheduled_time => $cron)
	{
		foreach ($cron as $action=>$schedule)
		{
                    $keys = array_keys($schedule);
                    $hash = $keys[0];
                    $schedule = $schedule[$hash];
                    if (is_wpr_cron($action))
                    {
                        if (empty($schedule['schedule']))
                            continue;
                        $argument_hash = base64_encode(serialize($schedule['args']));
                        $key_name = $action."------".$argument_hash;
                        $scheduled_cron_list[$key_name][] = $next_scheduled_time;
                    }
		}
		
	}

        // make a list of crons that are in the cron schedule array $GLOBALS['wpr_cron_schedules'] but are not scheduled
	$cron_sched = $GLOBALS['wpr_cron_schedules'];
	$must_exist_crons = array();
	foreach ($cron_sched as $cron)
	{
		$action = $cron['action'];
		$serialized = serialize($cron['arguments']);
		$encoded = base64_encode($serialized);
		$key_name = $action."------".$encoded;
		$must_exist_crons[$key_name] = $cron;
	}
	$scheduled_crons = array_keys($scheduled_cron_list);
	//ensure that all crons in cron array are in the list
	$missing_crons = array();
	foreach ($must_exist_crons as $key=>$must_exist_cron)
	{
		if (!in_array($key,$scheduled_crons))
		{
			$missing_crons[] = $must_exist_cron;
		}
	}

	//are there any missing crons?
	if (count($missing_crons) > 0)
	{
		//schedule them
		foreach ($missing_crons as $missing_cron)
		{
			$action = $missing_cron['action'];
			$schedule = $missing_cron['schedule'];
			$arguments = $missing_cron['arguments'];
			if (count($arguments) >0)
				wp_schedule_event(time(),$schedule,$action,$arguments);
			else
				wp_schedule_event(time(),$schedule,$action);
		}

	}

        
        //now remove duplicate scheduled crons specified in the cron schedule array.
	foreach ($scheduled_cron_list as $key=> $schedule_times)
	{
		if (count($schedule_times) > 1)
		{
			$parts = explode("------",$key);
			$action = $parts[0];
			$serialzied = base64_decode($parts[1]);
			$arguments = unserialize($serialzied);
			
			array_shift($schedule_times);

			foreach ($schedule_times as $scheduled_time)
			{
				if (count($arguments) > 0)
					wp_unschedule_event($scheduled_time,$action,$arguments);
				else
					wp_unschedule_event($scheduled_time,$action);
			}
		}
	}
}
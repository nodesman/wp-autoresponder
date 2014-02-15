<?php




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

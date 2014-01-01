<?php

function _wpr_admin_notices_get()
{
	$option = get_option("_wpr_admin_notices");
	$an_decoded = base64_decode($option);
	$array = unserialize($an_decoded);
	return $array;
}

function _wpr_admin_notice_delete($name)
{
	$notices = _wpr_admin_notices_get();
	unset($notices[$name]);
	_wpr_admin_notices_set($notices);
}

function _wpr_admin_notice_get($name)
{
	$notices = _wpr_admin_notices_get();
	
	if (isset($notices[$name]))
		return $notices[$name];
	else
		return false;
}

function _wpr_admin_notice_set($name,$value)
{
	$notices = _wpr_admin_notices_get();
	$notices[$name] = $value;
	_wpr_admin_notices_set($notices);
}

function _wpr_admin_notice_exists($name)
{
	$value = 	_wpr_admin_notice_get($name);
	return (!(!$value));
}

function _wpr_admin_notices_set($array_of_notices)
{
    if (!is_array($array_of_notices))
        return;
	$serialized = serialize($array_of_notices);
	$an_encoded = base64_encode($serialized);	
	delete_option("_wpr_admin_notices");
	add_option("_wpr_admin_notices",$an_encoded);
}


function _wpr_admin_notices_show()
{
	$notices = _wpr_admin_notices_get();
	if (is_array($notices) && count($notices) > 0)
	{
	?>
	<div class="error fade">
    <ul style="list-style: disc; padding-left:20px; margin:10px;">
    <?php
		foreach ($notices as $name=>$notice)
		{
			?>
			<li><?php echo $notice ?></li>
			<?php
		}
	?>
    </ul>
    </div>
    <?php
	}
}

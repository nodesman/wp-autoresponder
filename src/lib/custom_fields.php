<?php
function _wpr_newsletter_custom_fields_get($id)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where id=$id";
	$result = $wpdb->get_results($query);
	return $result[0];	
}

function _wpr_newsletter_all_custom_fields_get($id)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where nid=$id";
	$result = $wpdb->get_results($query);
	return $result;
}

function _wpr_newsletter_custom_fields_update($info)
{
	global $wpdb;
	$info = (object) $info;
	$query = "UPDATE  ".$wpdb->prefix."wpr_newsletters_custom_fields SET name='$info->name', type='$info->type', enum='$info->enum', label='$info->label' where id='$info->id';";	
	$result = $wpdb->query($query);
}


function getCustomField($cid,$name="",$value="")
{

	global $wpdb;

	$theField = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where id=$cid");

	$theField = $theField[0];	
	if ($name=="")
	{
		$name = $theField->name;
	}
	switch ($theField->type)
	{
		case 'text':
		
		return "<input type=\"text\" name=\"$name\" value=\"$value\"/>";
		
		break;
		case 'enum':
		
		$selectField = "<select name=\"$name\">\n<option value=\"\">&lt;Not Specified&gt;</option>";
		$options = explode(",",$theField->enum);
        $value = trim($value);
		foreach ($options as $option)
		{
            $option = trim($option);
			$selectField .="<option ".(($option==$value)?'selected="selected"':"").">$option</option>\n";
		}
		$selectField .="</select>";
		return $selectField;
		
		break;
		case 'hidden':

		return "<input type=\"text\" name=\"$name\" value=\"$value\"/>";
		
		break;
	
		
	}
}


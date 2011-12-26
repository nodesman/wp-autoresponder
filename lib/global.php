<?php
function validateEmail($email)

{
    //test with regular expressions.
    return eregi('^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$',$email);
}

function _wpr_option_get($name)
{
    if (!$GLOBALS['_wpr_options'] )
    {
        $GLOBALS['_wpr_options'] = get_option("_wpr_options");
    }
    $options = $GLOBALS['_wpr_options'];
    return $options[$name];
}

function _wpr_option_set($name,$value)
{
    $options = get_option("_wpr_options");
    $options[$name] = $value;
    update_option("_wpr_options",$options);
}

function wpr_sanitize($string,$html=true)
{
	if ($html)
		$string = strip_tags($string);
	$string = trim($string);
	if (get_magic_quotes_gpc())
	{
		return $string;	
	}
	else
	{
		return addslashes($string);	
	}
}

function wpr_replace_tags($sid,&$subject,&$body,$additional = array())
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers WHERE id='$sid'";
	$subscriber = $wpdb->get_results($query);
	$subscriber = $subscriber[0];
	$nid = $subscriber->nid;
	
	$newsletter = new Newsletter($nid);

	$parameters = array();

	//newsletter name

	$newsletterName = $newsletter->getNewsletterName();

	$parameters['newslettername'] = $newsletterName;

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where nid='$nid'";

	$custom_fields = $wpdb->get_results($query);

	

	//blog name 

	$parameters['sitename'] = get_bloginfo("name");;

	//blog url

	$parameters['homeurl'] = get_bloginfo("home");

	//subscriber name

	$parameters['name'] = $subscriber->name;

	//the address of the sender (as required by can spam)

	$parameters['address'] = get_option("wpr_admin_address");

	//the email address

	$parameters['email'] = $subscriber->email;

	//admin email

	$parameters['adminemail'] = get_option('admin_email');

	

	$query = "select * from ".$wpdb->prefix."wpr_subscribers_$nid where id=$id;";

	$subscriber = $wpdb->get_results($query);

	$subscriber = $subscriber[0];

	

	//custom fields defined by the administrator

	foreach ($custom_fields as $custom_field)
	{
		$name = $custom_field->name;
		$parameters[$custom_field->name] = $subscriber->{$name};
	}

	

	$parameters = array_merge($parameters,$additional);

	

	foreach ($parameters as $name=>$value)

	{

		$subject = str_replace("[!$name!]",$value,$subject);

		$body =str_replace("[!$name!]",$value,$body);		

	}

	

}

function ByteSize($bytes)  
{

	$size = $bytes / 1024; 
	if($size < 1024) 
	{ 
		$size = number_format($size, 2); 
		$size .= ' KB'; 
	}  
	else  
	{ 
		if($size / 1024 < 1024)  
		{ 
			$size = number_format($size / 1024, 2); 
			$size .= ' MB'; 
		}  
		else if ($size / 1024 / 1024 < 1024)   
		{ 
			$size = number_format($size / 1024 / 1024, 2); 
			$size .= ' GB'; 
		}  
	} 
	return $size; 
} 

/*
	
This function creates temporary tables to simplify the process of fetching the
subscribers and their custom field values from the database table.

*/
	
function wpr_make_subscriber_temptable($nid)
{
	global $wpdb;
	//create the main table for the other purposes.
	//get a list of all custom fields and then form the 
	$query = "select * from ".$wpdb->prefix."wpr_custom_fields where nid=$nid";

	$cfields = $wpdb->get_results($query);

	

	//get the columns of the subscribers table.
	$query = "show columns from ".$wpdb->prefix."wpr_subscribers";
	$columns = $wpdb->get_results($query);
	$subsTableColumnList = array();
	foreach ($columns as $column)

	{

		$subsTableColumnList[] = $column->Field;

	}

	

	$count = count($cfields);

	$finaltable = $count;

	$size = strlen(sprintf("%b",$count));

	$formatSpec = "%'0".$size."b";

	//used to specify the alias for the table in the table join to make the view.

	$fields = array();

	$tables = array();

	$args = array();

	$finaltable = sprintf($formatSpec,$finaltable);

	$mainTableAlias = str_replace("1","b",str_replace("0","a",$finaltable));

	if (count($cfields) >0)

	{

		foreach ($cfields as $num=>$cfield)

		{

			$name = $cfield->name;

			$number = sprintf($formatSpec,$num);

			//name of field

			$tableAlias = str_replace("1","b",str_replace("0","a",$number)); //replace 0=a , 1=b

			$table[$name] = $tableAlias;

			$fields[] = $tableAlias.".$name $name";

			$args[] = $tableAlias.".id=".$mainTableAlias.".id";

			

		}

	}

	$lastIndex = count($table)-1;

	

	//now to add the wp_wpr_subscribers table's columns.. i may change the structure later on.. so i do this.

	foreach ($subsTableColumnList as $name)

	{

		$fields[] = $mainTableAlias.".$name $name";

	}

	//the list of fields in the view.

	$fieldlist = implode(", ",$fields);

	$prefix = $wpdb->prefix;

	//the table names and their aliases

	$tablenames = array();

	if (count($table) > 0)

	{

		foreach ($table as $name=>$alias)

		{

			$tablenames[]  = $prefix."wpr_subscribers_".$nid."_".$name." $alias";

		}

	}

	$tablenames[] = $prefix."wpr_subscribers ".$mainTableAlias;



	if (count($tablenames) > 1)

		$tablenames = implode(", ",$tablenames);

	else

		$tablenames = $tablenames[0];

	if (count($args) > 0)

	{

		$joinsList = implode(" AND ",$args);

		$joiningConj = " AND ";

	}

	else

	{

		$joinsList = "";

		$joiningConj = "";

	}

	

	$joinsList .= $joiningConj.$mainTableAlias.".nid=$nid";

	

	$select = "SELECT $fieldlist FROM $tablenames WHERE $joinsList";
	$query = "CREATE TEMPORARY TABLE IF NOT EXISTS ".$prefix."wpr_subscribers_$nid as $select;";
	$wpdb->query($query);

}

function wpr_create_temporary_tables($nid)

{

	global $wpdb;

	$wpdb->show_errors();

	$customFieldListQuery = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where nid=$nid";

	$customFields = $wpdb->get_results($customFieldListQuery);

	if (count($customFields ) >0 )

	{

		foreach ($customFields as $field)

		{

			$name = $field->name;

			$query = "CREATE TEMPORARY TABLE IF NOT EXISTS ".$wpdb->prefix."wpr_subscribers_".$nid."_".$name." as SELECT a.sid id, a.value $name from ".$wpdb->prefix."wpr_custom_fields_values a, ".$wpdb->prefix."wpr_custom_fields b where a.nid=$nid and a.cid=b.id and b.name='$name';";

			$wpdb->query($query);

		}

		

		return true;

	}

	else

	{

		return false;

	}

}


function wpr_place_tags($sid,&$strings,$additional=array())
{	
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers WHERE id='$sid'";
	$subscriber = $wpdb->get_results($query);

	$subscriber = $subscriber[0];

	$nid = $subscriber->nid;

	$id = $subscriber->id;

	

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters where id='$nid'";

	$newsletter = $wpdb->get_results($query);

	$newsletter = $newsletter[0];

	$parameters = array();

	//newsletter name

	$newsletterName = $newsletter->name;

	$parameters['newslettername'] = $newsletterName;

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where nid='$nid'";

	$custom_fields = $wpdb->get_results($query);

	

	//blog name 

	$parameters['sitename'] = get_bloginfo("name");d;

	//blog url

	$parameters['homeurl'] = get_bloginfo("home");

	//subscriber name

	$parameters['name'] = $subscriber->name;

	//the address of the sender (as required by can spam)

	$parameters['address'] = get_option("wpr_admin_address");

	//the email address

	$parameters['email'] = $subscriber->email;

	//admin email

	$parameters['adminemail'] = get_option('admin_email');

	//custom fields defined by the administrator

	$query = "select * from ".$wpdb->prefix."wpr_subscribers_$nid where id=$id;";

	$subscriber = $wpdb->get_results($query);

	$subscriber = $subscriber[0];

	

	foreach ($custom_fields as $custom_field)
	{
		$name = $custom_field->name;
		$parameters[$custom_field->name] = $subscriber->{$name};
	}
	
	$parameters = array_merge($parameters,$additional);
	foreach ($parameters as $tag=>$value)
	{
		foreach ($strings as $index=>$string)
		{	
			$strings[$index] = str_replace("[!$tag!]",$value,$string);

		}
	}

}
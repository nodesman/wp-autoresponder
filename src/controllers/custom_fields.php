<?php

function _wpr_custom_fields_handler()
{
	$action = @$_GET['cfact'];
	switch ($action)
	{
		case 'create':
		  _wpr_newsletter_custom_fields_create();
		break;
		case 'edit':
			_wpr_newsletter_custom_fields_edit();
		break;
		case 'delete':
			_wpr_newsletter_custom_fields_delete();
		break;
		case 'manage':
			_wpr_newsletter_custom_fields_list();
			break;
		default:
		  wpr_customfields();
		  break;
		
	}
}

function _wpr_newsletter_custom_fields_edit()
{
	global $wpdb;
	$error="";
	$id = $_GET['cid'];
	if (isset($_POST['name']))
	{
		$params['nid'] = $nid = $_GET['nid'];
		$params['id'] = $cid = $_POST['id'];
		$params['name'] = $name = $_POST['name'];
		$params['type'] = $type = $_POST['type'];
		$params['label'] = $label = $_POST['label'];
		$params['enum'] = $enum = $_POST['enum'];
		if ($name && $type)
		{
			if ($type == "enum")
			{
				if (count(explode(",",$enum)) <= 1)
				{
					$error = "Not enough options given for multiple choice field or invalid format";
				}
			}
			else
			{
				$enum='';
			}
			if (!$error)
			{
				$query = "UPDATE `".$wpdb->prefix."wpr_custom_fields` SET `type`='$type',`label`='$label',`enum`='$enum' where id='$cid';" ;
				 $wpdb->query($query);
				wp_redirect("admin.php?page=_wpr/custom_fields&cfact=manage&nid=$nid");
				exit;
			}
		}
		else
		{
			$error = "The name and type fields are required";
		}
		$params = (object) $params;
	}
	
	if (!isset($params))
		$params = _wpr_newsletter_custom_fields_get($id);
		
	_wpr_set("_wpr_view","custom_fields_form");
	_wpr_set("parameters",$params);
	_wpr_set("error",$error);
	_wpr_set("title","Edit Custom Field");
	_wpr_set("buttontext","Save");
	_wpr_set("nameIsHidden",true);
}

function _wpr_newsletter_custom_fields_create()
{
	global $wpdb;
	$parameters = (object) array();
	$error="";
	if (isset($_POST['name']))
	{

		$nid = $_GET['nid'];
		$name = $_POST['name'];
		$type = $_POST['type'];
		$label = $_POST['label'];
		$enum = $_POST['enum'];
		if ($name && $type && $label)
		{
			if ($type == "enum")
			{
				if (!count(explode(",",$enum)) > 1)
				{
					$error = "Not enough options given for multiple choice field or invalid format";
				}
			}
			else
			{
				$enum='';
			}
			 preg_match_all("@[^a-z0-9_]@",$name,$match);


			if (count($match[0]) > 0)
			{
				$error = "Only lowercase characters and underscore is allowed in name";
			}						   
								 
			if (!$error)
			{
				$query = "INSERT INTO `".$wpdb->prefix."wpr_custom_fields` (`nid`,`type`,`name`,`label`,`enum`) values ('$nid','$type','$name','$label','$enum');" ;
				$wpdb->query($query);
					
				//get the id of this field
				$query = "SELECT id from ".$wpdb->prefix."wpr_custom_fields where nid=$nid and name='$name'";
				$cf = $wpdb->get_results($query);
				$cid = $cf[0]->id;
				
				$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers where nid=$nid";
				$subscribers = $wpdb->get_results($query);
				if (count($subscribers) > 0)
				{
					$qTemplate = " ( '$nid','$cid','%%sid%%','') ";
					$theQuery = "";
					foreach ($subscribers as $subscriber)
					{
						$theQuery[] = str_replace("%%sid%%",$subscriber->id,$qTemplate);
					}
					$theQuery = implode(", ",$theQuery);
					$theQuery = "INSERT INTO ".$wpdb->prefix."wpr_custom_fields_values (nid, cid, sid, value) VALUES ".$theQuery;
					$wpdb->query($theQuery);
				}
				wp_redirect("admin.php?page=_wpr/custom_fields&cfact=manage&nid=$nid");
				exit;
			}
			$parameters = (object) array();
			$parameters->name = $name;
			$parameters->label = $label;
			$parameters->type = $type;
			$parameters->enum = $enum;
		}
		else
		{
			$error = "The name, label and type fields are required fields";
		}
	}
	
	_wpr_set("_wpr_view","custom_fields_form");
	_wpr_set("parameters",$parameters);
	_wpr_set("error",$error);
	_wpr_set("title","Create Custom Field");
	_wpr_set("buttontext","Create Custom Field");
	_wpr_set("nameIsHidden",false);
}


function _wpr_newsletter_custom_fields_delete()
{
	global $wpdb;
	$cid = $_GET['cid'];
	$nid = $_GET['nid'];
	if (isset($_GET['confirm']) && $_GET['confirm'] == 'true')
	{

		$query = "DELETE FROM ".$wpdb->prefix."wpr_custom_fields WHERE id='$cid'";
		$wpdb->query($query);
		wp_redirect("admin.php?page=_wpr/custom_fields&cfact=manage&nid=$nid");
		exit;
    }
   $field = _wpr_newsletter_custom_fields_get($cid);
   _wpr_set("_wpr_view","delete_custom_fields");
   _wpr_set("field",$field);
   
}

function _wpr_newsletter_custom_fields_list()
{
	global $wpdb;
	$id = intval($_GET['nid']);
	$newsletter = _wpr_newsletter_get($id);
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where nid=$id";
	$result = $wpdb->get_results($query);
	_wpr_set("newsletter",$newsletter);
	_wpr_set("newsletterCustomFieldList",$result);
	_wpr_set("_wpr_view","newsletter_custom_fields_list");
}



function _wpr_custom_field_name($name,$options)
{
	switch ($name)
	{
		case 'text':
		  return 'One Line Text';
		  break;
		case 'enum':
		  return 'Multiple Choice'." ($options)";
		  break;
	}
	
}


function wpr_customfields()
{
	global $wpdb;
     
      $query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters";
      $newsletterList = $wpdb->get_results($query);
	  _wpr_set("newsletterList",$newsletterList);
	  _wpr_set("_wpr_view","custom_fields_list");
}


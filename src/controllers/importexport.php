<?php

/*
TODO:

1. After the user uplaods the csv file and then identifies the columns in the csv file, the custom field values in the csv file should be validated. Forexample, it the newsletter to which we are importing has an enumared custom field called gender(male,female), and we are trying to import some column in the csv - then the plugin should verify if all the values for that column is either 'male' or 'female'. .  Importing should not be allowed if it isn't.

2. During the import process, if the user already exists, the import for that user will fail and an empty field will be inserted to teh table. 

3. When creating the blog/followup subscription for the email subscriber just inserted, the procedure currently doesnt check if such a subscription already exists. This is necessary in the case that the subscriber with that email address already existed. 

*/
$importExportSessionName = "WPR-Import";

function _wpr_importexport_handler()
{
    global $importExportSessionName;
    session_name($importExportSessionName);
	session_start();

	$subact = $_GET['subact'];
	switch ($subact)
	{
		case 'step1':
		   _wpr_import_second_step();
		break;
		case 'step2':
		_wpr_import_third_step();
		break;
		case 'step3':
			_wpr_import_fourth_step();
		break;

		case 'step4':
           _wpr_import_fifth_step();
        break;
              
		case 'finished':
		_wpr_import_finished();
		break;

		default:
		_wpr_import_export_home();		
	}
}

add_action("_wpr_wpr_subscriber_export_post","_wpr_export");

function _wpr_export()
{
	$nid = $_POST['newsletter'];
	export_csv($nid);
	exit;
}


function _wpr_import_finished()
{
	$newsletter = _wpr_newsletter_get($_SESSION['wpr_import_newsletter']);
	_wpr_set("newsletter",$newsletter);
	_wpr_set("_wpr_view","import.finished");
}
	function export_csv($nid)
	{
		global $wpdb;
		$prefix = $wpdb->prefix;

		
		set_time_limit(0);
		
		$nid = intval($nid);
		if ($nid == 0)
			return;
		//does the newsletter exist?
		$query = "SELECT COUNT(*) FROM ".$prefix."wpr_newsletters where id=$nid";
		$results = $wpdb->get_results($query);
		if (count($results) == 0)
		{

			exit;
		}
		//if none of these error conditions occur, then start exporting:
		
		//fetch all custom fields associates with this newsletter
		$query = "select * from ".$prefix."wpr_custom_fields where nid=$nid";
		$results = $wpdb->get_results($query);
		$fieldHeaders = array();
		if (count ($results))
		{
			$customfields= array();
			foreach ($results as $field)
			{
				$customfields[] = $field->name;

			}
		}
		//get all the custom fields of this newsletter:
		
		$getCustomFieldsQuery = sprintf("SELECT * FROM %swpr_custom_fields WHERE nid=%d ORDER by id",$wpdb->prefix,$nid);
		//get all the custom field values for the first 1000 subscribers. 
		$customFields= $wpdb->get_results($getCustomFieldsQuery);
		
		//form the array for the custom field row names
		
		$fields = array();
		
		foreach ($customFields as $field) {
			$fields[$field->id] = $field->label;
		}

		//number of susbcribers
		$getNumberOfSubscribersQuery = sprintf("SELECT COUNT(*) num FROM %swpr_subscribers WHERE nid=%d",$wpdb->prefix,$nid);
		$numberOfSubscribersRes = $wpdb->get_results($getNumberOfSubscribersQuery);
		$number = $numberOfSubscribersRes[0]->num;
		
		$perIterationSize=100;
		//process 100 subscribers at a time
		$numberOfIterations = ceil($number/$perIterationSize);
		$index = 0;
		header ("Content-disposition: attachment; filename=export_$nid.csv");
			
		//output the header
		$fieldNamesArray = $fields;
		//array_walk($fieldNamesArray, "_wpr_export_escape_field_value");
		//process 100 at a time
		
		
        $fp = fopen("php://output","w");
		array_unshift($fieldNamesArray,"E-mail");
		array_unshift($fieldNamesArray,"Name"); 
                fputcsv($fp,$fieldNamesArray);
		for ($iter=0;$iter<$numberOfIterations;$iter++)
		{

			$start = $perIterationSize*$iter;
			
			$runtimeTableQuery = sprintf("SELECT * FROM %swpr_subscribers WHERE nid=%d ORDER BY id LIMIT %d, %d",$wpdb->prefix,$nid,$start, $perIterationSize);

			$subscribers = $wpdb->get_results($runtimeTableQuery);
			
			foreach ($subscribers as $subscriber) {
				
				$getCustomFieldValuesQuery = sprintf("SELECT * FROM %swpr_custom_fields_values WHERE sid=%d ORDER BY cid",$wpdb->prefix, $subscriber->id);
				
				$customFieldValues= $wpdb->get_results($getCustomFieldValuesQuery);
				
				$current = array();
				foreach ($customFieldValues as $id=>$value) {
					$current[$value->cid]=$value->value;
				}
				
				//array_walk($current,"_wpr_export_escape_field_value");
				//form the array of id value pairs
				$valueSet = array();
				$valueSet[] = $subscriber->name;
				$valueSet[] = $subscriber->email;
				
				foreach ($fields as $cid=>$name) 
				{
					$valueSet[]= $current[$cid];
				}
				fputcsv($fp,$valueSet);
			}
		}
		exit;
	
	}
function _wpr_import_second_step()
{
    global $importExportSessionName;
    session_name($importExportSessionName);
    session_start();

	if (isset($_SESSION['wpr_import_newsletter']))
	{
        $nid = $_SESSION['wpr_import_newsletter'];
		$autoresponders = Autoresponder::getAllAutoresponders();
		_wpr_set("autoresponderList",$autoresponders);
        $postSeries = _wpr_postseries_get_all();
        _wpr_set("postseriesList",$postSeries);
		_wpr_set("_wpr_view","import.secondstep");

	}
	else
	{
		wp_redirect("admin.php?page=_wpr/importexport");
	}
}

function _wpr_import_third_step()
{
      $args = array(
					'type'                     => 'post',
					'child_of'                 => 0,
					'orderby'                  => 'name',
					'order'                    => 'ASC',
					'hide_empty'               => false,
					'hierarchical'             => 0);

    $categories = get_categories($args);

    _wpr_set("categoryList",$categories);
    _wpr_set("_wpr_view","import.thirdstep");
}

function _wpr_import_export_home()
{

    $newsletters = _wpr_newsletters_get();
    _wpr_set("newslettersList",$newsletters);
    _wpr_setview("importexport");
}

add_action("_wpr_wpr_import_first_post","_wpr_import_first_post");
add_action("_wpr_wpr_import_followup_post","_wpr_import_followup_post");
function _wpr_import_first_post()
{
    global $importExportSessionName;
    session_name($importExportSessionName);
    session_start();

	$newsletter= trim($_POST['newsletter']);
	$_SESSION['wpr_import_newsletter'] = $newsletter;
	?>
<script>window.location='admin.php?page=_wpr/importexport&subact=step1';</script>
    <?php
    exit;
}

function _wpr_import_followup_post()
{
    global $importExportSessionName;
    session_name($importExportSessionName);
    session_start();
    $_SESSION['wpr_import_followup'] = $_POST['followup'];
    do_action("_wpr_import_post_first_step_handler");
    ?>
    <script>window.location='admin.php?page=_wpr/importexport&subact=step2';</script>
        <?php
    exit;
}
add_action("_wpr_wpr_import_blogsub_post","_wpr_import_blogsub_post");
add_action("_wpr_wpr_import_upload_post","_wpr_import_upload");

function _wpr_import_blogsub_post()
{
    global $importExportSessionName;
    session_name($importExportSessionName);
    session_start();
    $_SESSION['_wpr_import_blogsub'] = $_POST['blogsubscription'];
    wp_redirect("admin.php?page=_wpr/importexport&subact=step3");
    exit;
}


function _wpr_import_fourth_step()
{
    _wpr_set("_wpr_view","import.fourthstep");
}



function _wpr_import_upload()
{
    global $importExportSessionName;
    session_name($importExportSessionName);
	ini_set('auto_detect_line_endings', true);
    session_start();
    if ($_FILES['csv']['error']==UPLOAD_ERR_OK)
    {
		
        $_SESSION['_wpr_csv_file'] = file($_FILES['csv']['tmp_name']);
        wp_redirect('admin.php?page=_wpr/importexport&subact=step4');
        exit;
    }
    else
    {        
        $_SESSION['_wpr_import_error']="File upload failed";
        _wpr_set('_wpr_view','import.fourthstep');
    }
}

function _wpr_import_fifth_step()
{
    global $importExportSessionName;
    session_name($importExportSessionName);
    session_start();
    $csv = $_SESSION['_wpr_csv_file'];


    $count=0;
	
	$sample = array_slice($csv,0,100);
	
	$csv = splitToArray($sample);


    $customFields = _wpr_newsletter_all_custom_fields_get($_SESSION['wpr_import_newsletter']);



    $columnsRequired = array('name'=>'Name',
        'email'=>'E-Mail Address');

    foreach ($customFields as $field)
    {
        $columnsRequired[$field->name] = $field->label;
    }
    _wpr_set("list",$csv);
    _wpr_set("columns",$columnsRequired);
    _wpr_set("_wpr_view","import.fifthstep");
}

function splitToArray($data)
{
	$csvcontent = implode("\n",$data);
	$fp = tmpfile();
	fwrite($fp,$csvcontent);
	rewind($fp);
	$theoutput=array();

	while (!feof($fp))
	{
		$row = fgetcsv($fp);
		
		//damn the empty rows.
		if (!is_array($row) || empty($row) || strlen(implode("",$row))==0)
			continue;
		else
		{
			array_push($theoutput,$row);
		}
	}
	return $theoutput;
}

function _wpr_wpr_import_finish_post()
{
	//start importing.
    global $importExportSessionName;
    global $wpdb;
    session_name($importExportSessionName);
    session_start();
	$prefix = $wpdb->prefix;
	$arrayIndexes = array();
	
	$subscribers = &$_SESSION['_wpr_csv_file'];
	
	$subscribers = splitToArray($subscribers);
	

	foreach ($_POST as $name=>$value)
	{
		if (!empty($value))
			$arrayIndexes[$value] = str_replace("column_","",$name);
	}
	$nid = $_SESSION['wpr_import_newsletter'];

	$indexOfId = count($subcribers[0]);
	
	foreach ($subscribers as $index=>$subscriber)
	{

	
		$name = addslashes(trim($subscriber[$arrayIndexes['name']]));
		$email = trim($subscriber[$arrayIndexes['email']]);
                if (!validateEmail($email))
                    continue;		
		$currentSid = _wpr_subsciber_add_confirmed(array('nid'=>$nid,'name'=>$name,'email'=>$email));
		$subscribers[$index][$indexOfId]= $currentSid;

                do_action("_wpr_import_subscriber_added",$currentSid);
		//add all of the subscriber's followup subscriptions														 
	}
	
	if ($_SESSION['wpr_import_followup'] !="none")
	{
		$time = time();
		if (preg_match("@^autoresponder_[0-9]+@",$_SESSION['wpr_import_followup']))
		{
			$followuptype = "autoresponder";
			$eid = str_replace("autoresponder_","",$_SESSION['wpr_import_followup']);	
		}
		elseif (preg_match("@^postseries_[0-9]+@",$_SESSION['wpr_import_followup']))
		{
			$followuptype = "postseries";
			$eid = str_replace("postseries_","",$_SESSION['wpr_import_followup']);	
		}
		
		if (count($subscribers)>0)
		{
			foreach ($subscribers as $subscriber)
			{
				$currentSid = $subscriber[$indexOfId];
			
				$query = "INSERT INTO ".$prefix."wpr_followup_subscriptions (sid, type, eid,  sequence, last_date,doc) values ('$currentSid','$followuptype','$eid',-1,0,'$time');";
				$wpdb->query($query);
			}
		}
		
	}
	
	if ($_SESSION['_wpr_import_blogsub']!="none")
	{
		$subtype = $_SESSION['_wpr_import_blogsub'];
		$cat = 0;

		if (preg_match("@category_[0-9]+@",$subtype ))
		{
			$cat = str_replace("category_","",$subtype);
			$subtype = "cat";
		}
		else
		{
			$subtype="all";
			$cat=0;
		}
		
		foreach ($subscribers as $subscriber)
		{
			$currentSid=$subscriber[$indexOfId];
                        $deleteExistingSubscriptionQuery = sprintf("DELETE FROM %swpr_blog_subscription WHERE sid=%d AND type='%s' AND catid=%d",$wpdb->prefix,$currentSid,$subtype,$cat);
                        $wpdb->query($deleteExistingSubscriptionQuery);
			$subscriptionQuery = "INSERT INTO ".$prefix."wpr_blog_subscription (sid, type, catid) values ('$currentSid','$subtype','$cat');";
			$wpdb->query($subscriptionQuery);
		}
	}
	
	//custom fields..
	
	//fetch all of this newsletter's custom fields
	
	$query = "SELECT id,name from ".$prefix."wpr_custom_fields where nid=$nid";
	$customFieldsOfNewsletter = $wpdb->get_results($query);
	
		
	//create an array that we can use easily.
	foreach ($customFieldsOfNewsletter as $cust)
	{
		$customFields[$cust->name] = $cust->id;
	}
	
	$customFieldsAvailable=array();
	$customFieldsToNull=array();
	if (count($customFields) >0)
	{
		foreach ($customFields as $fieldName=>$fieldId)
		{
			if (array_key_exists($fieldName,$arrayIndexes))
			{
				$customFieldsAvailable[$fieldName] = $fieldId;
			}
			else
			{
				$customFieldsToNull[$fieldName] = $fieldId;
			}
		}
	
		foreach ($subscribers as $index=>$subscriber)
		{
			//first gather the relevant subscriber information
			$sid = $subscriber[$indexOfId];//we inserted the subscriber's id in the end. 
			foreach ($customFieldsAvailable as $fieldName=>$fieldId)
			{
				$customFieldIndex = $arrayIndexes[$fieldName];
				$value = trim($subscriber[$customFieldIndex],'"');
				$customFieldValueInsertQuery = "INSERT INTO ".$prefix."wpr_custom_fields_values (nid,sid,cid,value) values ('$nid','$sid','$fieldId','$value');";
				$wpdb->query($customFieldValueInsertQuery);
			}
			
			foreach ($customFieldsToNull as $fieldName=>$fieldId)
			{
				$value="";
				$customFieldNullInsertQuery = "INSERT INTO ".$prefix."wpr_custom_fields_values (nid,sid,cid,value) values ('$nid','$sid','$fieldId','$value');";
				$wpdb->query($customFieldNullInsertQuery);
			}
		}
	}
	
	
	foreach ($_SESSION as $name=>$value)
	{
		if (preg_match("@wpr_@",$name))
			unset($_SESSION[$name]);
	}
	
	wp_redirect("admin.php?page=_wpr/importexport&subact=finished");
        exit;
	//fetch the ids of the custom fields we are going to insert.
}


add_action("_wpr_wpr_import_finish_post","_wpr_wpr_import_finish_post");

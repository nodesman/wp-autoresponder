<?php

global $wpdb;

if ($_GET['subscribed'] == "true")
{
	require "templates/confirm_subscription.html";
	exit;
}

function error($error)
{

	?>

<div style="font-family: Arial">
  <h2 align="center">An Error Has Occured</h2>
  <div align="center">
    <div style="width: 400px; padding: 10px; text-align: left; background-color: #336699; color: #fff; font-weight:bold; font-family: Arial; border: 1px solid #ccc;"> <?php echo $error ?> </div>
    <a href="javascript:window.history.go(-1);">Click Here To Go Back</a> </div>
</div>
<?php
	// wp_credits(); // throws an fatal error ?! 
	exit;

}



/*

 * Used to validate an email address

 */

$success = (boolean) (isset($_POST['newsletter']) && isset($_POST['name']) && isset($_POST['email']));

if ($success)
{
	$name = wpr_sanitize($_POST['name']);
	$email = strtolower(wpr_sanitize($_POST['email']));
	$followup = wpr_sanitize($_POST['followup']);
	$newsletter = (int) wpr_sanitize($_POST['newsletter']);
	$bsubscription = wpr_sanitize($_POST['blogsubscription']);
	$responder = (int) wpr_sanitize($_POST['responder']);
	$bcategory = (int) wpr_sanitize($_POST['cat']);
	$return_url = wpr_sanitize($_POST['return_url']);
	$commentfield = $_POST['comment'];
	
	if (!empty($commentfield))
	{
		//stupid spambot spamming my subscription forms. damn the bot!
		exit;
	}
	
	do_action("_wpr_subscriptionform_prevalidate");

	$skiplist = array("name","email","followup","blogsubscription","cat","return_url","responder");
	
	$query = $wpdb->prepare("SELECT count(*) number_of FROM {$wpdb->prefix}wpr_newsletters where id=%d",$newsletter);	
	$results = $wpdb->get_results($query);	
	$count = $results[0]->number_of;	
	if ($count == 0)
	{  
	     error("The newsletter to which you are trying to subscribe doesn't exist in our records.");
	}

	$fid = (int) $_POST['fid'];
	if (!empty($followup) && !in_array($followup,array("autoresponder","postseries")))
	{
		  error('The form you filled out is coded improperly. The followup subscription hidden fields did not have a valid value.');
		  exit;
	}

        if (empty($name))

            {

            error('You have not filled the name field in the subscription form. Please <a href="javascript:window.history.go(-1);">go back</a> and enter your name in the name field.');

        }

	//start validations

	if (!validateEmail($email))   //the expression is just for now.

	{

		error('<center><div style="font-size: 20px;">Invalid Email Address</div></center> The e-mail address you mentioned is not a valid e-mail address. Please <a href="javascript:window.history.go(-1);">go back</a> and re-enter the e-mail in the correct format.');

	}
        
        $errors = array();
        
        $errors = apply_filters("_wpr_subscriptionform_validate",$errors);
        
        if (count($errors) !=0)
        {
            $errorString = implode("<li>",$errors);
            error("<ol>$errorString</ol>");
        }


	if (!empty($followup) && !empty($responder))

	{

		switch ($followup)

		{

			case 'postseries':

			$query = "SELECT COUNT(*) count FROM ".$wpdb->prefix."wpr_blog_series where id=".$responder;

			$items = $wpdb->get_results($query);

			$count = $items[0]->count;

			if ($count == 0)

			{

				error("There was a problem while processing your subscription request. The postseries you have subscribed to doesn't exist in our records. The post series you have subscribed to doesn't exist and/or may have been deleted by the site administrator. The site owner has been notified of the problem.");

				wpr_error("$name ($email) tried to subscribe to a non-existent postseries (of id $responder) that doesn't exist from ".$_SERVER['HTTP_REFERER']);

				

			}

			break;

			case 'autoresponder':
			$query = $wpdb->prepare("SELECT COUNT(*) count FROM {$wpdb->prefix}wpr_autoresponders where id=%d",$responder);
			$items = $wpdb->get_results($query);
			$count = $items[0]->count;
			if ($count == 0)

			{

				error("There was a problem while processing your subscription. The follow-up series you have subscribed to doesn't exist in our records. The site administrator has been notified of the problem.");

				wpr_error("$name ($email) tried to subscribe to autoresponder ( of id$responder) that doesn't exist from ".$_SERVER['HTTP_REFERER']);

			}

			break;
			default:
			print "Error! The form is badly formed. ";
			exit;
		}

	}

	

	if ($bsubscription == "cat")

	{

		$category = get_category($bcategory);

		if (empty($category))

		{
			error("The was a problem when processing your subscription. The content to which you are trying to subscribe doesn't exist. It may have been deleted by the site administrator. The site administrator has been notified of the problem.");

			wpr_error("$name ($email) tried to subscribe to a blog category ($bcategory) that doesn't exist from ".$_SERVER['REQUEST_URI']);

		}

	}

	$newsletter = _wpr_newsletter_get($newsletter);
	$nid = $newsletter->id;
	
	$hash = _wpr_subscriber_hash_generate();

	$query = $wpdb->prepare("SELECT * FROM `{$wpdb->prefix}wpr_subscribers` WHERE `email`=%s AND `nid`=%d;",$email,$nid);
	$subscribeList = $wpdb->get_results($query);
	$zone = date_default_timezone_get();
	date_default_timezone_set("UTC");
	if (count($subscribeList) ==0)  //the visitor is a new subscriber

	{
		//new subscriber, add him to records
		
		$date = time();
		$query = $wpdb->prepare("INSERT INTO `{$wpdb->prefix}wpr_subscribers` (`nid`,`name`,`email`,`date`,`active`,`fid`,`hash`) VALUES (%d,%s,%s,%s,1,%d,%s);",$nid,$name,$email,$date,$fid, $hash) ;
		$wpdb->query($query);
		//now get the subscriber object 
		$query = $wpdb->prepare("SELECT * FROM `{$wpdb->prefix}wpr_subscribers` WHERE `email`=%s AND `nid`=%d;",$email,$nid);
		$subscriber = $wpdb->get_results($query);
		$subscriber = $subscriber[0];
	}
	else   //the subscriber already exists
	{
		 //find if the subscriber had already subscribed before and is still subscribed.
		 $query = $wpdb->prepare("select * from {$wpdb->prefix}wpr_subscribers where active=1 and confirmed=1 and email=%s and nid=%d; ",$email,$nid);
		 $results = $wpdb->get_results($query);

		 if (count($results) >0)
		 {
 			 error("You are already subscribed to this newsletter.");
		 }
		 else
		 {
     			 $subscriber = $subscribeList[0];		 
			 $date = time();
			 $query = $wpdb->prepare("update {$wpdb->prefix}wpr_subscribers set active=1, confirmed=0, date=%s where email=%s and nid=%d;",$date, $email, $nid) ;
   			 $wpdb->get_results($query);
		 }
	}
	$id = intval($subscriber->id);
	
	
	//insert the subscriber's custom field values
	foreach ($_POST as $field_name=>$value)
	{
		if (preg_match('@cus_.*@',$field_name))
		{

			$name = base64_decode(str_replace("cus_","",$field_name));

			$query = $wpdb->prepare("select * from {$wpdb->prefix}wpr_custom_fields where name=%s and nid=%d",$name, $nid);
			$custom_fields = $wpdb->get_results($query);

			$custom_fields = $custom_fields[0];

			$cid = $custom_fields->id;

			$value = $_POST[$field_name];

			$query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}wpr_custom_fields_values WHERE nid=%d AND sid=%d AND cid=%d",$nid, $id, $cid) ;

			$wpdb->query($query);

			$query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpr_custom_fields_values (nid,sid,cid,value)  values (%d,%d,%d,%s);",$nid,$id,$cid,$value);

			$wpdb->query($query);

		}

		//sparing the inserted values, insert null for the rest of the custom fields defined for this newslette

					 

	}

	

	//what custom fields already exist? so that we dont try to insert duplicate values for those

	$query = $wpdb->prepare("SELECT b.name name from {$wpdb->prefix}wpr_custom_fields_values a, {$wpdb->prefix}wpr_custom_fields b where a.sid=%d and b.id=a.cid;",$id);

	$fields = $wpdb->get_results($query);
        
        $existing = array();

	if (count ($fields) > 0)

	{

		foreach ($fields as $field)

		{

			$existing[] = $field->name;

		}

	}

	if (count($existing) != 0)

		$notin = implode("','",$existing);


	else

		$notin ="";



	$notin = "IN('".$notin."')";

	$query = sprintf("SELECT * FROM {$wpdb->prefix}wpr_custom_fields WHERE `name` NOT %s AND nid=%d;",$notin,$nid);
	$otherfields = $wpdb->get_results($query);

	foreach ($otherfields as $field)
	{
		$cid  = $field->id;
		$query = sprintf("INSERT INTO {$wpdb->prefix}wpr_custom_fields_values (nid,sid,cid,value) VALUES (%d,%d,%d,'');",$nid,$id,$cid);
		$wpdb->query($query);
	}


	if ($followup)
	{
		$query = "SELECT a.* FROM ".$wpdb->prefix."wpr_followup_subscriptions a, ".$wpdb->prefix."wpr_subscribers b where a.sid=b.id and a.type='$type' and a.eid='$responder' and b.id='$id'";
		$subscriptions = $wpdb->get_results($query);
		//subscribe to autoresponder only if they aren't or ever haven't subscribed to this newsletter
		if (count($subscriptions) == 0)
		{
			$date = time();
			$query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}wpr_followup_subscriptions where sid=%d and type=%s and eid=%d;",$id,$followup,$responder);
			$wpdb->query($query);
			$query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpr_followup_subscriptions (sid,type,eid,sequence,doc) values (%d,%s,%d,-1,%s);",$id,$followup,$responder,$date);
			$wpdb->query($query);

		}

	}

	//if blog subscription is mentioned in the form
	if (!empty($bsubscription))
	{
                $deleteExistingSubscriptionQuery = sprintf("DELETE FROM %swpr_blog_subscription WHERE sid=%d AND type='%s' AND catid=%d",$wpdb->prefix,$id,$bsubscription,$bcategory);
                $wpdb->query($deleteExistingSubscriptionQuery);
                $timeNow = time();
                $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpr_blog_subscription (sid,type,catid, last_published_post_date) values (%d,%s,%d,%s);",$id, $bsubscription,$bcategory,$timeNow);
                $wpdb->query($query);
	}

	

	if (!empty($fid) && $fid != 0)

	{

		$query = $wpdb->prepare("SELECT * from {$wpdb->prefix}wpr_subscription_form where id=%d",$fid);

		$theForm = $wpdb->get_results($query);

		

		if (count($theForm) != 0)

		{

			$theForm = $theForm[0];

			$confirm_subject = $theForm->confirm_subject;

			$confirm_body = $theForm->confirm_body;

		}		

	}
	

	do_action("_wpr_subscriber_added",$id);

	$theqstring = $subscriber->id."%%".$subscriber->hash."%%".$fid;

	$p = trim(base64_encode($theqstring),"=");

	$link = home_url("/?wpr-confirm=".$p);
	
	$dirname = str_replace("optin.php","",__FILE__);
	$confirm = file_get_contents($dirname."/templates/confirm.txt");
	$confirm = str_replace("[!confirm_link!]",$link,$confirm);

	$newsletter = _wpr_newsletter_get($nid);

	$newslettername = $newsletter->name;

	$url = ($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"Unknown";

	$ip = $_SERVER['REMOTE_ADDR'];

	$date = "At ".date("g:i dS M, Y");



	$address = get_option('wpr_address');

	$confirm_subject = str_replace("[!ipaddress!]",$ip,$confirm_subject);
	$confirm_body = str_replace("[!ipaddress!]",$ip,$confirm_body);
	$confirm_subject = str_replace("[!date!]",$date,$confirm_subject);
	$confirm_body = str_replace("[!date!]",$date,$confirm_body);

	$confirm_subject = str_replace("[!url!]",$url,$confirm_subject);

	$confirm_body = str_replace("[!url!]",$url,$confirm_body);

	

	$confirm_subject = str_replace("[!newslettername!]",$newslettername,$confirm_subject);
	$confirm_body = str_replace("[!newslettername!]",$newslettername,$confirm_body);


	$confirm_subject = str_replace("[!address!]",$address,$confirm_subject);
	$confirm_body = str_replace("[!address!]",$address,$confirm_body);
	$confirm_body = str_replace("[!confirm!]",$confirm,$confirm_body);
	$additional_parameters = array(
								    	"ipaddress" => $_SERVER['REMOTE_ADDR'],
										"date"     => date("g:i d F Y",time()),
										"url"      => $_SERVER['HTTP_REFERER']
								   );

	$params = array();
	
	date_default_timezone_set($zone);
	
	$params[0] = $confirm_subject;
	$params[1] = $confirm_body;

    $subscriber = new Subscriber($id);

    foreach ($params as $index=>$value)
    {
        $params[$index] = Subscriber::replaceCustomFieldValues($value, $subscriber);
    }

	$from_email = $newsletter->fromemail;
	
	if (!$from_email)	
		$from_email = get_bloginfo("admin_email");	

	$from_name = $newsletter->fromname;
	
	if (!$from_name)
		$from_name = get_bloginfo("name");

	$subject = $params[0];
	$body = $params[1];
	
	$verificationEmail = array(
							   		'to'=>$email,
									'subject'=>$subject,
									'textbody'=>$body,
									'fromname'=>$from_name,
									'from'=>$from_email
								);
    	@dispatchEmail($verificationEmail);	 
	if (empty($return_url))
	{
		if (isset($theForm))
		   $return_url = $theForm->return_url;
	}

	if (!empty($return_url))
	{ 
        ?>
<script>
window.location='<?php echo $return_url; ?>';
</script>
<?php
		exit;
	}
	else
	{
        ?>
<script>
		window.location='<?php echo home_url("/?wpr-optin=2"); ?>';
		</script>
<?php
		exit;
	}
	exit;

}

else

{

	if (!isset($_POST['newsletter']))
	{
		?>
<div align="center" style="font-family:Georgia, 'Times New Roman', Times, serif; font-size:24px; width:600; margin-left:auto; margin-right:auto">
  <h2>Invalid Request</h2>
  This page should not be visited. Please use a subscription form to subscribe to a newsletter.</div>
<?php
	}

}
exit;

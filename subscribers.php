<?php
include("subscriber.lib.php");
function wpr_subscribers()
{
	$action = $_GET['action'];

        if (current_user_can("manage_newsletters"))
        {
	switch ($action)
	{
		case 'profile':
		$id = (int) $_GET['sid'];
		
		$subscriber = _wpr_subscriber_get($id);
		if (!$subscriber)
		{
			?>
            <h3>The specified subscriber was not found.</h3>
            <a class="button" href="admin.php?page=wpresponder/subscribers.php">&laquo; Back</a>            <script>
			window.setTimeout("gohome()",3000);
			function gohome()
			{
				window.location='admin.php?page=wpresponder/subscribers.php';
			}
			</script>
            <?php
			exit;
			
		}
		_wpr_subscriber_profile($subscriber);
		
		break;
		case 'nmanage':
		_wpr_subscriber_nmanage();
		break;
		case 'search':
		_wpr_subscriber_search();
		break;
                case 'delete':

                _wpr_subscribers_delete();
                break;

		default:
		_wpr_subscriber_home();
	}
        }
}
function _wpr_subscribers_delete()
{
    $subs = $_POST['sub'];
    global $wpdb;
    if (count($subs) > 0)
    {
        foreach ($subs as $id)
        {
            $query = "DELETE FROM ".$wpdb->prefix."wpr_subscribers where id=$id";
            
            $wpdb->query($query);
        }

    }
    ?>
<script>window.location='<?php echo $_POST['back'] ?>';</script>
    <?php
    exit;

}
function _wpr_subscriber_profile($subscriber)
{
	global $wpdb;
	$sid = $subscriber->id;
	if (isset($_POST['followupunsub']))
	{
		$aid = (int) $_POST['aid'];
		$query = "DELETE FROM ".$wpdb->prefix."wpr_followup_subscriptions where id=$aid";
		$wpdb->query($query);
		?>
		<script>window.location='admin.php?page=wpresponder/subscribers.php&action=profile&sid=<?php echo $subscriber->id ?>';</script>
        <?php
		exit;
	}
	
	
	if (isset($_POST['customfielddata']))
	{
		//Asume that all the custom fields are in the post data.
		$nid = $_POST['custom_field_newsletter'];
		$query = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where nid = $nid;";
		$results = $wpdb->get_results($query);
                $theSubscriberId = $_POST['custom_field_sid'];
		$formData = array();
		foreach ($_POST as $name=>$value)
		{
			$formData[trim($name)]= trim($value);
		}
		
		foreach ($results as $cfield)
		{

			$fieldName = trim('newsletter-'.$nid.'-cfield-'.$cfield->id);

			$value = $_POST[$fieldName];
			
			$cid = $cfield->id;
			
			$query = "DELETE FROM ".$wpdb->prefix."wpr_custom_fields_values where sid = $theSubscriberId and cid=$cid;";
                        
			$wpdb->query($query);			
			if (empty($value))
			    continue;
			$query = "INSERT INTO ".$wpdb->prefix."wpr_custom_fields_values (nid,sid,cid,value) VALUES ('$nid','$theSubscriberId','$cid','$value')";
                        
			$wpdb->query($query);
			
			
		}
					?>
			<script> window.location='admin.php?page=wpresponder/subscribers.php&action=profile&sid=<?php echo $sid ?>';
			</script>
			<?php

		exit;
		
	}
	
	
	if (isset($_POST['unsubscription_form']))
	{
		$sid = $_POST['sid'];
		$query = "UPDATE ".$wpdb->prefix."wpr_subscribers set active=0 where id=$sid";

		$wpdb->query($query);
		$query = "DELETE FROM ".$wpdb->prefix."wpr_followup_subscriptions where sid=$sid";
		$wpdb->query($query);
		
		$query = "DELETE FROM ".$wpdb->prefix."wpr_custom_fields_values where sid=$sid";
		$wpdb->query($query);
	}

	if (isset($_POST['subs_action']))
	{

		switch ($_POST['subs_action'])
		{
			case 'delete':
			
			$sid = $_POST['sid'];
                        $subscriber = _wpr_subscriber_get($sid);
                        $theEmail = $subscriber->email;
                        
                        $query = "SELECT id from ".$wpdb->prefix."wpr_subscribers where email='$theEmail';";
                        $subscriptions = $wpdb->get_results($query);

                        foreach ($subscriptions as $theSubscription)
                        {
							$currentSid = $theSubscription->id;
                            $deleteBlogSubscriptions = "DELETE FROM ".$wpdb->prefix."wpr_blog_subscription where sid=$currentSid";
                            $wpdb->query($deleteBlogSubscriptions);
                            $deleteFollowupSubscriptions = "DELETE FROM ".$wpdb->prefix."wpr_followup_subscriptions where sid=$currentSid";
                            $wpdb->query($deleteFollowupSubscriptions);
                            $deleteCustomFieldValues = "DELETE FROM ".$wpdb->prefix."wpr_custom_field_values where sid=$currentSid";
                            $wpdb->query($deleteCustomFieldValues);
                            $deleteSubscriber = "DELETE FROM ".$wpdb->prefix."wpr_subscribers where id=$currentSid";
                            $wpdb->query($deleteSubscriber);
                            
                        }
			?><script> window.location='admin.php?page=wpresponder/subscribers.php';</script><?php			
			return;
			break;			
			
			case 'unsubscribe':
			
			$newsletters = $_POST['newsletters'];
			foreach ($newsletters as $newsletter)
			{
				$query = "update ".$wpdb->prefix."wpr_subscribers set active=0 where nid=".$newsletter." and email='".$subscriber->email."'";
				
				$wpdb->query($query);
				
			}
			?>
            <script>window.history.go(-2);</script>
            <?php
			return;
			break;
			
			
			
		}
	}
	?>
<div class="wrap"><h2>Profile</h2></div>
<table>
  <tr>
    <td width="300">Name: </td>
    <td><?php 
				   $query = "select DISTINCT name from ".$wpdb->prefix."wpr_subscribers where email='".$subscriber->email."' order by active desc";
				   $results = $wpdb->get_results($query);
				   $names = array();
				   foreach ($results as $name)
				   {
					   array_push($names,$name->name);
				   }
				   $theName = implode(", ",$names);
				   echo $theName;
				   ?>
                  </td>
                  </tr>
                  <tr>
                    <td>E-Mail Address: </td>
                    <td><?php echo $subscriber->email ?>
                    </td>
                    </tr>
                    </table>
                    <p></p>
                    <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
<input type="hidden" name="subs_action" value="delete" />
<input type="hidden" name="sid" value="<?php echo $subscriber->id;  ?>" />
<input type="submit" onclick="return window.confirm('Are you sure you want to delete this subscriber?'); " value="Delete This Subscriber"  class="button-primary" />
</form>

<div style="clear:both"></div>

<h3>Current Newsletter Subscriptions:</h3>
<?php
$query = "select distinct a.id id, a.name name from ".$wpdb->prefix."wpr_newsletters a, ".$wpdb->prefix."wpr_subscribers b where a.id=b.nid and b.email='".$subscriber->email."' and b.active in(1,2)";

$subscribedNewsletters = $wpdb->get_results($query);
foreach ($subscribedNewsletters as $newsletter)
{
		  $nid = $newsletter->id;
		  $email = $subscriber->email;
		  $query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers where nid='".$newsletter->id."' and email='".$subscriber->email."';";
		  $results = $wpdb->get_results($query);
		  $theSubscriberObject = $results[0];
		  $sid = $theSubscriberObject->id;
	?>
    <fieldset style="border: 1px solid #000; width:1000px; padding: 15px; margin-bottom: 10px;"><legend><span style="font-family: Arial;font-size: 15px; margin: 10px; font-weight:bold"><?php echo $newsletter->name ?></span></legend>
    <table width="300" style="margin: 10px;">
       <tr>
          <td>Name: </td>
          <td><?php 

  	  	echo $theSubscriberObject->name;

		  ?>
		  </td>
        </tr>
        <tr>
          <td>Subscribed on:</td>
          <td><?php echo date("g:ia d F Y",$theSubscriberObject->date); ?>
          </td>        
        </tr>
     </table><br />

     
   
     <?php
	 $query = "SELECT * FROM ".$wpdb->prefix."wpr_followup_subscriptions where sid=$sid and type='autoresponder';";
	 $autoresponderSubscriptions = $wpdb->get_results($query);
	 ?>

     <?php
	 if (count($autoresponderSubscriptions)) {
		 ?>
              <h3>Follow-up Autoresponders Subscriptions</h3>
                   <table class="widefat">
     <tr>
       <th>Name Of Autoresponder</th>
       <th width="150">Currently Receiving?</th>
       <th>Progress in Autoresponder</th>
       <th>Date Of Subscription</th>
       <th>Stop</th>
    </tr>
     <?php

		
		 foreach ($autoresponderSubscriptions as $followup)
		 {
			 ?>
			 <tr id="autores-<?php echo $followup->eid ?>-row">
				<td><?php $query = "SELECT * FROM ".$wpdb->prefix."wpr_autoresponders where id=".$followup->eid.";";;
				$theAutoresponder = $wpdb->get_results($query);
				echo $theAutoresponder[0]->name;
				?>
				</td>
				<td>
				<?php
			   if (isAutoresponderSeriesActive($followup->eid))
			   {
				   echo "Receiving Follow-up Message.";   
			   }
			   else
			   {
				   echo "Has Received All Messages.";
			   }
				?>
				</td>
				<td>Has Received <?php
					echo $followup->sequence+1
					?> Messages.</td>
				<td>
				   <?php echo date("g:ia d F Y",$followup->doc); ?>
				</td>
				<td>
				<?php 
				if (isAutoresponderSeriesActive($followup->eid))
				{
				?>
					<form action="admin.php?page=wpresponder/subscribers.php&action=profile&sid=<?php echo $sid ?>&aresid=<?php echo $followup->eid ?>&subaction=delete" method="post">
					<input type="hidden" name="aid" value="<?php echo $followup->id ?>" />
					<input type="submit" class="button-primary" name="followupunsub" value="Stop" onclick="return confirm('Are you sure you want to stop this autoresponder sequence for this subscriber?');" />
					</form>
				<?php
				}
				else
				{
					?><center>Finished</center>
					<?php
				}
				?>
				</td>
			 </tr>
				<?php
	      }
		  ?> </table>
     <?php
	 
}
	 ?>
    
     
     
     <h3>Custom Field Values</h3>     
     <?php
	 //fetch the custom fields of this newsletter
	 
	 $query = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where nid=$nid";
	 $customFieldList = $wpdb->get_results($query);
	 
	 if (count($customFieldList))
	 {
			 ?>
			 <form name="newsletter-<?php echo $nid ?>-customfields" method="post">
			 <input type="hidden" name="customfielddata" value="1" />
                         <input type="hidden" name="custom_field_sid" value="<?php echo $theSubscriberObject->id ?>">
			 <input type="hidden" name="custom_field_newsletter" value="<?php echo $nid ?>" />
			 <table width="800">
			 <?php
			 foreach ($customFieldList as $formfield)
			 {
				   $cid = $formfield->id;
				 ?>
				 <tr> 
					 <td><?php echo $formfield->label ?></td>
					 <td><?php 
					 $query = "SELECT value from ".$wpdb->prefix."wpr_custom_fields_values where sid=$sid and cid=$cid";
					 $valueSet = $wpdb->get_results($query);
		
					 $value = $valueSet[0]->value;
					 if ($formfield->type !="hidden")
					 {
						 echo getCustomField($formfield->id,"newsletter-$nid-cfield-".$formfield->id,$value);
						 
					 }
					 else
					 {
						?><input type="text" name="<?php echo "newsletter-$nid-cfield-" .$formfield->id; ?>" value="<?php echo $value ?>" />(hidden type)<?php
					 }
					 
					 ?></td>
				 </tr>
				 <?php
			 }
			 
			 ?>
		</table>
		<input type="submit" class="button" value="Save Custom Field Information" style="display:block" /><br />
        </form>
        <?php
	 }
	 else
	 {
		 ?>No custom fields defined for this newsletter. <?php
	 }
	 
	 ?><br />



<?php
if ($theSubscriberObject->active==1)
{
	?>
<strong>Subscription Status: </strong> Subscribed<p></p>
<form action="admin.php?page=wpresponder/subscribers.php&action=profile&sid=<?php echo $sid ?>" method="post">
<input type="hidden" name="sid" value="<?php echo $sid ?>" />
<input type="hidden" name="unsubscription_form" value="1" />
<input type="submit" name="submit" onclick="return window.confirm('Are you sure you want to unsusbcribe this reader from this newsletter?');" value="Unsubscribe from this newsletter" class="button-primary" /> 
</form>
<?php
}
else if ($theSubscriberObject->active ==2)
{
    ?>
<strong>Subscription Status:</strong> Transfered. The subscriber's subscription to this newsletter was deactivated in accordance
with a <a href="admin.php?page=wpresponder/actions.php">transfer rule</a>.
    <?php
}
else
{
	?>User has Unsubscribed<?php
}
?>
    
    </fieldset>
    <?php

	
}
?> 
</form><br />
<a href="admin.php?page=wpresponder/subscribers.php" class="button">&laquo; Back</a>
    <?php
}

function _wpr_subscriber_search()
{
	global $wpdb;
	$keyword = $_GET['keyword'];
	$type = $_GET['stype'];
	if (!in_array($type,array("Name","E-Mail")))
	{
		?>
<div align="center">        <h1 style="font-family:Arial, Helvetica, sans-serif; font-size:24px;">Unrecognized Search Query.</h1>
The search keywords that were provided were not recognizable. Please go back and try again.
        <a href="admin.php?page=wpresponder/subscribers.php" class="button-primary">&laquo; Go Back</a></div>
        <?php
	}
	
	$pageNumber = (int) $_GET['pg'];
	$pageNumber = (int) $pageNumber;
	if ($pageNumber==0)
	    $pageNumber=1;
	$numberPerPage = (int)$_GET['perpage'];
	$numberPerPage  = ($numberPerPage <=0)?10:$numberPerPage;
	$start = ($pageNumber-1)*$numberPerPage;
	if (isset($_GET['nid']))
	{
		$nid = (int) $_GET['nid'];
		$newsletterClause = "and nid='$nid'";
	}
	else
	{
		$newsletterClause="";
	}
	$limitClause = "limit $start , $numberPerPage ";
	if ($type == "Name")
	{
		$query = "select * from ".$wpdb->prefix."wpr_subscribers where name like '%$keyword%' $newsletterClause order by active desc $limitClause ;";
	}
	else
	{
		$query = "select * from ".$wpdb->prefix."wpr_subscribers where email like '%$keyword%' $newsletterClause $limitClause;";
	}
	$subscribers = $wpdb->get_results($query);
	$numberOfPages = ceil(count($subscribers)/$numberPerPage);
	?>
	<div class="wrap"><h2>Search for '<?php echo $_GET['keyword']; ?>'</h2></div>
	<?php
	_wpr_subscriber_search_form();
	$back = "page=wpresponder/subscribers.php";
	_wpr_subscriber_list($subscribers,true,$back);
	pageNumbers(ceil(count($subscribers)/$numberPerPage));
	recordsPerPageSelector();		
	return; 
	
}
function _wpr_subscriber_nmanage()
{
	$nmact = $_GET['nmact'];
	switch ($nmact)
	{
		default:
		_wpr_subscriber_nmanage_home();
	}
}

function _wpr_subscriber_nmanage_home()
{
	global $wpdb;
	$nid = (int) $_GET['nid'];
	$querystring = $_SERVER['QUERY_STRING'];
	
	$pageNumber = (int) $_GET['pg'];
	$pageNumber = (int) $pageNumber;
	if ($pageNumber==0)
	    $pageNumber=1;
		$numberPerPage = (int)$_GET['perpage'];
		$numberPerPage  = ($numberPerPage <=0)?10:$numberPerPage;
		$start = ($pageNumber-1)*$numberPerPage;
		$start=$start;
		$limitClause = "limit $start , $numberPerPage ";

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers where nid=$nid order by date desc $limitClause;";
	
	$subscribers = $wpdb->get_results($query);
	$newsletter = _wpr_newsletter_get($nid);
		
		
		
	
	?>
    <div class="wrap"><h2>Manage Subscribers of <?php echo $newsletter->name ?></h2></div>  
   <?php 
    _wpr_subscriber_search_form($nid); 
?><br />
<div style="float:left;"><a href="admin.php?page=wpresponder/subscribers.php"> &laquo; Back To Subscribers' Home</a></div><div align="right"><div style="display:block; margin:10px;">	    <a href="admin.php?page=_wpr/importexport" class="button-primary">Import/Export Subscribers</a></div></div>
<?php
    $backUrl = "page=wpresponder/subscribers.php";
	_wpr_subscriber_list($subscribers,false,$backUrl);
	$query = "SELECT count(*) num from ".$wpdb->prefix."wpr_subscribers where nid=$nid;";
	$subscriberCountRetriever= $wpdb->get_results($query);

	$subscriberCount = $subscriberCountRetriever[0]->num;
?>
        
      <br />
<br />
  Total Number Of Subscribers: <?php echo $subscriberCount ?> subscribers<br/>
    <?php
	$numberOfPages = ceil($subscriberCount/$numberPerPage);
	pageNumbers($numberOfPages);
	recordsPerPageSelector();
}

function _wpr_subscriber_search_form($nid="")
{
	?>
    
<script>
function trim(s)
{
	return rtrim(ltrim(s));
}

function ltrim(s)
{
	var l=0;
	while(l < s.length && s[l] == ' ')
	{	l++; }
	return s.substring(l, s.length);
}

function rtrim(s)
{
	var r=s.length -1;
	while(r > 0 && s[r] == ' ')
	{	r-=1;	}
	return s.substring(0, r+1);
}

function submitSearchQuery()
	{
		  var keywordfield = document.searchForm.keyword
		  var keyword = trim(keywordfield.value);
		  var search_type = trim(document.searchForm.stype.options[document.searchForm.stype.selectedIndex].value);
		  if (keyword.length == 0)
		  {
			  alert("Please enter a search phrase in the field provided");
			  keywordfield.focus();
			  return false;
		  }
		  else
		  {
			  var theaction=document.searchForm.action;
			  var goto=theaction+"&stype="+search_type+"&keyword="+keyword;
			  if (document.searchForm.nid && document.searchForm.nid.checked)
			  {
				  var nid = document.searchForm.nid.value;
				  goto +="&nid="+nid;
			  }
			  window.location=goto;
		  }
		
	}
	</script>
    <div style="float:right; border: 1px solid #ccc; padding:10px; background-color:#f0f0f0;">
     <form name="searchForm" action="admin.php?page=wpresponder/subscribers.php&action=search" method="get">
    Search for subscribers whose : 
    <select name="stype">
      <option>Name</option>
      <option>E-Mail</option>
    </select> is like
    <input type="text" name="keyword" size="20" />
    <input type="hidden" name="search_form" value="1" />
    <input type="button" onclick="submitSearchQuery();" value="Search" />
    <?php
	if ($nid){ ?>
    <br/>
    <input type="checkbox" name="nid" checked="checked" value="<?php echo $nid ?>" /> only in this newsletter.
    <?php }
	 ?>
    </form>
    </div><br /><br />
<br />
    <?php
}
function _wpr_subscriber_home()
{
	global $wpdb;
	
	$numberOfSubscribesrPerPage = (int)$_GET['perpage'];
	$numberOfSubscribesrPerPage  = ($numberOfSubscribesrPerPage <=0)?10:$numberOfSubscribesrPerPage;
	
	$pageNumber = (int) $_GET['pg'];
	$pageNumber=($pageNumber<=0)?1:$pageNumber;	
	$start = ($pageNumber-1)* $numberOfSubscribesrPerPage;
	$limitClause = "limit $start,$numberOfSubscribesrPerPage";

	$query = "select * from ".$wpdb->prefix."wpr_newsletters; ";
	$newsletters = $wpdb->get_results($query);
	?>
<div align="right">    <div style="margin-top: 30px; display:block;">
	<?php
    _wpr_subscriber_search_form();
	?>
    </div>
    </div>
    
    <div class="wrap"><h2>Manage Newsletter Subscribers</h2></div>
    <table class="widefat" style="width:auto;">
        <thead>
        <tr>
            <th>Name</th>
            <th>Manage</th>
        </tr>
     </thead>
    <?php
	if (count($newsletters))
	{
		foreach ($newsletters as $newsletter)
		{
			
		?>
	 <tr> 
		<td><?php echo $newsletter->name ?></td>
		<td><a href="admin.php?page=wpresponder/subscribers.php&action=nmanage&nid=<?php echo $newsletter->id ?>" class="button">Manage Subscribers</a>&nbsp;</td>
		</tr>
		
		<?php
		}
	}
	else
	{
		?>
        <tr>
         <td colspan="10" align="center">No Subscribers Found</td>
        </tr>
        <?php
	}
	?>
    </table>
     <div class="wrap"><h2>All Subscribers</h2></div>
    <?php
	$query = "SELECT DISTINCT `email` from ".$wpdb->prefix."wpr_subscribers order by date $limitClause";

	$emails = $wpdb->get_results($query);
	$subscribers = array();
	foreach ($emails as $email)
	{
		$query = "select * from ".$wpdb->prefix."wpr_subscribers where email='".$email->email."'";

		$results = $wpdb->get_results($query);
		$row = $results[0];
		array_push($subscribers,$row);
	}
	$subscribers = array_reverse($subscribers);
	_wpr_subscriber_list($subscribers,true);
	$query = "SELECT DISTINCT email from ".$wpdb->prefix."wpr_subscribers";
	$allEmails = $wpdb->get_results($query);
	$number = count($allEmails);
	unset($allEmails);
	$numberOfPages = ceil($number/$numberOfSubscribesrPerPage);
	pageNumbers($numberOfPages);
	recordsPerPageSelector();
}

function recordsPerPageSelector()
{
?>
    <div style="float:right">Show <select name="numberPerPage" onchange="window.location='<?php echo preg_replace("@&{0,1}perpage=[0-9]*@" , "" , $_SERVER['REQUEST_URI'] ) ?>'+'&perpage='+this.options[this.selectedIndex].value;">
    <option <?php if (isset($_GET['perpage']) && $_GET['perpage'] == 10) { ?>selected="selected" <?php } ?>>10</option>
    <option <?php if (isset($_GET['perpage']) && $_GET['perpage'] == 30) { ?>selected="selected" <?php } ?>>30</option>
    <option <?php if (isset($_GET['perpage']) && $_GET['perpage'] == 50) { ?>selected="selected" <?php } ?>>50</option>
    <option <?php if (isset($_GET['perpage']) && $_GET['perpage'] == 100) { ?>selected="selected" <?php } ?>>100</option>
    <option <?php if (isset($_GET['perpage']) && $_GET['perpage'] == 500) { ?>selected="selected" <?php } ?>>500</option>
    <option <?php if (isset($_GET['perpage']) && $_GET['perpage'] == 1000) { ?>selected="selected" <?php } ?>>1000</option> 
    </select> subscribers per page.</div>
    <?php
}

function pageNumbers($numberOfPages)
{
	if ($numberOfPages > 1)
	{
		?>
		Page: <?php
		for ($i=1; $i<=$numberOfPages;$i++)
		{
		?>
		<a href="<?php
		$url = $_SERVER['REQUEST_URI'];
		$url = preg_replace("@&pg=[0-9]*@","",$url);
		$url .="&pg=$i";
		echo $url;
		?>"><?php echo $i ?></a><?php
		}
	}
	
}
function _wpr_subscriber_list($subscribers,$allNewslettersMode=true,$backUrl="")
{
	global $wpdb;

	?>
<form action="admin.php?page=wpresponder/subscribers.php&action=delete" method="post">
<?php
if ($allNewslettersMode) { ?><input type="hidden" name="delete_all" value="1"/><?php } ?>
    <input type="hidden" name="back" value="<?php echo $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'] ?>">
    <table class="widefat">
      <thead><tr>
      
           <th align="left"><input onclick="checkAllElements(this.checked);" type="checkbox" value="1" ></th>
         <th>Name(s)</th>
         <th>E-Mail</th>
         <?php if ($allNewslettersMode) { ?><th>Newsletter Subscription(s)</th>          <?php }
         else
         {
         ?>
         <th>Date Of Subscription</th>
         <?php
         }
         ?>
         <th>Edit</th>
      
      
      </tr> </thead>
      <?php
	  if (count($subscribers))
	  {
		    $end = $start+$numberPerPage-1;
		    $start = $start-1;
			
			foreach ($subscribers as $subscriber)
			{			
				$prefix = $wpdb->prefix;
				?>
				<tr>
                                    <td><input type="checkbox" name="sub[]" class="subselect" value="<?php echo $subscriber->id ?>"/></td>
				   <td><?php 
				   $query = "select DISTINCT name from ".$wpdb->prefix."wpr_subscribers where email='".$subscriber->email."'";
				   $results = $wpdb->get_results($query);
				   $names = array();
				   foreach ($results as $name)
				   {
					   array_push($names,$name->name);
				   }
				   $theName = implode(", ",$names);

				   echo $theName;
				   ?></td>
				   <td><?php echo $subscriber->email ?></td>
			  <?php if ($allNewslettersMode) 
				{
                                    ?><td><?php
                                       $query = "select a.name newsletter_name, b.active active, b.confirmed confirmed, b.date date from ".$prefix."wpr_newsletters a, ".$prefix."wpr_subscribers b where a.id=b.nid and b.email='".$subscriber->email."';";
                                       $subscribedNewsletters = $wpdb->get_results($query);
                                       $list = array();
                                       if (count($subscribedNewsletters))
                                       {
                                           foreach ($subscribedNewsletters as $newsletter_subscription)
                                           {
                                               $subscription = array();
                                               $subscription_status = _wpr_subscription_status($newsletter_subscription->active, $newsletter_subscription->confirmed);
                                               $subscription['newsletter'] = $newsletter_subscription->newsletter_name;
                                               $subscription['status'] = $subscription_status;
                                               $subscription['date'] = date("g:ia dS F Y",$newsletter_subscription->date);
                                               $subscription = (object) $subscription;
                                               array_push($list,$subscription);
                                           }
                                           ?>
                                        <table class="widefat">
                                            <thead>
                                                <tr>
                                                    <th>Newsletter Name</th>
                                                    <th>Subscription Status</th>
                                                    <th>Date Of Subscription</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            foreach ($list as $sub)
                                            {
                                                ?>
                                            <tr>
                                                <td>
                                                     <?php echo $sub->newsletter; ?>
                                                </td>
                                                <td>
                                                     <?php echo $sub->status; ?>
                                                </td>
                                                 <td>
                                                     <?php echo $sub->date; ?>
                                                </td>
                                            </tr>
                                            <?php

                                            }
                                            ?>
                                        </table>
                                        <?php
                                           
                                           
                                       }
                                       else
                                       {
                                           _e("Associated Newsletter Deleted");
                                       }
                                       
                                       ?>
                                       </td><?php
				   }
                                   else
                                   {
                                       ?>
                                       <td><?php echo date("g:ia dS F Y",$subscriber->date); ?></td>
                                       <?php
                                       
                                   }
				   ?>
                   <td>
				   <a href="admin.php?page=wpresponder/subscribers.php&action=profile&sid=<?php echo $subscriber->id ?>" class="button">Edit</a>&nbsp;
				   </td>
				   </tr>
				   <?php
			}
	  }
	  else
	  {
		  ?>
          <tr>
            <td colspan="10" align="center">-No Subscribers- </td>
          </tr>
           <?php
	  }
	?>
    </table>
                With Selected: <input type="submit" value="Delete" onclick="return confirm('Are you sure you want to delete the selected subscribers?');" class="button-primary">
            </form>
                <script>

                    function checkAllElements(state)
                    {
                        jQuery(".subselect").attr({  checked: state});
                    }

                    </script>
<br />
<br />
<?php if ($backUrl) { ?>    <a href="admin.php?<?php echo $backUrl; ?>" class="button"> &laquo; Back </a> <?php } ?>
    <?php
}

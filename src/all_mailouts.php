<?php
function wpr_all_mailouts()
{
	switch ($_GET['action'])
	{
		case 'edit':
		
		_wpr_edit_mailout();
		break;
		case 'show_broadcast':
		
		_wpr_show_broadcast();		
		
		break;
		default:
		?>
        <?php
		_wpr_pending_mailouts();	
		?>
	<br />
		<input type="button" class="button" value="Create Broadcast" onclick="window.location='admin.php?page=wpresponder/newmail.php';"/>
		<br />
<br />
		<?php
		_wpr_finished_mailouts();
	}

}
function _wpr_edit_mailout()
{
	global $wpdb;
	$id = $_GET['id'];
	$query = "select * from ".$wpdb->prefix."wpr_newsletter_mailouts where id=$id and status=0";
	$mailouts = $wpdb->get_results($query);
	if (count($mailouts) ==0)
	{
		?>
        This newsletter has been sent. It cannot be edited.<br />
<br />

        <a href="admin.php?page=allbroadcasts" class="button">&laquo; Back </a>
        <?php	
		return;
	}
	$param = $mailouts[0];
	$param->htmlenabled = (empty($param->htmlbody))?0:1;
	
	
	if (isset($_POST['subject']))
	{
	    $subject = $_POST['subject'];
		$nid = $_POST['newsletter'];
		$textbody = trim($_POST['body']);
		$htmlbody = trim($_POST['htmlbody']);
		$whentosend = $_POST['whentosend'];	
		$date = $_POST['date'];
		
		$attachimages = (isset($_POST['attachimages']) && $_POST['attachimages'] ==1)?1:0;																
		$htmlenabled  = (isset($_POST['htmlenabled']) && $_POST['htmlenabled'] == "on");

		$recipients = $_POST['recipients'];
		$hour = $_POST['hour'];
		$min = $_POST['minute'];
		$id = $_POST['mid'];
		if ($whentosend == "now")
			$timeToSend = time();
		else
		{
				$timeToSend = $_POST['actualTime'];
		}
		if (!(trim($subject) && trim($textbody)))
		{
			$error = "Subject and the Text Body are mandatory.";
		}
		if ($timeToSend < time()  && !$error)
		{
			$error = "The time mentioned is in the past. Please enter a time in the future.";
		}
		if ($htmlenabled && !$error)
		{
			if (empty($htmlbody))
			{
				$error = "HTML Body is empty. Enter the HTML body of this email";
			}
		}
		//if html body is present, it will be sent.
		if (!$htmlenabled)
		{
			$htmlbody = "";
		}
		
		$htmlenabled = ($htmlenabled)?1:0;
		
		
		if (!$error)
		{
			$query = "UPDATE ".$wpdb->prefix."wpr_newsletter_mailouts set subject='$subject', textbody='$textbody', htmlbody='$htmlbody',time='$timeToSend',attachimages='$attachimages',recipients='$recipients', nid='$nid' where id=$id;";
			$wpdb->query($query);
			_wpr_mail_sending();
			return;
		}
		
		$param = (object)  array("nid"=>$nid,"textbody"=>$textbody,"subject"=>$subject,"htmlbody"=>$htmlbody,"htmlenabled"=>!empty($htmlbody),"whentosend"=>$whentosend,"time"=>$timeToSend,"title"=>"New Mail","buttontext"=>"Save Broadcast");

	}
	
	
	
	wpr_mail_form($param,"new",$error);	
}

function _wpr_pending_mailouts()
{
	global $wpdb;
	$offset = get_option('gmt_offset');
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletter_mailouts where status=0;";
	$mailouts = $wpdb->get_results($query);
	?>
    <script>
	var delurl = '<?php bloginfo("url") ?>/?wpr-admin-action=delete_mailout';

	var currentDeletion;
	function deleteMailout(id)
	{
		currentDeletion = id;
		if (window.confirm("Are you sure you want to cancel this broadcast? "))
		{
			jQuery.ajax({
							type: "GET",
							url:  delurl+'&mid='+id,
							cache: false,
							success: removeRow
						});
		}
	}
	function removeRow()
	{
		var row = document.getElementById('mailout_'+currentDeletion);
		par = row.parentNode;
		par.removeChild(row);
	}	
	
	</script>
    <div class="wrap"><h2>Pending Broadcasts</h2></div>
    <table class="widefat">
    <tr>
      <thead>
        <th>Subject</th>
        <th>Newsletter</th>
        <th>To Be Sent at*</th>
        <th>Recipients</th>
        <th>Actions</th>
      </thead>
     </tr>
     <?php
	foreach ($mailouts as $mailout)
	{
		?>
        <tr id="mailout_<?php echo $mailout->id ?>">
           <td><?php echo $mailout->subject ?></td>
           <td><?php $newsletter = _wpr_newsletter_get($mailout->nid);
		   echo $newsletter->name ?></td>
           <td><?php 
		   echo date("g:ia \o\\n dS F Y",$mailout->time + ($offset * 3600)); ?>
</td>
           <td><?php $recipients = implode("<br>",explode("%set%",$mailout->recipients));
		   echo ($recipients)?$recipients:"All Subscribers";?></td>
           <td><input type="button" value="Edit" class="button" onclick="window.location='admin.php?page=wpresponder/allmailouts.php&action=edit&id=<?php echo $mailout->id ?>';" /><input type="button" value="Cancel" class="button" onclick="deleteMailout(<?php echo $mailout->id ?>)" /></td>
        </tr>
        <?php
	}
	?>
    </table>
    <?php
}

function _wpr_finished_mailouts()
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletter_mailouts where status=1;";
	$mailouts = $wpdb->get_results($query);
	$offset = get_option('gmt_offset');
	?>
    <div class="wrap"><h2>Sent Broadcasts</h2></div>
    <table class="widefat">
    <tr>
      <thead>
        <th>Subject</th>
        <th>Newsletter</th>
        <th>Sent at*</th>
        <th>Recipients</th>
        <th>Actions</th>
      </thead>
     </tr>
     <?php
	foreach ($mailouts as $mailout)
	{
		?>
        <tr>
           <td><?php echo $mailout->subject ?></td>
           <td><?php $newsletter = _wpr_newsletter_get($mailout->nid);
		   echo $newsletter->name ?></td>
           <td><?php echo date("g:ia d F Y",$mailout->time + ($offset * 3600)); ?></td>
           <td><?php $recipients = implode("<br>",explode("%set%",$mailout->recipients));
		   echo ($recipients)?$recipients:"All Subscribers";
		   ?></td>
           <td><a href="<?php echo $_SERVER['REQUEST_URI']?>&action=show_broadcast&id=<?php echo $mailout->id ?>" class="button" style="margin:10px; margin-top:20px;" >View Broadcast</a></td>
        </tr>
        <?php
	}
	?>
    </table>
    
<br />
    * Time is approximate. Actual send time depends on the frequency you set for the wordpress cron job or amount of traffic you get.
    <?php
}

function _wpr_show_broadcast()
{
	global $wpdb;
	$id = $_GET['id'];
	require "viewbroadcast.php";
//    $query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletter_mailouts where id=$";
?>



<?php
	
}



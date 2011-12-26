<?php
include "autoresponder.messages.lib.php";
function wpr_manage_responder()
{
	
	switch ($_GET['mact'])
	{
		case 'create':
		_wpr_manage_responder_create();
		break;
		case 'edit':
		_wpr_manage_responder_edit();		
		break;
		case 'delete':
		_wpr_manage_responder_delete();
		break;
		default:
		_wpr_manage_responder_list();
	}
}

function _wpr_manage_responder_delete()
{
	global $wpdb;
	$id = $_GET['id'];
	$message = _wpr_get_autoresponder_message($id);
	if ($_GET['confirm'] == "true")
	{
		$query = "DELETE FROM ".$wpdb->prefix."wpr_autoresponder_messages where id=".$id;
		$wpdb->query($query);
		?>
        <script>window.location='admin.php?page=wpresponder/autoresponder.php&action=manage&aid=<?php echo $_GET['aid'] ?>';</script>
        <?php
		
	}
	else
	{
		?>
        <div class="wrap"><h2>Confirm Deletion Of '<?php echo $message->subject ?>'</h2></div>
        <div class="updated fade">Are you sure you want to delete this message? </div>
        <a href="<?php echo $_SERVER['REQUEST_URI'] ?>&confirm=true" class="button">Yes</a>&nbsp; <a href="admin.php?page=wpresponder/autoresponder.php&aid=<?php echo $_GET['aid']; ?>&action=manage" class="button">Cancel</a>
        <?php
	}
}

function _wpr_manage_responder_create()
{
	global $wpdb;
	$wpr_autoresponder_messages = $wpdb->prefix."wpr_autoresponder_messages";	
	$parameters->htmlenabled=1;
	if (isset($_POST['subject']))
	{
		$subject = $_POST['subject'];
		$textbody = $_POST['body'];
		$aid = $_GET['aid'];
		$htmlenabled = (isset($_POST['htmlenabled']))?1:0;
        $attachimages = ($_POST['attachimages']=="1");
		$htmlbody = $_POST['htmlbody'];
		$sequence = intval($_POST['sequence']);
		
		$whetherAMessageExistsOnSaidDay = $wpdb->prepare("SELECT COUNT(*) num FROM $wpr_autoresponder_messages WHERE `aid`=$aid AND `sequence`=$sequence");
		$results = $wpdb->get_results($whetherAMessageExistsOnSaidDay);
		if ($results[0]->num != 0)
		{
			$error = "An autoresponder follow-up message has already been added for that day. Set this e-mail to go out another day.";
		}
		else
		{
			if (!($subject && $textbody))
			{
			  $error = "Subject and Text Body are required";
			}
			else
			{
				if ($htmlenabled && !$htmlbody)
				{
					$error = "You have enabled HTML E-Mail but not entered any content for the HTML E-mail.";
				}
				else
				{
					$sequence = (int) $sequence;
					$query = "INSERT INTO ".$wpdb->prefix."wpr_autoresponder_messages (aid, subject,htmlenabled,textbody,htmlbody,attachimages,sequence) values ('$aid','$subject','".((int) $htmlenabled)."','$textbody','$htmlbody','$attachimages',$sequence)";
					$wpdb->query($query);
					?>
					<div class="wrap"><h2>Message Added</h2></div>
					<a href="admin.php?page=wpresponder/autoresponder.php&action=manage&aid=<?php echo $_GET['aid'] ?>" class="button">OK</a>
					</a>
					<?php
					return;
				}
			}
		}
		
		$parameters->subject = $subject;
	$parameters->textbody = $textbody;
	$parameters->htmlenabled = ($htmlenabled)?1:0;
	$parameters->htmlbody = $htmlbody;
	$parameters->textbody = $textbody;
	$parameters->sequence = $sequence;

	}
	$parameters->buttontext = "Create Message";

	$parameters->formtitle = "Create Folllow Up Mail";
  	wpr_mail_form($parameters,"autoresponder",$error);
	
	
}

function _wpr_manage_responder_list()
{
	global $wpdb;
	$id = $_GET['aid'];
	$responder = _wpr_autoresponder_get($id);
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_autoresponder_messages where aid=".$id." order by sequence";
	$messages = $wpdb->get_results($query);
	?>
<div class="wrap">    <h2>'<?php echo $responder->name ?>' Follow Up Messages</h2></div>

<table class="widefat">
    <thead>
      <tr>

    	<th>Subject</th>
        <th>Sequence</th>
        <th>Action</th>
	    </tr>
    </thead>
    <?php
	foreach ($messages as $message)
	{?>
    <tr>
       <td><?php echo htmlspecialchars($message->subject) ?></td>
       <td><?php echo htmlspecialchars($message->sequence) ?></td>
       <td><a href="admin.php?page=wpresponder/autoresponder.php&action=manage&mact=edit&aid=<?php echo $_GET['aid'] ?>&id=<?php echo $message->id ?>" class="button">Edit</a>&nbsp;<a href="admin.php?page=wpresponder/autoresponder.php&action=manage&mact=delete&aid=<?php echo $_GET['aid'] ?>&id=<?php echo $message->id ?>" class="button">Delete</a></td>
    </tr>
       <?php
	}
	?>
</table>
<a href="admin.php?page=wpresponder/autoresponder.php" class="button">&laquo; Back To Autoresponders</a>&nbsp;<input type="button" value="Add Message" onclick="window.location='admin.php?page=wpresponder/autoresponder.php&action=manage&mact=create&aid=<?php echo $_GET['aid'] ?>'" class="button" />

<?php
}

function _wpr_manage_responder_edit()
{
	global $wpdb;
	$error="";
	if (isset($_POST['subject']))
	{
		$subject = $_POST['subject'];
		$textbody = $_POST['body'];
		$aid = $_GET['aid'];
		$id = $_GET['id'];
		$htmlenabled = ($_POST['htmlenabled'] == "on");
		$htmlbody = $_POST['htmlbody'];
		$sequence = $_POST['sequence'];
		if (!($subject && $textbody))
		{
		  $error = "Subject and Text Body are required";
		}
		else
		{
			if ($htmlenabled && !$htmlbody)
			{
				$error = "You have enabled HTML E-Mail but not entered any content for the HTML E-mail.";
			}
			else
			{
				$sequence = (int) $sequence;
				$query = "UPDATE ".$wpdb->prefix."wpr_autoresponder_messages set subject='$subject',textbody='$textbody',htmlbody='$htmlbody',htmlenabled='".((int)$htmlenabled)."',sequence=$sequence where id=$id";
				$wpdb->query($query);
				?>
<script>
window.location='admin.php?page=wpresponder/autoresponder.php&action=manage&aid=<?php echo $_GET['aid']; ?>';
</script>
<?php
				return;
			}
		}
		
		$parameters->subject = $subject;
		$parameters->textbody = $textbody;
		$parameters->htmlbody = $htmlbody;
		$parameters->sequence = $sequence;

	}

	if (empty($parameters))
	{
		$id = $_GET['id'];
		$parameters = _wpr_get_autoresponder_message($id);
	}
	$parameters->formtitle = "Edit Follow Up Mail";
	$parameters->buttontext = "Save Message";
  	wpr_mail_form($parameters,"autoresponder",$error);
	
	
}



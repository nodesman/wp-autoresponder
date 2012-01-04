<?php
include "autoresponder.lib.php";
include "manage_autoresponder.php";

function _wpr_autoresponder_delete()
{
	global $wpdb;
	$id = $_GET['aid'];
	//delete all subscriptions to this autoresponder
	if ($_GET['confirm'] == "true")
	{
		$query = "DELETE FROM ".$wpdb->prefix."wpr_followup_subscriptions where type='autoresponder' and eid='$id";
		$wpdb->query($query);
		$query = "DELETE FROM ".$wpdb->prefix."wpr_autoresponder_messages where aid='$id";
		$wpdb->query($query);
		$query = "DELETE FROM ".$wpdb->prefix."wpr_autoresponders where id='$id'";
		$wpdb->query($query);
		_wpr_autoresponder_back2home(0);
		return;
	}
?>
<div class="wrap"><h2>Delete Autoresponder</h2></div><br />
<blockquote>
<div style="background-color: #FFFF80; padding: 10px; border: 1px solid #A8A409"><strong>Are you sure you want to delete this autoresponder? This CANNOT be undone.</strong></div>
<br />
This will also:
<br />
<br />
<ol>
  <li>Delete all messages of this autoresponder.</li>
  <li>Stop sending follow up messages to subscribers who have subscribed to this autoresponder.</li>
  </ol>
</blockquote>
<br />
<a href="<?php echo $_SERVER['REQUEST_URI'] ?>&confirm=true" class="button-primary">Delete</a> <a href="<?php echo $_SERVER['REQUEST_URI'] ?>&confirm=true" class="button-primary">Cancel</a> 
<?php
	//delete all forms 
}
function wpr_autoresponder()
{
	if (_wpr_no_newsletters("To create an autoresponder"))
		return;
	$action = $_GET['action'];
	switch ($action)
	{
		case 'create':
			_wpr_autoresponder_create();
		break;	
		case 'delete':
		   _wpr_autoresponder_delete();
		break;
		case 'manage':
		wpr_manage_responder();
		break;
		default:
		_wpr_autoresponder_list();
	}
}


function _wpr_autoresponder_list()
{
	global $wpdb;
	$getAutorespondersQuery = "SELECT a.* FROM ".$wpdb->prefix."wpr_autoresponders a, ".$wpdb->prefix."wpr_newsletters b where a.nid=b.id;";
	$autoresponders = $wpdb->get_results($getAutorespondersQuery);
	
	?>
    <div class="wrap"><h2>Manage Autoresponders</h2></div>
    <table class="widefat">
              <thead>
    <tr>

        <th scope="col">Autoresponder Name</th>
        <th scope="col">Belongs To Newsletter</th>
        <th scope="col">Actions</th>
    </tr>
    </thead>
     <?php
	 if (count($autoresponders))
	 {
		foreach ($autoresponders as $responder)
		{
			?>
			<tr>
			   <td><?php echo $responder->name ?></td>
			   <td><?php $newsletter = _wpr_newsletter_get($responder->nid);
			   echo $newsletter->name ?></td>
			   <td><input type="button" value="Manage Messages" class="button" onclick="window.location='admin.php?page=wpresponder/autoresponder.php&action=manage&aid=<?php echo $responder->id ?>';" /><input type="button" value="Delete" onclick="window.location='admin.php?page=wpresponder/autoresponder.php&action=delete&aid=<?php echo $responder->id ?>';" class="button" />
			   </tr>
			<?php
		}
	 }
	 else
	 {
		 ?>
		 <tr>
		   <td colspan="10" align="center">-No Autoresponders -</td>
           </tr>
           <?php
	 }
	?>
    </table>
    <input type="button" value="Create New" class="button-primary" onclick="window.location='admin.php?page=wpresponder/autoresponder.php&action=create';" />
    <?php
}


function _wpr_autoresponder_create()
{
	global $wpdb;
	if (isset($_POST['name']))
	{
		$params = $_POST;
		$name = $_POST['name'];
		if ($name)
		{
			$name = $_POST['name'];
			$nid = $_POST['nid'];
			$query = "select * from ".$wpdb->prefix."wpr_autoresponders where nid='$nid' and name='$name'";
			$checkingRes = $wpdb->get_results($query);
			if (count($checkingRes))
			{
				$error = "An autoresponder of that name exists for that newsletter. Please choose a different name.";				
			}
			else
			{
				$query = "INSERT INTO ".$wpdb->prefix."wpr_autoresponders (nid, name) values ('$nid','$name')";
				$result = $wpdb->query($query);
				?>
                <script>window.location='admin.php?page=wpresponder/autoresponder.php';</script>
                <?php
			}
		}
		else
		{
			$error = "Name field is required";
		}

	}
	$params = (object) (isset($params))?$params: array();
	$form = array('title'=> 'Create Autoresponder','button'=>'Create','error'=>$error);
	$form = (object) $form;
	$form->error = $error;
	_wpr_autoresponder_form($params,$form);
}

function _wpr_autoresponder_form($params,$form)
{
	global $wpdb;
	?>
    <div style="color: red; font-weight: bold" align="center"><?php echo $form->error; ?></div>
<div class="wrap"><h2><?php echo $form->title ?></h2></div>
<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
<table>
  <tr>
    <td>Name:</td>
    <td><input type="text" name="name" value="<?php echo $params->name ?>" /></td>
  </tr>
  <tr>
  <td>Newsletter To Subscribe:</td>
    <td><select name="nid">
       <?php 
	   $query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters";
	   $newsletters = $wpdb->get_results($query);
	   foreach ($newsletters as $newsletter)
	   {
		 ?>
         <option value="<?php echo $newsletter->id ?>"  ><?php echo $newsletter->name ?></option>
         <?php
	   }
	   ?>
       </select>
       </td>
       </tr>
  
  <tr>
   <td colspan="2"><input type="submit" value="Create" class="button" /><input type="button" value="Cancel" onclick="window.location='admin.php?page=wpresponder/autoresponder.php'" class="button" /></td></tr>
</table>
</form>
<?php
}


function _wpr_autoresponder_back2home($delay=0)
{
	?>
    <script> 
	window.setTimeout("window.location ='admin.php?page=wpresponder/autoresponder.php'",<?php echo $delay ?>*1000);</script>
    <?php
}

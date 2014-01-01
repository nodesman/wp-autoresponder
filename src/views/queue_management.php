<?php

?>
<div class="wrap">
<h2>Queue Management</h2>

<P>The queue is a database table in which all of the outgoing emails are placed before they are processed for delivery. The emails will be fetched and delivered as per the hourly limit set in the <a href="admin.php?page=_wpr/settings">Settings</a> section. This queue has a limit on the number of e-mails can be filled in it. If the queue is filled WP Responder will not be able to send any more e-mail.
</P>
<table>
  <tr>
    <td>Number of E-mails Pending Delivery: </td>
    <td><?php echo $number_of_pending ?></td>
 </tr>
 <tr>
    <td>Number of Sent E-mails:  </td>
    <td><?php echo $number_of_sent ?></td>
 </tr>
 <tr>
    <td>Size of Queue Table:  </td>
    <td><?php echo ByteSize($queue_table_size) ?></td>
</tr>
</table>
<p></p>
<form style="display:inline" action="<?php echo admin_url("admin.php?page=_wpr/queue_management&subact=truncate_queue"); ?>" method="post">
<?php wp_nonce_field("_wpr_truncate_queue") ; ?>
<input type="submit" name="submit" value="Empty The Queue" onClick="return window.confirm('WARNING: Are you sure you want to delete all the rows in the queue table? All sent and unsent e-mails will be deleted. This action CANNOT be undone.');" class="button-primary" style="background-color:#F00"/></form>
<form style="display:inline" action="<?php echo admin_url("admin.php?page=_wpr/queue_management&subact=delete_sent_emails"); ?>" method="post">
<?php wp_nonce_field("_wpr_delete_sent_mail") ; ?>
<input type="submit" name="submit" value="Delete Only Sent E-mails" onClick="return window.confirm('Are you sure you want to delete all the sent e-mail from the queue? This action CANNOT be undone.');" class="button-primary" />
</form>
<p></p><p></p>

<strong>Important: </strong> All sent emails in queue will be automatically deleted if queue size exceeds <?php echo ByteSize(WPR_MAX_QUEUE_TABLE_SIZE); ?> in size. 
</div>

<?php
/*
<h2>Delivery Queue</h2>

<table class="widefat">
   <tr>
      <thead>
      <th> To </th>
      <th> Subject </th>
      <th></th>
    </thead>
   </tr>
<?php
if (is_array($emails_in_queue) && count($emails_in_queue) > 0) 
{
	foreach ($emails_in_queue as $email)
	{
		?>
           <tr>
               <td><?php echo $email->to ?></td>
               <td><?php echo $email->subject ?></td>
           </tr>
           <?php
	}
}
else {
	?>
    <tr>
       <td colspan="5"><center>--No Emails In Queue--</td>
    </tr>
   <?php	
}
?>
</table>
<?php
*/
?>
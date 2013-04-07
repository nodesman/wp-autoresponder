<div class="wrap">

<h2>Confirm Deletion Of Newsletter '<?php echo $newsletter_name ?>'?</h2>

<p>Are you sure you want to delete this newsletter? <br>
  <br>
  The following will also be deleted:
  
  
 </p>
<ol>
  <li>All autoresponders and all of the messages of the autorespodners of this newsletter</li>
  <li>All the custom fields of this newsletter</li>
  <li>All the subscribers (<?php echo intval($subscriber_count) ?> currently active) of this newsletter and their data</li>
  <li>All the subscription forms of this newsletter</li>
  <li><?php echo intval($emailsPendingDelivery); ?> emails that are pending delivery to the subscribers of this newsletter</li>
  <li>All subscriber transfer rules to move subscribers from and to this newsletter.</li>
</ol>
<p>&nbsp;</p>
<form action="<?php echo Routing::url('newsletter',array(
															'confirmed'=>'true',
															'act'=>'delete',
															'nid'=>$nid)
									  ); ?>" method="post">
<input type="hidden" name="nid" value="<?php echo $nid ?>" />
<table>
  <tr>
  <td width=200"><input type="submit" name="submit" value="Confirm Deletion" class="button-primary"></td>
  <td><input type="button" class="button" value="Cancel" name="submit" onClick="window.location='admin.php?page=_wpr/newsletter';"></td>
</tr>
</table>
</form>
</form>
</div>
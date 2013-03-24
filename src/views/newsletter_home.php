<div class="wrap">

<blockquote>

    <h2>Newsletters</h2>

    <table class="widefat">
    <thead><tr>
               <th scope="col">Id</th>
                <th scope="col">Name</th>
                <th scope="col">Reply-To</th>
                <th scope="col">From Name</th>
                <th scope="col">From E-mail</th>
                <th scope="col" width="600">Actions</th>      
     </tr></thead>

     <?php 
	 if (count($newsletterList))
	 {
		 foreach ($newsletterList as $list) { 
		 ?>
		 <tr>
			 <td><?php echo $list->id; ?></td>
  			 <td><?php echo $list->name; ?></td>
  			 <td><?php echo $list->reply_to; ?></td>
  			 <td><?php echo $list->fromname; ?></td>
  			 <td><?php echo $list->fromemail; ?></td>
		   <td>
		   <input type="button" name="Edit" onclick="window.location='admin.php?page=_wpr/newsletter&act=edit&nid=<?php echo $list->id; ?>';" value="Edit" class="button" />
		   <input type="button" name="Manage Leads" value="Manage Leads" class="button" onclick="window.location='admin.php?page=wpresponder/subscribers.php&action=nmanage&nid=<?php echo $list->id; ?>';" />
		   <input type="button" name="E-mails" value="Custom Fields" onclick="window.location='admin.php?page=_wpr/custom_fields&cfact=manage&nid=<?php echo $list->id; ?>';" class="button"/>
		   <input type="button" name="Delete" value="Delete" class="button" onclick="window.location='admin.php?page=_wpr/newsletter&act=delete&nid=<?php echo $list->id; ?>';" />
		   <?php do_action("_wpr_newsletter_home_actions"); ?>
		   </td>
	
		   </tr>
	
		   <?php
	
		 }
	 }
	 else
	 {
		 ?>
         <td colspan="10"><center>No Newsletters Created.</td>
         <?php
		 
	 }

?></table>
<input type="button" onclick="window.location='<?php echo $_SERVER['REQUEST_URI']; ?>&act=add'" value="Create Newsletter" class="button" />
</blockquote>
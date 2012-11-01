<div class="wrap">

    <div id="wpr-chrome">
        <div id="breadcrumb">
            <ul>
                <li><a href="admin.php?page=_wpr/autorespondres"><?php _e("Autoresponders"); ?></a></li>
                <li><a href="admin.php?page=_wpr/autoresponders&action=add"><?php _e("Add"); ?></a></li>
            </ul>
        </div>
        
        
        
        <div id="autoresponder-add-form">
        
        <h2>Add Autoresponder</h2>

			<form action="admin.php?_wpr/autoresponders&act" method="post">
			<div class="field">
				<label for="autoresponder_name">
				Name Of Autoresponder: 
				</label>
				<input type="text" name="autoresponder_name" id="autoresponder_name" value="" />
			</div>
			<div class="field">
				<label for="newsletter_select">
				Select Newsletter Of Autoresponder: 
				</label>
				<select name="newsletter" id="newsletter_select">
					<?php foreach ($newsletters as $newsletter) { ?>
					<option value="<?php echo $newsletter->getId(); ?>"><?php echo $newsletter->getName(); ?></option>
					<?php						
					}
					?>

				</select>
			</div>
			
			<input type="submit" value="Add" class="wpr-action-button">
			<a href="admin.php?page=_wpr/autoresponders" class="wpr-button">Cancel</a>
			</form>
		</div>
        
        
        
        
        
   </div>
   
</div>
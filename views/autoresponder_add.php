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

			<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">

            <table>

                <tr>
                    <td><label for="autoresponder_name">
                        <strong><?php _e("Name Of Autoresponder:","wpr_autoresponder") ?></strong>
                        <small><?php _e("Enter the name of the autoresponder you want to create. For example: Prospects Follow-Up Series"); ?></small>
                    </label>
                    </td>
                    <td valign="top"><input type="text" name="autoresponder_name" id="autoresponder_name" value="" /></td>
                </tr>
                <tr>
                      <td>
				<label for="newsletter_select">
				<strong><?php _e("Select Newsletter Of Autoresponder:","wpr_autoresponder"); ?></strong>
                    <small>Select the newsletter to which this autoresponder will be available</small>
				</label>
                         </td>
                <td>
                <select name="nid" id="newsletter_select">
                    <?php
                    if (0 < count($newsletters)) {
                    ?>
                    <?php
                     foreach ($newsletters as $newsletter) { ?>
                     <option value="<?php echo $newsletter->getId(); ?>"><?php echo $newsletter->getName(); ?></option>
                    <?php } ?>
                    <?php
                    }
                    ?>
                </select>
                      </td>
                </tr>
        </table>

                <input type="hidden" name="wpr_form" value="add_autoresponder"/>
                <?php wp_nonce_field('_wpr_add_autoresponder', '_wpr_add_autoresponder'); ?>
			<input type="submit" value="Add" class="wpr-action-button">
			<a href="admin.php?page=_wpr/autoresponders" class="wpr-button">Cancel</a>
			</form>
		</div>
        
        
        
        
        
   </div>
   
</div>
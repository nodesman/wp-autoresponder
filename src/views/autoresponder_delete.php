<div class="wrap">
     <div id="wpr-chrome">

         <div id="breadcrumb">
             <ul>
                 <li><a href="admin.php?page=_wpr/autoresponders"><?php _e("Autoresponders"); ?></a></li>
                 <li><a href="admin.php?page=_wpr/autoresponders&action=delete&id=<?php echo $autoresponder->getId(); ?>"><?php _e("Delete"); ?></a></li>
             </ul>
         </div>


        <h2><?php _e(sprintf("Are you sure you want to delete the autoresponder %s ?", $autoresponder->getName()), 'wpr_autoresponder'); ?></h2>
    <?php _e('The following will also be deleted:'); ?>

    <ul>
        <li><?php _e('All autoresponder messages'); ?></li>
        <li><?php _e('All emails pending delivery for this autoresponder'); ?></li>
    </ul>

<form action="<?php $_SERVER['REQUEST_URI'] ?>" method="post">
    <input type="hidden" name="autoresponder" value="<?php echo $autoresponder->getId(); ?>">
    <input type="hidden" name="wpr_form" value="delete_autoresponder"/>
    <?php wp_nonce_field('_wpr_delete_autoresponder', '_wpr_delete_autoresponder'); ?>
    <input type="submit" name="confirm" value="Confirm" class="wpr-action-button"/>
    <a href="admin.php?page=_wpr/autoresponders" class="wpr-button">Cancel</a>
</form>
 </div>
</div>
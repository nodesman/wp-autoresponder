<div class="wrap">
     <div class="wpr-chrome">

    <h2>Are you sure you want to delete the autoresponder '<?php echo $autoresponder->getName() ?>'?</h2>

    The following will also be deleted:

    <ul>
        <li>All autoresponder messages</li>
        <li>All emails pending delivery for this autoresponder</li>
    </ul>
<form action="<?php $_SERVER['REQUEST_URI'] ?>" method="post">
    <input type="hidden" value="autoresponder" value="<?php echo $autoresponder->getId(); ?>">
    <input type="hidden" value="wpr_form" value="delete_autoresponder"/>
    <input type="submit" name="confirm" value="Confirm" class="wpr-action-button"/>
    <a href="admin.php?page=_wpr/autoresponders" class="wpr-button">Cancel</a>
</form>


 </div>


</div>
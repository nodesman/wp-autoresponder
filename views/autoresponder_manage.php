<div class="wrap">
<div id="wpr-chrome" class="autoresponder-manage">

    <div id="breadcrumb">
        <ul>
            <li><a href="admin.php?page=_wpr/autoresponders"><?php _e("Autoresponders"); ?></a></li>
            <li><a href="admin.php?page=_wpr/autoresponders&action=manage&id=<?php echo $autoresponder->getId(); ?>"><?php _e(sprintf("Manage '%s'", $autoresponder->getName())); ?></a></li>
        </ul>
    </div>
    <h2>Manage Autoresponder</h2>
    <div class="autoresponder-manage">


        <div class="row head">
            <div class="day-index column" valign="middle">Day #</div>
            <div class="message-title column">Title</div>
        </div>

        <?php
foreach ($messages as $message) {

 ?>
    <div class="row">
            <div class="day-index column" valign="middle">Day 9</div>
            <div class="message-title column"><a href="#"><?php echo $message->getSubject(); ?></a></div>
            <div class="edit-link column"><a href="admin.php?page=_wpr/autoresponders&action=message_edit&id=<?php echo $message->getId(); ?>" class="wpr-action-button">Edit</a></div>
            <div class="delete-link column"><a href="admin.php?page=_wpr/autoresponders&action=message_delete&id=<?php echo $message->getId(); ?>" class="wpr-action-button">Delete</a> </div>
        </div>
    <?php
}

?>
    </div>
    <script>
        $(document).ready(function() {
            $('.wpr-chrome').css("height",document.availHeight);
        });
    </script>




</div>

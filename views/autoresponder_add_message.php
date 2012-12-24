<div class="wrap compose_message" id="wpr-chrome">
    <h2>Add Message</h2>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
    <?php do_action("_wpr_autoresponder_add_message_before_sidebars"); ?>

    <div id="compose-sidebar">
        <strong>For autoresponder:</strong> <p id="autoresponder-name-sidebar"><?php echo $autoresponder->getName(); ?></p>
        <strong>To be sent on:</strong> <p id="autoresponder-offset"><input type="text" size="3" value="0" id="offset"/> days after subscription</p>
        <?php do_action("_wpr_autoresponder_message_add_sidebar_before_action_buttons"); ?>
        <input id="add_autoresponder_button" type="submit" value="<?php echo _e("Add Message"); ?>" class="wpr-action-button"/>
        <a id="autoresponder_add_cancel" href="admin.php?page=_wpr/autoresponders&action=manage&id=<?php echo $autoresponder->getId(); ?>" class="wpr-action-button"><?php echo _e("Cancel"); ?></a>
    </div>
    <div id="custom-fields-sidebar">
         <h2>Custom Fields Placeholders</h2>

        <p>Use the following placeholders in the email to have the recipient information appear where you place them:</p>
        <ul>
            <li>[!name!] - Name of subscriber</li>
            <?php foreach ($custom_fields as $key=>$label) {
  ?>
                <li>[!<?php echo $key?>!] - <?php echo $label ?></li>
    <?php
}
?>
        </ul>

    </div>
    <?php do_action("_wpr_autoresponder_add_message_after_sidebars"); ?>

    <input type="text" name="subject" id="post-compose-subject" value="Subject..."/>
    <div id="composition-section">

        <div id="compose_tabs">
            <ul>
                <li><a href="#rich_body">Rich Text</a></li>
                <li><a href="#text_body">Plain Text</a></li>
            </ul>

            <div id="rich_body">
                <textarea name="rich_text" id="rich_body_field"></textarea>
            </div>
            <div id="text_body">
                <textarea name="text_body" id="text_body_field"></textarea>

            </div>
            <?php do_action("_wpr_autoresponder_message_after_custom_fields"); ?>
        </div>
    </div>
    <?php do_action("_wpr_autoresponder_add_message_after_compose_box"); ?>

</form>
</div>
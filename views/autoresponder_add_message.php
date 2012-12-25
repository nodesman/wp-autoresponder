<div class="wrap compose_message" id="wpr-chrome">
    <h2>Add Message</h2>

    <?php
    if (isset($errors) && 0 != count($errors)) {
    ?>
        <div class="wpr-error">
<ul>
            <?php
            foreach ($errors as $error) {
                ?>
                <li><?php echo $error; ?></li>
                <?php
            }
            ?>
</ul>

        </div>
    <?php
    }
?>


<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
    <?php do_action("_wpr_autoresponder_add_message_before_sidebars"); ?>

    <div id="compose-sidebar">
        <strong>For autoresponder:</strong> <p id="autoresponder-name-sidebar"><?php echo $autoresponder->getName(); ?></p>
        <strong>To be sent on:</strong> <p id="autoresponder-offset"><input type="text" name="offset" size="3" value="0" id="offset"/> days after subscription</p>
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

    <input type="text" name="subject" id="post-compose-subject" placeholder="Subject..." value="<?php if (isset($form_values['subject'])) { echo $form_values['subject']; } ?>"/>
    <div id="composition-section">

        <div id="compose_tabs">
            <ul>
                <li><a href="#rich_body">Rich Text Email Body</a></li>
                <li><a href="#text_body">Plain Text Email Body</a></li>
            </ul>

            <div id="rich_body">
                <textarea name="htmlbody" id="rich_body_field"><?php if (isset($form_values['htmlbody'])) { echo $form_values['htmlbody']; } ?></textarea>
            </div>
            <div id="text_body">
                <textarea name="textbody" id="text_body_field" placeholder="Enter text body here..."><?php if (isset($form_values['textbody'])) { echo $form_values['textbody']; } ?></textarea>

            </div>
            <?php do_action("_wpr_autoresponder_message_after_custom_fields"); ?>
        </div>
    </div>
    <?php wp_nonce_field('_wpr_add_autoresponder_message', '_wpr_add_autoresponder_message'); ?>
    <input type="hidden" name="wpr_form" value="add_autoresponder_message"/>
    <?php do_action("_wpr_autoresponder_add_message_after_compose_box"); ?>

</form>
</div>
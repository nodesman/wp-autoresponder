<div class="wrap compose_message edit_autoresponder_message" id="wpr-chrome">

    <?php
    if (isset($_GET['success'])) 
    {
    ?>
    <div class="updated">
        <p><?php _e('Message Updated.'); ?> </p>
    </div>
    <?php     
    }
    ?>
    
    <h2><?php _e('Edit Message'); ?></h2>

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
            <script>
                var whetherSecondRendering = true;
            </script>

        </div>
    <?php
    }
?>

<form action="admin.php?page=_wpr/autoresponders&action=edit_message&id=<?php echo $message->getId(); ?>" method="post">
    <?php do_action("_wpr_autoresponder_update_message_before_sidebars"); ?>

    <div id="compose-sidebar">
        <strong>For autoresponder:</strong> <p id="autoresponder-name-sidebar"><?php echo $autoresponder->getName(); ?></p>
        <strong>To be sent on:</strong> <p id="autoresponder-offset"><input type="text" name="offset" size="3" value="<?php echo (isset($form_values))?$form_values['offset']:''; ?>" id="offset"/> days after subscription</p>
        <?php do_action("_wpr_autoresponder_message_update_sidebar_before_action_buttons"); ?>
        <input id="update_autoresponder_button" type="submit" value="<?php echo _e("Update Message"); ?>" class="wpr-action-button"/>
        <a id="autoresponder_update_cancel" href="admin.php?page=_wpr/autoresponders&action=manage&id=<?php echo $autoresponder->getId(); ?>" class="wpr-action-button"><?php echo _e("Cancel"); ?></a>
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
    <?php do_action("_wpr_autoresponder_update_message_after_sidebars"); ?>

    <input type="text" name="subject" id="post-compose-subject" placeholder="Subject..." value="<?php if (isset($form_values['subject'])) { echo $form_values['subject']; } ?>"/>
    <div id="composition-section">

        <div id="compose_tabs">
            <ul>
                <li><a href="#rich_body">Rich Text Email Body</a></li>
                <li><a href="#text_body">Plain Text Email Body</a></li>
            </ul>

            <div id="rich_body">
                <div class="rich-text-loader" >
                    <p>Loading</p>
                    <img src="data:image/gif;base64,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"/>
                </div>
                <textarea name="htmlbody" id="rich_body_field"><?php if (isset($form_values['htmlbody'])) { echo $form_values['htmlbody']; } ?></textarea>
            </div>
            <div id="text_body">
                <textarea name="textbody" id="text_body_field" placeholder="Enter text body here..."><?php if (isset($form_values['textbody'])) { echo $form_values['textbody']; } ?></textarea>

            </div>
            <?php do_action("_wpr_autoresponder_message_after_custom_fields"); ?>
        </div>
    </div>
    <?php wp_nonce_field('_wpr_update_autoresponder_message', '_wpr_update_autoresponder_message'); ?>
    <input type="hidden" name="wpr_form" value="update_autoresponder_message"/>
    <?php do_action("_wpr_autoresponder_update_message_after_compose_box"); ?>

</form>
</div>
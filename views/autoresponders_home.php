<div class="wrap">

    <div id="wpr-chrome">
        <div id="breadcrumb">
            <ul>
                <li><a href="#"><?php _e("Autoresponders"); ?></a></li>
            </ul>
        </div>

    <div id="autoresponder-manage-header">

        <div class="alignright">
            <input type="button" value="Add Autoresponder" class="wpr-action-button"id="autoresponder-add">
        </div>


        <label for="autoresponder-dropdown"><?php _e("Select Autoresponder:") ?></label>
        <select name="autoresponder" id="autoresponder-dropdown">
            <?php
                foreach ($autoresponders as $responder) {
                    ?>
                    <option><?php echo $responder->getName() ?></option>
                    <?php
                }
            ?>
        </select>

        <input type="button" value="Manage" class="wpr-action-button">

    </div>
        <div class="wpr-list-wrapper">
            <div class="wpr-list-content">
            <?php
if ( isset($autoresponders) && 0 < count($autoresponders))  {

    foreach ($autoresponders as $autoresponder) {

                    ?>
                    <div class="wpr-autoresponder-list-item">

                        <div class="autoresponder-messages-list">
                            <h2><?php printf(__('Messages in %s Autoresponder'),$autoresponder->getName()); ?></h2>

                            <div class="autoresponder-message-actions">
                                <input type="button" class="wpr-action-button block" value="<?php _e('Add Follow-Up Message'); ?>">
                                <input type="button" class="wpr-action-button block" value="<?php _e('Delete Follow-Up Message'); ?>">
                                <input type="button" class="wpr-action-button block" value="<?php _e('Edit Follow-Up Message'); ?>">
                            </div>

                            <select name="messages[]" size="10">
                                <?php
                                $messages = $autoresponder->getMessages();
                                foreach ($messages as $message)  {
                                    ?>
                                    <option><?php printf(__("Day %d"), $message->sequence) ?> : <?php echo $message->subject; ?></option>
                                    <?php

                                }
                                ?>
                            </select>
                        </div>

                        <h2 class="wpr-autoresponder-title"><?php echo $autoresponder->getName(); ?></h2>

                        <h3><?php echo printf(__('belongs to %s newsletter') ,$autoresponder->getNewsletter()->getNewsletterName()); ?></h3>

                        <input type="button" value="<?php _e("Delete Autoresponder"); ?>" class="wpr-action-button block">

                    </div>
                    <?php

                }
}
            else {
                ?>
                <div class="empty-list=message">
                    <?php _e('No autoresponders have been created yet. Click on Add Autoresponder button to create one now.') ?>
                </div>
                    <?php
            }
            ?>
            </div>
        </div>


    </div>
</div>
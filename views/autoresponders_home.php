<div class="wrap">

    <div id="wpr-chrome">
        <div id="breadcrumb">
            <ul>
                <li><a href="#"><?php _e("Autoresponders"); ?></a></li>
            </ul>
        </div>

        <div id="autoresponder-manage-header" ng-app="WPRAutoresponderManage">

            <div class="alignright">
                <input type="button" value="Add Autoresponder" class="wpr-action-button" id="autoresponder-add">
            </div>


        </div>
        <div class="wpr-list-wrapper">
            <div class="wpr-list-content">
                <?php
                if ((true === isset($autoresponders)) && 0 < count($autoresponders)) {

                    foreach ($autoresponders as $autoresponder) {
                        ?>
                        <div class="wpr-autoresponder-list-item">

                            <div class="wpr-autoresponder-overview">

                                <div class="wpr-autoresponder-overview-heading">
                                    Autoresponder Status
                                </div>

                                <table>
                                    <tr>
                                        <td><strong><?php _e('Messages: '); ?></strong></td>
                                        <td><?php echo count($autoresponder->getMessages()); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e('Subscribers: '); ?></strong></td>
                                        <td><?php echo count($autoresponder->getMessages()); ?></td>
                                    </tr>
                                </table>
                                <?php do_action("_wpr_autoresponder_list_item_overview", $autoresponder->getId()); ?>
                            </div>

                            <h2 class="wpr-autoresponder-title"><a href="#"><?php echo $autoresponder->getName(); ?></a>
                            </h2>

                            <h3><?php printf(__('belongs to %s newsletter.'), $autoresponder->getNewsletter()->getNewsletterName()); ?></h3>

                            <div class="wpr-autoresponder-action-items">
                                <a href="#"><?php _e('Delete Autoresponder'); ?></a> | <a href="#">Manage Messages</a>
                                <?php do_action('_wpr_autoresponder_list_item_actions_section', $autoresponder->getId()); ?>
                            </div>


                        </div>
                        <?php

                    }
                    //end foreach
                } else {
                    ?>
                    <div class="empty-list=message">
                        <?php _e('No autoresponders have been created yet. Click on Add Autoresponder button to create one now.') ?>
                    </div>
                    <?php
                }

                ?>

                <script>
                    var autoresponderInfo = <?php
                    $autoresponderListArray = array();
                    foreach ($autoresponders as $responder) {
                        $autoresponderListArray[] = $responder->getId();
                    }
                    echo json_encode($autoresponderListArray);
                    ?>;
                </script>
            </div>
        </div>


    </div>
</div>
<div class="wrap">
    <div id="wpr-chrome">
        <div id="breadcrumb">
            <ul>
                <li><a href="#"><?php _e("Autoresponders"); ?></a></li>
            </ul>
        </div>

        <div id="autoresponder-manage-header" ng-app="WPRAutoresponderManage">

            <?php
            do_action("_wpr_autoresponders_manage_list_header");
            ?>

            <div class="alignright">
                <a href="admin.php?page=_wpr/autoresponders&action=add" class="wpr-action-button" id="autoresponder-add"> Add Autoresponder </a>
            </div>
        </div>
        <div class="wpr-list-wrapper">
            <div class="wpr-list-content">
                <?php
                if ((true === isset($autoresponders)) && 0 < count($autoresponders)) {

                    foreach ($autoresponders as $index=>$autoresponder) {
                        ?>
                        <div class="wpr-table">
	                        <div class="wpr-autoresponder-list-item <?php echo (($index+1) % 2 == 0 ) ? "even": "odd"; ?>">
                            <div class="title-actions">
                            	<h2 class="wpr-autoresponder-title"><a href="admin.php?page=_wpr/autoresponders&action=manage&id=<?php echo $autoresponder->getId() ?>"><?php echo $autoresponder->getName(); ?></a>
                            	</h2>

                            	<h3><?php printf(__('belongs to %s newsletter.'), $autoresponder->getNewsletter()->getName()); ?></h3>

	                            <div class="wpr-autoresponder-action-items">
	                                <a href="admin.php?page=_wpr/autoresponders&action=delete&id=<?php echo $autoresponder->getId() ?>"><?php _e('Delete Autoresponder'); ?></a> <a href="admin.php?page=_wpr/autoresponders&action=manage&id=<?php echo $autoresponder->getId(); ?>">Manage Messages</a>
	                                <?php do_action('_wpr_autoresponder_list_item_actions_section', $autoresponder->getId()); ?>
	                            </div>
                            </div>
                             <div class="wpr-autoresponder-overview">
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
	                        <div class="wpr-autoresponder-messages">
	                        <h2>Messages</h2>
	                            	<ul>
	                            	<?php 
	                            	$messagesInResponder = $autoresponder->getMessages();
	                            	if (0 == count($messagesInResponder)) {
	                            		
	                            		?>
	                            		<div class="empty">-- No Messages Defined --</div>
	                            		<?php
		                            	
	                            	}
	                            	else
	                            	{
		                            	foreach ($messagesInResponder as $message) 
		                            	{
			                            ?>
			                            <li><?php echo $message->getSubject() ?></li>
			                            <?php
		                            	}
		                            }
	                            	?>
	                            	
	                            	</ul>
                            
                                 </div>                         
	                           
	                        </div>
                        </div>
                        <?php

                    }
                    //end foreach
                } else {
                    ?>
                    <div class="empty-list-message">
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
        <?php include_once WPR_DIR."/views/templates/paging.php"; ?>
    </div>
</div>
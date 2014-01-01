<?php
?>
<div class="wrap">

<h2>Background Processes</h2>

Below are a list of background processes of WP Responder. You can use the Run Now button to trigger the execution of each of these processes.

<table class="widefat">
   <thead>
      <tr>
	      <th> Name Of Cron</th>
          <th> Run Now </th>
      </tr>      
   </thead>
      <tr>
         <td>Autoresponder Processor</td>
        <td> <form action="admin.php?page=_wpr/background_procs&run=autoresponder" method="post"><?php wp_nonce_field("_wpr_autoresponder_run"); ?><input type="submit" class="button-primary" onclick="return window.confirm('The page will take much time to load. Do NOT press escape, click on the stop button or press refresh. Do you want to continue?');" name="submit" value="Run Now"/></form></td>
     </tr>
      <tr>
         <td>Postseries Processor</td>
        <td> <form action="admin.php?page=_wpr/background_procs&run=postseries" method="post"><?php wp_nonce_field("_wpr_postseries_run"); ?><input type="submit" class="button-primary" onclick="return window.confirm('The page will take much time to load. Do NOT press escape, click on the stop button or press refresh. Do you want to continue?');" name="submit" value="Run Now"/></form></td>
     </tr>
      <tr>
         <td>Newsletter Broadcast Processor</td>
        <td> <form action="admin.php?page=_wpr/background_procs&run=newsletter_process" method="post"><?php wp_nonce_field("_wpr_newsletter_process_run"); ?><input type="submit" class="button-primary" onclick="return window.confirm('The page will take much time to load. Do NOT press escape, click on the stop button or press refresh. Do you want to continue?');"  name="submit" value="Run Now"/></form></td>
     </tr>
      <tr>
         <td>Blog Post E-mail Delivery Processor</td>
        <td> <form action="admin.php?page=_wpr/background_procs&run=blogpost_processor" method="post"><?php wp_nonce_field("_wpr_blogpost_processor_run"); ?><input type="submit" class="button-primary" onclick="return window.confirm('The page will take much time to load. Do NOT press escape, click on the stop button or press refresh. Do you want to continue?');" name="submit" value="Run Now"/></form></td>
     </tr>
      <tr>
         <td>Blog Category Subscription E-mail Delivery Processor</td>
        <td> <form action="admin.php?page=_wpr/background_procs&run=blogcat_processor" method="post"><?php wp_nonce_field("_wpr_blogcat_processor_run"); ?><input type="submit" class="button-primary" onclick="return window.confirm('The page will take much time to load. Do NOT press escape, click on the stop button or press refresh. Do you want to continue?');" name="submit" value="Run Now"/></form></td>
     </tr>
      <tr>
         <td>Delivery Queue Processor<br/>
         <small>Delivers maximum of 100 e-mails from the queue</small></td>
        <td> <form action="admin.php?page=_wpr/background_procs&run=delivery_queue" method="post"><?php wp_nonce_field("_wpr_delivery_queue_run"); ?><input type="submit" class="button-primary" onclick="return window.confirm('The page will take much time to load. Do NOT press escape, click on the stop button or press refresh. Do you want to continue?');" name="submit" value="Run Now"/></form></td>
     </tr>
</table>

<h2>Force Delivery</h2>

To force delivery of e-mails in queue, set the hourly limit to 0 at the <a href="<?php echo admin_url("admin.php?page=_wpr/settings"); ?>">settings page</a>, return to this interface and then click on the "Run Now" button against the Delivery Queue Processor. This will process and deliver 100 emails in the queue.

</div>
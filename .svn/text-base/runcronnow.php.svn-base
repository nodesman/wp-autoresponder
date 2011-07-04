<?php


function wpr_runcronnow_start()
{
	 switch ($_GET['action'])
	 {
		 
		 case 'true':
?>
<div align="center">
<h2>Running Cron...</h2>
<?php
do_action("wpr_cronjob");
?>
</div>
<h2>All Done!</h2>
<a class="button-primary" href="admin.php?page=wpresponder/runcronnow.php">&laquo; Back </a>
<?php
			break;
	    default:
?>
<h1>Run WPR Cron</h1>
<h3>What is WPR Cron Job?</h3>
<p>WP Responder relies on the wordpress's cron facility. The cron facility enables some internal functions to be triggered at a particular time (For example: at the first minute of every hour). When a website visitor visits your website, wordpress internally starts cron jobs.</p>

<p>The delivery of e-mail broadcasts, follow up email, blog subscriptions and post series subscriptions is done entirely in a cronjob. This is the only way to deliver emails without slowing down the website for you and your website visitors. Although the WP Respodner's crons are scheduled to run every 5 minutes, sometimes it may not run at the scheduled time because your website may not have enough traffic. <br>
</p>

<h3>Run it now?</h3>
To force the cron to run click on the button below. <strong>Please note that it may take much time for the next page to load:</strong><br>

<br>

<a href="<?php print $_SERVER['REQUEST_URI'] ?>&action=true" class="button-primary">Run WP Responder Cron Now</a>
<br>
<br>
Note: This runs only the crons related to WP Responder. Other plugins and their scheduled crons will remain unaffected.
</p>
<?php
	 }
}
	  
		

    
	
<?php
ob_end_clean();
header("Connection: close\r\n");
header("Content-Encoding: none\r\n");
ignore_user_abort(true);
ob_start();
global $wpdb;

$string = $_GET['wpr-confirm'];
$args = base64_decode($string);

$args = explode("%%",$args);

$id = (int) $args[0];
$hash = trim($args[1]);

if (get_magic_quotes_gpc()==1)
{
    $hash = addslashes($hash);
}
global $wpdb;
$subscribers_table = $wpdb->prefix."wpr_subscribers";
$query = $wpdb->prepare("SELECT * FROM $subscribers_table WHERE id=%d AND hash='%s' AND active=1 AND confirmed=0",$id,$hash);
$subs = $wpdb->get_results($query);
if (count($subs) == 0)
{
	?>
	<div align="center"><h2>Your subscription does not exist or you are already subscribed. </h2></div>
	<?php
	exit;
}
$subs = $subs[0];
$query = $wpdb->prepare("UPDATE $subscribers_table set confirmed=1,  active=1 where id=%d and hash='%s';",$id,$hash);
$wpdb->query($query);

$redirectionUrl = get_bloginfo("home")."/?wpr-confirm=2";
$subscriber = _wpr_subscriber_get($id);
_wpr_move_subscriber($subscriber->nid,$subscriber->email);
//This subscriber's follow up subscriptions' time of creation should be updated to the time of confirmation. 
$currentTime = time();
$followup_subscriptions_table = $wpdb->prefix."wpr_followup_subscriptions";
$query = $wpdb->prepare("UPDATE $followup_subscriptions_table SET doc='%s', last_date='%s' WHERE sid=%d;",$currentTime,$currentTime,$id);
$wpdb->query($query);
do_action("_wpr_subscriber_confirmed",$id);
sendConfirmedEmail($id);

?><script>
window.location='<?php echo $redirectionUrl ?>';
</script><?php

$size = ob_get_length();
header("Content-Length: $size");
ob_end_flush();     
flush();            
ob_end_clean();

do_action("_wpr_autoresponder_process",$id);


<?php



function _wpr_sub_rename_email_post_handler() {
    global $wpdb;
    $referer = $_SERVER['HTTP_REFERER']; 
    $old_email = trim($_POST['old_email']);
    $email = trim($_POST['email']);
    if (empty($email))
    {
        _wpr_admin_notice_set("_sub_email_edit_empty",__("The email address cannot be left empty"));
        wp_redirect($referer);
    }
    
    if ($old_email != $email)
    {
        $updateAllSubscriptionsQuery = sprintf("UPDATE %swpr_subscribers SET email='%s' WHERE email='%s';", $wpdb->prefix, $email, $old_email);
        $wpdb->query($updateAllSubscriptionsQuery);
        wp_redirect($referer);
    }
    
}


function _wpr_sub_subscribe_newsletter_form_post_handler()
{
    global $wpdb;
    $nonce=$_REQUEST['_wpnonce'];
    
    if (! wp_verify_nonce($nonce, '_wpr_sub_subscribe_newsletter') ) 
      die("Security check failed.");
    
    $sid = intval($_POST['sid']);
    $nid = intval($_POST['nid']);
    $email = $_POST['email'];
    $timeStamp = time();
    $hash = _wpr_subscriber_hash_generate();
    $addSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (nid, name, email, date, fid, active, confirmed, hash)
                                                                SELECT %d, name, email, %s,0,1,1,'%s' FROM %swpr_subscribers WHERE id=%d;
                                   ",$wpdb->prefix,$nid,$timeStamp, $hash, $wpdb->prefix, $sid);
    
    $wpdb->query($addSubscriberQuery);
    
    //above query will fail if the subscribe was already subscribed at one time
    //run the other query:
    $activateSubscriptionQuery = sprintf("UPDATE %swpr_subscribers SET active=1, confirmed=1 
                                         WHERE email='%s'
                                         AND nid=%d;",$wpdb->prefix, $email, $nid );
    $wpdb->query($activateSubscriptionQuery);
    wp_redirect($_SERVER['HTTP_REFERER']);
}


?>

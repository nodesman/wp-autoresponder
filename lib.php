<?php

function isReceivingFollowupPosts($sid)
{
    global $wpdb;



    //fetch all the post series subscriptions of this subscriber



    $query = "SELECT * FROM ".$wpdb->prefix."wpr_followup_subscriptions where sid=$sid;";

    $results = $wpdb->get_results($query);



    if (count($results) ==0)

        return false;


    //for each post series or follow up series subscription, check if it is active

    foreach ($results as $subscription)

        {

        if ($subscription->type == 'postseries')
                {
            return isPostSeriesSubscriptionActive($subscription);
        }
        else if ($subscription->type == 'autoresponder')
        {
            return isAutoresponderSeriesActive($subscription);
        }



    }



}



/*

 * This function checks if the subscription

 */

function isPostSeriesSubscriptionActive($subscription)

{

    global  $wpdb;

    //get number of posts in the category



    //get the post series

    $pid= $subscription->eid;

    $query = "SELECT * FROM ".$wpdb->prefix."wpr_blog_series where id=$pid";

    $results = $wpdb->get_results($query);

    if (count($results) != 1)

        {

        return;

    }

    //get the category id

    $catId = $results[0]->catid;



    //get the number of posts in that category

    $postsInCategory = get_posts("category=$catId");

    $numberOfPosts = count($postsInCategory);



    //get the number of the last post that was delivered.

    //      get the sequence number - the number of the last post that was delivered
    //if equal return yes otherwise return false.
    return ($subscription->sequence+1 < $numberOfPosts);
}


function isAutoresponderSeriesActive($subscription)
{
    global $wpdb;
    //get the number of emails in the follow up series
    if (-2== intval($subscriptoin->eid))
            return false;
    else
        return true;

}

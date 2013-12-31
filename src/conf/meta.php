<?php
/*
Given below is the database structure of the plugin. The installation procedure 
and the table integrity checker uses the following rules to make sure that the 
table is of the correct type.
*/

$database_structure = array();

$database_structure["wpr_subscribers"] = array('columns' => array(
    'nid' => "INT NOT NULL",
    'id' => "INT NOT NULL",
    'name' => "VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL",
    'email' => "VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL",
    'date' => "VARCHAR(12) DEFAULT 0",
    'active' => "TINYINT(1) NOT NULL DEFAULT '0'",
    'confirmed' => "TINYINT(1) NOT NULL DEFAULT '0'",
    'fid' => "TINYINT(1) NOT NULL DEFAULT '1'",
    'hash' => "VARCHAR(50) NOT NULL"
),
    'primary_key' => 'id',
    'auto_increment' => 'id',
    'unique' => array(
        "unique_email_for_newsletter" => array("nid", "email")
    )
);


$database_structure["wpr_subscriber_transfer"] = array('columns' => array(
    'id' => "TINYINT unsigned NOT NULL",
    'source' => 'TINYINT unsigned NOT NULL',
    'dest' => 'TINYINT unsigned NOT NULL',
),

    'primary_key' => "id",
    'auto_increment' => 'id',
    'unique' => array(
        "unique_rules" => array("source", "dest")
    )
);
$database_structure["wpr_subscription_form"] = array(

    'columns' => array(
        'id' => "INT NOT NULL",
        'name' => "VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL",
        'return_url' => "VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL",
        'followup_type' => "enum('postseries','autoresponder','none') NOT NULL",
        'followup_id' => "INT NOT NULL",
        'blogsubscription_type' => "enum('all','cat','none') NOT NULL",
        'blogsubscription_id' => "INT NOT NULL",
        'nid' => "INT NOT NULL",
        'custom_fields' => "VARCHAR(100) NOT NULL",
        'confirm_subject' => "TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL",
        'confirm_body' => "TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL",
        'confirmed_subject' => "TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL",
        'confirmed_body' => "TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL",
        'confirm_url' => "VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL",
        'submit_button' => "VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'Subscribe'"
    ),

    'primary_key' => "id",
    'auto_increment' => 'id',
    'unique' => array(
        "unique_subscription_form_names" => array('name')
    )
);

$database_structure["wpr_queue"] = array(
    'columns' => array(
        'id' => "INT NOT NULL",
        'from' => "VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin  DEFAULT NULL",
        'fromname' => "VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL",
        'to' => "VARCHAR(256) DEFAULT NULL",
        'reply_to' => 'VARCHAR(100)  CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL',
        'subject' => "text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL",
        'htmlbody' => "text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL",
        'textbody' => "text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL",
        'headers' => "text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL",
        'sent' => "INT DEFAULT 0",
        'date' => 'INT DEFAULT 0',
        'sid' => 'INT DEFAULT 0',
        'delivery_type' => "tinyint(1) NOT NULL DEFAULT '0'",
        'hash' => 'VARCHAR(32)  NOT NULL',
        'meta_key' => 'VARCHAR(30)  NOT NULL',
        'htmlenabled' => "TINYINT NOT NULL DEFAULT 1",
    ),
    'auto_increment' => 'id',
    'primary_key' => "id",
    'unique' => array(
        "hash_is_unique" => array('hash'),
        "meta_key_is_unique" => array('meta_key')
    )
);


$database_structure["wpr_newsletter_mailouts"] = array('columns' => array(
    'id' => "INT NOT NULL",
    'nid' => "INT NOT NULL",
    'subject' => "VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin  NOT NULL",
    'textbody' => "TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL",
    'htmlbody' => "TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL",
    'time' => "VARCHAR(25) NOT NULL",
    'status' => "TINYINT NOT NULL",
),
    'auto_increment' => 'id',
    'primary_key' => "id",
    "unique" => array()
);


$database_structure["wpr_newsletters"] = array('columns' => array(
    'id' => "INT NOT NULL",
    'name' => "VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL",
    'reply_to' => "VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL",
    'fromname' => "VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL",
    'fromemail' => "VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL"
),
    'primary_key' => "id",
    'auto_increment' => 'id',
    "unique" => array(
        "unique_name_for_newsletters" => array("name")
    )
);
$database_structure["wpr_delivery_record"] = array('columns' => array(
    'id' => "INT NOT NULL ",
    'sid' => "INT NOT NULL",
    'type' => "VARCHAR(30) NOT NULL",
    'eid' => "INT NOT NULL",
    'timestamp' => "BIGINT NOT NULL"
),
    'primary_key' => "id",
    'auto_increment' => 'id',
    'unique' => array(
        "unique_records" => array("sid", "type", "eid")
    )
);


$database_structure["wpr_custom_fields_values"] = array('columns' => array(
    'id' => "INT NOT NULL AUTO_INCREMENT",
    'nid' => "INT NOT NULL",
    'sid' => "INT NOT NULL",
    'cid' => "INT NOT NULL",
    'value' => "text  CHARACTER SET utf8 COLLATE utf8_bin NOT NULL"
),
    'primary_key' => "id",
    'auto_increment' => 'id',
    'unique' => array(
        "only_one_per_subscriber_per_field" => array("nid", "cid", "sid")
    )
);


$database_structure["wpr_custom_fields"] = array('columns' => array(

    'id' => "INT NOT NULL",
    'nid' => "INT NOT NULL",
    'type' => "enum('enum','text','hidden') NOT NULL",
    'name' => "VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin  NOT NULL",
    'label' => "VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL",
    'enum' => "VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL"
),
    'primary_key' => "id",
    'auto_increment' => 'id',
    'unique' => array(
        "unique_field_names_in_newsletters" => array("nid", "name")
    )
);


$database_structure["wpr_blog_subscription"] = array('columns' => array(
    'id' => "INT NOT NULL",
    'sid' => "INT NOT NULL",
    'type' => "enum('all','cat') NOT NULL",
    'catid' => "INT NOT NULL",
    'last_processed_date' => 'INT NOT NULL',
    'last_published_postid' => 'INT NOT NULL',
    'last_published_post_date' => 'BIGINT NOT NULL DEFAULT 0',
    'pending_reprocess' => 'TINYINT NOT NULL DEFAULT 0',
),
    'primary_key' => "id",
    'auto_increment' => 'id',
    'unique' => array(
        "unique_blog_subscriptions_per_subscriber" => array('sid', 'type', 'catid')
    )
);

$database_structure["wpr_blog_series"] = array('columns' => array(
    'id' => "TINYINT NOT NULL",
    'name' => "VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin  NOT NULL",
    'catid' => "SMALLINT NOT NULL",
    'frequency' => "TINYINT NOT NULL"
),
    'primary_key' => "id",
    'auto_increment' => 'id',
    'unique' => array(
        "unique_names_for_blog_series" => array("name")
    )
);
$database_structure["wpr_autoresponder_messages"] = array(
    'columns' => array(
        'id' => " INT NOT NULL",
        'aid' => " INT NOT NULL",
        'subject' => " text CHARACTER SET utf8 COLLATE utf8_bin  NOT NULL",
        'htmlenabled' => " TINYINT NOT NULL DEFAULT 1",
        'textbody' => " text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL",
        'htmlbody' => " text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL",
        'sequence' => " INT DEFAULT 0"
    ),
    'primary_key' => "id",
    'unique' => array(
        "only_one_email_for_a_day_in_followup" => array("aid", "sequence")
    ),
    'auto_increment' => 'id'
);

$database_structure["wpr_autoresponders"] = array('columns' => array(
    'nid' => "INT NOT NULL",
    'id' => "INT NOT NULL",
    'name' => "varchar(50) CHARACTER SET utf8 COLLATE utf8_bin  NOT NULL"
),
    'primary_key' => "id",
    'auto_increment' => 'id',
    'unique' => array(
        'unique_autoresponder_names_in_newsletter' => array('nid', 'name')
    )
);
$database_structure["wpr_followup_subscriptions"] = array('columns' => array(
    'id' => "INT NOT NULL ",
    'sid' => "INT NOT NULL",
    'type' => "enum('autoresponder','postseries') NOT NULL",
    'eid' => "INT NOT NULL",
    'sequence' => "SMALLINT NOT NULL DEFAULT 0",
    'last_date' => "INT NOT NULL",
    'last_processed' => "BIGINT NOT NULL DEFAULT 0",
    'doc' => "VARCHAR(20) NOT NULL"
),
    'primary_key' => "id",
    'auto_increment' => 'id',
    'unique' => array(
        "unique_subscriptions_for_subscribers" => array("sid", "type", "eid")
    )
);

$GLOBALS['data_structure'] = $database_structure;
$GLOBALS['wpr_defaults'] = array();

//TODO: Get rid of two arrays for list of crons, modify all code that uses this code to work with the single structure.
/*
Important Note: The same action CANNOT be scheduled in different schedules. Create a different action with a different name
*/
$GLOBALS['wpr_cron_schedules'] = array(
    array(
        'action' => '_wpr_queue_management_cron',
        'schedule' => 'every_ten_minutes',
        'arguments' => array()
    ),
    array(
        'action' => '_wpr_autoresponder_process',
        'schedule' => 'hourly',
        'arguments' => array()
    ),
    array(
        'action' => '_wpr_postseries_process',
        'schedule' => 'every_ten_minutes',
        'arguments' => array()
    ),
    array(
        'action' => '_wpr_process_broadcasts',
        'schedule' => 'every_ten_minutes',
        'arguments' => array()
    ),
    array(
        'action' => '_wpr_process_blog_subscriptions',
        'schedule' => 'every_ten_minutes',
        'arguments' => array()
    ),
    array(
        'action' => '_wpr_process_queue',
        'schedule' => 'every_ten_minutes',
        'arguments' => array()
    ),
    array(
        'action' => '_wpr_process_blog_category_subscriptions',
        'schedule' => 'every_ten_minutes',
        'arguments' => array()
    ),
    array(
        'action' => '_wpr_maintenance',
        'schedule' => 'daily',
        'arguments' => array()
    ),
);

$GLOBALS['_wpr_crons'] = array(
    '_wpr_autoresponder_process',
    '_wpr_postseries_process',
    '_wpr_process_broadcasts',
    '_wpr_process_blog_subscriptions',
    '_wpr_process_blog_category_subscriptions',
    '_wpr_queue_management_cron',
    '_wpr_process_queue',
    '_wpr_maintenance',
    'wpr_tutorial_cron',
    'wpr_updates_cron',
    'wpr_send_errors'
);

$schedules = array();
$schedules['every_five_minutes'] = array(
    'interval' => 300,
    'display' => __('Every 5 Minutes')
);

$schedules['every_ten_minutes'] = array(
    'interval' => 600,
    'display' => __('Every 10 Minutes')
);

$schedules['every_minute'] = array(
    'interval' => 60,
    'display' => __('Every Minute')
);

$schedules ['every_half_hour'] = array(
    'interval' => 1800,
    'display' => __('Every Half an Hour')
);

$GLOBALS['schedules'] = $schedules;

//predefined options
$initial_wpr_options = array(
    '_wpr_admin_notices' => base64_encode(serialize(array())),
    'wpr_hourlylimit' => '100',
    'wpr_sent_posts' => 'off',
    'wpr_address' => '',
    '_wpr_options' => array(
        '_wpr_ensure_single_instances_of_crons_last_run' => 0
    )
);

$GLOBALS['initial_wpr_options'] = $initial_wpr_options;


/************QUEUE MANAGEMENT****************************/
//maximum emails processed in the queue per minute
define("WPR_MAX_QUEUE_EMAILS_SENT_PER_MINUTE", 100);
define("WPR_MAX_QUEUE_TABLE_SIZE", 1073741824); // maximum size of the table before it is truncated
define("WPR_MAX_DELIVERY_RECORD_TABLE_SIZE", 1073741824); // maximum size of the table before it is truncated
define("WPR_MAX_QUEUE_EMAILS_SENT_PER_ITERATION", 100); //maximum number of emails that will be loaded to memory per iteration
define("WPR_MAX_BLOG_SUBSCRIPTION_PROCESSED_PER_ITERATION", 100); //maximum number of blog post subscriptions that will be loaded to memory per iteration

define("WPR_MAX_QUEUE_DELIVERY_EXECUTION_TIME", 300); //the queue delivery burst can run for a maximum of 5 minutes at a time.
define("WPR_MAX_POSTSERIES_PROCESS_EXECUTION_TIME", 300); //the postseries processor can run for a maximum of 5 minutes at a time.
define("WPR_MAX_NEWSLETTER_PROCESS_EXECUTION_TIME", 1800); //the newsletter broadcast processor can run for a maximum of half an hour at a time.
define("WPR_MAX_AUTORESPONDER_PROCESS_EXECUTION_TIME", 300); //the autoresponder processor can run for a maximum of 5 minutes at a time.


define("WPR_ENSURE_SINGLE_INSTANCE_CHECK_PERIODICITY", 86400); //the period between runs of the _wpr_ensure_single_instances_of_crons cron.


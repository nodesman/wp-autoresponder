
CREATE TABLE IF NOT EXISTS `wp_wpr_autoresponders` (
  `nid` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
);



--
-- Table structure for table `wp_wpr_autoresponder_messages`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_autoresponder_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL,
  `subject` text NOT NULL,
  `htmlenabled` tinyint(1) NOT NULL,
  `textbody` text NOT NULL,
  `htmlbody` text NOT NULL,
  `sequence` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ;



--
-- Table structure for table `wp_wpr_blog_series`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_blog_series` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `catid` varchar(100) NOT NULL,
  `frequency` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ;



--
-- Table structure for table `wp_wpr_blog_subscription`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_blog_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  `type` enum('all','cat') NOT NULL,
  `catid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ;



--
-- Table structure for table `wp_wpr_broadcasts`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_broadcasts` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL,
  `subject` int(11) NOT NULL,
  `body` int(11) NOT NULL,
  `time` varchar(25) NOT NULL,
  `status` int(11) NOT NULL
) ;



--
-- Table structure for table `wp_wpr_custom_fields`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_custom_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` int(11) NOT NULL,
  `type` enum('enum','text','hidden') NOT NULL,
  `name` varchar(50) NOT NULL,
  `label` varchar(50) NOT NULL,
  `enum` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ;



--
-- Table structure for table `wp_wpr_custom_fields_values`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_custom_fields_values` (
  `id` int(11) NOT NULL,
  `nid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `value` text NOT NULL
) ;



--
-- Table structure for table `wp_wpr_followup_subscriptions`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_followup_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  `type` enum('autoresponder','blogseries') NOT NULL,
  `eid` int(4) NOT NULL,
  `sequence` smallint(6) NOT NULL,
  `last_date` int(11) NOT NULL,
  `doc` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
) ;



--
-- Table structure for table `wp_wpr_newsletters`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_newsletters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `reply_to` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `confirm_subject` varchar(100) NOT NULL,
  `confirm_body` text NOT NULL,
  `confirmed_subject` varchar(100) NOT NULL,
  `confirmed_body` text NOT NULL,
  PRIMARY KEY (`id`)
);



--
-- Table structure for table `wp_wpr_newsletter_mailouts`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_newsletter_mailouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `textbody` text NOT NULL,
  `htmlbody` text NOT NULL,
  `time` varchar(25) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ;



--
-- Table structure for table `wp_wpr_subscribers`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_subscribers` (
  `nid` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `date` varchar(30) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `fid` int(11) NOT NULL,
  `confirmed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nid` (`nid`)
) ;



--
-- Table structure for table `wp_wpr_subscription_form`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_subscription_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `return_url` varchar(150) NOT NULL,
  `followup_type` enum('postseries','autoresponder','none') NOT NULL,
  `followup_id` int(11) NOT NULL,
  `blogsubscription_type` enum('all','cat','none') NOT NULL,
  `blogsubscription_id` int(11) NOT NULL,
  `nid` int(11) NOT NULL,
  `custom_fields` varchar(100) NOT NULL,
  `confirm_subject` text NOT NULL,
  `confirm_body` text NOT NULL,
  `confirmed_subject` text NOT NULL,
  `confirmed_body` text NOT NULL,
  PRIMARY KEY (`id`)
) ;

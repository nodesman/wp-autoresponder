-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 05, 2013 at 08:32 PM
-- Server version: 5.5.28-0ubuntu0.12.04.3
-- PHP Version: 5.3.10-1ubuntu3.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `freeness`
--
use myapp_test;
-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_autoresponders`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_autoresponders` (
  `nid` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_autoresponder_names_in_newsletter` (`nid`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_autoresponder_messages`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_autoresponder_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL,
  `subject` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `htmlenabled` tinyint(4) NOT NULL,
  `textbody` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `htmlbody` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `sequence` int(11) NOT NULL,
  `attachimages` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `only_one_email_for_a_day_in_followup` (`aid`,`sequence`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_blog_series`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_blog_series` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `catid` smallint(6) NOT NULL,
  `frequency` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_names_for_blog_series` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_blog_subscription`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_blog_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  `type` enum('all','cat') NOT NULL,
  `catid` int(11) NOT NULL,
  `last_processed_date` int(11) NOT NULL,
  `last_published_postid` int(11) NOT NULL,
  `last_published_post_date` bigint(20) NOT NULL DEFAULT '0',
  `pending_reprocess` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_blog_subscriptions_per_subscriber` (`sid`,`type`,`catid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_custom_fields`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_custom_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` int(11) NOT NULL,
  `type` enum('enum','text','hidden') NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `label` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `enum` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_field_names_in_newsletters` (`nid`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_custom_fields_values`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_custom_fields_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `value` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `only_one_per_subscriber_per_field` (`nid`,`cid`,`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_delivery_record`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_delivery_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  `type` varchar(30) NOT NULL,
  `eid` int(11) NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_records` (`sid`,`type`,`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_followup_subscriptions`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_followup_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  `type` enum('autoresponder','postseries') NOT NULL,
  `eid` int(11) NOT NULL,
  `sequence` smallint(6) NOT NULL,
  `last_date` int(11) NOT NULL,
  `last_processed` bigint(20) NOT NULL DEFAULT '0',
  `doc` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_subscriptions_for_subscribers` (`sid`,`type`,`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_newsletters`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_newsletters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `reply_to` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `fromname` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `fromemail` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name_for_newsletters` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_newsletter_mailouts`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_newsletter_mailouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` int(11) NOT NULL,
  `subject` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `textbody` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `htmlbody` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `time` varchar(25) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `recipients` text NOT NULL,
  `attachimages` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_queue`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `fromname` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `to` varchar(256) NOT NULL,
  `reply_to` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `subject` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `htmlbody` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `textbody` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `headers` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `sent` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `delivery_type` tinyint(1) NOT NULL DEFAULT '0',
  `email_type` enum('user_verify_email','user_confirmed_email','user_followup_autoresponder_email','user_followup_postseries_email','user_blogsubscription_email','user_blogcategorysubscription_email','user_unsubscribed_notification_email','critical_queue_limit_approaching_email','system_subscription_errors_email','system_analytics_email','misc') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'misc',
  `hash` varchar(32) NOT NULL,
  `meta_key` varchar(30) NOT NULL,
  `htmlenabled` tinyint(4) NOT NULL,
  `attachimages` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash_is_unique` (`hash`),
  UNIQUE KEY `meta_key_is_unique` (`meta_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_subscribers`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_subscribers` (
  `nid` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `date` varchar(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `fid` tinyint(1) NOT NULL DEFAULT '1',
  `hash` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email_for_newsletter` (`nid`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_subscriber_transfer`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_subscriber_transfer` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `source` tinyint(3) unsigned NOT NULL,
  `dest` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rules` (`source`,`dest`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wpr_subscription_form`
--

CREATE TABLE IF NOT EXISTS `wp_wpr_subscription_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `return_url` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `followup_type` enum('postseries','autoresponder','none') NOT NULL,
  `followup_id` int(11) NOT NULL,
  `blogsubscription_type` enum('all','cat','none') NOT NULL,
  `blogsubscription_id` int(11) NOT NULL,
  `nid` int(11) NOT NULL,
  `custom_fields` varchar(100) NOT NULL,
  `confirm_subject` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `confirm_body` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `confirmed_subject` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `confirmed_body` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `confirm_url` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `submit_button` varchar(45) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'Subscribe',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_subscription_form_names` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

<?php
class NewsletterNotFoundException extends Exception { }

class InvalidNewsletterIDException extends Exception { }

class DeletedNewsletterAccessException extends Exception { }

class Newsletter
{
	
	private  $id;
	private  $name;
	private  $reply_to;
	private  $description;
	private  $confirm_subject;
	private  $confirm_body;
	private  $confirmed_subject;
	private  $confirmed_body;
	private  $fromname;
	private  $fromemail;
	private  $deleted = false;
	
	
	function Newsletter($nid)
	{
		global $wpdb;
		$nid = intval($nid);
		
		if (0 == $nid)
			throw new InvalidNewsletterIDException();
		
		$tableName = $wpdb->prefix."wpr_newsletters";
		
		$getNewsletterInformationQuery = sprintf("SELECT * FROM %s WHERE id=%d",$tableName,$nid);
		$newsletters = $wpdb->get_results($getNewsletterInformationQuery);
		
		if (0 == count($newsletters))
			throw new NewsletterNotFoundException();
		
		$newsletter = $newsletters[0];
		$this->id = $newsletter->id;
		$this->name = $newsletter->name;
		$this->reply_to = $newsletter->reply_to;
		$this->description = $newsletter->description;
		$this->confirm_subject = $newsletter->confirm_subject;
		$this->confirm_body = $newsletter->confirm_body;
		$this->confirmed_subject = $newsletter->confirmed_subject;
		$this->confirmed_body = $newsletter->confirmed_body;
		$this->fromname = $newsletter->fromname;
		$this->fromemail = $newsletter->fromemail;
	}
	
	function getNewsletterName()
	{
		$this->ensureNotDeleted();
		return $this->name;
	}
	function getNewsletterId()
	{
		$this->ensureNotDeleted();
		return $this->id;
	}
	function getConfirmSubject()
	{
		$this->ensureNotDeleted();
		return $this->confirm_subject;
	}
	
	function getConfirmedSubject()
	{
		$this->ensureNotDeleted();
		return $this->confirmed_subject;
	}
	
	function getConfirmBody()
	{
		$this->ensureNotDeleted();
		return $this->confirm_body;	
	}
	
	function getConfirmedBody()
	{
		$this->ensureNotDeleted();
		return $this->confirmed_body;
	}
	
	function getDescription()
	{
		$this->ensureNotDeleted();
		return $this->description;
	}
	
	function getNewsletterReplyToEmailAddress()
	{
		$this->ensureNotDeleted();
		return $this->reply_to;
	}
	
	function getFromName()
	{
		$this->ensureNotDeleted();
		return $this->fromname;
	}
	
	function ensureNotDeleted()
	{
		if ($this->deleted)
			throw new DeletedNewsletterAccessException();
	}
	
	function getFromEmail()
	{
		$this->ensureNotDeleted();
		return $this->fromemail;
	}
	
	
	function delete()
	{
		global $wpdb;
		$prefix = $wpdb->prefix;
		$id = $this->id;
		$deletionQueries = array(
				"deleteCustomFieldData" => sprintf("DELETE FROM %swpr_custom_fields_values WHERE nid=%d",$prefix, $id),
				"deleteCustomFieldsDefinitions" => sprintf("DELETE FROM %swpr_custom_fields WHERE nid=%d",$prefix,$id),
				"deleteAutoresponderSubscriptions" =>sprintf("DELETE FROM %swpr_followup_subscriptions WHERE sid=(SELECT id FROM %swpr_subscribers WHERE nid=%d);",$prefix,$prefix,$id),
				
				"deleteAutoresponderMessages" => sprintf("DELETE FROM %swpr_autoresponder_messages WHERE aid=(SELECT id FROM %swpr_autoresponders WHERE nid=%d);",$prefix,$prefix,$id),
				"deleteAutoresponders" => sprintf("DELETE FROM %swpr_autoresponders WHERE nid=%d",$prefix,$id),
				"deleteSubscriptionForms" => sprintf("DELETE FROM %swpr_subscription_form WHERE nid=%d",$prefix,$id),
				"deleteSubscriberTransferRules" => sprintf("DELETE FROM %swpr_subscriber_transfer WHERE source=%d OR dest=%d",$prefix, $id,$id),
				"deleteEmailsPendingDelivery"=> sprintf("DELETE FROM %swpr_queue WHERE sid=(SELECT id FROM %swpr_subscribers WHERE nid=%d",$prefix,$prefix,$id),

				
				"deleteBlogSubscriptions" => sprintf("DELETE FROM %swpr_blog_subscription WHERE sid=(SELECT id FROM %swpr_subscribers WHERE nid=%d);",$prefix,$prefix,$id),
				"deleteSubscribers"=> sprintf("DELETE FROM %swpr_subscribers WHERE nid=%d",$prefix,$id),
				"deleteNewsletterQuery"=>sprintf("DELETE FROM %swpr_newsletters WHERE id=%d",$prefix,$id)
		);
		foreach ($deletionQueries as $query)
		{
			$wpdb->query($query);	
		}
		$this->deleted=true;
	}
	
	function getNumberOfSubscribers()
	{
		global $wpdb;
		$getNumberOfSubscribersQuery = sprintf("SELECT count(*) number FROM %swpr_subscribers WHERE nid=%d",$wpdb->prefix,$this->id);	
		$result = $wpdb->get_results($getNumberOfSubscribersQuery);
		$number = $result[0]->number;
		return $number;
	}
	
	function getNumberOfUnsubscribed()
	{
		global $wpdb;
		$getNumberOfSubscribersQuery = sprintf("SELECT count(*) number FROM %swpr_subscribers WHERE nid=%d AND active=0 AND confirmed=1",$wpdb->prefix,$this->id);	
		$result = $wpdb->get_results($getNumberOfSubscribersQuery);
		$number = $result[0]->number;
		return $number;
		
	}
	
	function getNumberOfUnconfirmed()
	{
		//TODO: Implement this
	}
	
	
	
	function getNumberOfActiveSubscribers()
	{
			global $wpdb;
		$getNumberOfSubscribersQuery = sprintf("SELECT count(*) number FROM %swpr_subscribers WHERE nid=%d AND active=1 AND confirmed=1",$wpdb->prefix,$this->id);	
		$result = $wpdb->get_results($getNumberOfSubscribersQuery);
		$number = $result[0]->number;
		return $number;
	}

}
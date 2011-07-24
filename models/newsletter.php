<?php
class NewsletterNotFoundException extends Exception { }

class InvalidNewsletterIDException extends Exception { }


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
		return $this->name;
	}
	function getNewsletterId()
	{
		return $this->id;
	}
	function getConfirmSubject()
	{
		return $this->confirm_subject;
	}
	
	function getConfirmedSubject()
	{
		return $this->confirmed_subject;
	}
	
	function getConfirmBody()
	{
		return $this->confirm_body;	
	}
	
	function getConfirmedBody()
	{
		return $this->confirmed_body;
	}
	
	function getDescription()
	{
		return $this->description;
	}
	
	function getNewsletterReplyToEmailAddress()
	{
		return $this->reply_to;
	}
	
	function getFromName()
	{
		return $this->fromname;
	}
		
	
	function getFromEmail()
	{
		return $this->fromemail;
	}
	
	
	
}
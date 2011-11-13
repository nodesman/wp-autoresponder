<?php


class SubscriberNotFoundException extends Exception {}

class InvalidSubscriberIDException extends Exception {} 

class Subscriber
{
	
	function getHash()
	{
		return $this->hash;		
	}
	
	
	function Subscriber($id) 
	{
		global $wpdb;
		$sid = intval($id);
		if ($sid == 0)
			throw new InvalidSubscriberIDException("Invalid Subscriber ID: $sid");
		$tableName = $wpdb->prefix."wpr_subscribers";
		$getSubscriberInfoQuery = sprintf("SELECT * FROM %s WHERE id=%d",$tableName,$sid);
		$queryResults = $wpdb->get_results($getSubscriberInfoQuery);
		if (0 == count($queryResults))
			throw new SubscriberNotFoundException();
			
		$subscriber = $queryResults[0];
		$this->id = $subscriber->id;
		$this->nid = $subscriber->nid;
		$this->name = $subscriber->name;
		$this->email = $subscriber->email;
		$this->date_of_subscription = $subscriber->date;
		$this->active = $susbcriber->active;
		$this->confirmed = $subscriber->confirmed;
		$this->fid = $subscriber->fid;
		$this->hash = $subscriber->hash;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
	
	public function getDateOfSubscription() 
	{
		return $this->date_of_subscription;
	}
	
	public function getActive()
	{
		return $this->active;
	}
	
	public function getConfiremd()
	{
		return $this->confirmed;
	}
	
	public function isUnsubscribed()
	{
		return ($this->active == 0 && $this->confirmed==1);
	}
	
	public function isUnconfirmed()
	{
		return ($this->active == 1 && $this->confirmed == 0);	
	}
	
	public function isMoved()
	{
		return ($this->active == 3 && $this->confimed == 1);	
	}
	
	public function getNewsletterId()
	{
		return ($this->nid);
	}
	
	public static function replaceCustomFieldValues($string, $sid)
	{
		throw new Exception("To be implemented: replaceCustomFieldValues");
		return $string;	
	}
	
	public function getCustomFieldValuesByLabels()
	{
		global $wpdb;
		$prefix = $wpdb->prefix;
		$getCustomFieldsQuery = sprintf("SELECT fields.name name, fields.label label, fields.id cid, `values`.value value FROM `%swpr_custom_fields` `fields`, `%swpr_custom_fields_values` `values` WHERE `fields`.`nid`=%d AND `fields`.`id`=`values`.`cid` AND `values`.`sid`=%d",$prefix,$prefix, $this->getNewsletterId(),$this->getId());
		$customFields = $wpdb->get_results($getCustomFieldsQuery);
		
		$result = array();
		if (count($customFields) > 0)
		{
			foreach ($customFields as $field)
			{
				$result[$field->label] = $field->value;
				
			}
		}
		return $result;
		
	}
        
        function getNewsletter()
        {
            $newsletter = new Newsletter($this->getNewsletterId());
            return $newsletter;
        }
	
}
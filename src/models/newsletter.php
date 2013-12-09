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
	private  $from_name;

    public function getFromName()
    {
        return $this->from_name;
    }

    public function getFromEmail()
    {
        return $this->from_email;
    }
	private  $from_email;
	private  $deleted = false;


    private function __construct($nid) {

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
        $this->from_name = $newsletter->fromname;
        $this->from_email = $newsletter->fromemail;



    }

    public static function getNewsletter($id) {

        global $wpdb;

        $getNewsletterQuery = sprintf("SELECT * FROM %swpr_newsletters WHERE id=%d", $wpdb->prefix, $id);

        $newsletterRes = $wpdb->get_results($getNewsletterQuery);

        if (count($newsletterRes) == 0 )
            throw new NonExistentNewsletterException();

        return new Newsletter($id);
    }

    public function getCustomFieldKeys() {
        global $wpdb;

        $getCustomFieldKeysQuery = sprintf("SELECT name FROM %swpr_custom_fields WHERE nid=%d", $wpdb->prefix, $this->id);
        $custom_fields = $wpdb->get_col($getCustomFieldKeysQuery);

        return $custom_fields;
    }


    public function getCustomFieldKeyLabelPair() {
        global $wpdb;

        $getCustomFieldsQuery = sprintf("SELECT * FROM %swpr_custom_fields WHERE nid=%d", $wpdb->prefix, $this->id);
        $custom_fields = $wpdb->get_results($getCustomFieldsQuery);

        $result = array();
        foreach ($custom_fields as $field) {
            $result[$field->name] = $field->label;
        }

        return $result;


    }

	
	function getName()
	{
		return $this->name;
	}
	function getId()
	{
		return $this->id;
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

	public static function whetherNewsletterIDExists($newsletter_id) {
		global $wpdb;
		$checkWhetherNewsletterIDExistsQuery = sprintf("SELECT COUNT(*) result_count FROM {$wpdb->prefix}wpr_newsletters WHERE id=%d",$newsletter_id);
		$newslettersCountRes = $wpdb->get_results($checkWhetherNewsletterIDExistsQuery);
		$count = (int) $newslettersCountRes[0]->result_count;
		return (0 != $count);
	}

    public static function getAllNewsletters() {
        global $wpdb;

        $getAllNewslettersQuery = sprintf("SELECT * FROM {$wpdb->prefix}wpr_newsletters");
        $newsletters = $wpdb->get_results($getAllNewslettersQuery);

        $result = array();
        foreach ($newsletters as $newsletter) {
            $result[] = Newsletter::getNewsletter($newsletter->id);
        }

        return $result;
    }

    private static function getNumberOfNewsletters() {
        global $wpdb;
        $getCountNewslettersQuery = sprintf("SELECT count(*) num FROM {$wpdb->prefix}wpr_newsletters");
        $results = $wpdb->get_results($getCountNewslettersQuery);
        $count = $results[0]->num;
        return $count;
    }

    public static function whetherNoNewslettersExist() {
        return 0 == self::getNumberOfNewsletters();
    }

	function getNumberOfActiveSubscribers()
	{
	    global $wpdb;
		$getNumberOfSubscribersQuery = sprintf("SELECT COUNT(*) number FROM %swpr_subscribers WHERE nid=%d AND active=1 AND confirmed=1",$wpdb->prefix,$this->id);
		$result = $wpdb->get_results($getNumberOfSubscribersQuery);
		$number = $result[0]->number;
		return $number;
	}

}
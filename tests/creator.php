<?php


function createTestBaseData()
{
     global $wpdb;
	 
	 
	 //create a newsletter
	 $createNewsletterQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletters (name, reply_to, description, fromname, fromemail)
	 								VALUES ('%s','%s','%s','%s','%s');","TestOne_".time(),"testscript@wpresponder.com", "This is a newsletter created as part of autoresponder tests", "Raj", "tester@wpresponder.com");
									
	 $wpdb->show_errors();
									
	$wpdb->query($createNewsletterQuery);
 	$newsletter_id = $wpdb->insert_id;
	
	//create autoresponders
	
	echo "Inserting autoresponders...\r\n";
	$autoresponders = array();
	for ($iter =0; $iter <5; $iter++)
	{
		$autoresponderCreationQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_autoresponders (nid, name) values (%d,'%s')",$newsletter_id, "Autoresponder_".microtime());
		$wpdb->query($autoresponderCreationQuery);
		$autoresponders[] = $wpdb->insert_id;
		echo "Added autoresponder {$wpdb->insert_id}....\r\n";
	}
	
	echo "\r\n\r\n";
	
	foreach ($autoresponders as $responder)
	{
		$sequence = 0;
		echo "Inserting autoresponder for autoresponder id {$responder}...\r\n ";
		for ($iter = 0; $iter < 20; $iter++ )
		{
	    	while (1){ 
			    $offset = rand(0,10);
				 
			    $sequence+= $offset;
				if ($offset == 0 && $iter > 0)
				   continue;
				else
				   break;
			}
		    $subject = md5("subject{$iter}{$responder}{$offset}");
			$htmlbody = md5("htmlbody{$iter}{$responder}{$offset}");
			$textbody = md5("textbody{$iter}{$responder}{$offset}");
			$addAutoresponderQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_autoresponder_messages (`aid`, `subject`, `htmlbody`, `textbody`, `sequence`, `htmlenabled`) VALUES ('%s','%s', '%s','%s', '%s','%s')",$responder, $subject, $htmlbody, $textbody, $sequence, 1);
			$wpdb->query($addAutoresponderQuery);
			
			echo "Added message {$wpdb->insert_id}....\r\n";
			
		}
	}
	
	return $newsletter_id;
	
}
=== WP Autoresponder And Newsletter Plugin ===  
Contributors: rajasekharan
Donate link: http://wpresponder.com/donate/  
Tags: email, newsletter, autoresponder, subscribers, mailing list, follow up email autoresponder, email marketing  
Requires at least: 2.8.2  
Tested up to: 3.3.1
Stable tag: 5.2.8

With WP Autoresponder you can create email newsletters, follow up autoresponders and provide subscription to blog via e-mail.  

== Description ==  

This is a email newsletter and autoresponder plugin. With this plugin you can create:

* Create unlimited number of newsletter lists
* Create followup email responses that can be schedueld to go out after a specific number of days since the subscriber subscribes.
* Add subcription forms to your sidebar using widgets.
* Provide email subscriptions to your blog without using third party services like Feedburner
* Generate subscription forms and then use them on your website anywhere. 
* Collect more information about your subscribers by generating custom fields for your subscription forms. 
* Schedule e-mail broadcasts to your email newsletters in text/html. You can even send the broadcast to specific sections of your newsletter by selecting them using the custom fields. 
* Provide email subscription to specific categories in your blog
* Import your subscribers from Feedburner and Aweber.
* Define rules to unsubscribe users from one newsletter if they subscribe to another newsletter. 

The newsletters work indepedent of the working of the blog. This plugin is aimed as an alternative to Aweber, Mailchimp and other paid services. You DO NOT need a third party service or license to use this plugin

== Installation ==

To install WP Responder:

* Download the Latest version from :

http://wordpress.org/extend/plugins/wp-responder-email-autoresponder-and-newsletter-plugin/

* Extract the downloaded archive to a local directory. 
* Upload the wpresponder directory to the wp-content/plugins directory of your wordpress installation.
* Activate the plugin through the 'Plugins' menu in WordPress
* Enter your postal address to the Newsletter > Settings page to avoid spam complaints. 

== Frequently Asked Questions ==

**The confirmation link is missing in the confirmation e-mail. Why?**

If you are running version 4.7 or below, please upgrade it to 4.8 or above. This is because of the long URL that is generated for subscription confirmation gets stripped out by some e-mail providers. 

**I see a Fatal Error when running the installation.Why is this?**  

This is because WP Autoresponder is designed to work only with PHP5. You are most likely using PHP4. 

**My email broadcasts take forever to go out.Why does this happen?**
  
There may be two causes for this:  

* This happens because WP Autoresponder relies on wordpress's cron system. Wordpress's cron system relies on your webstie traffic to trigger the various scheduled activities. If your website has low traffic then this will not work. The solution is to **create a cron job on wp-cron.php found in the wordpress root to solve this probelm**.  
* Further the hourly limit on the number of emails to go out in a single hour is set to 100 by default. Increase it or set it to 0 if you want to remove the limit on the number of emails that go out in an hour. The hourly limit can be set at Wordpress Dashboard > Newsletters > Settings

**I need your help  to integrate WP Responder wwith some application/customize it/etc**

Please visit the plugin website:   
  
http://www.wpresponder.com
  
And get in touch with me. 


== ChangeLog ==

**WP Responder 4.9.2**

Bug Fixes:

* In the autoresponder series page, the HTML body is disabled by default. 
* When preview email is sent there is an error when there is an image in the post. 
* The preview email doesn't replace the [!email!] field with the email address. 
* The  optin page has a spelling mistake in the title - "addres"
* The subscription form should show only the autoresponders that have been created for the currently selected newsletter. 
* There are some empty rows in the blog subscription table - Rows with null data. 
* The preview email takes get_bloginfo("adminemail") when the from email is not set in the preview email form in the autoresponder messages page.
* There is a field called [!!] in the autoresponder page in the list of custom fields in the new broadcast field.
*  Breaks are inserted in the confirm and confirmation e-mail templates when they are saved or displayed in the form.
* Scheduling/Rescheduling a broadcast threw an error saying you were trying to schedule a broadcast in the future.
* If the src of images specified in a e-mail's HTML body is a HTTP url, then the user will see warnings when previewing e-mail or running the WPR Cron.
* The new broadcast form did not validate the subject, textbody and HTML body fields. Now it does. 

New Features:

* Added mechanism to delete subscription forms.

**WP Responder 4.9.3**

Bug Fixes:

* Preview e-mail button wasn't working in the form for add new messages to autoresponder series
* Sometimes users will not be able to create a newsletter using the create newsletter form
* Subscriber profile page did not allow editing a subscriber's custom field values if they are subscribed to more than one newsletter.

* If multiple autoresponder messages were scheduled to be delivered on the same day, only one will be delivered.
* Subscription form allowed the selection of any autoresponder for any newsletter.

New Features:

* Automatic Subscriber Transfer - Automatically deactivate a subscriber of Newsletter A when they subscribe to Newsletter B. Ideal for separating buyers from prospects.
* Delete subscribers in mass
* Delete subscription forms in mass
* Put subscription forms in sidebar using widgets!


**WP Responder 4.9.4.2**

Bug Fixes:

* PHP Syntax error when users try to subscribe to any newsletter. Occurs sometimes. 
* The fseek function calls in Swift library have been silenced so that they don't throw an error when sending an e-mail that has an image with a source which is a HTTP url. 
* Subscription forms now do not accept malformed e-mail addresses.

New Features

* Small antispam measure in subscription forms
* Subscription forms now have a lot of span tags so that they can be easily themed if needed.


**WP Responder 4.9.5.1**


New Features:

* When delivering blog posts as a part of a post series, the plugin was checking if a post has been disabled and skipping it. It doesn't do so anymore. All posts in a post series category will be delivered regardless of the skip settings. 

* The blog email customization features were hidden when the blog e-mail customization was disabled. Now the fields are merely disabled. 

* The blog email customization was enabled by default. Now it is disabled by default. 

* The import/export feature was rebuilt so that the user can now select all the rows and columns in the CSV file themselves. This means all types of CSV files can now be supported. No more confusion about what format the CSV files are to be used. 

* The format in which the plugin exports data is now in a standard easily understandable CSV format.

Bug Fixes:

* The subscribers who unsubscribed were marked as Transfered in the subscribers management page. This has been corrected.

* The new/edit broadcast page was erroneously coded to check if the scheduled time is in the past even if the dispatch time is 'Immediately'.

* Deleting a subscriber threw the error: 'Catchable fatal error: Object of class stdClass could not be
converted to string in wp_dir/wp-content/plugins/wp-responder-email-autorespond
er-and-newsletter-plugin/subscribers.php on line 157'. Fixed

* Invalid e-mail addresses in the database prevent all broadcasts from being processed further. This resulted in the plugin not sending any e-mail when a broadcast is scheduled/dispatched.


**WP Autoresponder v5.2.7 **

Code Changes:

* Centrailzed delivery record insertion for blog posts: The delivery record for blog post is now inserted at the function that delivers blog post as against in the processes that perform blog post deliveries
* Properly escaped all queries in optin.php
* Prevented SQL errors from occuring during initial installation
* Prevented crons from being scheduled again when they are already scheduled
* Modified autoresponder subscribers query to not return duplicate rows
* Made the subscription form's follow-up selection interface extensible so that a custom follow-up type can be added by an external plugin
* Removed ad placeholder from settings page. Some other time. In some other form. 
* Fixed incorrect query arguments for fetching list of categories in blog category processing
* Fixed administration area slowed down by the plugin

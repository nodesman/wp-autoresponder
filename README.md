WP-Autoresponder
================

Email marketing and newsletter plugin for WordPress

This is a WordPress plugin used to set up and run email marketing campaigns. With WPR you can:

* Create a newsletter list - A set of subscribers to whom you can regularly email a broadcast from time to time. 
* Create a follow-up autoresponder series - A series of messages to be sent on a specific number of days since subscription to each subscriber

Subscribers can receive blog post deliveries, newsletter broadcasts and follow-up autoresponder messages all side by side. 

How to Build
============

After making modifications to the plugin, you need to ensure that you have not yet broken any existing functionality. The way to do this is by:

Set up your copy of wordpress-unit (already placed in this repository under tests/). By following the instructions here - [Unit Testing WordPress Plugins](http://stackoverflow.com/questions/9138215/unit-testing-wordpress-plugins)

After you make your changes, run the following command in the repository root:


phpunit


If you see a green bar, you're all good to go. 


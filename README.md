Development Discontinued
========================

All further work on this plugin has been discontinued as of June 12th, 2013. 


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

Ongoing Rewrite
===============

The plugin is currently undergoing a phased rewrite/refactoring. Don't be surprised if you find well written code somewhere and blasphemous ungodly warp of fecal matter in some other sections. I've made potentially every single mistake that a developer could possibily make, invented a few extra ones and made them all in this code base and I am working on getting the application to some level of stability which I am guessing is going to take a few months or even a year or two.  
Making assumptions about the programming constructs that will be available in the plugin - functions, classes are not at all safe. The only constant between now and the future versions are the hooks and filters that I have implemented in the plugin. You can rely on them. If you want your own, implement them and write tests for them.

Bon voyage!

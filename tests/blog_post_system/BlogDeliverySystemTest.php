<?php


class BlogDeliverySystem extends WP_UnitTestCase {

    public function setUp() {

        //truncate queue

        //truncate subscribers section


    }

    public function testWhetherMakingABlogPostResultsInBlogPostBeingDeliveredToTargetSubscribers() {

        //add a few unsubscribed subscribers

        //add a few subscribers who are not confirmed

        //add a few confirmed non-blog subscribers in the same newsletter

        //add a few subscribers who are confirmed and are actually subscribed to the blog.

        //run the blog cron

        //check whether it caused the delivery of blog posts for only the exact subscribers who are subscribed.

    }

    public function testWhetherDeliveryOfBlogCategorySubscriptionWorks() {

    }

    public function testWhetherDeliveryOfABlogPostThroughBlogCategorySubscriptionIsDeliveredOnlyOnceWhenSubscriberSubscribedToMultipleBlogCategoriesThatApply() {

    }


    public function testWhetherAGivenEmailAddressWillReceiveAEmailOnlyOnceEvenThoughSubscribedToMultipleNewsletters() {

    }


    public function tearDown() {

    }

}
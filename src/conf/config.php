<?php
/**
 * Created by JetBrains PhpStorm.
 * User: raj
 * Date: 12/27/12
 * Time: 11:22 PM
 * To change this template use File | Settings | File Templates.
 */


class WPR_Config
{
    private static $AutoresponderMessagesBatchSize = 10;
    private static $WhetherAttachImagesWithEmails = true;

    public static function autoresponderBatchSize() {
        return WPR_Config::$AutoresponderMessagesBatchSize;
    }

    public static function attach_images_with_emails() {
        return WPR_Config::$WhetherAttachImagesWithEmails;
    }
}
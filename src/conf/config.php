<?php

class JavelinConfig
{
    private static $AutoresponderMessagesBatchSize = 10;
    private static $WhetherAttachImagesWithEmails = true;
    private static $SenderAddressOptionKey = 'wpr_address';

    public static function senderAddress($address = null)
    {
        if (null == $address) {
            return get_option(self::$SenderAddressOptionKey);
        } else {
            update_option(self::$SenderAddressOptionKey, $address);
        }
    }

    public static function autoresponderBatchSize() {
        return JavelinConfig::$AutoresponderMessagesBatchSize;
    }

    public static function attach_images_with_emails() {
        return JavelinConfig::$WhetherAttachImagesWithEmails;
    }
}
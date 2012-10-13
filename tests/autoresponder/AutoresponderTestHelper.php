<?php

class AutoresponderTestHelper
{



    public static function addAutoresponderAndFetchRow($newsletterId, $nameOfAutoresponder) {
        global $wpdb;
        $addAutoresponder = sprintf("INSERT INTO {$wpdb->prefix}wpr_autoresponders (`nid`, `name`) VALUES(%d, '%s');", $newsletterId, $nameOfAutoresponder);
        $wpdb->query($addAutoresponder);

        $getAutoresponderJustInserted = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponders WHERE name='%s' AND nid=%d", $nameOfAutoresponder, $newsletterId);
        $autoresponder = $wpdb->get_row($getAutoresponderJustInserted);
        return $autoresponder;
    }


    public static function getDifferenceInAutoresponders($autorespondersList, $NUMBER_OF_AUTORESPONDERS_QUERIED, $autoresponderRowsAdded)
    {
        $autoresponderNames = self::getNamesOfAutoresponders($autorespondersList);
        $autoresponderRowsAddedToLimit = array_slice($autoresponderRowsAdded,0,$NUMBER_OF_AUTORESPONDERS_QUERIED);

        $autorespondersAddedNames = self::getNamesOfAutoresponderRows($NUMBER_OF_AUTORESPONDERS_QUERIED, $autoresponderRowsAdded);

        $difference = array_diff($autoresponderNames, $autorespondersAddedNames);
        return $difference;
    }

    private static function getNamesOfAutoresponderRows($NUMBER_OF_AUTORESPONDERS_QUERIED, $autoresponders)
    {
        for ($i = 0; $i < $NUMBER_OF_AUTORESPONDERS_QUERIED; $i++) {
            $autorespondersAddedNames[] = $autoresponders[$i]->name;
        }
        return $autorespondersAddedNames;
    }

    private static function getNamesOfAutoresponders($autorespondersList)
    {
        foreach ($autorespondersList as $autoresponderObject) {
            $autoresponderNames[] = $autoresponderObject->getName();
        }
        return $autoresponderNames;
    }

    public static function addAutoresponderObjects($newsletter_id, $numberOfAutorespondersToAdd)
    {
        $autoresponders = array();
        for ($i = 0; $i < $numberOfAutorespondersToAdd; $i++) {
            $autoresponders[] = AutoresponderTestHelper::addAutoresponderAndFetchRow($newsletter_id, "Autoresponder_" . md5(microtime()));
        }

        return $autoresponders;
    }

}
<?php 

class ShipitDebug {

    static function debug($message) {
        $date = new DateTime();
        $timeZone = $date->getTimezone();
        $currentDate = date('d/m/Y H:i:s');
        $message = $message.PHP_EOL;
        error_log($timeZone->getName().' '.$currentDate.' '.$message, 3, dirname(__FILE__) .'/../debug.log');
    }
    
}
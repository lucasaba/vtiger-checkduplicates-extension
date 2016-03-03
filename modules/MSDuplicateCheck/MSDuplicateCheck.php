<?php

include_once 'modules/Vtiger/CRMEntity.php';

class MSDuplicateCheck extends Vtiger_CRMEntity {

    /**
     * Invoked when special actions are performed on the module.
     *
     * @param   String Module name
     * @param   String Event Type
     */
    function vtlib_handler ($moduleName, $eventType) {
        if ($eventType == 'module.postinstall') {
            self::initializeSchema();
            self::checkSchema();
        }
        if ($eventType == 'module.postupdate') {
            self::initializeSchema();
            self::checkSchema();
        }
    }

    /**
     *  Creates table if not exists
     */
    private static function initializeSchema() {
        if (!Vtiger_Utils::CheckTable('ms_duplicatecheck')) {
            // create table
            Vtiger_Utils::CreateTable('ms_duplicatecheck', '(module varchar(50), tablename varchar(255), columnname varchar(255), field_htmlid varchar(255))', true);
        }
    }
    
    private static function checkSchema(){
        global $adb;
        $result = $adb->pquery("show columns from ms_duplicatecheck like ?", array('save_blocker_status'));
        if (!($adb->num_rows($result))) {
            $adb->pquery("ALTER TABLE  `ms_duplicatecheck` ADD  `save_blocker_status` TINYINT( 1 ) NOT NULL DEFAULT  '0'", array());
        }
    }
}
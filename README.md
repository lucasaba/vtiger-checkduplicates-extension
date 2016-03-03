# vtiger-checkduplicates-extension

This is a vtiger extension to check for duplicates. It requires manual changes in the database after installation and is therefore only recommended to be used if familiar with manually changing anything in the vtiger database. Finding all necessary values to setup the duplicate check also requires knowledge about the vtiger DOM.

The table ms_duplicatecheck contains the fields that need checking for duplicates and defines what action should happen in case the system found a duplicate.

`module` contains the module name

`tablename` contains the full table name where the field to be checked can be found

`columnname` contains the columnname of the field to be checked

`field_htmlid` contains the html value of the ID attribute. Find this through the vtiger DOM

`save_blocker_status` a status if a duplicate should prevent from saving by deactivating the save button

Example data for checking duplicate serialnumbers in the assetmodule:
```
INSERT INTO `ms_duplicatecheck` (`module`, `tablename`, `columnname`, `field_htmlid`, `save_blocker_status`) VALUES
('Assets', 'vtiger_assets', 'serialnumber', 'Assets_editView_fieldName_serialnumber', 0);
```

Maybe someone wants to build some user interface for this sometime.

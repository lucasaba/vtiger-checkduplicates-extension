<?php
/**
 * Processes the duplicate check ajax requests
 */
class MSDuplicateCheck_MSDuplicateCheckAjax_Action extends Vtiger_Action_Controller {
	
    private $db;
    
	public function __construct() {
		parent::__construct();
		$this->exposeMethod('getDuplicateCheckFields');
		$this->exposeMethod('checkDuplicate');
		$this->db = PearDatabase::getInstance();
	}
	
	public function checkPermission(Vtiger_Request $request) {
		return;
	}	

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
        
	/**
	 * Get the defined fields to be checked and have the check registered
	 * 
	 * @param Vtiger_Request $request
	 */
    public function getDuplicateCheckFields (Vtiger_Request $request){
        $module = $request->get("requestingModule");
        $result = $this->db->pquery("select * FROM ms_duplicatecheck WHERE module='$module'");
        $number = $this->db->num_rows($result);
        $return = array();
        for($j=0; $j<$number; $j++) {
            $row = $this->db->query_result_rowdata($result, $j);
            $return[] = array('field_htmlid' => $row['field_htmlid'], 'save_blocker_status' => $row['save_blocker_status']);
        }
        $return = array("status" => "200", "content" => array("fields" => $return));
        $response = new Vtiger_Response();
        $response->setResult($return);
        $response->emit();
        return;
    }          

    /**
     * Check if the value already exists
     * 
     * @param Vtiger_Request $request
     */
    public function checkDuplicate(Vtiger_Request $request){
        // double check if field was defined as duplicate checking and get the internal definiton of the field
        $query = "SELECT * FROM `ms_duplicatecheck` WHERE module = ? AND field_htmlid = ?";
        $params = array($request->get('requestingModule'), $request->get('requestingField'));
        $result = $this->db->pquery($query, $params);
        $number = $this->db->num_rows($result);
        $return = array();
        if($number > 0){
            for($x=0; $x<$number2; $x++) {
                $row = $this->db->query_result_rowdata($result, $x);
                // check for duplicate values 
                $result2 = $this->db->pquery("SELECT * FROM {$row['tablename']} WHERE {$row['columnname']} = ?",
                    array($request->get('checkValue'))
                );
                $number2 = $this->db->num_rows($result2);
                if ($number2>0){
                    for($j=0; $j<$number2; $j++) {
                        $row = $this->db->query_result_rowdata($result2, $j);
                        // first row is always the entity id
                        $entityId = $row[0];
                        // double check it's not a deleted
                        $result3 = $this->db->pquery("SELECT * FROM `vtiger_crmentity` WHERE `crmid`=? AND deleted=0", array($entityId));
                        $number3 = $this->db->num_rows($result3);
                        if($number3>0){
                            // result could be multiple ids
                            $return[] = $entityId;
                        }
                    }
                }
            }
        }

        // prepare response array
        $return = array("status" => "200", "content" => array("duplicate_ids" => $return), "msg" => vtranslate('JS_MSDUPLICATE_FOUND', 'MSDuplicateCheck'));
        $response = new Vtiger_Response();
        $response->setResult($return);
        $response->emit();
        return;
    }
}

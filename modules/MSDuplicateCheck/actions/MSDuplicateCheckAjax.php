<?php

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

    public function checkDuplicate(Vtiger_Request $request){
        $result = $this->db->pquery("SELECT * FROM ms_duplicatecheck 
                                     WHERE module='{$request->get('requestingModule')}'
                                     AND field_htmlid='{$request->get('requestingField')}'");
        $number = $this->db->num_rows($result);
        $return = array();
        if($number > 0){
            $row = $this->db->query_result_rowdata($result, 0);
            $result2 = $this->db->pquery("SELECT * FROM {$row['tablename']}
                                         WHERE {$row['columnname']}='{$request->get('checkValue')}'");
            $number2 = $this->db->num_rows($result2);
            if ($number2>0){
                for($j=0; $j<$number2; $j++) {
                    $row = $this->db->query_result_rowdata($result2, $j);
                    $entityId = $row[0];
                    $result3 = $this->db->pquery("SELECT * FROM `vtiger_crmentity` WHERE `crmid`=? AND deleted=0", array($entityId));
                    $number3 = $this->db->num_rows($result3);
                    if($number3>0){
                        $return[] = $entityId;
                    }
                }
            }
        }

        $return = array("status" => "200", "content" => array("duplicate_ids" => $return));
        $response = new Vtiger_Response();
        $response->setResult($return);
        $response->emit();
        return;
    }
}

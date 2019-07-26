<?php

class opennessDuty extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
		$this->load->model('openness_column_model', 'openness_column');
    }

    public function index() {
       // 行政职权目录及流程图

		$column_list = $this->openness_column->find(array('code' =>50100,'removed'=>false,'status'=>true, 'site_id' => $this->site_id), null, 0, array('branch_id'));
		
		$column_arr=array();
		foreach($column_list as $key => $value){
			$column_arr[]= $value['branch_id'];
		}
		
		$branch_list = $this->site_branch->find(array('status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('full_name','name'),array('sort'=>'desc'));	
		
		$this->vals['duty_branch_list'] = array();
		
		foreach($branch_list as $key => $value){
			$_id=(string)$value['_id'];		
			if(in_array($_id,$column_arr)){
				$this->vals['duty_branch_list'][] = $value;
			}
		}
         $View = new Blitz('template/openness/openness-list-duty.html');
        $View->display($this->vals);
    }

}

?>
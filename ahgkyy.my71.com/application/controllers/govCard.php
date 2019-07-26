<?php

class GovCard extends MY_Controller {

    public function __construct() {
        parent::__construct();
		
    }

    public function index() {
		
		$_mid = (string)$this->input->get('_mid');
		$branch_id = (string)$this->input->get('branch_id');
		
        $View = new Blitz('template/gov-card.html');
        $struct_list = $View->getStruct();

		$this->load->model('site_branch_model', 'site_branch');

        $branch = $this->site_branch->find(array("_id" => $branch_id));
        //var_dump($branch);
		if(empty($branch)) {
			header("Content-Type: text/html;charset=utf-8"); 
            show_error('抱歉，缺少部门信息！');exit;
        }
		if($branch['supervision_on'] == true){
			$data['can_supervision'] = 1;
		}
		if($branch['service_on'] == true){
			$data['can_service'] = 1;
		}
		if($branch['openness_on'] == true){
			$data['can_openness'] = 1;
		}
		if(!empty($branch['website'])){
			$data['website'] = 1;
		}
		$data['branch'] = $branch;
		$data['_mid'] = $_mid;

        $View->display($data);
    }
}

?>
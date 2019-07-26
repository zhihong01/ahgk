<?php

class opennessFinance extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
    }


    public function index() {

        $arr_sort = array('sort' => 'DESC');
        $View = new Blitz('template/openness/finance.html');
		$struct_list = $View->getStruct();
        $data = array();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'branch') {
                    list($parent_id) = explode('_', $matches[2]);
                    $branch_list = $this->site_branch->find(array('parent_id'=>$parent_id, 'status' => true, 'removed' => false),500,0,array('name','_id'),$arr_sort);
					$this->load->model('openness_column_model', 'openness_column');
					foreach($branch_list as $k=>$v){
						$ql_column= $this->openness_column->find(array('branch_id' =>(string)$v['_id'],'name'=>new MongoRegex("/预算/i"),'status' => true, 'removed' => false));
						if(empty($ql_column)){
							continue;
						}else{
							$item_list[$k]= $v;
						}
					}		
                }
                $data[$struct_val] = $item_list;
            }
        }
		//print_r($item_list);die();
        $View->display($data);
    }

}

?>
<?php

class opennessChart extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
    }


    public function index() {
		$data=array();
		$barnch_id_list = $this->site_branch->find(array('parent_id'=>array("\$in"=>array('55fccd686eed738658cbca72','55fce03c6eed73165abad536')), 'status' => true, 'removed' => false),200,0,array('name','_id'));
		
		$this->load->model('openness_column_model', 'openness_column');
		foreach($barnch_id_list as $k=>$v){
			$ql_column= $this->openness_column->find(array('branch_id' =>(string)$v['_id'],'name'=>'权力清单和责任清单','status' => true, 'removed' => false));
			if(empty($ql_column)){
				continue;
			}else{
				$data['branch_list'][$k]= $v;
			}
		}		


        $View = new Blitz('template/openness/chart.html');
		
        $View->display($data);
    }

}

?>
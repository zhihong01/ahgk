<?php

class top extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    //部门排名
    protected function itemTopBranch($type,$limit,$offset,$length=8){
    	$this->load->model('content_dept_counter_model', 'content_dept_counter');
        $filter = array('site_id' => $this->site_id);
        $select = array('this_year_total', 'this_year_shown_dept_total');
        $item_counter = $this->content_dept_counter->find(null,1,0,$select);
        $item_list=array();
        if($item_counter){
        	$total_dept_arr=$item_counter['this_year_shown_dept_total'];
        	
	         //获取当前类型下所有部门
	         $this->load->model('site_branch_model', 'site_branch');
	         $dept_item=$this->site_branch->find(array('site_id' => $this->site_id,'parent_id'=>$type),100,0,array('name'));
	         $dept_arr=array();
	         $i=0;
	         foreach ($dept_item as $key => $item) {
		         $dept_arr[$i]=$item['name'];
		         $i++;
		         $dept_child_item=$this->site_branch->find(array('site_id' => $this->site_id,'parent_id'=>(string)$item['_id']),200,0,array('name'));
		         foreach ($dept_child_item as $key1 => $item1) {
		         	$dept_arr[$i]=$item1['name'];
		         	$i++;
		         }
	         }
	         
	         $j=0;
	         foreach ($total_dept_arr as $key => $item){//根据部门类型排除
	         	if(in_array($item['branch_name'],$dept_arr)){
	                $item_list[$j]=$item;
	         		if (mb_strlen($item['branch_name']) > $length) {
		                $item_list[$j]['short_name'] = mb_substr($item['branch_name'], 0, $length);
		            }else{
		            	$item_list[$j]['short_name'] =$item['branch_name'];
		            }
		            $item_list[$j]['num']=$j+1;
	                $j++;
	            }
	         }
         }
        return $item_list;
    }

    public function index(){
    	$View = new Blitz('template/top-dept.html');
    	$struct_list = $View->getStruct();       
        $data = array();
        $type_id=$this->input->get('type');
        if(empty($type_id)){
        	$type_id='53daf0fa9a05c21469b6a866';//默认呼伦贝尔市政府网站
        }
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'dept') {
                    // 部门信息排行
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if($type=='current'){
                    	$type=$type_id;
                    }
                
			         if($type=='5418def79a05c2c05c230f0d'){//旗市区
			         	 $item_list[0]=array('name'=>'旗市区');
			         	 $item_list[0]['year']=date('Y');
			         	 $item_list[0]['month']=date('m');
			         	 $item_list[0]['child']=$this->itemTopBranch('5418def79a05c2c05c230f0d',200,0,10);
			         }else{
	                  	$this->load->model('site_branch_model', 'site_branch');
				         $dept_item=$this->site_branch->find(array('site_id' => $this->site_id,'parent_id'=>$type,'removed'=>false,'status'=>true),100,0,array('name'),array('sort' => 'DESC'));
				         $dept_arr=array();
				         $item_list=array();
				         foreach ($dept_item as $key => $item) {
				         	if($item['_id']!='54164b539a05c213473596ab'&&$item['_id']!='54379aa69a05c27865da5f34'){
						         $item_list[$key]=$item;
					         	 $item_list[$key]['year']=date('Y');
					         	 $item_list[$key]['month']=date('m');
					         	 $item_list[$key]['child']=$this->itemTopBranch((string)$item['_id'],200,0,10);
				         	}
				         }
			         }
			         
                }
                $data[$struct_val] = $item_list;
            }
        }
        $View->display($data);
    }
    
}

?>
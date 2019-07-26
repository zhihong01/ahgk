<?php

class opennessPubFive extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
		$this->load->model('openness_column_model', 'openness_column');
    }


/*     public function index() {
	$View = new Blitz('template/openness/publicity.html');
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
                    $branch_list = $this->site_branch->find(array('parent_id'=>$parent_id, 'status' => true, 'removed' => false),500,0,array('name','_id'));
					$this->load->model('openness_column_model', 'openness_column');
					$i=0;
					foreach($branch_list as $k=>$v){
						$ql_column= $this->openness_column->find(array('branch_id' =>(string)$v['_id'],'name'=>new MongoRegex("/预算/i"),'status' => true, 'removed' => false));
						if(empty($ql_column)){
							continue;
						}else{
							$item_list[$i]['name']= $v['name'];
							$item_list[$i]['url']= '/opennessContent/?branch_id='.$v['_id'];
							$item_list[$i]['_id']= $v['_id'];
							$i++;
						}
					}		
                }
                $data[$struct_val] = $item_list;
            }
        }
		//echo "<pre>";print_r($item_list);die();
        $View->display($data);
    } */
	
	protected function branchList($parent_id, $limit = 10, $offset = 0, $length = 60, $sort_by = 0) {

        $type_id = (int) $type_id;
        $arr_sort = array('sort' => 'DESC');

        $select = array('_id', 'name','website');
        $item_list = $this->site_branch->find(array('parent_id' => $parent_id,  'openness_on' => true, 'removed' => False), $limit, $offset, $select, $arr_sort);
		

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
			
			$spjg = $this->openness_column->find(array('branch_id' => (string)$item['_id'],'code'=>70203,'status' => true, 'removed' => false));
			
			$xzcf = $this->openness_column->find(array('branch_id' => (string)$item['_id'],'code'=>70302,'status' => true, 'removed' => false));
			
			if($spjg['code'] == 70203 && $xzcf['code'] == 70302){
				if (mb_strlen($item['name']) > $length) {
					$item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
				}else{
					$item_list[$key]['short_name'] = $item['name'];
				}
				$item_list[$key]['spjg'] = "审批结果";
				$item_list[$key]['spjg_url'] = "/opennessContent/?branch_id=".$item['_id']."&column_code=070203";
				$item_list[$key]['xzcf'] = "行政处罚结果";
				$item_list[$key]['xzcf_url'] = "/opennessContent/?branch_id=".$item['_id']."&column_code=070302";	
			}elseif($spjg['code'] == 70203){
				if (mb_strlen($item['name']) > $length) {
					$item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
				}else{
					$item_list[$key]['short_name'] = $item['name'];
				}
				$item_list[$key]['spjg'] = "审批结果";
				$item_list[$key]['spjg_url'] = "/opennessContent/?branch_id=".$item['_id']."&column_code=070203";
				
			}elseif($xzcf['code'] == 70302){
				if (mb_strlen($item['name']) > $length) {
					$item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
				}else{
					$item_list[$key]['short_name'] = $item['name'];
				}
				$item_list[$key]['xzcf'] = "行政处罚结果";
				$item_list[$key]['xzcf_url'] = "/opennessContent/?branch_id=".$item['_id']."&column_code=070302";	
			}else{
				if (mb_strlen($item['name']) > $length) {
					$item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
				}else{
					$item_list[$key]['short_name'] = $item['name'];
				}
				$item_list[$key]['spjg'] = "";
				$item_list[$key]['spjg_url'] = "";
				$item_list[$key]['xzcf'] = "";
				$item_list[$key]['xzcf_url'] = "";
			}
            
        }
        return $item_list;
    }
	
	public function index() {
        $View = new Blitz('template/openness/openness-wgk.html');
		$struct_list = $View->getStruct();
        $data = array();
		$arr_sort=array('sort'=>'DESC');
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';
				//获取部门列表
                if ($action == 'branch') {
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->branchList($parent_id, $limit, $offset, $length, $sort_by, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $View->display($data);
    }

}

?>
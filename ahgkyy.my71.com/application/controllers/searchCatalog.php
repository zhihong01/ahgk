<?php

class searchCatalog extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_column_model', 'openness_column');
    }

    

    protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $like = array(),$from_date, $to_date,$keywords) {
        $this->load->model('openness_content_model', 'openness_content');

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $where_array['status'] = True;
        $where_array['removed'] = False;
        $where_array['branch_id'] = $branch_id;
        $select = array('_id', 'name', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'openness_date','link_url');
     //    $select = array('*');
     // $item_list = $this->openness_column->searchList($branch_id, $where_array, $limit, $offset, $select, $arr_sort, $like,$from_date, $to_date,$keywords);
           $item_list = $this->openness_column->findList(null,$keywords, $where_array, null,null,$limit, $offset, $select, $arr_sort, $like,$from_date, $to_date);
     
        foreach ($item_list as $key => $item) {

            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }

            $item_list[$key]['date'] = $item['openness_date'];
            $item_list[$key]['url'] = $item['link_url']?$item['link_url']:"/openness/detail/content/" . $item['_id'] . '.html';
        }
        return $item_list;
    }

   

	
    public function index() {
		 $this->load->model('openness_column_model', 'openness_column');
        //urrent_branch_id = $this->input->get('branch_id') ? $this->input->get('branch_id') : $this->gov_branch;
		 $current_branch_id = $this->input->get('branch_id') ? $this->input->get('branch_id') : $this->gov_branch;
		//var_dump($current_branch_id);die();
        $keywords = $this->input->get('keywords')?(string)$this->input->get('keywords'):null;
		//var_dump($keywords);die();
        $way = $this->input->get('way')?$this->input->get('way'):null;
        $from_date = $this->input->get('from_date')?$this->input->get('from_date'):null;
        $to_date = $this->input->get('to_date')?$this->input->get('to_date'):null;
		
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
		$where_array=array('status'=>True,'removed'=>False,'branch_id'=>$current_branch_id);
		
		if($keywords){
			if($way){
				$like=array($way=>$keywords);
			}else{
				$like=array('name'=>$keywords);
			}
		} 
		$select = array('_id', 'name', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'code', 'openness_date');
		$arr_sort = array('sort' => 'DESC');
		$item_list = $this->openness_column->findList(null,$keywords, $where_array, null,null,1, 0, $select, $arr_sort,$way); 
		
		
        
		foreach ($item_list as $key => $item) {

            $item_list[$key]['code'] = (string) ($item['code']);
            
        }
		$item_list['code'] = $item_list[$key]['code'];
		//var_dump($item_list['$keywords']);die();
		header("Location: /opennessContent/?branch_id=".$current_branch_id."&column_code=".$item_list['code']);
                 exit();
        $View = new Blitz('template/openness/list-catalog.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];

                $struct_val = trim($matches[0], '/');
                //列表
                if ($action == 'search') {
                    list($branch_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($branch_id != 'current') {
                        $branch_id = explode('-', $branch_id);
                    } else {
                        $branch_id = $current_branch_id;
                    }

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->opennessList($branch_id, $where_array, $limit, $offset, $length, $date_format,$like,$from_date, $to_date,$keywords);
					
                }


                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count, 0);
                    $item_list[0]['page'] = $link;
                }
                $data[$struct_val] = $item_list;
            }
        }

        $data['keywords'] = $keywords;
        $data['total_row'] = $total_row;
        //print_r($data);die();

        $View->display($data);
    }

    

}

?>
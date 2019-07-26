<?php

class opennessFocus extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_column_model', 'openness_column');
        $this->load->model('openness_topic_model', 'openness_topic');
		 $this->load->model('friend_link_model', 'friend_link');
    }
    protected function opennessListAll($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = 0,$qtjg,$qtlc) {

        $where_array = array(
            'site_id' => $this->site_id,
            'status' => True,
            'removed' => False
        );
      
        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'openness_date');
        $arr_sort = array('openness_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
		if($qtjg){
			$codearr =array(50402,50502,50602,50702,50802,50902);
			$where_array = array(
				'site_id' => $this->site_id,
				'status' => True,
				'removed' => False,
				'column_code'=>array("\$in" => $codearr),
				
			);
			$item_list = $this->openness_content->find($where_array, $limit, $offset, $select, $arr_sort);
		}elseif($qtlc){
			$codearr =array(50201,50301,50401,50501,50601,50701,50801,50901,51001,51101);
			$where_array = array(
				'site_id' => $this->site_id,
				'status' => True,
				'removed' => False,
				'column_code'=>array("\$in" => $codearr),
				
			);
			$item_list = $this->openness_content->find($where_array, $limit, $offset, $select, $arr_sort);
		}else{
			 $item_list = $this->openness_content->findList($branch_id, $where_array, $limit, $offset, $select, $arr_sort, (int)$code);
		}
	
        foreach ($item_list as $key => $item) {
			 $branch = $this->site_branch->find(array('_id' => $item['branch_id']));
			 $item_list[$key]['branchname'] = $branch['name'];
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }
            $item_list[$key]['date'] = $item['openness_date'];
            $item_list[$key]['url'] = "/openness/detail/content/" . $item['_id'] . '.html';
        }
        return $item_list;
    }
    // Get Links list
    protected function itemFriendLink($_id_list, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $where_array = array("type_id" => $_id_list, 'status' => true, 'removed' => False, 'site_id' => $this->site_id);
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'link_url');

        $item_list = $this->friend_link->find($where_array, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = (string) ($item['link_url']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
        }
        return $item_list;
    }

     //Obtaining group information
    protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = 0) {
     
        if ($branch_id == 'all') {
            $branch_id = NULL;
        } 
        $where_array = array(
            'site_id' => $this->site_id,
            'status' => True,
            'removed' => False
        );
      
        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'openness_date','link_url');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
	
        $item_list = $this->openness_content->findList($branch_id, $where_array, $limit, $offset, $select, $arr_sort, (int)$code);
        //print_r($this->mongo_db->last_query());die;
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

 
	// 普通新闻列表
    protected function contentList2($_id_list, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $this->load->model('content_model', 'content');

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date');
        $filter = array('status' => 1, 'removed' => false);
        $item_list = $this->content->findList($_id_list, NULL, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['url'] = $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['short_title'] = strip_tags(html_entity_decode($item_list[$key]['short_title']));
            $item_list[$key]['title'] = strip_tags(html_entity_decode($item_list[$key]['title']));
            if (mb_strlen($item['description']) > 52) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, 52) . '...';
            }
            if (mb_strlen($item['thumb_name']) == 20) {
                $item_list[$key]['thumb'] = "/data/upfile/" . substr($item['thumb_name'], 0, 1) . "/images/" . substr($item['thumb_name'], 2, 4) . "/" . $item['thumb_name'];
            } else {
                $item_list[$key]['thumb'] = $item['thumb_name'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }

    public function index() {
        $where_array = array();
        
        $gov_branch = $this->gov_branch;
        $current_branch_id = $this->input->get('branch_id') ? $this->input->get('branch_id') : $gov_branch;
       
        $current_column_code = $this->input->get('column_code') ? (int) $this->input->get('column_code') : null;
        
        $limitarr = array(
            'branch_id'=>$current_branch_id,
            'code'=>$current_column_code
        );
		
        $codenamearr = $this->openness_column->find($limitarr);
        //print_r($codenamearr);
        //echo $current_column_code;
        if(empty($current_column_code)){
            $name ='所有信息';
        }else{
            @$name = $codenamearr['name'];
        }
        
        $current_topic_id = $this->input->get('topic_id') ? $this->input->get('topic_id') : null;
         
        
        $data['branch'] = $this->site_branch->find(array('_id' => $current_branch_id));
		
		// 获取当前或父类column_id，用于左侧选中效果
		if(($current_column_code % 10000) === 0){
			$data['current_column_id'] = (string)$codenamearr['_id'];
		}else{
			$data['current_column_id'] = $codenamearr['parent_id'];
		}


        $page = (int) $this->input->get('page');

        if ($page == 0) {
            $page = 1;
        }

        $item_list = array();
        if ($current_column_code) {
            $data['column'] = $this->openness_column->find(array('code' => (int) $current_column_code, 'branch_id' => $current_branch_id));
        } else {
            $data['column']['name'] = '所有信息';
        }

        if ($current_topic_id) {
            $where_array = array('topic_id' => $current_topic_id);
            $topic = $this->openness_topic->find(array('_id' => $current_topic_id));
            $data['column']['name'] = $topic['name'];
        }
        // $total_row = $this->openness_content->listCount($current_branch_id,null,$current_column_code);

        $total_row = $this->openness_content->listCount($current_branch_id, $where_array, $current_column_code);
        //print_r($total_row);die();
        $View = new Blitz('template/openness/focus.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');

                if ($action == 'list') {
                    list($branch_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($branch_id != 'current') {
                        $branch_id = explode('-', $branch_id);
                    } else {
                        $branch_id = $current_branch_id;
                    }

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }

                    $item_list = $this->opennessList($branch_id, $where_array, $limit, $offset, $length, $date_format, $current_column_code);
                }
				if ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $_id_list = $channel_id;
                    $item_list = $this->itemFriendLink($_id_list, $limit, $offset, $length);

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
				if ($action == 'conbycolumn') {
                    list($column_code, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->opennessList($this->gov_branch, null, $limit, $offset, $length, $date_format, (int) $column_code);
                }
				
				if ($action == 'conbycolumnallqtjg') {
                    list($column_code, $limit, $offset, $length, $sort_by, $date_format,$branch_id) = explode('_', $matches[2]);
				
                    @$item_list = $this->opennessListAll($branch_id, null, $limit, $offset, $length, $date_format, $column_code,true);  
				}
				
				if ($action == 'conbycolumnallfb') {
                    list($column_code, $limit, $offset, $length, $sort_by, $date_format,$branch_id) = explode('_', $matches[2]);
				
                    @$item_list = $this->opennessListAll($branch_id, null, $limit, $offset, $length, $date_format, $column_code,false,true);  
				}
				
				
				if ($action == 'conbycolumnall') {
                    list($column_code, $limit, $offset, $length, $sort_by, $date_format,$branch_id) = explode('_', $matches[2]);
				
                    @$item_list = $this->opennessListAll($branch_id, null, $limit, $offset, $length, $date_format, $column_code);  
				}
                $data[$struct_val] = $item_list;
            }
        }
     
        $data['location'] = "<a href='/'>首页</a> > <a href='/opennessContent/?branch_id=" . $current_branch_id . "'>" . $data['branch']['name'] . "信息公开</a> > ". $name;
        $data['bname'] = $data['branch']['full_name'] . '信息公开';
        $data['siteurl'] = $this->vals['setting']['site_url'];
        $data['current_branch_id'] = $current_branch_id;
        
        $this->vals = array_merge($this->vals, $data);
        $View->display($this->vals);
    }

}

?>
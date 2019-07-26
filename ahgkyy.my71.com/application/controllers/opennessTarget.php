<?php

class opennessTarget extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_column_model', 'openness_column');
        $this->load->model('openness_topic_model', 'openness_topic');
    }

    protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
        $this->load->model('openness_content_model', 'openness_content');

        $arr_sort = array('openness_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $where_array['status'] = True;
        $where_array['removed'] = False;

        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'column_code', 'openness_date', 'id','topic_id','link_url');

        $item_list = $this->openness_content->findList($branch_id, $where_array, $limit, $offset, $select, $arr_sort, $code);


        foreach ($item_list as $key => $item) {
			$item_list[$key]['mouse'] = substr($item['_id'],-6,6);
            if ($item['branch_id']) {
                $this_branch = $this->site_branch->find(array('_id' => $item['branch_id']));
                $item_list[$key]['branch'] = $this_branch['name'];
            }
            if ($item['column_id']) {
                $this_column = $this->openness_column->find(array('_id' =>$item['column_id']));
                $item_list[$key]['column'] = $this_column['name'];
            }
			if (!empty($item['topic_id'])) {
				if (is_array($item['topic_id'])) {
					$item_list[$key]['topic']='';
					foreach ($item['topic_id'] as $val) {
						$current_topic = $this->openness_topic->find(array('_id' => (string) $val), 1, 0);
						$item_list[$key]['topic'] = !empty($current_topic) ? $current_topic['name'] . "&nbsp;&nbsp;" . $item_list[$key]['topic'] : '';
					}
				}
			}
			if ($item['tag']) {
				foreach ($item['tag'] as $val) {
					$item_list[$key]['tags'] = $item_list[$key]['tags'] . $val . "&nbsp;&nbsp;";
				}
			}
			
            $item_list[$key]['_id'] = (string) ($item['_id']);
			  $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            }else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item_list[$key]['date'] = $item['openness_date'];
            // $item_list[$key]['url'] = "/openness/detail/content/" . $item['_id'] . '.html';
			  $item_list[$key]['url'] = $item['link_url']?$item['link_url']:"/openness/detail/content/" . $item['_id'] . '.html';
        }

        return $item_list;
    }

    protected function topicList($branch_id, $parent_id, $limit = 10, $offset = 0, $length = 60) {
        $this->load->model('openness_topic_model', 'openness_topic');

        $arr_sort = array('sort' => 'DESC');
        $where_array['status'] = True;
        //$where_array['removed'] = False;

        $select = array('_id', 'name');
        $item_list = $this->openness_topic->findList($parent_id, null, $where_array, null, null, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            }
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $branch_id . "&topic_id=" . $item['_id'];
        }

        return $item_list;
    }

    protected function branchList($parent_id, $limit = 10, $offset = 0, $length = 60, $sort_by = 0) {

        $arr_sort = array('sort' => 'DESC');

        $select = array('_id', 'name', 'website');
        $item_list = $this->site_branch->find(array('parent_id' => $parent_id, 'status' => true, 'openness_on' => true, 'removed' => False), null, $offset, $select, $arr_sort);


        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            }
            if (empty($item['website'])) {
                $item_list[$key]['is_website'] = true;
                $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id'];
                $item_list[$key]['url_guide'] = "/opennessGuide/?branch_id=" . $item['_id'];
                $item_list[$key]['url_annual_report'] = "/opennessAnnualReport/?branch_id=" . $item['_id'];
            } else {
                $item_list[$key]['url'] = $item['website'];
                $item_list[$key]['target'] = "_blank";
            }
        }

        return $item_list;
    }

    public function index() {

        $current_branch_id = $this->input->get('branch_id') ? $this->input->get('branch_id') : null;
        $current_type = $this->input->get('type') ? $this->input->get('type') : null;
        if ($this->input->get('branch_id') == 'all') {
            $current_branch_id = $this->gov_branch;
        }
        $current_column_code = $this->input->get('column_code') ? (int) $this->input->get('column_code') : null;
        $current_topic_id = $this->input->get('topic_id') ? $this->input->get('topic_id') : null;
		
		if($current_branch_id){
			$data['branch'] = $this->site_branch->find(array('_id' => $current_branch_id));
			if(empty($data['branch'])){
				show_404();
			}
		}
		//宣城多栏目显示
		
		if(empty($current_column_code)){
			$this_child_column=$this->openness_column->find(array('branch_id'=>$current_branch_id,'removed'=>false,'status'=>true,'parent_id'=>'/'),null,0,array('name','code'),array("sort" => 'desc','code'=>'asc'));
			if($this_child_column[0]){
				$current_type='folder';
			}
		}elseif($current_column_code % 10==0){
			$this_child_column=$this->openness_column->findFloder(array('branch_id'=>$current_branch_id,'removed'=>false,'status'=>true), $current_column_code,null,0,array('name','code'),array("sort" => 'desc','code'=>'asc'));//print_r($this_child_column);die();
			if($this_child_column[0]&&$current_column_code){
				$current_type='folder';
			}
		}
		
        $page = (int) $this->input->get('page');

        if ($page == 0) {
            $page = 1;
        }

        $item_list = array();
        if ($current_column_code) {
            $data['column'] = $this->openness_column->find(array('code' => (int) $current_column_code, 'branch_id' => $current_branch_id));
        }

        if ($current_topic_id) {
            $where_array = array('topic_id' => $current_topic_id);
            $topic = $this->openness_topic->find(array('_id' => $current_topic_id));
            $data['column']['name'] = $topic['name'];
        }

        $where_array['status'] = True;
        $where_array['removed'] = False;
        if ($this->input->get('branch_id') == 'all') {
            $current_branch_id = null;
            $data['column']['name'] = '';
            $data['branch'] = '';
        }
        $total_row = $this->openness_content->listCount($current_branch_id, $where_array, $current_column_code);

        if($current_type=='folder'){
			
			foreach($this_child_column as $key => $val){
				if (($current_column_code % 10000) === 0) {
					if($val['code'] % 100 == 0){
						$level_one[$key]['name']=$val['name'];
						$level_one[$key]['url']="/opennessContent/?branch_id=$current_branch_id&column_code=".$val['code'];
						$level_one[$key]['code']=$val['code'];
						$level_one[$key]['info']=$this->opennessList($current_branch_id, $where_array, 6, 0, 35, 1, (int)$val['code']);						
				     }					
				} elseif (($current_column_code % 100) === 0) {
					if($val['code'] % 100 != 0){
						$level_one[$key]['name']=$val['name'];
						$level_one[$key]['url']="/opennessContent/?branch_id=$current_branch_id&column_code=".$val['code'];
						$level_one[$key]['code']=$val['code'];
						$level_one[$key]['info']=$this->opennessList($current_branch_id, $where_array, 6, 0, 35, 1, (int)$val['code']);
						
					}
					
				}
				
			}
			
			$View = new Blitz('template/openness/openness-target-item.html');
			$data['level_one'] = $level_one;
		}else{
			$View = new Blitz('template/openness/openness-target.html');
		}
        
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

                //获取信息公开专题列表
                if ($action == 'topic') {
                    list($branch_id, $parent_id, $limit, $offset, $length, $sort_by) = explode('_', $matches[2]);
                    if ($branch_id != 'current') {
                        $branch_id = explode('-', $branch_id);
                    } else {
                        $branch_id = $current_branch_id;
                    }
                    $parent_id = explode('-', $parent_id);
                    $item_list = $this->topicList($branch_id, $parent_id, $limit, $offset, $length, $sort_by);
                }

                //获取部门列表
                if ($action == 'branch') {
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($parent_id == 'current') {
                        $parent_id = $parent_branch_id;
                    }
                    $item_list = $this->branchList($parent_id, $limit, $offset, $length, $sort_by, $date_format);
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

        $data['is_column'] = $data['column']['name'] ? $data['column']['name'] : "最新信息公开";
        $data['current_branch_id'] = $current_branch_id;
        $data['openness_type'] = "信息公开目录";
        $data['openness_content'] = "hover";
        $data['gov_branch_id'] = $this->gov_branch;


        $View->display($data);
    }

}

?>
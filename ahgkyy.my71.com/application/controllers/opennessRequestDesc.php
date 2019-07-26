<?php

class opennessRequestDesc extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
		$this->load->model('site_attach_model', 'site_attach');
		$this->load->model('openness_request_model', 'openness_request');
        $this->load->model('openness_request_desc_model', 'openness_request_desc');
        $this->load->model('openness_dir_setting_model', 'openness_dir_setting');
		 $this->load->model('openness_request_counter_model', 'openness_request_counter');
		  $this->load->model('openness_request_r_counter_model', 'openness_request_r_counter');
		$this->branch_list = $this->getBranchName();
		
    }
	// 依申请公开
    protected function requestList($branch_id, $limit = 10, $offset = 0, $length = 50, $date_format = 0) {

		if(!empty($branch_id)){
			$filter = array('request_branch'=>$branch_id,'status' => true, 'removed' => false, 'site_id' => $this->site_id);
		}else{
			$filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
		}
        $select = array('_id', 'name', 'create_date', 'request_branch', 'content', 'as_type', 'unit_contact', 'reply_type');
        $date_format = $this->date_foramt[$date_format];
        $arr_sort = array('create_date' => 'DESC');

        $item_list = $this->openness_request->find($filter, $limit, $offset, $select, $arr_sort);
        $i = 1;
        foreach ($item_list as $key => $item) {
            // 依申请公开的编号
            $item_list[$key]['key'] = $offset + $i;
            $item_list[$key]['url'] = '/openness/requestDetail/?_id=' . $item['_id'];
            if ($item['as_type'] == 1) {
                $item_list[$key]['name'] = $item['name'];
            } else {
                $item_list[$key]['name'] = $item['unit_contact'];
            }
            if (isset($this->branch_list[$item['request_branch']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['request_branch']];
            } else {
                $item_list[$key]['branch'] = '';
            }
            if ($item['reply_type'] == '0') {
                $item_list[$key]['reply_type'] = '尚未办理';
            } elseif ($item['reply_type'] == '1') {
                $item_list[$key]['reply_type'] = '同意公开';
            } elseif ($item['reply_type'] == '2') {
                $item_list[$key]['reply_type'] = '同意部分公开';
            } elseif ($item['reply_type'] == '3') {
                $item_list[$key]['reply_type'] = '信息不存在';
            } elseif ($item['reply_type'] == '4') {
                $item_list[$key]['reply_type'] = '非本部门掌握';
            } elseif ($item['reply_type'] == '5') {
                $item_list[$key]['reply_type'] = '申请信息不明确';
            } else {
                $item_list[$key]['reply_type'] = '状态不明';
            }
            if (mb_strlen($item['content']) > $length) {
                $item_list[$key]['content'] = mb_substr($item['content'], 0, $length) . '...';
            }
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
            $i++;
        }
        return $item_list;
    }
	
	protected function requestdescList($branch_id, $request_type,$limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
        $this->load->model('openness_request_desc_model', 'openness_request_desc');
		
		if(!empty($branch_id)){
			$filter = array('branch_id'=>$branch_id,'status' => true, 'removed' => false, 'site_id' => $this->site_id,'request_type'=>$request_type);
		}else{
			$filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id,'request_type'=>$request_type);
		}
        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'openness_date');

        $item_list = $this->openness_request_desc->find($filter, $limit, $offset, $select, $arr_sort);
//print_r($item_list);die();
        foreach ($item_list as $key => $item) {

            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }

            $item_list[$key]['date'] = $item['openness_date'];
            $item_list[$key]['url'] = "/openness/detail/request_desc/" . $item['_id'] . '.html';
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

    public function index() {

        $current_branch_id = $this->input->get('branch_id') ? $this->input->get('branch_id') : $this->gov_branch;

        $data['branch'] = $this->site_branch->find(array('_id' => $current_branch_id));
		if ($page == 0) {
            $page = 1;
        }
		
		
		// 默认显示的类别
		$type = (int) $this->input->get('type');
		if($type == 0){
			$data['type0'] = true;
		}elseif($type == 1){
			$data['type1'] = true;
		}elseif($type == 2){
			$data['type2'] = true;
		}elseif($type == 3){
			$data['type3'] = true;
		}elseif($type == 4){
			$data['type4'] = true;
		}elseif($type == 5){
			$data['type5'] = true;
		}elseif($type == 6){
			$data['type6'] = true;
		}elseif($type == 7){
			$data['type7'] = true;
		}
		
		$View = new Blitz('template/openness/request-desc.html');
		
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');

                

				// 获取部门发布的已申请公开目录
                if ($action == 'requestdesc') {
                    list($branch_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    
					$branch_id = (string) $current_branch_id;
					
					if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
					
					$item_list = $this->requestdescList($branch_id,(string)$type, $limit, $offset, $length, $sort_by, $date_format);
					
					$total_row = count($this->openness_request_desc->find(array('branch_id'=>(string) $current_branch_id,'status' => true, 'removed' => false, 'site_id' => $this->site_id,'request_type'=>(string)$type),null,0));
                }
				
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

        $data['current_branch_id'] = $current_branch_id;
        $data['openness_request'] = "hover";
        $data['openness_type'] = "依申请公开";
        $data['gov_branch_id'] = $this->gov_branch;
		
       //依申请公开统计
		$request=array();
		$request['all']=$this->openness_request->count(array('status'=>true,'removed'=>false));//收到申请
		$request['done']=$request['all']-$this->openness_request->count(array('reply_type'=>0,'status'=>true,'removed'=>false));//已经办理
		$request['type0']=$this->openness_request->count(array('reply_type'=>0,'status'=>true,'removed'=>false));//尚未办理
		$request['type1']=$this->openness_request->count(array('reply_type'=>1,'status'=>true,'removed'=>false));//同意公开
		$request['type2']=$this->openness_request->count(array('reply_type'=>2,'status'=>true,'removed'=>false));//同意部分公开
		$request['type3']=$this->openness_request->count(array('reply_type'=>3,'status'=>true,'removed'=>false));//信息不存在
		$request['type4']=$this->openness_request->count(array('reply_type'=>4,'status'=>true,'removed'=>false));//非本部门掌握
		$request['type5']=$this->openness_request->count(array('reply_type'=>5,'status'=>true,'removed'=>false));//申请信息不明确
		$data['request']=$request;
		
		$data['openness_dir_setting'] = $this->openness_dir_setting->find(array('site_id' => $this->site_id));
		$data['openness_download'] = $this->site_attach->find(array('site_id' => $this->site_id,'module'=>'opennessRequestDirSetting'));
        
        $View->display($data);
    }

	
	public function detail() {

        $_id = (string) $this->input->get('_id');

        $View = new Blitz('template/openness/detail-openness-request.html');
        $struct_list = $View->getStruct();
        $content = $this->openness_request->find(array('_id' => $_id), 1);

        if (empty($content)) {
            show_error('抱歉，此内容不存在或已被删除！');
        }
        if ($content['as_type'] == '1') {
            $data['is_people'] = 1;
            $content['as_type'] = '公民';
        } else {
            $content['as_type'] = '法人/其他组织';
        }

        if ($content['author_open'] == '1') {
            $content['author_open'] = '公开';
        } else {
            $content['author_open'] = '不公开';
        }

        if (!empty($content['offer_type'])) {
            foreach ($content['offer_type'] as $key => $item) {
                if ($item == '1') {
                    $data['offer_one'] = 1;
                } elseif ($item == '2') {
                    $data['offer_two'] = 1;
                } elseif ($item == '3') {
                    $data['offer_three'] = 1;
                } elseif ($item == '4') {
                    $data['offer_four'] = 1;
                }
            }
        }

        if (!empty($content['for_type'])) {
            foreach ($content['for_type'] as $key => $item) {
                if ($item == '1') {
                    $data['for_one'] = 1;
                } elseif ($item == '2') {
                    $data['for_two'] = 1;
                } elseif ($item == '3') {
                    $data['for_three'] = 1;
                } elseif ($item == '4') {
                    $data['for_four'] = 1;
                } elseif ($item == '5') {
                    $data['for_five'] = 1;
                }
            }
        }
        if ($content['reply_type'] == '0') {
            $content['reply_type'] = '尚未办理';
        } elseif ($content['reply_type'] == '1') {
            $content['reply_type'] = '同意公开';
        } elseif ($content['reply_type'] == '2') {
            $content['reply_type'] = '同意部分公开';
        } elseif ($content['reply_type'] == '3') {
            $content['reply_type'] = '信息不存在';
        } elseif ($content['reply_type'] == '4') {
            $content['reply_type'] = '非本部门掌握';
        } elseif ($content['reply_type'] == '5') {
            $content['reply_type'] = '申请信息不明确';
        } else {
            $content['reply_type'] = '状态不明';
        }

        $content['date'] = ($content['create_date']) ? date('Y-m-d', $content['create_date']) : '';

        $data['data'] = $content;

        $View->display($data);
    }

}

?>
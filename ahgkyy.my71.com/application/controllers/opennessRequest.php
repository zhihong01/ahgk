<?php

class opennessRequest extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('site_attach_model', 'site_attach');
        $this->load->model('openness_request_model', 'openness_request');
		$this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_request_counter_model', 'openness_request_counter');
        $this->load->model('openness_request_r_counter_model', 'openness_request_r_counter');
        $this->load->model('openness_dir_setting_model', 'openness_dir_setting');
		$this->branch_list = $this->getBranchName();
	
		
    }

    protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
        $this->load->model('openness_content_model', 'openness_content');

        $arr_sort = array('sort' => 'DESC', 'openness_date' => 'DESC', 'create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        //$where_array['status'] = True;
        $where_array['removed'] = False;

        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'openness_date','link_url');

        $item_list = $this->openness_content->findList($branch_id, $where_array, $limit, $offset, $select, $arr_sort, $code);

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
                $item_list[$key]['reply_type'] = '<font color="#936216">尚未办理</font>';
            } elseif ($item['reply_type'] == '1') {
                $item_list[$key]['reply_type'] = '<font color="#36BD53">同意公开</font>';
            } elseif ($item['reply_type'] == '2') {
                $item_list[$key]['reply_type'] = '<font  color="#52ADF2">同意部分公开</font>';
            } elseif ($item['reply_type'] == '3') {
                $item_list[$key]['reply_type'] = '<font  color="#ff0000">信息不存在</font>';
            } elseif ($item['reply_type'] == '4') {
                $item_list[$key]['reply_type'] = '<font  color="#9B8868">非本部门掌握</font>';
            } elseif ($item['reply_type'] == '5') {
                $item_list[$key]['reply_type'] = '<font  color="#D3C01C">申请信息不明确</font>';
            } else {
                $item_list[$key]['reply_type'] = '<font  color="#999">状态不明</font>';
            }
			
			
            if (mb_strlen($item['content']) > $length) {
                 $item_list[$key]['content'] = mb_substr($item['content'], 0, $length) . '...';
             }
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
            $i++;
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

    protected function branchList($parent_id, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0, $current_id = '') {

        $arr_sort = array('sort' => 'DESC');

        $select = array('_id', 'name', 'website');
		if (strlen($parent_id) == 1) {
			 $item_list = $this->site_branch->find(array('type_id' => (int)$parent_id, 'openness_on' => true, 'removed' => False), null, $offset, $select, $arr_sort);
		}else{
			 $item_list = $this->site_branch->find(array('parent_id' => $parent_id, 'openness_on' => true, 'removed' => False), null, $offset, $select, $arr_sort);
		}


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
			if((string)$item['_id'] == $current_id){
				$item_list[$key]['selected'] = true;
			}
        }

        return $item_list;
    }
	
	protected function opennessSearchList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $like = array(), $from_date, $to_date) {
        //$this->load->model('openness_content_model', 'openness_content');

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $where_array['status'] = True;
        $where_array['removed'] = False;

        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'openness_date');

        $item_list = $this->openness_content->searchList($branch_id, $where_array, $limit, $offset, $length, $arr_sort, $like,$from_date, $to_date);
		
        foreach ($item_list as $key => $item) {

            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }

            $item_list[$key]['date'] = $item['openness_date'];
            $item_list[$key]['url'] = "/openness/detail/content/" . $item['_id'] . '.html';
        }
        return $item_list;
    }
	
protected function requestCounterList($limit = 10, $offset = 0) {
        $this->load->model('openness_request_counter_model', 'openness_request_counter');
		$this->load->model('site_branch_model', 'site_branch');
        $arr_sort = array('value.total' => 'DESC');

        $item_lists = $this->openness_request_counter->find(array('_id.site_id' => $this->site_id), $limit, $offset, array('branch','value.total'), $arr_sort);
		/* $item_lists = $this->openness_request_counter->find(null, $limit, $offset, array('branch','value.total'), $arr_sort); */
	
       foreach ($item_lists as $key => $item) {
			if(empty($item['_id']['branch_id'])){
				continue;
			}
            $item_list[$key]['branch'] = $this->branch_list[$item['_id']['branch_id']];
			$item_list[$key]['total'] = $item['value']['total'];
			$item_list[$key]['url'] = $item['website']?$item['website']:"/opennessRequest/?branch_id=" . $item['_id']['branch_id'];
        }
//        echo "<pre>";
//        var_dump($item_list);
        return $item_list;
    }
	
	protected function counterMonthList($limit = 10, $offset = 0) {
        $this->load->model('openness_counter_month_model', 'openness_counter_month');
        $this->load->model('site_branch_model', 'site_branch');
        $arr_sort = array('value.total' => 'DESC');

        $select = array('_id', 'value');
        $item_list_all = $this->openness_counter_month->find(array('_id.site_id' => $this->site_id), $limit, $offset, $select, $arr_sort);
        foreach ($item_list_all as $key => $item) {
            $branch = $this->site_branch->find(array('_id' => $item['_id']['branch_id']));
            $item_list[$key]['branch'] = $branch['full_name'];
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
			$item_list[$key]['class'] = $key+1;
        }

        return $item_list;
    }
	
	protected function counterYearList($limit = 10, $offset = 0) {
        $this->load->model('openness_counter_year_model', 'openness_year_month');
        $this->load->model('site_branch_model', 'site_branch');
        $arr_sort = array('value.total' => 'DESC');

        $select = array('_id', 'value');
        $item_list_all = $this->openness_year_month->find(array('_id.site_id' => $this->site_id), $limit, $offset, $select, $arr_sort);
        foreach ($item_list_all as $key => $item) {
            $branch = $this->site_branch->find(array('_id' => $item['_id']['branch_id']));
            $item_list[$key]['branch'] = $branch['full_name'];
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
			$item_list[$key]['class'] = $key+1;
        }

        return $item_list;
    }
	
	protected function requestdirList($branch_id, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
        $this->load->model('openness_request_dir_model', 'openness_request_dir');
		
		if(!empty($branch_id)){
			$filter = array('branch_id'=>$branch_id,'status' => true, 'removed' => false, 'site_id' => $this->site_id);
		}else{
			$filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
		}
        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'openness_date');

        $item_list = $this->openness_request_dir->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {

            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }

            $item_list[$key]['date'] = $item['openness_date'];
            $item_list[$key]['url'] = "/openness/detail/request_dir/" . $item['_id'] . '.html';
        }
        return $item_list;
    }

    public function index() {

        $current_branch_id = $this->input->get('branch_id') ? $this->input->get('branch_id') : $this->gov_branch;

        $data['branch'] = $this->site_branch->find(array('_id' => $current_branch_id));
		
		// 依申请公开目录搜索
		$page = (int) $this->input->get('page');
		$keyword = $this->security->xss_clean($this->input->get('keyword'));
		$from_date = $this->security->xss_clean($this->input->get('from_date'));
		$to_date = $this->security->xss_clean($this->input->get('to_date'));
		
		if ($page == 0) {
            $page = 1;
        }
		if (empty($keyword)) {
			$keyword = null;
        }else{
			$data['keyword'] = $keyword;
			$keyword = array('title' => $keyword);
		}
		if (empty($from_date)) {
			$from_date = null;
        }else{
			$data['from_date'] = $from_date;
		}
		if (empty($to_date)) {
			$to_date = null;
        }else{
			$data['to_date'] = $to_date;
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
		}
		
		$View = new Blitz('template/openness/request.html');
		
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

                    $item_list = $this->rulesList($branch_id, $limit, $offset, $length, $sort_by, $date_format);
                }

				
				// 获取部门发布的已申请公开目录(有搜索)
                if ($action == 'conbycolumn') {
                    list($column_code, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    
					$where_array = array('column_code' => (int)$column_code, 'status' => true, 'removed' => false);
                    $item_list = $this->opennessSearchList($current_branch_id, $where_array, $limit, $offset, $length, $sort_by, $keyword, $from_date, $to_date);
					
					$total_row = $this->openness_content->searchCount($current_branch_id, $where_array, $keyword, $from_date, $to_date);
                }

                //获取部门列表
                if ($action == 'branch') {
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($parent_id == 'current') {
                        $parent_id = $parent_branch_id;
                    }
                    $item_list = $this->branchList($parent_id, $limit, $offset, $length, $sort_by, $date_format, $current_branch_id);
                }
				
				// 依申请公开列表
               if ($action == 'request') {
                    list($limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
					$branch_id = (string) $current_branch_id[0];
					if ($offset == 'page') {
						$offset = $limit * ($page - 1);
					}
					$item_list = $this->requestList(null, $limit, $offset, $length, $date_format);
					
					$total_row = $this->openness_request->count(array('status' => true, 'removed' => false));
                }
				
				// 获取部门发布的已申请公开目录
                if ($action == 'requestdir') {
                    list($branch_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    
					$branch_id = (string) $current_branch_id;
					
					if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
					
					$item_list = $this->requestdirList($branch_id, $limit, $offset, $length, $sort_by, $date_format);
					
					$total_row = count($this->openness_request_dir->find(array('branch_id'=>(string) $current_branch_id,'status' => true, 'removed' => false, 'site_id' => $this->site_id),null,0));
                }
				
				// 依申请公开统计
                if ($action == 'requestcounter') {
                    list($limit, $offset) = explode('_', $matches[2]);
					$item_list = $this->requestCounterList($limit, $offset);
                }
				
				// 调取单篇信息
                if ($action == 'content') {
                    list($_id) = explode('_', $matches[2]);
					$openness_content = $this->openness_content->find(array('_id' => (string)$_id), 1, 0, array('body'));
					$item_list = $openness_content;
                }
				
				// 调取公开指南
                if ($action == 'rule') {
                    list($_id) = explode('_', $matches[2]);
					 $this->load->model('openness_rules_model', 'openness_rules');
					$openness_content = $this->openness_rules->find(array('_id' => (string)$_id), 1, 0, array('body'));
					$item_list = $openness_content;
                }
				
				if ($action == 'counterm') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterMonthList($limit, $offset);
                }elseif ($action == 'countery') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterYearList($limit, $offset);
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
        
        //依申请统计
        $request_counter=$this->openness_request_counter->find(array('_id.branch_id' => $current_branch_id));
        $request_r_counter=$this->openness_request_r_counter->find(array('_id.branch_id' => $current_branch_id));
        //print_r($request_r_counter);die();


        $data['current_branch_id'] = $current_branch_id;
        $data['openness_request'] = "hover";
        $data['openness_type'] = "依申请公开";
        $data['gov_branch_id'] = $this->gov_branch;
        $data['request_counter'] = $request_counter['value']['total'];
        $data['request_r_counter'] = $request_r_counter['value']['total'];
		
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
		$data['openness_dir_setting']['openness_dir_workflow_chart'] = str_replace('media/upload/','http://guangde.u.my71.com/media/upload/',$data['openness_dir_setting']['openness_dir_workflow_chart']);
		
		$data['openness_download'] = $this->site_attach->find(array('site_id' => $this->site_id,'module'=>'opennessRequestDirSetting'));
        
        $View->display($data);
    }

	public function save() {

		$this->load->model('sequence_model', 'sequence');

        $data = array();

        $data = $this->security->xss_clean($this->input->post('datas'));

        if ($data['as_type'] == '1') {

            if ($data['request_branch'] == '' || $data['name'] == '' || $data['workunit'] == '' || $data['paper_name'] == '' || $data['paper_id'] == '' || $data['phone'] == '' || $data['fax'] == '' || $data['addr'] == '' || $data['email'] == '') {
                $this->resultJson('带*号的为必填项！');
            }
            if (!$this->valid_email($data['email'])) {
                $this->resultJson('邮件地址不正确');
            }
        } else {
            if ($data['request_branch'] == '' || $data['unit_name'] == '' || $data['unit_legal'] == '' || $data['unit_contact'] == '' || $data['unit_phone'] == '' || $data['unit_fax'] == '' || $data['unit_addr'] == '' || $data['unit_email'] == '') {
                $this->resultJson('带*号的为必填项！');
            }
            if (!$this->valid_email($data['unit_email'])) {
                $this->resultJson('邮件地址不正确');
            }
        }
        $data['offer_type'] = $this->input->post('offer_type');
        $data['for_type'] = $this->input->post('for_type');
        if (strlen($data['content']) < 20) {
            $this->resultJson('信息正文太短');
        }

        if ($data['password'] == $data['repassword']) {
			
			// 查看查询码是否已经存在
			$request_content = $this->openness_request->find(array('password' => md5($data['password'])), 1, 0, array('_id'));
			if($request_content){
				$this->resultJson('查询码已存在，请重新输入！');
			}
			// 加密一下
            $data['password'] = md5($data['password']);
        } else {
            $this->resultJson('两次输入的密码不一致！');
        }
		// 取出部门的unitcode
		$branch = $this->site_branch->find(array('_id' => (string)$data['request_branch']), 1, 0, array('branch_code', 'city_code'));
		if (strlen($branch['branch_code']) == 2) {
			$branch['branch_code'] = '0' . $branch['branch_code'];
		} elseif (strlen($branch['branch_code']) == 1) {
			$branch['branch_code'] = '00' . $branch['branch_code'];
		}
		$data['branchunit'] = $branch['city_code'] . $branch['branch_code'];

        $data['create_date'] = time();
		$data['submit_date'] = time();
		$data['site_id'] = $this->site_id;
        $data['removed'] = false;
        $data['reply_type'] = '0';
        $data['client_ip'] = $this->input->ip_address();
		
		$supervision_no = $this->sequence->getSeq("supervision");
        $data['no'] = $supervision_no;
        $result = $this->openness_request->create($data);

        if ($result) {
            //$this->session->unset_userdata('captcha_chars');
	
            $this->resultJson('恭喜，信息提交成功！', '+OK');
        } else {
            $this->resultJson('抱歉，信息提交失败！');
        }
    }
	
	 public function search() {

         $search_code = $this->security->xss_clean($this->input->post('search_code'));
		
         if(empty($search_code)){
			 $this->resultJson('请输入查询码！');
		 }
		
		 $captcha_chars = $_SESSION['captcha_chars'];
         if ( $this->input->post('vcode')&&strcasecmp($captcha_chars, $this->input->post('vcode'))) {
            $this->resultJson('验证码错误');
         }
		
		 $request_content = $this->openness_request->find(array('password' => md5($search_code)), 1, 0, array('_id'));
		 if($request_content){
			 $referer = '/opennessRequest/detail/'.$request_content['_id'].'.html';
			 $this->resultJson('查询成功，正在跳转...', '+OK', array('referer' => $referer));
		 }else{
			 $this->resultJson('查询码错误.');
		 }
     }
	// public function search() {

        // $search_code = $this->security->xss_clean($this->input->post('search_code'));
		// $vcode = $this->security->xss_clean($this->input->post(vcode));
        // if(empty($search_code)){
			// $this->resultJson('请输入查询码！');
		// }
		// if(empty($vcode)){
			// $this->resultJson('请输入验证码！');
		// }
		
		// $captcha_chars = $_SESSION['captcha_chars'];
        // if ( $this->input->post('vcode')&&strcasecmp($captcha_chars, $this->input->post('vcode'))) {
            // $this->resultJson('验证码错误');
        // }
		
		// $request_content = $this->openness_request->find(array('password' => md5($search_code)), 1, 0, array('_id'));
		// if($request_content){
			// $referer = '/opennessRequest/detail/'.$request_content['_id'].'.html';
			// $this->resultJson('查询成功，正在跳转...', '+OK', array('referer' => $referer));
		// }else{
			// $this->resultJson('查询码错误.');
		// }
    // }
	
	// public function sear() {

        // $search_code = $this->security->xss_clean($this->input->post('search_code'));
		// $vcode = $this->security->xss_clean($this->input->post(vcode));
        // if(empty($search_code)){
			// $this->resultJson('请输入查询码！');
		// }
		// if(empty($vcode)){
			// $this->resultJson('请输入验证码！');
		// }
		
		// $captcha_chars = $_SESSION['captcha_chars'];
        // if ( $this->input->post('vcode')&&strcasecmp($captcha_chars, $this->input->post('vcode'))) {
            // $this->resultJson('验证码错误');
        // }
		
		// $request_content = $this->openness_request->find(array('password' => md5($search_code)), 1, 0, array('_id'));
		// if($request_content){
			// $referer = '/opennessRequest/detail/'.$request_content['_id'].'.html';
			// $this->resultJson('查询成功，正在跳转...', '+OK', array('referer' => $referer));
		// }else{
			// $this->resultJson('查询码错误.');
		// }
    // }
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
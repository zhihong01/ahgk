<?php

class opennessAll extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_column_model', 'openness_column');
        $this->load->model('openness_topic_model', 'openness_topic');
        $this->load->model('openness_request_model', 'openness_request');
		$this->load->model('openness_request_desc_model', 'openness_request_desc');
    }

    protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
        $this->load->model('openness_content_model', 'openness_content');

        $arr_sort = array('openness_date'=>'DESC','sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $where_array['status'] = True;
        $where_array['removed'] = False;

        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'column_code', 'openness_date', 'id','topic_id','link_url');
        if($branch_id=='57a3df762c262ea9a00aae7e'){
		$item_list = $this->openness_content->findLis($branch_id, $where_array, $limit, $offset, $select, $arr_sort, $code,$keyword);			
		}else{
        $item_list = $this->openness_content->findList($branch_id, $where_array, $limit, $offset, $select, $arr_sort, $code);
        }

        foreach ($item_list as $key => $item) {
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
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item_list[$key]['date'] = $item['openness_date'];
            $item_list[$key]['url'] = $item['link_url']?$item['link_url']:"/openness/detail/content/" . $item['_id'] . '.html';
        }

        return $item_list;
    }

    protected function topicList($branch_id, $parent_id, $limit = 10, $offset = 0, $length = 60) {
        $this->load->model('openness_topic_model', 'openness_topic');

        $arr_sort = array('sort' => 'DESC');
        $where_array['status'] = True;
        $where_array['removed'] = False;

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

        $type_id = (int) $type_id;
        $arr_sort = array('sort' => 'DESC');

        $select = array('_id', 'name','website');
        $item_list = $this->site_branch->find(array('parent_id' => $parent_id, 'status' => true, 'openness_on' => true, 'removed' => False), $limit, $offset, $select, $arr_sort);


        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            }
			if($parent_id=="53fed0122f81a5843bd57125"){
				$item_list[$key]['url'] ="/opennessTowns/?branch_id=" . $item['_id'];
			}else{
				$item_list[$key]['url'] = $item['website']?$item['website']:"/opennessDepartment/?branch_id=" . $item['_id'];
			}
            
        }

        return $item_list;
    }
	
	protected function branchOpeness($parent_id, $limit = 10, $offset = 0, $length = 60, $sort_by = 0) {

        $type_id = (int) $type_id;
        $arr_sort = array('sort' => 'DESC');

        $select = array('_id', 'name','website');
        $item_list = $this->site_branch->find(array('parent_id' => $parent_id,  'openness_on' => true, 'removed' => False), $limit, $offset, $select, $arr_sort);


        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
            }else{
				$item_list[$key]['short_name'] = $item['name'];
			}
            // if (empty($item['website'])) {
                // $item_list[$key]['is_website']=true;
				// $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id'];
				// $item_list[$key]['url_guide'] = "opennessGuide/?branch_id=" . $item['_id'];
				// $item_list[$key]['url_annual_report'] = "/opennessAnnualReport/?branch_id=" . $item['_id'];
				
            // }else{
                // $item_list[$key]['url'] = $item['website'];
                // $item_list[$key]['target']="_blank";
            // }
			$item_list[$key]['is_website']=true;
			$item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id'];
			$item_list[$key]['url_guide'] = "opennessGuide/?branch_id=" . $item['_id'];
			$item_list[$key]['url_annual_report'] = "/opennessAnnualReport/?branch_id=" . $item['_id'];
        }

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
            $item_list[$key]['branch'] = $branch['name'];
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
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
            $item_list[$key]['branch'] = $branch['name'];
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
        }

        return $item_list;
    }
	
	protected function branchGuidList($request_type, $limit = 10,$length=10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'confirm_date','link_url');		
        $item_list = $this->openness_request_desc->findList(null,array('branch_id'=>(string)$request_type,'status'=>true,'removed'=>false,'site_id'=>$this->site_id));
		//echo "<pre>";var_dump($item_list);die();

        foreach ($item_list as $key => $item) {
			if($item['request_type'][0]=='2'){
            $item_des[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_des[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }
			$item_des[$key]['title'] =$item['title'];
            $item_des[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
            $item_des[$key]['url'] = !empty($item['link_url'])?$item['link_url']:"/openness/detail/request_desc/" . $item['_id'] . '.html';
			}
        }
//echo "<pre>";print_r($item_des);die();
        return $item_des;
    }

    public function index() {

        $branch_type = $this->input->get('parent_branch_id') ? addslashes($this->input->get('parent_branch_id')) : $this->gov_branch;
		$current_branch_id=$this->gov_branch;
        $current_branch_type = $this->input->get('branch_type') ? (int) addslashes($this->input->get('branch_type')) : null;
        $current_column_code = (int)$this->input->get('column_code') ? (int) addslashes($this->input->get('column_code')) : null;
        $current_topic_id = $this->input->get('topic_id') ? addslashes($this->input->get('topic_id')) : null;
		//echo $current_column_code;die;
		
		if(!empty($current_branch_id) && $current_branch_id !='all'){
			$data['branch'] = $this->site_branch->find(array('_id' => $current_branch_id));
		}
//var_dump($current_branch_id);
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

        
		$type = $this->input->get('type') ? $this->input->get('type') : null;
		$View = new Blitz('template/openness/list-content-all.html');
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
                    $item_list = $this->branchOpeness($parent_id, $limit, $offset, $length, $sort_by, $date_format);
                }
				
				if ($action == 'counterm') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterMonthList($limit, $offset);
                }elseif ($action == 'countery') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterYearList($limit, $offset);
                }
				
				if ($action == 'branchguid') {
                    list($branch_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($branch_id != 'current') {
                        $branch_id = explode('-', $branch_id);
                    } else {
                        $branch_id = $current_branch_id;
                    }

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }

                    $item_list = $this->branchGuidList($branch_id, $limit, $offset, $length, $sort_by, $date_format);
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
		
        $data['current_column_code'] = $current_column_code;
        $data['current_topic_id'] = $current_topic_id;
        $data['openness_type'] = "信息公开目录";
        $data['openness_content'] = "hover";
        $data['gov_branch_id'] = $this->gov_branch;
		$data['branch_type']=$branch_type;

		//依申请公开统计
		/* $request=array();
		$request['all']=$this->openness_request->count(array('status'=>true,'removed'=>false,'request_branch'=>$current_branch_id));//收到申请
		$request['done']=$request['all']-$this->openness_request->count(array('reply_type'=>0,'status'=>true,'removed'=>false,'request_branch'=>$current_branch_id));//已经办理
		$request['type0']=$this->openness_request->count(array('reply_type'=>0,'status'=>true,'removed'=>false,'request_branch'=>$current_branch_id));//尚未办理
		$request['type1']=$this->openness_request->count(array('reply_type'=>1,'status'=>true,'removed'=>false,'request_branch'=>$current_branch_id));//同意公开
		$request['type2']=$this->openness_request->count(array('reply_type'=>2,'status'=>true,'removed'=>false,'request_branch'=>$current_branch_id));//同意部分公开
		$request['type3']=$this->openness_request->count(array('reply_type'=>3,'status'=>true,'removed'=>false,'request_branch'=>$current_branch_id));//信息不存在
		$request['type4']=$this->openness_request->count(array('reply_type'=>4,'status'=>true,'removed'=>false,'request_branch'=>$current_branch_id));//非本部门掌握
		$request['type5']=$this->openness_request->count(array('reply_type'=>5,'status'=>true,'removed'=>false,'request_branch'=>$current_branch_id));//申请信息不明确
		$data['request']=$request; */
		
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
		
        $View->display($data);
    }

}

?>
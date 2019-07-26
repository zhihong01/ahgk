<?php

//行政职权及流程图
class opennessFlow extends MY_Controller {

    public function __construct() {
        parent::__construct();
		$this->load->model('site_branch_model', 'site_branch');
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_column_model', 'openness_column');
        $this->load->model('openness_topic_model', 'openness_topic');
        $this->load->model('openness_request_model', 'openness_request');
    }
	
	

    protected function branchList($parent_id, $limit = 10, $offset = 0, $length = 60, $sort_by = 0) {
        $arr_sort = array('sort' => 'desc');
        $select = array('_id', 'name','full_name');
        $item_list = $this->site_branch->find(array('parent_id' => $parent_id, 'status' => True,'openness_on' => true, 'removed' => False), 150, 0, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
            }else{
				$item_list[$key]['short_name'] = $item['name'];
			}
			if (mb_strlen($item['full_name']) > $length) {
                $item_list[$key]['full_name'] = mb_substr($item['full_name'], 0, $length) . '...';
            }else{
				$item_list[$key]['full_name'] = $item['full_name'];
			}
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id'];
            $item_list[$key]['url_flow'] = "/opennessContent/?branch_id=" . $item['_id'] . "&column_code=50000";
            $item_list[$key]['url_guide'] = "/opennessGuide/?branch_id=" . $item['_id'];
            $item_list[$key]['url_annual_report'] = "/opennessAnnualReport/?branch_id=" . $item['_id'];
			//$has_shenpi = $this->openness_column->find(array('code' => 50200, 'branch_id' => (string)$item['_id'],'status' => True,'removed' => False));
			$has_shenpi = $this->openness_column->find(array('name' => '行政审批', 'branch_id' => (string)$item['_id'],'status' => True,'removed' => False));
			if(!empty($has_shenpi)||$item_list[$key]['name']=='市统计局'||$item_list[$key]['name']=='市农业委员会'||$item_list[$key]['name']=='市科技局'||$item_list[$key]['name']=='市住房公积金管理中心')
			{
				if($item_list[$key]['name']=='市公安局'||$item_list[$key]['name']=='市政务服务中心'||$item_list[$key]['name']=='市林业局'||$item_list[$key]['name']=='阜阳银监分局'||$item_list[$key]['name']=='市烟草专卖局'||$item_list[$key]['name']=='阜阳海关'||$item_list[$key]['name']=='阜阳民航局'){
				$item_list[$key]['shenpi_name']="无行政审批";
				$item_list[$key]['url_shenpi']="";
				} elseif($item_list[$key]['name']=='市国税局'){
					$item_list[$key]['shenpi_name']="服务指南";
					$item_list[$key]['url_shenpi']="href='/opennessContent/?branch_id=" . $item['_id'] . "&column_code=50213'";
				} elseif($item_list[$key]['name']=='市人力资源和社会保障局'){
					$item_list[$key]['shenpi_name']="服务指南";
					$item_list[$key]['url_shenpi']="href='/opennessContent/?branch_id=" . $item['_id'] . "&column_code=60002'";
				} elseif($item_list[$key]['name']=='市农业委员会'){
					$item_list[$key]['shenpi_name']="服务指南";
					$item_list[$key]['url_shenpi']="href='/opennessContent/?branch_id=" . $item['_id'] . "&column_code=50105'";
				} else{
				$item_list[$key]['shenpi_name']="服务指南";
				$item_list[$key]['url_shenpi']="href='/opennessContent/?branch_id=" . $item['_id'] . "&column_code=50205'";
				}
			}
			else{
				$item_list[$key]['shenpi_name']="无行政审批";
				$item_list[$key]['url_shenpi']="";
			}
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
	
	protected function counterMonthList($limit = 10, $offset = 0,$length = 8) {
        $this->load->model('openness_counter_month_model', 'openness_counter_month');
        $this->load->model('site_branch_model', 'site_branch');
        $arr_sort = array('value.total' => 'DESC');

        $select = array('_id', 'value');
        $item_list_all = $this->openness_counter_month->find(array('_id.site_id' => $this->site_id,'_id.report_month'=>date('Y-m',strtotime("-1 month"))), $limit, $offset, $select, $arr_sort);
	
        foreach ($item_list_all as $key => $item) {
             $branch = $this->site_branch->find(array('_id' => $item['_id']['branch_id']));
             $item_list[$key]['branch'] = $branch['name'];
			if (mb_strlen($branch['name']) > $length) {
				$item_list[$key]['short_branch'] = mb_substr($branch['name'],0,$length). '...';
			}else{
				 $item_list[$key]['short_branch'] = $branch['name'];
			}
			$item_list[$key]['i'] = $key+1;
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
        }

        return $item_list;
    }
	
	protected function counterYearList($limit = 10, $offset = 0,$length = 8) {
        $this->load->model('openness_counter_year_model', 'openness_year_month');
        $this->load->model('site_branch_model', 'site_branch');
        $arr_sort = array('value.total' => 'DESC');

        $select = array('_id', 'value');
        $item_list_all = $this->openness_year_month->find(array('_id.site_id' => $this->site_id), $limit, $offset, $select, $arr_sort);
        foreach ($item_list_all as $key => $item) {
            $branch = $this->site_branch->find(array('_id' => $item['_id']['branch_id']));
			$item_list[$key]['branch'] = $branch['name'];
			if (mb_strlen($branch['name']) > $length) {
				$item_list[$key]['short_branch'] = mb_substr($branch['name'],0,$length). '...';
			}else{
				 $item_list[$key]['short_branch'] = $branch['name'];
			}
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
        }

        return $item_list;
    }

    protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
        $this->load->model('openness_content_model', 'openness_content');

        $arr_sort = array('sort' => 'DESC');
        $where_array['status'] = True;
        $where_array['removed'] = False;

        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'column_code', 'openness_date', 'id');

        $item_list = $this->openness_content->findList($branch_id, $where_array, $limit, $offset, $select, $arr_sort, $code);


        foreach ($item_list as $key => $item) {
            if ($item['branch_id']) {
                $this_branch = $this->site_branch->find(array('_id' => $item['branch_id']));
                $item_list[$key]['branch'] = $this_branch['name'];
            }
            if ($item['column_code'] && $item['branch_id']) {
                $this_column = $this->openness_column->find(array('code' => (string) $item['column_code'], 'branch_id' => $item['branch_id']));
                $item_list[$key]['column'] = $this_column['name'];
            }
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            }else{
				$item_list[$key]['short_title'] = $item['title'];
			}

            $item_list[$key]['date'] = $date_format==0?substr($item['openness_date'],5,5):$item['openness_date'];
            $item_list[$key]['url'] = "/openness/detail/content/" . $item['_id'] . '.html';
        }

        return $item_list;
    }	

    public function index() {
		$current_branch_id=$gov_branch = $this->gov_branch;
        $View = new Blitz('template/openness/openness-list-flow.html');
        $struct_list = $View->getStruct();
		$data = array(
            'current_branch_id'=> $current_branch_id,
            'siteurl' => $this->vals['setting']['site_url'],
            'folder_prefix' => $this->folder_prefix,
			'topic1' => $this->vals['topic1'],
			'topic2' => $this->vals['topic2'],
			'topic3' => $this->vals['topic3'],
			'topic4' => $this->vals['topic4'],
			'topic5' => $this->vals['topic5'],
			'location' =>'行政职权目录及流程图',
        );
        $item_list = '';
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                //获取部门列表
                if ($action == 'branch') {
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->branchList($parent_id, $limit, $offset, $length, $sort_by, $date_format);
                }elseif ($action == 'counterm') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterMonthList($limit, $offset);
                }elseif ($action == 'countery') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterYearList($limit, $offset);
                }
                $data[$struct_val] = $item_list;
            }
        }

		
        $View->display($data);
    }
	
	public function content() {

        $current_branch_id = $this->input->get('branch_id') ? addslashes($this->input->get('branch_id')) : $this->gov_branch;
        $current_branch_type = $this->input->get('branch_type') ? (int) addslashes($this->input->get('branch_type')) : null;
        $current_column_code = (int)$this->input->get('column_code') ? (int) addslashes($this->input->get('column_code')) : null;
        $current_topic_id = $this->input->get('topic_id') ? addslashes($this->input->get('topic_id')) : null;
		
		$current_cljg = $this->input->get('cljg') ? $this->input->get('cljg') : null;
		$current_tag = $this->input->get('tag') ? $this->input->get('tag') : null;
		
		if(!empty($current_branch_id) && $current_branch_id !='all'){
			$data['branch'] = $this->site_branch->find(array('_id' => $current_branch_id));
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
		
		if ($current_branch_type) {
            $where_array['branch_type']= $current_branch_type;
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
		$View = new Blitz('template/openness/openness-list-content.html');
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
				
				if ($action == 'counterm') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterMonthList($limit, $offset);
                }elseif ($action == 'countery') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterYearList($limit, $offset);
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

        $data['is_column'] = $data['column']['name'] ? $data['column']['name'] : $current_cljg;
        $data['current_branch_id'] = $current_branch_id;
		
        $data['current_column_code'] = $current_column_code;
        $data['current_topic_id'] = $current_topic_id;
		$data['cljg'] = $current_cljg;
		$data['tag'] = $current_tag;
        $data['openness_type'] = "信息公开目录";
        $data['openness_content'] = "hover";
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
		
        $View->display($data);
    }

}

?>
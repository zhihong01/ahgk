<?php

class opennessRight extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
		     $this->load->model('openness_request_model', 'openness_request');
    }

	
	  protected function requestList($branch_id, $limit = 10, $offset = 0, $length = 50, $date_format = 0) {
        $where_array = array('site_id' => $this->site_id, 'status' => True, 'removed' => false);
        $select = array('_id', 'name', 'create_date', 'request_branch', 'content', 'as_type', 'unit_contact', 'reply_type');
        $date_format = $this->date_foramt[$date_format];
        $arr_sort = array('create_date' => 'DESC');
        $item_list = $this->openness_request->find($where_array, $limit, $offset, $select, $arr_sort);
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
        //  print_r($item_list);die;
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
        $item_list = $this->site_branch->find(array('parent_id' => $parent_id, 'openness_on' => true, 'removed' => False), null, $offset, $select, $arr_sort);


        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            }
			$item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id'];
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

    public function index() {

        $parent_branch_id = $this->input->get('parent_branch_id');

        $data['branch'] = $this->site_branch->find(array('_id' => $parent_branch_id));

		$View = new Blitz('template/openness/list-right.html');
        
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');

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
                    if ($parent_id != 'current') {
                        $parent_id = explode('-', $parent_id);
                    } else {
                        $parent_id = $parent_branch_id;
                    }
                    $item_list = $this->branchList($parent_id, $limit, $offset, $length, $sort_by, $date_format);
                } if ($action == 'request') {  //依申请公开列表
                    list($branch_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $branch_id = $current_branch_id;
                    $item_list = $this->requestList($branch_id, $limit, $offset, $length, $sort_by, $date_format);
                }
				
				if ($action == 'counterm') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterMonthList($limit, $offset);
                }elseif ($action == 'countery') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterYearList($limit, $offset);
                }

                $data[$struct_val] = $item_list;
            }
        }


        $data['type_name'] = $type_name;
        $data['current_branch_id'] = $current_branch_id;
		$data['openness_branch']="hover";
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
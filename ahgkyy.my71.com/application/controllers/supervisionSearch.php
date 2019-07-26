<?php

class supervisionSearch extends MY_Controller {

    public function __construct() {

        parent::__construct();
        $this->load->model('supervision_model', 'supervision');
		$this->question_list = $this->questionList();
    }

    protected function mailSearchList($keywords = NULL, $search_where = array(), $limit = 10, $offset = 0, $length = 0, $date_format = 0) {
		$this->branch_list=$this->getBranchName();
        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'subject', 'no','process_status','branch_id', 'create_date','question_id', 'hit');
		
        $item_list = $this->supervision->findLists($keywords, $search_where, $limit, $offset, $select, $arr_sort,$question_id);
		//print_r($item_list);die();
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['subject']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['subject'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['subject'];
            }
			 // 留言的状态
            if (isset($this->supervision_status[$item['process_status']])) {
                $item_list[$key]['process_status'] = $this->supervision_status[$item['process_status']];
            } else {
                $item_list[$key]['process_status'] = $this->supervision_status[0];
            }

			if (isset($this->question_list[$item['question_id']])) {
                $item_list[$key]['question_name'] = $this->question_list[$item['question_id']];
            } else {
			$item_list[$key]['question_name'] = $this->question_list[0];
            }
            // 取部门
            if (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['branch_id']];
            } else {
                $item_list[$key]['branch'] = '';
            }
            $item_list[$key]['title'] = $item['subject'];
            $item_list[$key]['url'] = "/supervision/detail/" . $item['_id'] . '.html';
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
        }
        return $item_list;
    }
	
	 // 部门列表
    protected function itemBranch($channel_id, $limit = 20, $offset = 0, $length = 10, $current_id = '') {

        $this->load->model('site_branch_model', 'site_branch');

        $filter = array('parent_id' => $channel_id, 'status' => true, 'supervision_on' => true, 'removed' => False);

        $select = array('_id', 'name', 'id');
        $arr_sort = array('sort' => 'desc');

        $item_list = $this->site_branch->find($filter, $limit, $offset, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            if ($item['_id'] == $current_id) {
                $item_list[$key]['aon'] = 'class="aon"';
                $item_list[$key]['selected'] = 'selected';
            } else {
                $item_list[$key]['aon'] = '';
            }
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length);
            } else {
                $item_list[$key]['short_name'] = $item['name'];
            }
            $item_list[$key]['url'] = '/supervision/branch/' . $item['_id'] . '/';
        }
        return $item_list;
    }

	 protected function getMenu($channel_id, $limit = 50, $offset = 0, $length = 60) {
        $result = array();
        $this->load->model("site_channel_tree_model", "site_channel_tree");
        $channel_tree = $this->site_channel_tree->find(array('_id' => $channel_id), 1);

        if (isset($channel_tree['child'])) {
            $i = 0;
            foreach ($channel_tree['child'] as $key => $value) {
                if ($i >= $limit) {
                    break;
                }
                if ($i < $offset) {
                    continue;
                }
                $result[$key] = mb_substr($value, 0, $length);
                $i++;
            }
        }
        return $result;
    }

	protected function itemQuestion() {
        $this->load->model('supervision_question_model', 'supervision_question');
        $item_list = $this->supervision_question->find(array('status'=>true,'removed' => false, 'site_id' => $this->site_id), null, NULL, "*", array("create_date" => "DESC"));
        return $item_list;
    }
	
    public function index() {
        $keywords = (string) $this->security->xss_clean($this->input->get('keywords'));
        $question_id = (string) $this->security->xss_clean($this->input->get('question_id'));
        //$branch_id = (string) $this->security->xss_clean($this->input->get('branch_id'));
        $process_status = (int) $this->security->xss_clean($this->input->get('process_status'));
		//var_dump($process_status);
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $search_where = array('question_id' => $question_id, 'process_status' => $process_status, 'share_on' => true, 'cancelled' => false, 'removed' => false,'status'=>true, 'site_id' => $this->site_id);
        if (mb_substr($keywords, 0, 1) == "2" && strlen($keywords) == 12) {
            $search_where['no'] = $keywords;
            $keywords = NULL;
        }
        $total_row = $this->supervision->listCount($keywords, $search_where);
        $View = new Blitz('template/supervision/list-supervision.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';
                //列表
                if ($action == 'supervision') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
					//print_r($item_list);die('dd');
                    $item_list = $this->mailSearchList($keywords, $search_where, $limit, $offset, $length, $date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
                }
				if ($action == 'question') {
                    $item_list = $this->itemQuestion();
                }
				if ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $current_id = $parent_id;
                    }
                    $menu_list = $this->getMenu($current_id, $limit, $offset, $length);
                    $s = 0;
                    foreach ($menu_list as $key => $menu) {
                        $item_list[$s]['_id'] = $key;
                        $item_list[$s]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$s]['name'] = $menu;
                        $s++;
                    }
                }
				
				if ($action == 'branch') {
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->itemBranch($channel_id, $limit, $offset, $length);
                } 
                $data[$struct_val] = $item_list;
            }
        }
		$data['channel_name'] = '政民互动';
        $data['keywords'] = $keywords;
        $data['total_row'] = $total_row;
		$data['location']='<a href="/">网站首页 > <a href="/interaction/">政民互动</a> > 搜索列表';
        $View->display($data);
    }

}

?>
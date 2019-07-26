<?php

class mobileOpenness extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_column_model', 'openness_column');
        $this->load->model('openness_topic_model', 'openness_topic');
    }

    protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
        $this->load->model('openness_content_model', 'openness_content');

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $where_array['status'] = True;
        $where_array['removed'] = False;

        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'column_code', 'openness_date', 'id','topic_id');

        $item_list = $this->openness_content->findList($branch_id, $where_array, $limit, $offset, $select, $arr_sort, $code);


        foreach ($item_list as $key => $item) {

            if ($item['column_id']) {
                $this_column = $this->openness_column->find(array('_id' =>$item['column_id']));
                $item_list[$key]['column'] = $this_column['name'];
            }

			
            $item_list[$key]['_id'] = (string) ($item['_id']);
			
			if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item_list[$key]['date'] = $item['openness_date'];
            $item_list[$key]['url'] = "/mobileOpenness/detail/content/" . $item['_id'] . '.html';
        }

        return $item_list;
    }

	
	public function index() {
        $View = new Blitz('template/mobile/list-openness.html');
		$data=array();
		$View->display($data);
	}
	
    public function content() {

        $current_branch_id = $this->input->get('branch_id') ? $this->input->get('branch_id') : $this->gov_branch;
        
        $current_column_code = $this->input->get('column_code') ? (int) $this->input->get('column_code') : null;

        $page = (int) $this->input->get('page');

        if ($page == 0) {
            $page = 1;
        }

        $item_list = array();
        if ($current_column_code) {
            $data['column'] = $this->openness_column->find(array('code' => (int) $current_column_code, 'branch_id' => $current_branch_id));
        }


        $where_array['status'] = True;
        $where_array['removed'] = False;
        $total_row = $this->openness_content->listCount($current_branch_id, $where_array, $current_column_code);


        $View = new Blitz('template/mobile/list.html');
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
		$data['current_topic_id'] = $current_topic_id;
        $data['current_column_code'] = $current_column_code;
        $data['openness_type'] = "信息公开目录";
        $data['openness_content'] = "hover";

		$data['location']='<a href="/">网站首页</a> / <a href="/mobileOpenness/">信息公开</a> / <a >'.$data['is_column'].'</a>';

        $View->display($data);
    }
	
	public function detail() {

        $this->load->model('openness_rules_model', 'openness_rules');
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_annual_report_model', 'openness_annual_report');
        $this->load->model('openness_topic_model', 'openness_topic');
        $this->load->model('openness_column_model', 'openness_column');
        $this->load->model('openness_request_dir_model', 'openness_request_dir');


        $_id = $this->input->get('_id');
        $type = 'openness_' . $this->input->get('type');

        $View = new Blitz('template/mobile/detail.html');
        $struct_list = $View->getStruct();
        $openness = $this->$type->find(array('_id' => $_id, 'status' => true, 'removed' => false), 1);
        if (empty($openness)) {
            show_404();
        }


        $current_branch = $this->site_branch->find(array('_id' => $openness['branch_id']), 1, 0);
        $openness['branch'] = $current_branch['name'];
        if (!empty($openness['column_code'])) {
            $current_column = $this->openness_column->find(array('code' => (int) $openness['column_code'], 'branch_id' => $openness['branch_id']));
            $openness['column'] = $current_column['name'];
        }
        if (!empty($openness['topic_id'])) {
            if (is_array($openness['topic_id'])) {
                $openness['topic'] = '';
                foreach ($openness['topic_id'] as $val) {
                    $current_topic = $this->openness_topic->find(array('_id' => (string) $val), 1, 0);
                    $openness['topic'] = !empty($current_topic) ? $current_topic['name'] . "&nbsp;&nbsp;" . $openness['topic'] : '';
                }
            }
        }

        $openness['title'] = !empty($openness['title']) ? $openness['title'] : $openness['name'];
        $openness['date'] = !empty($openness['openness_date']) ? $openness['openness_date'] : date('Y-m-d', $openness['openness_date']);


        $is_content = $type == 'openness_content' ? 1 : null;


        if ($openness['tag']) {
            foreach ($openness['tag'] as $val) {
                $openness['tags'] = $openness['tags'] . $val . "&nbsp;&nbsp;";
            }
        }


        $data = array(
            'content' => $openness,
            'is_content' => $is_content,
            'folder_prefix' => $this->folder_prefix,
            'location' => '<a href="/">网站首页</a> / <a href="/mobileOpenness/">信息公开</a> / <a >信息浏览</a>',
        );


        //print_r($data);die();
		$data['_id']=$_id;
        $View->display($data);
    }

}

?>
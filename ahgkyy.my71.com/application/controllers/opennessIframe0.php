<?php

class opennessIframe extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_column_model', 'openness_column');
    }

    protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
        $this->load->model('openness_content_model', 'openness_content');

        $arr_sort = array('sort' => 'DESC', 'openness_date' => 'DESC', 'create_date' => 'DESC');
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
				$item_list[$key]['short_title']=$item['title'];
			}
			if($date_format=='1'){
				$item_list[$key]['date'] = substr($item['openness_date'],5,6);
			}else{
				$item_list[$key]['date'] = $item['openness_date'];
			}
            
            $item_list[$key]['url'] = "/openness/detail/content/" . $item['_id'] . '.html';
        }

        return $item_list;
    }

    public function index() {

        $current_branch_id = $this->input->get('branch_id') ? $this->input->get('branch_id') : $this->gov_branch;
        $current_column_code = $this->input->get('column_code') ? (int) $this->input->get('column_code') : null;
        $current_topic_id = $this->input->get('topic_id') ? $this->input->get('topic_id') : null;
        $style = $this->input->get('style') ? $this->input->get('style') : null;
		
		$limit=$this->input->get('count') ? $this->input->get('count') : 10;
		$length=$this->input->get('length') ? $this->input->get('length') : 20;
		$date_format=$this->input->get('date') ? $this->input->get('date') : 0;


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
            $data['branch'] = '';
        }
		
		if($style==1){
			$View = new Blitz('template/openness-iframe-style1.html');
		}else{
			$View = new Blitz('template/openness-iframe.html');
		}
        
		
		$item_list = $this->opennessList($current_branch_id, $where_array, $limit, 0, $length, $date_format, $current_column_code);
		$data['list'] = $item_list;
        $View->display($data);
    }

}

?>
<?php

class opennessService extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_column_model', 'openness_column');
        $this->load->model('openness_topic_model', 'openness_topic');
		$this->load->model('service_content_model', 'service_content');
        $this->load->model('service_type_model', 'service_type');
        $this->load->model('service_download_model', 'service_download');
        $this->load->model('service_policy_model', 'service_policy');
    }

    

    protected function topicList($branch_id, $parent_id, $limit = 10, $offset = 0, $length = 60) {
        $this->load->model('openness_topic_model', 'openness_topic');

        $arr_sort = array('sort' => 'ASC');
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
	
	protected function serviceList($_id = '', $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
		
		$filter = array('type' => (string)$_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
		$select = array('_id', 'title', 'branch_id', 'confirm_date', 'download', 'policy','link_url','down_ids');
		$arr_sort = array('sort' => 'DESC');
		$date_format = $this->date_foramt[$date_format];

        $item_list = $this->service_content->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
			$item_list[$key]['url'] = $item['link_url']?$item['link_url']:'/service/contentDetail/'.$item['_id'].'.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            }else{
				$item_list[$key]['short_title'] = $item['title'];
			}
			
            if(!empty($item['download'])){
				$item_list[$key]['download'] = '<a href="/service/contentDetail/'.$item['_id'] . '.html#download" target="_blank" class="bmtype">表格下载</a>';
			}else{
				$item_list[$key]['download'] = '';
			}
			if(!empty($item['policy'])){
				$item_list[$key]['policy'] = '<a href="/service/contentDetail/'.$item['_id'] . '.html#policy" target="_blank" class="bmtype">相关政策<a/>';
			}else{
				$item_list[$key]['policy'] = '';
			}
        }
        return $item_list;
    }

    // 相关政策
    protected function policyList($_id = '', $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0, $type = 's') {

        $filter = array('service_type' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
		$select = array('_id', 'title', 'branch_id', 'confirm_date');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        
        $item_list = $this->service_policy->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
			$item_list[$key]['url'] = '/service/policyDetail/'.$item['_id'].'.html';
			$item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            }else{
				$item_list[$key]['short_title'] = $item['title'];
			}
            $item_list[$key]['download'] = '';
            $item_list[$key]['policy'] = '';
        }
        return $item_list;
    }

    // 资料下载
    protected function downloadList($_id ='', $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0, $type = 's') {

        $filter = array('service_type' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
		$select = array('_id', 'title', 'branch_id', 'confirm_date');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        
        $item_list = $this->service_download->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
			$item_list[$key]['url'] = '/service/downloadDetail/'.$item['_id'].'.html';
			$item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            }else{
				$item_list[$key]['short_title'] = $item['title'];
			}
            $item_list[$key]['download'] = '';
            $item_list[$key]['policy'] = '';
        }
        return $item_list;
    }

    protected function serviceTypeList($channel_id, $limit = 20, $offset = 0, $length = 60, $sort_by = 0, $current_id = '') {

        $arr_sort = array($this->sort_by[$sort_by] => 'ASC');
        $select = array('_id', 'name', 'linkurl');
        $item_list = $this->service_type->find(array('parent_id' => $channel_id,'removed'=>false), $limit, $offset, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            if ($item['_id'] == $current_id) {
                $item_list[$key]['aon'] = 'class="aon"';
            } else {
                $item_list[$key]['aon'] = '';
            }
            if (empty($item['linkurl'])) {
                $item_list[$key]['url'] = '/opennessService/?_id=' . $item['_id'];
            } else {
                $item_list[$key]['url'] = $item['linkurl'];
            }
        }
        return $item_list;
    }

    public function index() {

        $service_id = $this->input->get('_id') ? (string) $this->input->get('_id') : '';
        $type = $this->input->get('type');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }

        if ($service_id) {
            $current_service = $this->service_type->find(array("_id" => $service_id), 1, 0, array('name'));
        } else {
            $current_service['name'] = '所有类别';
        }


        if ($type == 'policy') {
            $total_row = $this->service_policy->count(array('service_type' => $service_id, 'status' => true, 'removed' => False));
            $data['policy'] = array('aon' => 'aon');
        } else if ($type == 'download') {
            $total_row = $this->service_download->count(array('service_type' => $service_id, 'status' => true, 'removed' => False));
            $data['download'] = array('aon' => 'aon');
        } else {
            $where_array = array('status' => true, 'removed' => false);
            if (!empty($service_id)) {
                $where_array['type'] = $service_id;
            }
            $total_row = $this->service_content->count($where_array);
            $data['list'] = array('aon' => 'aon');
        }

        $View = new Blitz('template/openness-service.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //获取信息公开专题列表
                if ($action == 'topic') {
                    list($branch_id, $parent_id, $limit, $offset, $length, $sort_by) = explode('_', $matches[2]);
                    if ($branch_id != 'current') {
                        $branch_id = explode('-', $channel_id);
                    } else {
                        $branch_id = (array) $current_branch_id;
                    }
                    $parent_id = explode('-', $parent_id);
                    $item_list = $this->topicList($branch_id, $parent_id, $limit, $offset, $length, $sort_by);
                }

                // 信息统计部门列表
                if ($action == 'counter') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterList($limit, $offset);
                }

                if ($action == 'type') {
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);

                    $_id_list = $channel_id;

                    $item_list = $this->serviceTypeList($_id_list, $limit, $offset, $length, $sort_by, $service_id);
                }

                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    $_id_list = $service_id;

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }

                    $item_list = $this->serviceList($_id_list, $limit, $offset, $length, $sort_by, $date_format);
                }

                if ($action == 'policy') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    $_id_list = $service_id;

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }

                    $item_list = $this->policyList($_id_list, $limit, $offset, $length, $sort_by, $date_format);
                }

                if ($action == 'download') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    $_id_list = $service_id;

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }

                    $item_list = $this->downloadList($_id_list, $limit, $offset, $length, $sort_by, $date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
					
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
					
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['list_url'] = '/opennessService/?_id=' . $service_id . '&type=work';
        $data['download_url'] = '/opennesSservice/?_id=' . $service_id . '&type=download';
        $data['policy_url'] = '/opennessService/?_id=' . $service_id . '&type=policy';
        $data['location'] = "<a href='/'>首页</a> > 公共企事业单位办事公开 > " . $current_service['name'];

        $View->display($data);
    }

}

?>
<?php

class serviceScene extends MY_Controller {

    public function __construct() {

        parent::__construct();
        $this->load->model("service_type_model", "service_type");
        $this->load->model("service_content_model", "service_content");
    }

    // 服务类型
    protected function itemServiceType($_id, $limit = 50, $offset = 0, $length = 60, $type_id = null) {

        $this->load->model('service_type_model', 'service_type');

        $filter = array('parent_id' => $_id, 'removed' => false,'status'=>true, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');

        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);

            $services = $this->service_type->find(array('parent_id' => (string) $item['_id'], 'removed' => false, 'site_id' => $this->site_id));
            if (empty($services)) {
                $item_list[$key]['url'] = '/serviceScene/content/?type=' . $type_id . '&_id=' . $item['_id'];
            } else {
                $item_list[$key]['url'] = '/serviceScene/?type=' . $type_id . '&_id=' . $item['_id'];
            }
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_name'] = $item['name'];
            }
        }
        return $item_list;
    }

    // 服务指南
    protected function itemServiceContent($_id, $limit = 50, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $this->load->model('service_content_model', 'service_content');

        $filter = array('type' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'branch_id', 'confirm_date', 'download', 'policy');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->service_content->find($filter, $limit, $offset, $select, $arr_sort);
        
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/service/contentDetail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            // 取部门
            if (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['branch_id']];
                $item_list[$key]['branch_url'] = '/supervision/branch/?_id=' . $item['branch_id'];
            } else {
                $item_list[$key]['branch'] = '';
            }
            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }
	
	// 服务类型
    protected function serviceTypeList($parent_id, $limit = 50, $offset = 0, $length = 60, $category = "") {

        $this->load->model('service_type_model', 'service_type');

        $filter = array('parent_id' => $parent_id, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');

        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);

        if (empty($item_list)) {
            $item_list = $this->service_type->find(array('_id' => $parent_id, 'removed' => false, 'site_id' => $this->site_id), $limit, $offset, $select, $sort);
        }

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['parent_id'] = $parent_id;
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_name'] = $item['name'];
            }

            $child_service = $this->service_type->find(array('parent_id' => (string) $item['_id'], 'removed' => false, 'site_id' => $this->site_id), null, 0);

            if (!empty($child_service)) {
                foreach ($child_service as $k => $v) {
                    $child_service[$k]['url'] = "/serviceScene/?category=" . $category . "&type=" . $v['_id'];
                    $child_service[$k]['title'] = $v['name'];
                }
                $item_list[$key]['list'] = $child_service;
            } else {
                $service_content = $this->service_content->find(array('type' => (string) $item['_id'], 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0);
                foreach ($service_content as $k => $v) {
                    $service_content[$k]['url'] = "/serviceScene/?m=detail&category=" . $category . "&_id=" . $v['_id'];
                    $service_content[$k]['title'] = $v['title'];
                }
                $item_list[$key]['list'] = $service_content;
            }
        }
        //print_r($item_list);die();
        return $item_list;
    }


    public function index() {
        $type_id = (string) $this->input->get('type');
        $service_id = (string) $this->input->get('_id');
		if(empty($type_id)){
			$type_id='58425dc067299a56258f426d';//户籍服务
		 }
		
	
        $page = (int) $this->input->get('page');
        if (empty($service_id)) {
            $service_id = $type_id;
        }
        $current_service = $this->service_type->find(array('_id' => $service_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('name'));
        $total_row = $this->service_content->count(array('type' => $service_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        if (empty($current_service)) {
            show_error("服务类型不存在");
        }

        $View = new Blitz('template/service/scene.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                
                if ($action == 'list') {
                    list($_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($_id == 'current') {
                        $_id = $service_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemServiceContent($_id, $limit, $offset, $length);
                } else if ($action == 'type') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->serviceTypeList($type_id, $limit, $offset, $length, $category);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['type_id'] = $type_id;
		$data['scene_name'] = $current_service['name'];
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上服务</a> /  <span>' . $current_service['name'] . '</span>';
		
		 // $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上服务</a> / <a href="/serviceScene/?type=5842587667299a5a0a95639f">场景式服务</a> / <span>' . $current_service['name'] . '</span>';
        $View->display($data);
    }

    /*
     *  场景式服务的内容列表
     */

    public function content() {
        $type_id = (string) $this->input->get('type');
        $service_id = (string) $this->input->get('_id');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $current_service = $this->service_type->find(array('_id' => $service_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('name'));
        if (empty($current_service)) {
            show_404();
        }

        $total_row = $this->service_content->count(array('type' => $service_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        $View = new Blitz('template/cjfw.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($_id == 'current') {
                        $_id = $service_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemServiceContent($_id, $limit, $offset, $length);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['type_id'] = $type_id;
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a> / <a href="/serviceScene/?type=5842587667299a5a0a95639f">场景式服务</a> / <span>' . $current_service['name'] . '</span>';

        $View->display($data);
    }
	
	public function guide(){
		$View = new Blitz('template/service/guide.html');
		$View->display();
	}
	
	//ajax方式获取服务或者内容
    public function getHtml() {
        $_id = (string) $this->input->get("_id");
        $service = $this->service_type->find(array('_id' => $_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('name', 'parent_id'));
        $tree = $this->getTree($_id);
        $service_child = $this->service_type->find(array('parent_id' => $_id, 'removed' => false, 'site_id' => $this->site_id), NULL, 0, array("_id", "name"), array("sort" => "DESC"));
        if (empty($service_child)) {
            $View = new Blitz('template/service/getContentHtml.html');
            $struct_list = $View->getStruct();
            foreach ($struct_list as $struct) {
                $matches = array();
                if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                    $action = $matches[1];
                    $struct_val = trim($matches[0], '/');
                    $item_list = '';
                    if ($action == 'content') {
                        list($service_id, $limit, $offset, $length) = explode('_', $matches[2]);
                        if ($service_id == 'current') {
                            $service_id = $_id;
                        }
                        $item_list = $this->itemServiceContent($service_id, $limit, $offset, $length);
                    }
                    $data[$struct_val] = $item_list;
                }
            }
            $data['tree'] = $tree;
            $data['scene_name'] = $service['name'];
            $View->display($data);
        } else {
            $View = new Blitz('template/service/getTypeHtml.html');
            $struct_list = $View->getStruct();
            foreach ($struct_list as $struct) {
                $matches = array();
                if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                    $action = $matches[1];
                    $struct_val = trim($matches[0], '/');
                    $item_list = '';
                    //栏目列表
                    if ($action == 'type') {
                        list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                        $item_list = $this->serviceTypeList($_id, $limit, $offset, $length, $category);
                    }
                    $data[$struct_val] = $item_list;
                }
            }
            $data['tree'] = $tree;
            $data['scene_name'] = $service['name'];
            $View->display($data);
        }
    }

    protected function getTree($_id = NULL, $tree = -1) {
        $tree++;
        if (empty($_id)) {
            return $tree;
        }
        $service = $this->service_type->find(array('_id' => $_id), 1, 0, array('parent_id'));
        if (empty($service)) {
            return $tree;
        }
        if ($service['parent_id'] == "/") {
            return $tree;
        }
        return $this->getTree($service['parent_id'], $tree);
    }

}

?>
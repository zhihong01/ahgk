<?php

/*
 *  主题服务（百件实事网上办）
 *  
 *  ..因为是都是栏目列表，所以没有做分页
 */

class serviceSubject extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('service_type_model', 'service_type');
        $this->load->model('service_content_model', 'service_content');
    }

    // 服务类型
    protected function itemServiceType($_id, $limit = 50, $offset = 0, $length = 60) {

        $this->load->model('service_type_model', 'service_type');

        $filter = array('parent_id' => $_id, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');

        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/serviceSubject/content/?_id=' . $item['_id'];
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

    public function index() {
        $service_id = (string) $this->input->get('_id');
        // 当前栏目
        $current_service = $this->service_type->find(array('_id' => $service_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('name', 'parent_id'));
        $View = new Blitz('template/service-subject.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //左侧大菜单
                if ($action == 'type') {
                    list($_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($_id == 'current') {
                        $_id = $service_id;
                    }
                    $item_list = $this->itemServiceType($_id, $limit, $offset, $length);
                    foreach ($item_list as $key => $item) {
                        $item_list[$key]['url'] = '/serviceSubject/' . $item['_id'] . '/';
                    }
                } elseif ($action == 'list') {
                    list($_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($_id == 'current') {
                        $_id = $service_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemServiceType($_id, $limit, $offset, $length);
                    foreach ($item_list as $key => $item) {
                        $item_list[$key]['child'] = $this->itemServiceType($item['_id'], null, 0, 15);
                    }
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
        $data['serviceName'] = $current_service['name'];
        $data['location'] = '<a href="/">网站首页</a>/<a href="/service/">在线服务</a>/便民公共服务/<span>' . $current_service['name'] . '</span>';
        $View->display($data);
    }

    public function content() {

        $service_id = (string) $this->input->get('_id');
        $current_service = $this->service_type->find(array('_id' => $service_id, 'removed' => false), 1, 0, array('name', 'parent_id', 'linkurl'));
        if (empty($current_service)) {
            show_404();
        }
        if (!empty($current_service['linkurl'])) {
            header("Location: " . $current_service['linkurl']);
            exit();
        }
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }

        $total_row = $this->service_content->count(array('type' => $service_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));

        $View = new Blitz('template/list-service-subject.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'type') {
                    list($_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($_id == 'current') {
                        $_id = $service_id;
                    }
                    $item_list = $this->itemServiceType($_id, $limit, $offset, $length);
                    foreach ($item_list as $key => $item) {

                        $item_list[$key]['url'] = '/serviceSubject/' . $item['_id'] . '/';
                    }
                } elseif ($action == 'list') {
                    list($_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($_id == 'current') {
                        $_id = $service_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemServiceContent($_id, $limit, $offset, $length, $sort_by, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $View->block($struct, array('page' => $link));
                }
                $data[$struct_val] = $item_list;
            }
        }

        $data['current_service'] = $current_service;
        $data['serviceName'] = $current_service['name'];
        $data['location'] = '<a href="/">首页</a>/<a href="/service/">在线服务</a>/便民公共服务/<span>' . $current_service['name'] . '</span>';

        $View->display($data);
    }

    public function subjectServices() {

        $service_id = (string) $this->input->get('_id');
        $service_id;
        if (empty($service_id)) {
            $service_id = '5215dfb85cef2fc63dedcc48';
        }
        $page = (int) $this->input->get('page');
        $per_count = 15;
        $services = $this->service_type->find(array('_id' => $service_id), 1, 0, array('_id', 'name', 'parent_id'));
        $parents = $this->service_type->find(array('_id' => $services['parent_id']), 1, 0, array('name'));
        $total_row = count($this->service_content->find(array('type' => $service_id), 10000, 0, array('_id')));
        $data = array();
        $special_lists = $this->service_content->find(array('type' => $service_id), $per_count, $per_count * ($page - 1), array(), array("sort" => "DESC"));
        $data = array();
        foreach ($special_lists as $key => $service) {

            // 判断是否有子栏目，如果有就在内容上面显示
            /* $service_child = $this->service_type->find(array('parent_id' => $service['_id']), 10, 0, array('_id', 'name'));
              if(!empty($service_child)){
              echo 1111111111;
              $service_block = '';
              foreach ($service_child  as $item) {
              $service_block = "<a href='/service/special/'" . $menu['_id'] . "'/'>".$menu['name']."</a>";
              }
              $data['service_block'][] = $service_block;
              } */

            if (mb_strlen($service['title']) > 40) {
                $title = mb_substr($service['title'], 0, 40) . '...';
            } else {
                $title = $service['title'];
            }
            if (empty($service['linkurl'])) {
                $url = '/service/detail/' . $service['_id'] . '.html';
            } else {
                $url = $service['linkurl'];
            }
            if (empty($service['branch_id'])) {
                $url = '/interaction/mailLists/';
            } else {
                $url = '/interaction/mailList/' . $service['branch_id'] . '/';
            }
            $special_lists = array('url' => $url, '_id' => $service['_id'], 'name' => $title);
            $special_lists['child'][] = array('url' => '/service/detail/' . $service['_id'] . '.html', 'name' => '办事指南');
            $special_lists['child'][] = array('url' => $url, 'name' => '网上咨询');
            $special_lists['child'][] = array('url' => 'http://www.ahlazw.gov.cn', 'name' => '网上申请');
            $special_lists['child'][] = array('url' => 'http://www.ahlazw.gov.cn', 'name' => '办件查询');
            if (!empty($service['old_down_ids'])) {
                $special_lists['child'][] = array('url' => '/service/detail/' . $service['_id'] . '.html#download', 'name' => '表格下载');
            }
            if (!empty($service['old_policy_ids'])) {
                $special_lists['child'][] = array('url' => '/service/detail/' . $service['_id'] . '.html#policy', 'name' => '相关政策');
            }
            $data['special'][] = $special_lists;
        }
        $data['currend_id'] = $services['_id'];
        $data['currend_name'] = $services['name'];
        // 菜单栏大标题
        $data['menu_name'] = $parents['name'];
        $View = new Blitz('template/service_special.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $View->block($struct, array('page' => $link));
                }

                //菜单
                if ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    //if ($parent_id != 'current') {
                    $channel_id = '5215dfb85cef2fc63dedcc42';
                    // } else {
                    //$channel_id = $services['parent_id'];
                    //}
                    $menu_list = $this->getMenu($channel_id, $limit, $offset, $length);
                    foreach ($menu_list as $key => $menu) {
                        $View->block($struct, array('_id' => $key, 'url' => '/subjectService/' . $menu['_id'] . '/', 'name' => $menu['name']));
                    }
                }
            }
        }
        //$data['current_id'] = $service_id;
        $data['current_name'] = $services['name'];
        $data['parent_id'] = $parents['_id'];
        $data['parent_name'] = $parents['name'];
        $View->display($data);
    }

}

?>
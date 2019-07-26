<?php

class mobileServer extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('service_content_model', 'service_content');
        $this->load->model('site_attach_model', 'service_download');
        $this->load->model('service_type_model', 'service_type');
        $this->load->model('service_policy_model', 'service_policy');
    }

    // 部门列表
    protected function itemBranch($channel_id, $limit = 20, $offset = 0, $length = 10, $current_id = FALSE) {

        $this->load->model('site_branch_model', 'site_branch');

        $filter = array('is_service'=>array("\$ne" => null),'parent_id' => (string) $channel_id, 'status' => true, 'service_on' => true, 'removed' => False, 'site_id' => $this->site_id);
        $select = array('_id', 'name', 'id');
        $arr_sort = array('sort' => 'DESC');

        $item_list = $this->site_branch->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            if ($item['_id'] == $current_id) {
                $item_list[$key]['aon'] = 'class="aon"';
            } else {
                $item_list[$key]['aon'] = '';
            }
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length);
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
            $item_list[$key]['url'] = '/serviceBranch/?type=' . $channel_id . '&_id=' . $item['_id'];
        }
        return $item_list;
    }

    //表格下载(老版附件在service_download中，新版在)
    protected function serviceDownloadList($service_type, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {
        $this->load->model('site_attach_model', 'site_attach');
        $select = array('_id', 'title', 'release_date');
        $sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        if ($service_type) {
            $filter = array('service_type' => $service_type, 'removed' => false, 'status' => true, 'module' => "serviceDownload", 'site_id' => $this->site_id);
        } else {
            $filter = array('removed' => false, 'status' => true, 'module' => "serviceDownload", 'site_id' => $this->site_id);
        }
        $item_list = $this->site_attach->find($filter, $limit, $offset, $select, $sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/download/?_id=' . $item['_id'];
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

    //办事指南
    protected function serviceContentList($service_type, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {
        $this->load->model('service_content_model', 'service_content');
        $select = array('_id', 'title', 'release_date');
        //$sort = array('sort' => 'DESC');
        $sort = array('release_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        if ($service_type) {
            $filter = array('service_type' => $service_type, 'removed' => false, 'status' => true, 'site_id' => $this->site_id);
        } else {
            $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id);
        }
        $item_list = $this->service_content->find($filter, $limit, $offset, $select, $sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/gov/detail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

    // 服务指南
    protected function itemServiceContent($_id, $limit = 50, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $this->load->model('service_content_model', 'service_content');

        $filter = array('type' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'branch_id', 'confirm_date', 'download', 'policy', 'link_url');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->service_content->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : '/mobileServer/detail/' . $item['_id'] . '.html';
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
    protected function itemServiceType($_id, $limit = 50, $offset = 0, $length = 60) {

        $this->load->model('service_type_model', 'service_type');

        $filter = array('parent_id' => $_id, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');

        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/gov/type/?type=' . $_id . '&_id=' . $item['_id'];
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_name'] = $item['name'];
            }
        }
        return $item_list;
    }

    protected function serviceTypeList($parent_id, $limit = 50, $offset = 0, $length = 60) {

        $this->load->model('service_type_model', 'service_type');

        $filter = array('parent_id' => $parent_id, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');

        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/gov/type/?type=' . $parent_id . '&_id=' . $item['_id'];
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
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
	
	public function index() {
        $View = new Blitz('template/mobile/list-server.html');
		$data=array();
		$View->display($data);
	}
	

    public function content() {
        $type_id = (string) $this->input->get('type');
        $_id = (string) $this->input->get('_id');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $parent_type = $this->service_type->find(array('_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name'));
        if (empty($parent_type)) {
            show_404();
        }
        if (empty($_id)) {
            $current_type = $this->service_type->find(array('parent_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'), array('sort' => 'DESC'));
        } else {
            $current_type = $this->service_type->find(array('_id' => $_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'));
        }

        if (empty($current_type)) {
            show_404($current_type);
        }
        if (!empty($current_type['linkurl'])) {
            header("Location: " . $current_type['linkurl']);
            exit();
        }
        $total_row = $this->service_content->count(array('type' => (string) $current_type['_id'], 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id'));

        $View = new Blitz('template/mobile/list.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    } else {
                        $type = $type_id;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } elseif ($action == 'list') {
                    // 办事指南
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($parent_id == 'current') {
                        $type = (string) $current_type['_id'];
                    } else {
                        $type = $parent_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemServiceContent($type, $limit, $offset, $length, $sort_by, $date_format);

                    foreach ($item_list as $key => $item) {
                        $item_list[$key]['content'] = '<a href="/gov/contentDetail/' . $item['_id'] . '.html" target="_blank">办事指南</a>';
                        if (!empty($item['download'])) {
                            $item_list[$key]['download'] = '<a href="/gov/contentDetail/' . $item['_id'] . '.html#download" target="_blank">表格下载</a>';
                        } else {
                            $item_list[$key]['download'] = '';
                        }
                        if (!empty($item['policy'])) {
                            $item_list[$key]['policy'] = '<a href="/gov/contentDetail/' . $item['_id'] . '.html#policy" target="_blank">相关政策<a/>';
                        } else {
                            $item_list[$key]['policy'] = '';
                        }
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
        $data['parent_type'] = $parent_type;
        $data['service_id'] = $_id;
        $data['location'] = '<a href="/mobile/">网站首页</a> / <a href="/mobileServer/">政务大厅</a> / <span>' . $current_type['name'] . '</span>';
        $View->set(array('folder_prefix' => $this->folder_prefix, 'jstag' => 'service'));
        $View->display($data);
    }

    public function detail() {
        $_id = $this->input->get('_id');

        $content = $this->service_content->find(array('_id' => $_id, 'status' => true, 'removed' => false));

        if (!empty($content['link_url'])) {
            header("Location: " . $content['link_url']);
        }

        $content['body'] = htmlspecialchars_decode($content['content']);
        $content['release_date'] = ($content['release_date']) ? date('Y-m-d', $content['release_date']) : '';

        $content['table_name'] = 'service_content';
        $data = array(
            'content' => $content,
            'folder_prefix' => $this->folder_prefix,
        );
        
        $data['location'] = "<a href='/'>网站首页</a> / <a href='/mobileServer/'>政务大厅</a> / <a>内容详细</a> ";

        $View = new Blitz('template/mobile/detail.html');
        $struct_list = $View->getStruct();

        $View->display($data);
    }


}

/* End of file gov.php */
/* Location: ./application/controllers/gov.php */
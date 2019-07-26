<?php

class specialzz extends MY_Controller {

    private $channel_id = '53d6fe9cd55d501e44d13292';

    public function __construct() {
        parent::__construct();

        $this->load->model('special_model', 'special');
        $this->load->model('special_content_model', 'special_content');
        $this->load->model('special_column_model', 'special_column');
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
    }

    // 友情链接
    protected function friendLinkList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {

        $this->load->model('friend_link_model', 'friend_link');

        $filter = array("type_id" => $_id_list, 'status' => true, 'removed' => False, 'site_id' => $this->site_id);
        $arr_sort = array('sort' => 'ASC');

        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'link_url', 'file_path', 'width', 'height', 'target', 'confirm_date');

        $item_list = $this->friend_link->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = (string) ($item['link_url']);
            $item_list[$key]['thumb'] = (string) ($item['file_path']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }

    // 取出所有专题
    protected function specialList($limit = 10, $offset = 0, $length = 60, $sort_by, $date_format = 0) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'create_date');
        $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);

        $item_list = $this->special->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['url'] = '/special/column/?_id=' . $item['_id'];
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
        }
        return $item_list;
    }

    // 专题栏目
    protected function columnList($_id_list, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'name', 'create_date');
        $filter = array('special_id' => $_id_list, 'removed' => false);

        $item_list = $this->special_column->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
            $item_list[$key]['title'] = $item['name'];
            $item_list[$key]['url'] = '/special/content/?_id=' . $item['_id'];
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
        }
        return $item_list;
    }

    // 专题内容
    protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0, $description_length = 0, $is_pic = false) {

        $arr_sort = array('release_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'release_date', 'body', 'thumb');
        // 如果是图片，过滤没有图片的
        if ($is_pic) {
            $filter = array('column_id' => $_id_list, 'thumb' => array("\$ne" => ""), 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('column_id' => $_id_list, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        }

        $item_list = $this->special_content->find($filter, $limit, $offset, $select, $arr_sort);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            if ($description_length != 0) {
                if (mb_strlen($item['body']) > $description_length) {
                    $item_list[$key]['description'] = mb_substr(str_replace("&nbsp;", "", strip_tags($item['body'])), 0, $description_length) . '...';
                } else {
                    $item_list[$key]['description'] = str_replace("&nbsp;", "", strip_tags($item['body']));
                }
            }
            $item_list[$key]['url'] = '/special/detail/?_id=' . $item['_id'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
			 $item_list[$key]['thumb'] = $item['thumb'];
        }
        return $item_list;
    }
	
	

    // 专题内容
    protected function menuList($filter, $limit = 10, $offset = 0, $length = 60) {

        $arr_sort = array('morder' => 'desc','create_date' => 'asc');
        $select = array('_id', 'name');

        $item_list = $this->special_column->find($filter, $limit, $offset, $select, $arr_sort);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        $i = 0;
        foreach ($item_list as $key => $item) {
            $item_list[$key]['i'] = $i;
            if ($i == 0) {
                $item_list[$key]['aon'] = "class='aon'";
            }
            //$item_list[$key]['count'] = $limit - 1;
            $item_list[$key]['count'] = $limit;
            $item_list[$key]['name'] = $item['name'];
            $item_list[$key]['url'] = '/special/content/?_id=' . $item['_id'];
            $i++;
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

    protected function advertList($location_id, $limit = 10, $offset = 0) {

        $this->load->model('advert_resource_model', 'advert_resource');
        $this->load->model('advert_size_model', 'advert_size');

        $filter = array('site_id' => $this->site_id, 'location_id' => $location_id, 'status' => true, 'removed' => false);
        $select = array('_id', 'name', 'media_path', 'target_url', 'start_date', 'end_date', 'size_id');
        $sort_by = array('sort' => 'DESC');

        $item_list = $this->advert_resource->find($filter, $limit, $offset, $select, $sort_by);
        if ($limit == 1) {
            $item_list = array(0 => $item_list);
        }

        foreach ($item_list as $key => $item) {

            $item_list[$key]['_id'] = (string) $item['_id'];
            $item_list[$key]['url'] = $item['target_url'];
            $item_list[$key]['thumb'] = $item['media_path'];
            $item_list[$key]['name'] = $item['name'];
            if (!empty($item['target_url'])) {
                $item_list[$key]['have_advert'] = 1;
            }
            // 判断有没有位置信息 
            if (!empty($item['size_id'])) {
                $size_list = $this->advert_size->find(array('_id' => (string) $item['size_id'], 'site_id' => $this->site_id, 'removed' => false), 1, 0, array('width', 'height'));
                if (empty($size_list['width'])) {
                    $item_list[$key]['width'] = '300';
                } else {
                    $item_list[$key]['width'] = $size_list['width'];
                }
                if (empty($size_list['height'])) {
                    $item_list[$key]['height'] = '200';
                } else {
                    $item_list[$key]['height'] = $size_list['height'];
                }
            } else {
                $item_list[$key]['width'] = '300';
                $item_list[$key]['height'] = '200';
            }
            // 如果没有图片，则不显示 
            if (!empty($item_list[$key]['thumb'])) {
                $item_list[$key]['isshow'] = 1;
            }
        }
        return $item_list;
    }

    public function index() {

      
        $View = new Blitz('template/special-list-zhuanzai.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->specialList($limit, $offset, $length, $sort_by, $date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, True));
                }

                //子菜单
                if ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $_id = $parent_id;
                    } else {
                        $_id = $parent_channel['_id'];
                    }
                    $menu_list = $this->getMenu($_id, $limit, $offset, $length);

                    $i = 0;
                    foreach ($menu_list as $key => $menu) {
                        if ($key == $channel_id) {
                            $item_list[$i]['current'] = true;
                        }
                        $item_list[$i]['_id'] = $key;
                        $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
                        $i++;
                    }
                }
                if ($action == 'advert') {
                    list($location_id, $limit, $offset) = explode('_', $matches[2]);
                    $item_list = $this->advertList((string) $location_id, $limit, $offset);
                    foreach ($item_list as $item) {
                        $View->block($struct, array('_id' => $item['_id'], 'url' => $item['url'], 'width' => $item['width'], 'height' => $item['height'], 'isshow' => $item['isshow'], 'thumb' => $item['thumb'], "have_advert" => $item['have_advert']));
                    }
                }
                if ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                    foreach ($item_list as $item) {
                        $View->block($struct, array('_id' => $item['_id'], 'url' => $item['url'], 'title' => $item['title'], 'thumb' => $item['thumb_name'], 'short_title' => $item['short_title'], 'date' => $item['date']));
                    }
                }
                $this->vals[$struct_val] = $item_list;
            }
        }
        $this->vals['channel_id'] = $parent_channel['_id'];
        $this->vals['channel_name'] = $parent_channel['name'];
        $this->vals['menu_id'] = $channel_tree['_id'];
        $this->vals['menu_name'] = $channel_tree['name'];
        $this->vals['location'] = implode(' / ', array('<a href="/">网站首页</a>', '重要转载'));
        $View->display($this->vals);
    }

    // 模板首页，
    public function template() {

        $special_id = (string) $this->input->get('_id');

        if (empty($special_id)) {
            show_404('专题不存在');
        }
        $special = $this->special->find(array('_id' => $special_id,  'removed' => false), 1);
        if (empty($special)) {
            show_404('专题不存在');
        }

        // 如果指定了模板，跳转到指定模板，
        if (empty($special['template_id'])) {
            header("Location: /special/column/?_id=" . $special_id);
            exit();
        }
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        // 每个模板都有指定的文件夹
		if($special_id=='55bad27b6eee1d6434000000'){//政府网站普查 专题
			$View = new Blitz('template/special/' . $special['template_id'] . '/index_content_error.html');
		}else{
			$View = new Blitz('template/special/' . $special['template_id'] . '/index.html');
		}
        

        $struct_list = $View->getStruct();
        $special_column = $this->special_column->find(array('special_id' => $special_id, 'removed' => false),1);
        $total_row = $this->special_content->count(array('column_id' => (string) $special_column['_id'], 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                // 取导航栏目
                if ($action == 'specialmenu') {
                    list($limit) = explode('_', $matches[2]);
                    $item_list = $this->menuList(array('special_id' => $special_id, 'navigation_on' => true, 'removed' => false), $limit);
                }

                // 取栏目名称和链接
                if ($action == 'column') {
                    list($location_id, $offset, $limit) = explode('_', $matches[2]);
                    if (!empty($location_id)) {
                        if (empty($limit)) {
                            $limit = 1;
                        }
                        $item_list = $this->menuList(array('special_id' => $special_id, 'location' => (string) ($location_id - 1), 'removed' => false), $limit, $offset);
                    }
                }

                if ($action == 'list') {
                    list($location_id, $column_offset, $limit, $offset, $length, $sort_by, $date_format, $description_length) = explode('_', $matches[2]);
                    // 取出对应位置的id
                    $special_column = $this->special_column->find(array('special_id' => $special_id, 'location' => (string) ($location_id - 1)), 1, $column_offset, array('_id'), array('morder' => 'desc','create_date' => 'asc'));
                    $column_id = (string) $special_column['_id'];
                    if (!empty($special_column) || !empty($column_id)) {
                        if ($offset == 'page') {
                            $offset = $limit * ($page - 1);
                        }
                        $item_list = $this->contentList($column_id, $limit, $offset, $length, $sort_by, $date_format, $description_length);
                    }
                }

                if ($action == 'hot') {
                    list($location_id, $column_offset, $limit, $offset, $length, $sort_by, $date_format, $description_length) = explode('_', $matches[2]);
                    // 取出对应位置的id
                    $special_column = $this->special_column->find(array('special_id' => $special_id, 'location' => (string) ($location_id - 1)), 1, $column_offset, array('_id'), array('create_date' => 'asc'));
                    $column_id = (string) $special_column['_id'];
                    if (!empty($special_column) || !empty($column_id)) {
                        $item_list = $this->contentList($column_id, $limit, $offset, $length, $sort_by, $date_format, $description_length);
                    }
                }
				
				if ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                } 

                // 图片
                // 图片
                if ($action == 'slider') {
                    list($location_id, $column_offset, $limit, $offset, $length, $sort_by, $date_format, $description_length) = explode('_', $matches[2]);
                    // 取出对应位置的id
                    $special_column = $this->special_column->find(array('special_id' => $special_id, 'location' => (string) ($location_id - 1)), 1, $column_offset, array('_id'), array('create_date' => 'asc'));
                    $column_id = (string) $special_column['_id'];
                    if (!empty($special_column) || !empty($column_id)) {
                        $item_list = $this->contentList($column_id, $limit, $offset, $length, $sort_by, $date_format, $description_length, true);
                    }
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, FALSE));
                }
                $this->vals[$struct_val] = $item_list;
            }
        }
        $this->vals['special'] = $special;
        $View->display($this->vals);
    }

    // 没有模板时的专题，列出所有的栏目
    public function column() {

        $special_id = (string) $this->input->get('_id');
        if (empty($special_id)) {
            show_404('专题不存在');
        }
        $special = $this->special->find(array('_id' => $special_id, 'removed' => false), 1);
        if (empty($special)) {
            show_404('专题不存在');
        }
		if($special['template_id'] == "two"){
			header("Location: /specialTwo/?_id=$special_id");
			exit();
		}
        // 如果指定了模板，跳转到指定模板，
        if (!empty($special['template_id'])) {
            header("Location: /special/template/?_id=" . $special_id);
            exit();
        }

        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }

        $channel_id = $this->channel_id;

        $channel_tree = $this->site_channel_tree->find(array('_id' => $channel_id), 1);
        if (empty($channel_tree)) {
            show_error('抱歉，缺少频道信息！');
        }


        $_id_list = array($channel_id);
        if (count($channel_tree['child']) > 0) {
            foreach ($channel_tree['child'] as $key => $val) {
                $_id_list[] = (string) $key;
            }
        }

        $array_keys = array_reverse(array_keys($channel_tree['parent']));

        if (count($array_keys) > 1 && count($channel_tree['child']) == 0) {
            $parent_channel = array('_id' => $array_keys[0], 'name' => $channel_tree['parent'][$array_keys[0]]);
        } else {
            $parent_channel = array('_id' => (string) $channel_tree['_id'], 'name' => $channel_tree['name']);
        }

        $total_row = $this->special_column->count(array('special_id' => $special_id, 'removed' => false));

        $View = new Blitz('template/list.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->columnList($special_id, $limit, $offset, $length, $sort_by, $date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, True));
                }

                if ($action == 'advert') {
                    list($location_id, $limit, $offset) = explode('_', $matches[2]);
                    $item_list = $this->advertList((string) $location_id, $limit, $offset);
                    foreach ($item_list as $item) {
                        $View->block($struct, array('_id' => $item['_id'], 'url' => $item['url'], 'width' => $item['width'], 'height' => $item['height'], 'isshow' => $item['isshow'], 'thumb' => $item['thumb'], "have_advert" => $item['have_advert']));
                    }
                }

                //子菜单
                if ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $_id = $parent_id;
                    } else {
                        $_id = $parent_channel['_id'];
                    }
                    $menu_list = $this->getMenu($_id, $limit, $offset, $length);

                    $i = 0;
                    foreach ($menu_list as $key => $menu) {
                        if ($key == $channel_id) {
                            $item_list[$i]['current'] = true;
                        }
                        $item_list[$i]['_id'] = $key;
                        $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
                        $i++;
                    }
                }

                $this->vals[$struct_val] = $item_list;
            }
        }
        $this->vals['channel_id'] = $parent_channel['_id'];
        $this->vals['channel_name'] = $parent_channel['name'];
        $this->vals['menu_id'] = $channel_tree['_id'];
        $this->vals['menu_name'] = $channel_tree['name'];
        $this->vals['location'] = implode(' / ', array('<a href="/">首页</a>', '<a href="/special/">专题专栏</a>', $special['title']));
        $View->display($this->vals);
    }

    public function content() {

        $column_id = (string) $this->input->get('_id');
        if (empty($column_id)) {
            show_404('专题不存在');
        }
        $column = $this->special_column->find(array('_id' => $column_id, 'removed' => false, 'site_id' => $this->site_id), 1);
        if (empty($column)) {
            show_404('专题不存在');
        }

        $special = $this->special->find(array('_id' => $column['special_id'], 'removed' => false, 'site_id' => $this->site_id), 1);

        if (empty($special)) {
            show_404('专题不存在');
        }
		
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }

        $channel_id = $this->channel_id;

        $channel_tree = $this->site_channel_tree->find(array('_id' => $channel_id), 1);
        if (empty($channel_tree)) {
            show_error('抱歉，缺少频道信息！');
        }


        $_id_list = array($channel_id);
        if (count($channel_tree['child']) > 0) {
            foreach ($channel_tree['child'] as $key => $val) {
                $_id_list[] = (string) $key;
            }
        }

        $array_keys = array_reverse(array_keys($channel_tree['parent']));

        if (count($array_keys) > 1 && count($channel_tree['child']) == 0) {
            $parent_channel = array('_id' => $array_keys[0], 'name' => $channel_tree['parent'][$array_keys[0]]);
        } else {
            $parent_channel = array('_id' => (string) $channel_tree['_id'], 'name' => $channel_tree['name']);
        }


        $total_row = $this->special_content->count(array('column_id' => $column_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));

        // 如果指定了模板，跳转到指定模板，
        if (!empty($special['template_id'])) {
            $View = new Blitz('template/special/' . $special['template_id'] . '/list.html');
        } else {
            $View = new Blitz('template/list.html');
        }

        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';


                // 取导航栏目
                if ($action == 'specialmenu') {
                    list($limit) = explode('_', $matches[2]);
                    $item_list = $this->menuList(array('special_id' => (string) $special['_id'], 'navigation_on' => true, 'removed' => false), $limit);
                }

                if ($action == 'advert') {
                    list($location_id, $limit, $offset) = explode('_', $matches[2]);
                    $item_list = $this->advertList((string) $location_id, $limit, $offset);
                    foreach ($item_list as $item) {
                        $View->block($struct, array('_id' => $item['_id'], 'url' => $item['url'], 'width' => $item['width'], 'height' => $item['height'], 'isshow' => $item['isshow'], 'thumb' => $item['thumb'], "have_advert" => $item['have_advert']));
                    }
                }
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->contentList($column_id, $limit, $offset, $length, $sort_by, $date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, false));
                }
                $this->vals[$struct_val] = $item_list;
            }
        }
        $this->vals['channel_id'] = $parent_channel['_id'];
        $this->vals['channel_name'] = $parent_channel['name'];
        $this->vals['menu_id'] = $channel_tree['_id'];
        $this->vals['menu_name'] = $channel_tree['name'];
        $this->vals['special'] = $special;
        $this->vals['column'] = $column;
        $this->vals['location'] = implode(' / ', array('<a href="/">网站首页</a>', '<a href="/special/">专题专栏</a>', "<a href='/special/column/?_id=" . $special['_id'] . "'>" . $special['title'] . "</a>", $column['name']));
        $View->display($this->vals);
    }

    protected function attachList($content_id) {
        $this->load->model('site_attach_model', 'site_attach');

        $item_list = $this->site_attach->find(array('module_id' => $content_id), NULL);
        return $item_list;
    }

    public function detail() {

        $_id = (string) $this->input->get('_id');
		
        if (empty($_id)) {
            show_404('该条信息不存在');
        }
        $content = $this->special_content->find(array('_id' => $_id, 'status' => true, 'removed' => false), 1);//print_r($content);
        if (empty($content)) {
            show_404('该条信息不存在');
        }
		
		if ($content['target_url']) {
            header("Location:" . $content['target_url']);
        }
        $special = $this->special->find(array('_id' => $content['special_id'],  'removed' => false), 1);
        if (empty($special)) {
            show_404('专题不存在');
        }
		
		
        // 如果指定了模板，跳转到指定模板，
        if (!empty($special['template_id'])) {
            $View = new Blitz('template/special/' . $special['template_id'] . '/detail.html');
        } else {
            $View = new Blitz('template/detail.html');
        }
//echo 'template/special/' . $special['template_id'] . '/detail.html';
        $column = $this->special_column->find(array('_id' => $content['column_id']), 1);

        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                // 取导航栏目
                if ($action == 'specialmenu') {
                    list($limit) = explode('_', $matches[2]);
                    $item_list = $this->menuList(array('special_id' => (string) $special['_id'], 'navigation_on' => true, 'removed' => false), $limit);
                }

                if ($action == 'advert') {
                    list($location_id, $limit, $offset) = explode('_', $matches[2]);
                    $item_list = $this->advertList((string) $location_id, $limit, $offset);
                    foreach ($item_list as $item) {
                        $View->block($struct, array('_id' => $item['_id'], 'url' => $item['url'], 'width' => $item['width'], 'height' => $item['height'], 'isshow' => $item['isshow'], 'thumb' => $item['thumb'], "have_advert" => $item['have_advert']));
                    }
                }

                

                $this->vals[$struct_val] = $item_list;
            }
        }

        $content['release_date'] = ($content['release_date']) ? date('Y-m-d H:i', $content['release_date']) : '';
        if (!empty($content['author'])) {
            $content['author'] = '作者： ' . $content['author'];
        }
        if (!empty($content['copy_from'])) {
            $content['copy_from'] = '信息来源： ' . $content['copy_from'];
        }
       
           if ($View->hasContext('attach')) {
            $item_list = $this->attachList($_id);
            $this->load->helper('number');
            foreach ($item_list as $item) {
                $View->block('/attach', array('_id' => $item['_id'],
                    'downloads' => $item['downloads'],
                    'file_size' => byte_format($item['file_size']),
                    'name' => "附件：" . $item['real_name'],
                    'url' => '/download/?mod=site_attach&_id=' . $item['_id'],
                    'file_type' => $item['file_type'],
                        )
                );
            }
        }

        $this->vals['content'] = $content; //print_r($content);die();
        $this->vals['column'] = $column;
        $this->vals['special'] = $special;
		
        $this->vals['location'] = implode(' / ', array('<a href="/">网站首页</a>', '<a href="/special/">专题专栏</a>', "<a href='/special/column/?_id=" . $special['_id'] . "'>" . $special['title'] . "</a>", $content['title']));
        $View->display($this->vals);
		
    }

}

?>
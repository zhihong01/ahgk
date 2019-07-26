<?php

class sitemap extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }
	
	protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {
		
		$this->load->model('content_model', 'content');

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type');
        if ($is_pic) {
            $filter = array('status' => true, 'thumb_name' => array("\$ne" => ''), 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        }
        $item_list = $this->content->findList($_id_list, NULL, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item['description'] = str_replace(Chr(32), " ", $item['description']);
            if (mb_strlen($item['description']) > $description_length) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            $item_list[$key]['thumb'] = $item['type'] == 1 ? $item['thumb_name'] : $item['thumb_large'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }

    protected function getMenu($channel_id, $limit = 50, $offset = 0, $length = 60) {
        $result = array();
		$this->load->model("site_channel_tree_model", "site_channel_tree");
		$this->load->model("site_channel_model", "site_channel");
		
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
                $result[$key] = $value;
                $i++;
            }
        }

        return $result;
    }

    public function index() {

        $View = new Blitz('template/sitemap.html');
        $struct_list = $View->getStruct();

        $data = array(
            'folder_prefix' => $this->folder_prefix,
        );

        if ($View->hasContext('channel')) {
            $this->load->model("site_channel_model", "site_channel");
            $select = array('_id', 'parent_id', 'name');
            $channel_list = $this->site_channel->find(array('site_id' => $this->site_id,'status'=> True, 'removed' => FALSE), null, 0, $select, array('sort' => 'desc'));
            if ($channel_list) {
                foreach ($channel_list as $key => $value) {
                    $channel_list[$key]['_id'] = (string) $value['_id'];
                }

                $this->load->library('tree');
                $tree = new Tree();

                $tree->makeDataList($channel_list, '/', '_id');
                $tree_list = $tree->listarr;


                $data['channel'] = array();
                foreach ($tree_list as $channel) {
                    if ($channel['layer'] == 2) {

                        $channel_list = array('_id' => $channel['_id'], 'url' => $this->folder_prefix . '/channel/' . $channel['_id'] . '/', 'name' => $channel['name']);
                        $channel_list['menu'] = array();
                        foreach ($tree_list as $menu) {
                            if ($menu['parent_id'] == $channel['_id']) {
                                $channel_list['menu'][] = array('_id' => $menu['_id'], 'url' => $this->folder_prefix . '/channel/' . $menu['_id'] . '/', 'name' => $menu['name']);
                            }
                        }

                        $data['channel'][] = $channel_list;
                    }
                }
            }
        }

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = $_id_list;
                    }

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->contentList($_id_array, $limit, $offset, $length, $date_format);
                    
                }elseif ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $channel_id = $parent_id;
                    } else {
                        $channel_id = $parent_channel['_id'];
                    }

                    $menu_list = $this->getMenu($channel_id, $limit, $offset, $length);
                    $i = 0;
                    foreach ($menu_list as $key => $menu) {
                        $item_list[$i]['_id'] = $key;
                        $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
                        $i++;
                    }
                }

                $data[$struct_val] = $item_list;
            }
        }
		$data['location'] = "<a href='/'>网站首页</a> / <a href='/sitemap/'>网站地图</a>";

        $View->display($data);
    }

}

?>
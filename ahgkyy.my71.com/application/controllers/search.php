<?php

class search extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('content_model', 'content');
    }

	protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {

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

    protected function searchList($keywords = NULL, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length, $field = null) {
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'description', 'release_date');
        $item_list = $this->content->findList(null, $keywords, array('status' => True, 'removed' => false, 'site_id' => $this->site_id), NULL, NULL, $limit, $offset, $select, $arr_sort, $field);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item['description'] = str_replace("\n", "<br/>", str_replace(Chr(32), "&nbsp;", $item['description']));
            if (mb_strlen($item['description']) > $description_length) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }
			if(strpos($item['body'],'UploadFile')){
				$item['body'] = str_replace('UploadFile','',$item['body']);
			}
			$item['body'] = str_replace("\r","\n", str_replace(Chr(32), "&nbsp;", $item['body']));
			$item['body'] = strip_tags($item['body']);
			if (mb_strlen($item['body']) > $description_length) {
				
                $item_list[$key]['body'] = mb_substr($item['body'] , 0, $description_length) . '...';
            }
			if(strpos($item_list[$key]['body'],'UploadFile')){
				$item_list[$key]['body'] = str_replace('UploadFile','',$item_list[$key]['body']);
			}
            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

    protected function searchCount($keywords = NULL, $field = null) {
        $count = $this->content->listCount(null, $keywords, array('status' => True, 'removed' => false, 'site_id' => $this->site_id), null, null, $field);
        return $count;
    }

    protected function getMenu($channel_id, $limit = 50, $offset = 0, $length = 60) {
		
		$this->load->model('site_channel_tree_model', 'site_channel_tree');
        $result = array();
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
         /* $keywords = $this->security->xss_clean ($this->input->get('keywords'));
        $page = (int)$this->security->xss_clean ($this->input->get('page')); */
		$keywords = $this->security->xss_clean(htmlentities($this->input->get('keywords'),ENT_COMPAT,'UTF-8'));
        $page = (int) $this->security->xss_clean(htmlentities($this->input->get('page'),ENT_COMPAT,'UTF-8'));
        if ($page == 0) {
            $page = 1;
        }
        $field = $this->input->get('field') ? (string) $this->security->xss_clean($this->input->get('field')) : null;
        $total_row = $this->searchCount($keywords, $field);
        $View = new Blitz('template/search.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';
                //列表
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
                    
                }elseif ($action == 'search') {
                    list($limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->searchList($keywords, $limit, $offset, $length, $date_format, $description_length, $field);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count, 0);
                    $item_list['page'] = $link;
                } elseif ($action == 'menu') {
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

        $data = array_merge($data, array(
            'keywords' => $keywords,
            'total_row' => $total_row,
            'folder_prefix' => $this->folder_prefix,
        ));
		
		$data['location'] = "<a href='/'>网站首页</a> / <a href='javascript:void(0);'>站内搜索</a>";

        $View->display($data);
    }

}

?>
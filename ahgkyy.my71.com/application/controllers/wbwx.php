<?php
class wbwx extends MY_Controller {

    public function __construct() {
        parent::__construct();
		$this->load->model('site_channel_model', 'site_channel');
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
        $this->load->model('content_model', 'content');
    }
	
	protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type', 'title_color','source_table');
        if ($is_pic) {
            $filter = array('status' => true, 'thumb_name' => array("\$nin" => array('',null)), 'removed' => false, 'site_id' => $this->site_id);
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


			$item['description'] = strip_tags($item['description']);
            if (mb_strlen($item['description']) > $description_length) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
			
			
			if(strstr($item['thumb_name'],'/jcms/')){
				$item_list[$key]['thumb'] =  $item['type'] == 1 ? 'http://old.huaibei.gov.cn/'.$item['thumb_name']:$item['thumb_large'];
			}else{
				$item_list[$key]['thumb'] = $item['type'] == 1 ? $item['thumb_name'] : $item['thumb_large'];
			}
           
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }
	
    public function index() {
        $View = new Blitz('template/wbwx.html');
		$struct_list = $View->getStruct();
		$data = array();
        foreach ($struct_list as $struct) {
            $matches = array();
			if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';
				if ($action == 'list') {
					list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
					$this_channel = $this->site_channel_tree->find(array('_id' => $channel_id));
					if (!empty($this_channel['child'])) {
						unset($_id_list);
						foreach ($this_channel['child'] as $key => $val)
							$_id_list[] = $key;
					} else {
						$_id_list = explode('-', $channel_id);
					}
					$item_list = $this->contentList($_id_list, $limit, $offset, $length, $date_format, $description_length);
				}
				$data[$struct_val] = $item_list;
			}
			
        }
        $View->display($data);
    }
}

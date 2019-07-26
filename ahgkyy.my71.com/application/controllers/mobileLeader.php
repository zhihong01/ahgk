<?php

class mobileLeader extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('site_leader_model', 'site_leader');
        $this->load->model('site_channel_model', 'site_channel');
        $this->load->model('site_branch_model', 'site_branch');
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

    // 领导列表
    protected function leaderList($type_id, $limit = 10, $offset = 0, $length = 60) {

        $this->load->model('site_leader_model', 'site_leader');

        $filter = array("type_id" => $type_id, 'status' => true, 'removed' => False, 'site_id' => $this->site_id);
        $arr_sort = array('sort' => 'DESC');

        $select = array('_id', 'name', 'job_title','photo');

        $item_list = $this->site_leader->find($filter, $limit, $offset, $select, $arr_sort);
		
		if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);

            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }

            $item_list[$key]['url'] = "/mobileLeader/detail/?_id=" . $item['_id'];
			
			$item_list[$key]['date'] = $item['job_title'];
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
        $data = array();
        $_id = $this->input->get('_id');
		
		$leader = $this->site_leader->find(array('name' => array('$ne' => ''), 'removed' => false, 'status' => true, 'site_id' => $this->site_id), 1, 0, '*', array('sort' => 'DESC'));
		
		$type_id=$leader['type_id'];
		
        $View = new Blitz('template/mobile/list.html');

        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
				
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);

                    $item_list = $this->leaderList($type_id, $limit, $offset, $length);
                }
                $data[$struct_val] = $item_list;
            }
        }

		$data['location'] = '<a href="/">网站首页</a> / <a href="/mobileLeader/">市长之窗</a>';
        $View->display($data);
    }
	
	public function detail() {
        $_id = $this->input->get('_id');

        $content = $this->site_leader->find(array('_id' => $_id, 'status' => true, 'removed' => false));

        if (!empty($content['link_url'])) {
            header("Location: " . $content['link_url']);
        }

        $content['release_date'] = ($content['release_date']) ? date('Y-m-d', $content['release_date']) : '';

        $data = array(
            'content' => $content,
            'folder_prefix' => $this->folder_prefix,
        );
        
        $data['location'] = "<a href='/'>网站首页</a> / <a href='/mobileLeader/'>市长之窗</a>";

        $View = new Blitz('template/mobile/detail-leader.html');
        $struct_list = $View->getStruct();

        $View->display($data);
    }

}

?>
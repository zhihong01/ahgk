<?php

class jubao extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('site_channel_model', 'site_channel');
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
        $this->load->model('content_model', 'content');
		 $this->load->model('site_feedback_model', 'site_feedback');
        $this->load->model('site_feedback_type_model', 'site_feedback_type');
    }

    protected function getLocation($channel_tree, $current_id, $current_name) {
        $result = array();
        $result[] = array('/', '网站首页');

        if (count($channel_tree['parent'])) {
            array_shift($channel_tree['parent']);
        }
        foreach ($channel_tree['parent'] as $key => $value) {
            $result[] = array('/bmhzjqq/content/' . $key . '/', $value);
        }

        $result[] = array('/bmhzjqq/content/' . $current_id . '/', $current_name);

        return $result;
    }

    protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type','body','author');
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

            $item['description'] = str_replace(Chr(32), " ", strip_tags($item['description']));
            if (mb_strlen($item['description']) > $description_length) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] :  '/bmhzjqq/detail/' . $item['_id'] . '.html';
            $item_list[$key]['thumb'] = $item['type'] == 1 ? $item['thumb_name'] : $item['thumb_large'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
            $item_list[$key]['author'] = $item['author'];
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

    protected function attachList($content_id) {
        $this->load->model('site_attach_model', 'site_attach');

        $item_list = $this->site_attach->find(array('module_id' => $content_id), NULL);
        return $item_list;
    }

    // 获取互动信件列表
    protected function itemSupervision($filter, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('supervision_model', 'supervision');
        $filter = array_merge($filter, array('status' => true,  'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
        $select = array('_id', 'process_status', 'subject', 'create_date', 'branch_id', 'hit');
        $arr_sort = array($this->sort_by[$sort_by] => "DESC");
        $date_format = $this->date_foramt[$date_format];
        $item_list = $this->supervision->find($filter, $limit, $offset, $select, $arr_sort);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
            $item_list[$key]['url'] = '/supervision/detail/' . $item['_id'] . '.html';
            $item_list[$key]['_id'] = (string) ($item['_id']);
            // 留言的状态
            if (isset($this->supervision_status[$item['process_status']])) {
                $item_list[$key]['process_status'] = $this->supervision_status[$item['process_status']];
            } else {
                $item_list[$key]['process_status'] = $this->supervision_status[0];
            }
            // 取部门
            if (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['branch_id']];
//				if (mb_strlen($item_list[$key]['branch']) > 4) {
//					$item_list[$key]['short_branch'] = mb_substr($item_list[$key]['branch'], 0, 4);
//				}else{
//					$item_list[$key]['short_branch'] = $item_list[$key]['branch'];
//				}
            } else {
                $item_list[$key]['branch'] = '';
            }
            $item['subject'] = strip_tags(html_entity_decode($item['subject']));
            if (mb_strlen($item['subject']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['subject'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['subject'];
            }
            $item_list[$key]['title'] = $item['subject'];
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';

        }

        return $item_list;
    }

    public function index() {
        $data=array();
        $View = new Blitz('template/12380/index.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'slider') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    if ($channel_id != 'current') {
                        if(strstr($channel_id,'-')){
                            $_id_list = explode('-', $channel_id);
                        }else{
                            $this_channel = $this->site_channel_tree->find(array('_id' => $channel_id));
                            if (!empty($this_channel['child'])) {
                                unset($_id_list);
                                foreach ($this_channel['child'] as $key => $val)
                                    $_id_list[] = $key;
                            }else{
                                $_id_list=array($channel_id);
                            }
                        }
                    }else{
                        $_id_list =null;
                    }
                    $item_list = $this->contentList($_id_list, $limit, $offset, $length, $date_format, $description_length, true);

                } elseif ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);

                    if ($channel_id != 'current') {
                        if(strstr($channel_id,'-')){
                            $_id_list = explode('-', $channel_id);
                        }else{
                            $this_channel = $this->site_channel_tree->find(array('_id' => $channel_id));
                            if (!empty($this_channel['child'])) {
                                unset($_id_list);
                                foreach ($this_channel['child'] as $key => $val)
                                    $_id_list[] = $key;
                            }else{
                                $_id_list=array($channel_id);
                            }
                        }
                    }else{
                        $_id_list =null;
                    }
                    $item_list = $this->contentList($_id_list, $limit, $offset, $length, $date_format, $description_length);
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
                        $item_list[$i]['url'] = '/12380/content/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
                        $i++;
                    }
                }elseif ($action == 'product') {
                    //信箱列表
                    list($product_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->itemSupervision(array("product_id" =>(int)$product_id), $limit, $offset, $length, $sort_by, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }

        $View->set(array('folder_prefix' => $this->folder_prefix));
        $View->display($data);
    }

	public function wyjb() {
        $data=array();
        $View = new Blitz('template/12380/wyjb.html');
        $struct_list = $View->getStruct();

		 $View->set(array('rand' => rand(1, 9999999)));
        $View->display($data);
    }

	public function jbxz() {
        $data=array();
        $View = new Blitz('template/12380/jbxz.html');
        $struct_list = $View->getStruct();

        $View->set(array('folder_prefix' => $this->folder_prefix));
        $View->display($data);
    }

	public function fkcx() {
        $data=array();
        $View = new Blitz('template/12380/fkcx.html');
        $struct_list = $View->getStruct();
      
        $View->set(array('folder_prefix' => $this->folder_prefix));
        $View->display($data);
    }

	public function searchresult() {
        $_id = (string) $this->input->get('_id');
        $data = array();
        $View = new Blitz('template/12380/searchresult.html');
        $data = $this->site_feedback->find(array('_id' => $_id, 'site_id' => $this->site_id), 1, 0);
		if($data){
        $data['date'] = ($data['confirm_date']) ? date('Y-m-d H:i', $data['confirm_date']) : '';
		$data['reply_date'] = ($data['reply_date']) ? date('Y-m-d H:i', $data['reply_date']) : '';
		}
           
        $struct_list = $View->getStruct();
        $View->display($data);
    }


	 public function search() {

        $captcha_chars = $_SESSION['captcha_chars'];
        if ((strlen(captcha_chars) != 4) && (strcasecmp($captcha_chars, $this->input->post('vcode')) != 0)) {
            $this->resultJson('验证码不正确');
        }

        $data = array();

        $rand_key =$this->input->post('rand_key');

        $result = $this->site_feedback->find(array('rand_key'=>$rand_key),1,0);
        if ($result) {
            $_SESSION['captcha_chars'] = '';
			$id = $result['_id'];
			$url='/jubao/searchresult/'.$id.'.html';
            $this->resultJson('查询成功正在跳转,请稍等...', '2',array('url'=>$url));
        } else {
            $this->resultJson('查询码错误！');
        }
    }


    
    public function save() {

        $captcha_chars = $_SESSION['captcha_chars'];
        if ($this->input->post('vcode') == '') {
            $this->resultJson('验证码不可为空');
        }
        if (strcasecmp($captcha_chars, $this->input->post('vcode')) != 0) {
            $this->resultJson('验证码不正确');
        }

        $data = array();

        $data['name'] = htmlspecialchars($this->input->post('name'));
        $data['email'] = htmlspecialchars($this->input->post('email'));
        $data['phone'] = htmlspecialchars($this->input->post('phone'));
		$data['address'] = htmlspecialchars($this->input->post('address'));
        $data['title'] = htmlspecialchars($this->input->post('title'));
		$reported_position=$this->input->post('reported_position');
        $reported_name=$this->input->post('reported_name');
		$reported_content=$this->input->post('reported_content');
        $data['body'] = htmlspecialchars_decode('
被举报人:['.$reported_name.']。
被举报人所在单位及职位:['.$reported_position.']。
举报详情:['.$reported_content.']。');
        $data['type_id'] = "5bd287e77f8b9ab54c13dff4";
        $data['no'] = time();
        $data['site_id'] = $this->site_id;
		$data['rand_key'] = $this->randomkeys(10);
        $data['create_date'] = time();
        $data['client_ip'] = $this->client_ip;
		$data['removed'] = false;

        $result = $this->site_feedback->create($data);

        if ($result) {
            $_SESSION['captcha_chars'] = '';
            $this->resultJson('<p>恭喜，信息提交成功!</p><p> 您的查询码为:   '.$data['rand_key'].'</p>', 2);
        } else {
            $this->resultJson('抱歉，信息提交失败！');
        }
    }


}

?>
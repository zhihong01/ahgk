<?php

class wapfeedback extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('site_feedback_model', 'site_feedback');
        $this->load->model('site_feedback_type_model', 'site_feedback_type');
		$this->load->model('interaction_coll_model', 'interaction_coll');
		$this->load->model('interaction_coll_feedback_model', 'interaction_coll_feedback');
		
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

    protected function feedbackList($keyword = '' ,$filter, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {
        $arr_sort = array('create_date'=>'DESC','sort' => 'DESC');
  

        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'name', 'client_ip', 'create_date', 'replied', 'reply_name', 'body', 'reply_content', 'reply_date');

        $item_list = $this->site_feedback->findList($keyword,$filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);

            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
			$item_list[$key]['url'] = '/wapfeedback/detail/' . $item['_id'] . '/';
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
            $item_list[$key]['reply_date'] = ($item['reply_date']) ? date($date_format, $item['reply_date']) : '';
			$item_list[$key]['state'] = ($item['reply_name'])? '已回复' : '未回复';
			
			
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

    // 取专题栏目
    protected function feedbackTypeList($limit = 10, $offset = 0, $length = 60, $type_id = null) {

        $select = array('_id', 'name');

        $item_list = $this->site_feedback_type->find(array('site_id' => $this->site_id), $limit, $offset, $select,array('create_date'=>'ASC'));
		
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);

            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['name'] = $item['name'];
            }

            $item_list[$key]['url'] = '/feedback/' . $item['_id'].'/';
			
			if($type_id)  $item_list[$key]['menu_id'] = $type_id;
        }

        return $item_list;
    }
	
	//信件统计
	protected function messageCount($_id){
		//来信总数
		$messageCount['all'] = $this->site_feedback->count(array('removed' => false, 'site_id' => $this->site_id,'type_id'=>$_id ));
		//选登数量
		$messageCount['pass'] = $this->site_feedback->count(array('status' => true, 'removed' => false, 'site_id' => $this->site_id, 'type_id'=>$_id));
		//今日来信
		$messageCount['today'] = $this->site_feedback->count(
			array(
				'removed' => false,
				'site_id' => $this->site_id,
				'type_id'=>$_id,
				'create_date'=> array('$gte' => strtotime(date('Y-m-d', time())))
				)
			);
		//今日回复
		$messageCount['todayReply'] = $this->site_feedback->count(array('status' => true,'create_date'=> array('$gte' => strtotime(date('Y-m-d', time()))), 'removed' => false, 'site_id' => $this->site_id));
		
		return $messageCount;
	}
	
	//我要写信
	public function write() {
		
        $type_id = $this->input->get('type_id') ? (string) $this->input->get('type_id') : '';
 
        $total_row = $this->site_feedback->count(array('status' => true, 'removed' => false, 'site_id' => $this->site_id));
		
        $View = new Blitz('template/feedback/feedback-write.html');

        $struct_list = $View->getStruct();
        $data = array();
		$menu = array();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';
                //列表
                if ($action == 'feedback') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    $_id_array = $type_id;
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->feedbackList($_id_array, $limit, $offset, $length, $date_format);
                } elseif ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);

                    if ($parent_id != 'current') {
                        $channel_id = $parent_id;
                        $menu_list = $this->getMenu($channel_id, $limit, $offset, $length);
                        $i = 0;
                        foreach ($menu_list as $key => $menu) {
                            $item_list[$i]['_id'] = $key;
                            $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                            $item_list[$i]['name'] = $menu;
                            $i++;
                        }
						$this->load->model("site_channel_model", "site_channel");
						$menu = $this->site_channel->find(array("_id" => $parent_id), 1);
                    } else {
                        $item_list = $this->feedbackTypeList($limit, $offset, $length);
                    }
                } elseif ($action == 'type') {
					$item_list = $this->feedbackTypeList(100, 0, 60,$type_id);
				}

                $data[$struct_val] = $item_list;
            }
        }
         $View->set(array('rand' => rand(1, 9999999)));
		
        $current_type = $this->site_feedback_type->find(array('_id' => $type_id), 1, 0);
		$data['menu_id'] = $type_id;
		$data['channel_name'] = "网上互动";
		$data['menu_name'] = $current_type['name'];
        $data['type_id'] = $type_id;
        $data['location'] = "<a href='/'>网站首页</a>"."</a> / <a href='/feedback/".$type_id."/'>" . $current_type['name'] . "</a> ";
		$data['messageCount'] = $this->messageCount($type_id);

        $View->display($data);
	}

	//列表页
    public function index() {
        $page = (int) $this->input->get('page');
		
        $type_id = $this->input->get('type_id') ? (string) $this->input->get('type_id') : '5a1694dea6039c166b7b422a';
		
		$keyword = $this->input->get('keyword') ? $this->input->get('keyword') : null;
		$from_date = $this->input->get('from_date') ? $this->input->get('from_date') : null;
		$to_date = $this->input->get('to_date') ? $this->input->get('to_date') : null;
		$where_array = array('type_id' => $type_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
		$data = array();
		if($keyword){
			$data['keyword'] = $keyword;
		}
		//print_r($where_array['title']);
		if($from_date || $to_date){
			$where_array['create_date'] = array();
			if($from_date){
				$where_array['create_date'] = array_merge($where_array['create_date'],array('$gte'=>strtotime($from_date)));
				$data['from_date'] = $from_date;
			}
			if($to_date){
				$where_array['create_date'] = array_merge($where_array['create_date'],array('$lte'=>strtotime($to_date)));
				$data['to_date'] = $to_date;
			}
		}
        if ($page == 0) {
            $page = 1;
        }

        $total_row = $this->site_feedback->countList($keyword,$where_array);

        $View = new Blitz('template/wap/template/feedback.html');

        $struct_list = $View->getStruct();
      
		$menu = array();
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
                    
                }elseif ($action == 'feedback') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->feedbackList($keyword,$where_array, $limit, $offset, $length, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $item_list['page'] = $link;
                } elseif ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);

                    if ($parent_id != 'current') {
                        $channel_id = $parent_id;
                        $menu_list = $this->getMenu($channel_id, $limit, $offset, $length);
                        $i = 0;
                        foreach ($menu_list as $key => $menu) {
                            $item_list[$i]['_id'] = $key;
                            $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                            $item_list[$i]['name'] = $menu;
                            $i++;
                        }
						$this->load->model("site_channel_model", "site_channel");
						$menu = $this->site_channel->find(array("_id" => $parent_id), 1);
                    } else {
                        $item_list = $this->feedbackTypeList($limit, $offset, $length);
                    }
                }

                $data[$struct_val] = $item_list;
            }
        }

        $View->set(array('rand' => rand(1, 9999999)));
		
        $current_type = $this->site_feedback_type->find(array('_id' => $type_id), 1, 0, array('name'),array("sort"=>"DESC"));
		$data['menu_id'] = $type_id;
		$data['channel_name'] = "网上互动";
		$data['menu_name'] = $current_type['name'];
        $data['type_id'] = $type_id;
        $data['location'] = "<a href='/'>网站首页</a>"."</a> / <a href='/feedback/".$type_id."/'>" . $current_type['name'] . "</a> ";
		$data['messageCount'] = $this->messageCount($type_id);
		
        $View->display($data);
    }
	
	//列表页
    public function listFeedback() {
        $page = (int) $this->input->get('page');
		
        $type_id = $this->input->get('type_id') ? (string) $this->input->get('type_id') : '5a1694dea6039c166b7b422a';
		
		$keyword = $this->input->get('keyword') ? $this->input->get('keyword') : null;
		$from_date = $this->input->get('from_date') ? $this->input->get('from_date') : null;
		$to_date = $this->input->get('to_date') ? $this->input->get('to_date') : null;
		$where_array = array('type_id' => $type_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
		$data = array();
		if($keyword){
			$data['keyword'] = $keyword;
		}
		//print_r($where_array['title']);
		if($from_date || $to_date){
			$where_array['create_date'] = array();
			if($from_date){
				$where_array['create_date'] = array_merge($where_array['create_date'],array('$gte'=>strtotime($from_date)));
				$data['from_date'] = $from_date;
			}
			if($to_date){
				$where_array['create_date'] = array_merge($where_array['create_date'],array('$lte'=>strtotime($to_date)));
				$data['to_date'] = $to_date;
			}
		}
        if ($page == 0) {
            $page = 1;
        }

        $total_row = $this->site_feedback->countList($keyword,$where_array);

        $View = new Blitz('template/wap/template/list-feedback.html');

        $struct_list = $View->getStruct();
      
		$menu = array();
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
                    
                }elseif ($action == 'feedback') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->feedbackList($keyword,$where_array, $limit, $offset, $length, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $item_list['page'] = $link;
                } elseif ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);

                    if ($parent_id != 'current') {
                        $channel_id = $parent_id;
                        $menu_list = $this->getMenu($channel_id, $limit, $offset, $length);
                        $i = 0;
                        foreach ($menu_list as $key => $menu) {
                            $item_list[$i]['_id'] = $key;
                            $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                            $item_list[$i]['name'] = $menu;
                            $i++;
                        }
						$this->load->model("site_channel_model", "site_channel");
						$menu = $this->site_channel->find(array("_id" => $parent_id), 1);
                    } else {
                        $item_list = $this->feedbackTypeList($limit, $offset, $length);
                    }
                }

                $data[$struct_val] = $item_list;
            }
        }

        $View->set(array('rand' => rand(1, 9999999)));
		
        $current_type = $this->site_feedback_type->find(array('_id' => $type_id), 1, 0, array('name'),array("sort"=>"DESC"));
		$data['menu_id'] = $type_id;
		$data['channel_name'] = "网上互动";
		$data['menu_name'] = $current_type['name'];
        $data['type_id'] = $type_id;
        $data['location'] = "<a href='/'>网站首页</a>"."</a> / <a href='/feedback/".$type_id."/'>" . $current_type['name'] . "</a> ";
		$data['messageCount'] = $this->messageCount($type_id);
		
        $View->display($data);
    }
	
	function findtest1 (){
		 $result = $this->site_feedback->find(array('site_id' => $this->site_id),1);
		 print_r($result);
	}
	
	public function findtest(){
		//获取信箱的有关设置
        $this->load->model("supervision_setting_model", "supervision_setting");
        $supervision_setting = $this->supervision_setting->find(array("site_id" => $this->site_id), 1);
		print_r($supervision_setting);
	}
	
    public function save() {
		
        $captcha_chars = $_SESSION['captcha_chars'];
        if ((strlen(captcha_chars) != 4) && (strcasecmp($captcha_chars, $this->input->post('vcode')) != 0)) {
            $this->resultJson('验证码不正确');
        } 
		
        $data = array();

        $data['name'] = htmlspecialchars($this->input->post('name'));
        $data['phone'] = htmlspecialchars($this->input->post('phone'));
        $data['title'] = htmlspecialchars($this->input->post('title'));
        $data['body'] = htmlspecialchars($this->input->post('body'));
        $data['type_id'] = (string)htmlspecialchars($this->input->post('type_id'));
        $data['no'] = time();
        if (strlen($data['body']) < 20) {
            $this->resultJson('信息正文太短');
        }
        $data['site_id'] = $this->site_id;
        $data['create_date'] = time();
        $data['client_ip'] = $this->input->ip_address();
   
		//获取信箱的有关设置
        $this->load->model("supervision_setting_model", "supervision_setting");
        $supervision_setting = $this->supervision_setting->find(array("site_id" => $this->site_id), 1);
        //获取附件
		
        if (isset($_FILES['attach'])) {
            if ($_FILES['attach']['error'] === 0) {
				
                $tp = array(
                    "image/gif",
                    "image/pgif",
                    "image/x-gif",
                    "image/jpeg",
                    "image/pjpeg",
                    "image/png",
                    "image/x-png"
                );
                //检查上传文件是否在允许上传的类型  
                if (!in_array($_FILES["attach"]["type"], $tp)) {
                    $this->resultJson('文件格式不对,请上传图片文件');
                }
				
                // mongodb 文件上传
                $fileFS = $this->site_feedback->gridFS();
               
                //限制附件上传大小
				
                if ((int)$size > 1024*1024) {
                    $this->resultJson('您上传的图片太大，请上传1M以内文件！');
                }
				
                $md5 = md5_file($_FILES['attach']['tmp_name']);
                // 查找文件是否已存在(查找出来的是个对象)
                $exists = $fileFS->findOne(array('md5' => $md5, 'length' => $size), array('md5'));

                if (empty($exists->file['md5'])) {
                    $data['file_attach_id'] = (string) $fileFS->storeFile($_FILES['attach']['tmp_name'], array('filename' => $_FILES['attach']['name'], 'contentType' => $_FILES["attach"]["type"], 'size' => $size, 'resoure' => 'attach'));
                } else {
                    $data['file_attach_id'] = (string) $exists->file['_id'];
                }
            }else{
				$this->resultJson('图片上传失败');
			}
        }
		//$this->resultJson($data, '2');
	    $result = $this->site_feedback->create($data);
       if ($result) {
            $_SESSION['captcha_chars'] = '';
            $this->resultJson('信件发送成功', '2');
        } else {
            $this->resultJson('信件发送失败');
        } 
    }

    public function detail() {
        $_id = (string) $this->input->get('_id');
        $View = new Blitz('template/wap/template/detail-feedback.html');

        $content = $this->site_feedback->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), 1, 0);
        $content['date'] = ($content['create_date']) ? date('Y-m-d H:i', $content['create_date']) : '';
        $content['reply_date'] = ($content['reply_date']) ? date('Y-m-d H:i', $content['reply_date']) : '';

        $struct_list = $View->getStruct();
        $data = array();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);

                    $item_list = $this->feedbackTypeList($limit, $offset, $length);
                }

                $data[$struct_val] = $item_list;
            }
        }
		$data['location'] = "<a href='/'>网站首页</a>"."</a> / <a href='/feedback/".$content['type_id']."/'>" .'公众咨询' . "</a> ";

        $data = array(
            'content' => $content,
			'location'=> $data['location'] 
        );

        $View->display($data);
    }

}

?>
<?php

class wapcontent extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('content_model', 'content');
		$this->load->model("site_channel_model", "site_channel");
        $this->load->model("site_channel_tree_model", "site_channel_tree");
		$this->load->model('interaction_coll_model', 'interaction_coll');
		$this->load->model('interaction_coll_feedback_model', 'interaction_coll_feedback');
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
            $result[] = array($this->folder_prefix . '/channel/' . $key . '/', $value);
        }

        $result[] = array($this->folder_prefix . '/channel/' . $current_id . '/', $current_name);

        return $result;
    }
	protected function deleteHtml($str) 
		{
			$str = strip_tags($str);
			$str = trim($str); //清除字符串两边的空格
			$str = preg_replace("/\t/","",$str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
			$str = preg_replace("/\r\n/","",$str); 
			$str = preg_replace("/\r/","",$str); 
			$str = preg_replace("/\n/","",$str); 
			$str = preg_replace("/ /","",$str);
			$str = preg_replace("/  /","",$str);  //匹配html中的空格
			$str = str_replace("&nbsp;","",$str);  
			$str = str_replace(" ","",$str);  
			
			return trim($str); //返回字符串
			
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
        $item_list = $this->content->findList($_id_list, NULL, $filter, NULL, NULL, $limit, $offset, '*', $arr_sort);
		//print_r($item_list);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
			$description = $this->deleteHtml($item['description']);
            $description = str_replace(Chr(32), " ", $description);
            if (mb_strlen($description) > $description_length) {
                $item_list[$key]['description'] = mb_substr($description, 0, $description_length) . '...';
            }else{
				$item_list[$key]['description'] = $description;
			}
			
            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] :  '/wapcontent/detail/' . $item['_id'] . '.html';
            $item_list[$key]['thumb'] = $item['type'] == 1 ? $item['thumb_name'] : $item['thumb_large'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
		
        return $item_list;
    }
	
	protected function companyList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {

        $arr_sort = array('sort' => 'DESC');
        //$date_format = $this->date_foramt[$date_format];
		$date_format = "Y.m";
		
        //$select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type');
        if ($is_pic) {
            $filter = array('status' => true, 'thumb_name' => array("\$ne" => ''), 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        }
        $item_list = $this->content->findList($_id_list, NULL, $filter, NULL, NULL, $limit, $offset, '*', $arr_sort);
		//print_r($item_list);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

			$item_list[$key]['description'] = $item['description']; //认定商品
			$item_list[$key]['subhead'] = $item['subhead']; //商标名称
			$item_list[$key]['prefix'] = $item['prefix_title']; //级别
			
            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] :'';
			
			$item_list[$key]['start_date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
			$item_list[$key]['end_date'] = ($item['close_date']) ? date($date_format, $item['close_date']) : '';
			
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }
	
	//意见征集
	 protected function collectionList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {

        $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'confirm_date','overdate','startdate','link_url','release_date');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->interaction_coll->find($filter, $limit, $offset, $select, $arr_sort);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] :'/interactionColl/detail/' . $item['_id'] . '.html';
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
			
			if($item['startdate'] > time()) {
				$item_list[$key]['state'] = '未开始';
			} elseif( $item['overdate'] <time()) {
				$item_list[$key]['state'] = '已结束';
			}else {
				$item_list[$key]['state'] = '进行中';
			}
        }
        return $item_list;
    }

    protected function itemCount($channel_id) {

        $count = $this->content->listCount((array) $channel_id, NULL, array('status' => True, 'removed' => false));
        return $count;
    }

    protected function picList($content_id) {
        $this->load->model('content_picture_model', 'content_picture');

        $item_list = $this->content_picture->find(array('content_id' => $content_id, 'status' => False), NULL);
        return $item_list;
    }

    protected function attachList($content_id) {
        $this->load->model('site_attach_model', 'site_attach');
        $item_list = $this->site_attach->find(array('module_id' => $content_id), NULL);

        return $item_list;
    }

    protected function getMenu($channel_id, $limit = 50, $offset = 0, $length = 60) {
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
	
	 // 友情链接
    protected function friendLinkList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {

        $this->load->model('friend_link_model', 'friend_link');

        $filter = array("type_id" => $_id_list, 'status' => true, 'removed' => False, 'site_id' => $this->site_id);
        $arr_sort = array('sort' => 'ASC');

        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'link_url', 'file_path', 'width', 'height', 'target', 'confirm_date');

        $item_list = $this->friend_link->find($filter, $limit, $offset, $select, $arr_sort);
		 if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
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
	

	
	
	//我要写信
	public function write() {
		$channel_id = '5a0e9b54a6039cd718237b5b';
        $page = (int) $this->input->get('page');
        $channel_tree = $this->site_channel_tree->find(array('_id' => $channel_id), 1);
        if ($page == 0) {
            $page = 1;
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

        $total_row = $this->content->listCount($_id_list, NULL, array('status' => True, 'removed' => false));

        //$View = new Blitz('template/' . $channel_tree['list_template']);
		$View = new Blitz('template/feedback.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $channelid = $parent_id;
                    } else {
                        $channelid = $parent_channel['_id'];
                    }
                    $menu_list = $this->getMenu($channelid, $limit, $offset, $length);
                    $i = 0;
                    foreach ($menu_list as $key => $menu) {
                        $item_list[$i]['_id'] = $key;
                        $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
						$has_child=$this->getMenu($key, 10, 0, 10);
						$item_list[$i]['child_menu_html']='';
						foreach($has_child as $k=>$v){
							$item_list[$i]['child_menu_html']=$item_list[$i]['child_menu_html'].'<a href="/content/channel/'.$k.'/">'.$v.'</a>';
						}
                        $i++;
                    }
                } elseif ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                } 
                $data[$struct_val] = $item_list;
            }
        }

        $data['channel_id'] = $parent_channel['_id'];
        $data['channel_name'] = $parent_channel['name'];
        $data['menu_id'] = $channel_tree['_id'];
        $data['menu_name'] = $channel_tree['name'];
		$data['rand'] = time();


        $View->display($data);
	}
	

    public function index() {
        $channel_id = (string) $this->input->get('channel');
		
        $page = (int) $this->input->get('page');

        $channel_tree = $this->site_channel_tree->find(array('_id' => $channel_id), 1);
		
        if ($channel_tree['link_url']) {
            header("Location: " . $channel_tree['link_url']);
        }
        if (empty($channel_tree)) {
            show_error('抱歉，缺少频道信息！');
        }

        if ($page == 0) {
            $page = 1;
        }

        $_id_list = array($channel_id);
        if (count($channel_tree['child']) > 0) {
            foreach ($channel_tree['child'] as $key => $val) {
                $_id_list[] = (string) $key;
            }
        }

        $array_keys = array_reverse(array_keys($channel_tree['parent']));

        if (count($array_keys) > 1 && count($channel_tree['child']) == 0) {
            $parent_channel = array('_id' => $array_keys[0], 'name' => $channel_tree['parent'][$array_keys[0]],'child'=>$channel_id);
        } else {
            $parent_channel = array('_id' => (string) $channel_tree['_id'], 'name' => $channel_tree['name'],'child'=>$channel_id);
        }
		

        $total_row = $this->content->listCount($_id_list, NULL, array('status' => True, 'removed' => false));
		if($channel_tree['list_template']=="list-one.html"){
			$View = new Blitz('template/wap/template/list-one.html');
		}else{
		$View = new Blitz('template/wap/template/list.html');
		}
	   // $View = new Blitz('template/wap/template/' . $channel_tree['list_template']);
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //列表
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $date_format,$description_length) = explode('_', $matches[2]);
                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = $_id_list;
                    }

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->contentList($_id_array, $limit, $offset, $length, $date_format,$description_length);
                    
                } elseif ($action == 'company') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = $_id_list;
                    }

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->companyList($_id_array, $limit, $offset, $length, $date_format);
                    
                }elseif ($action == 'coll') {	//意见征集
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = $_id_list;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->collectionList($_id_array, $limit, $offset, $length, $date_format);
                    
                } elseif ($action == 'feedback') {	//政治质询
                   list($channel_id, $limit, $offset, $length,  $date_format) = explode('_', $matches[2]);
					if($channel_id == 'all'){
						$channel_id = null;
					}
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemCollFeedback($_id, $limit, $offset, $length, $date_format);
                    
                } elseif ($action == 'slider') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    $this_channel = $this->site_channel_tree->find(array('_id' => $channel_id));
                    if (!empty($this_channel['child'])) {
                        unset($_id_list);
                        foreach ($this_channel['child'] as $key => $val)
                            $_id_list[] = $key;
                    } else {
                        $_id_list = explode('-', $channel_id);
                    }
                    $item_list = $this->contentList($_id_list, $limit, $offset, $length, $date_format, $description_length, true);
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
                        $channelid = $parent_id;
                    } else {
                        $channelid = $parent_channel['_id'];
                    }

                    $menu_list = $this->getMenu($channelid, $limit, $offset, $length);
                    $i = 0;
                    foreach ($menu_list as $key => $menu) {
                        $item_list[$i]['_id'] = $key;
                        $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
						$has_child=$this->getMenu($key, 10, 0, 10);
						$item_list[$i]['child_menu_html']='';
						foreach($has_child as $k=>$v){
							$item_list[$i]['child_menu_html']=$item_list[$i]['child_menu_html'].'<a href="/content/channel/'.$k.'/">'.$v.'</a>';
						}
                        $i++;
                    }

                } elseif ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }

                $data[$struct_val] = $item_list;
            }
        }

        $data['channel_id'] = $parent_channel['_id'];
		$data['id'] = $parent_channel['child'];
        $data['channel_name'] = $parent_channel['name'];
        $data['menu_id'] = $channel_tree['_id'];
        $data['menu_name'] = $channel_tree['name'];
        $data['total_row'] = $total_row;

        //当前位置
        if ($View->hasContext('location')) {
            $location = array();
            $result = $this->getLocation($channel_tree, $channel_tree['_id'], $channel_tree['name']);
            foreach ($result as $val) {
                $location[] = '<a href="' . $val[0] . '">' . $val[1] . '</a>';
            }

            $data['location'] = implode(' / ', $location);
        }
		
		
        //直接显示频道最后一条内容
        if ($View->hasContext('content')) {
        	$channel_id = (string) $this->input->get('channel');
            $content = $this->content->find(array('channel' => $channel_id, 'status' => True, 'removed' => False), 1);
            if ($content) {
                $data['content'] = $content['body'];
            } else {
                $data['content'] = '资料正在整理中...';
            }
        }
		
		
        $View->display($data);
    }

    public function detail() {
        $_id = $this->input->get('_id');

        $content = $this->content->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));

        $channel_id = count($content['channel'])>1?$content['channel'][1]:$content['channel'][0];
		
        $channel_tree = $this->site_channel_tree->find(array('_id' => $channel_id), 1);

        if (empty($content)) {
            show_404();
        }
        if (!isset($content['channel'][0]) || empty($content['channel'][0]) || empty($channel_tree)) {
            show_error('栏目不存在');
        }
		
		$this->content->update(array('_id' => $_id), array('views' => $content['views'] + 1));
		$content['views'] = $content['views'] + 1;


        if (!empty($content['link_url'])) {
            header("Location: " . $content['link_url']);
        }

        $release_date = $content['sort'];
        $content['body'] = htmlspecialchars_decode($content['body']);
		$content['body'] = str_replace('"@/upload', '"/data/upload', $content['body']);
        $content['release_date'] = ($content['release_date']) ? date('Y-m-d', $content['release_date']) : '';
        if (!empty($content['author'])) {
            $content['author'] = '作者： ' . $content['author'];
        }

        if (!empty($content['copy_from'])) {
            $content['copy_from'] = '信息来源： ' . $content['copy_from'];
        }

        if ($channel_tree['detail_template'] == 'detail-picture.html' && !strstr($content['thumb_name'], 'http://')) {
            $View = new Blitz('template/wap/template/detail.html');
        }else {
            $View = new Blitz('template/wap/template/' . $channel_tree['detail_template']);
        }


        $struct_list = $View->getStruct();


        //picture
        if ($View->hasContext('picture')) {
            $item_list = $this->picList($_id);
            $View->set(array('conut' => count($item_list)));
            $count = count($item_list);
            $i = 0;
            foreach ($item_list as $item) {
                $j = $i + 1;
                $View->block('/picture', array('_id' => $item['_id'], 'i' => $i, 'j' => $j,
                    'small_thumb' => $this->vals['setting']['upload_url'] . substr($item['small_thumb'], 0, 8) . '/' . $item['small_thumb'],
                    'medium_thumb' => $this->vals['setting']['upload_url'] . substr($item['medium_thumb'], 0, 8) . '/' . $item['medium_thumb'],
                    'large_thumb' => $this->vals['setting']['upload_url'] . substr($item['large_thumb'], 0, 8) . '/' . $item['large_thumb'],
                    'xlarge_thumb' => $this->vals['setting']['upload_url'] . substr($item['xlarge_thumb'], 0, 8) . '/' . $item['xlarge_thumb'],
                    'xxlarge_thumb' => $this->vals['setting']['upload_url'] . substr($item['xxlarge_thumb'], 0, 8) . '/' . $item['xxlarge_thumb'],
                    'description' => $item['description'],
                    'count' => $count,
                        )
                );
                $i++;
            }
        }

        if ($View->hasContext('video')) {

            $this->load->model('content_video_model', 'content_video');
            
            $item_list = $this->content_video->find(array('content_id' => $_id));
           
            if (empty($item_list)) {
                $item_list['_id'] = '';
                $item_list['medium_thumb'] = '';
                $item_list['medium_name'] = '';
            }
            
           /* $View->set(array('_id' => $item_list['_id'], 'video_player' => 'http://file.i.my71.com/media/player/player.swf?v1.3.5', 'video_skin' => 'http://file.i.my71.com/media/player/skins/mySkin.swf', 'video_thumb' => $content['thumb_large'], 'video' => $this->vals['setting']['upload_url'] . substr($item_list['medium_name'], 0, 8) . '/' . $item_list['medium_name'],
			'file_type' => $item_list['file_type']));*/
			$View->set(array('_id' => $item_list['_id'],
			'video_player' => '/media/player/player.swf?v1.3.5',
			'video_skin' => '/media/player/skins/mySkin.swf',
			'video_thumb' => $content['thumb_large'],
			//'video' =>$this->vals['setting']['upload_url'].'/'.$this->site_id.'/'. substr($item_list['medium_name'], 0, 6) . '/' . $item_list['medium_name']));
			'video' =>$this->vals['setting']['upload_url'].'/'. substr($item_list['medium_name'], 0, 8) . '/' . $item_list['medium_name'],
			'file_type' => $item_list['file_type']));
        }


         //上一条新闻
        if ($View->hasContext('content_prev')) {
            $content_prev = $this->content->prev($content['channel'], $release_date);
            $content_prev['title'] = strip_tags(html_entity_decode($content_prev['title']));
            if ($content_prev['_id']) {
				if (mb_strlen($content_prev['title']) > 20) {
					$content_prev['title'] = mb_substr($content_prev['title'], 0, 20) . '...';
				}
                $View->set(array('content_prev' => '<a href="/wapcontent/detail/' . $content_prev['_id'] . '.html">' . $content_prev['title'] . '</a>'));
            }
        }

        //下一条新闻
        if ($View->hasContext('content_next')) {
            $content_next = $this->content->next($content['channel'], $release_date);
            $content_next['title'] = strip_tags(html_entity_decode($content_next['title']));
            if ($content_next['_id']) {
				if (mb_strlen($content_next['title']) > 20) {
					$content_next['title'] = mb_substr($content_next['title'], 0, 20) . '...';
				}
                $View->set(array('content_next' => '<a href="/wapcontent/detail/' . $content_next['_id'] . '.html">' . $content_next['title'] . '</a>'));
            }
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
        //当前位置
        $item_list = $this->picList($_id);
        $count = count($item_list);
        $content['title_br'] = str_replace("\n", '<br/>', $content['title']);
        $content['description'] = nl2br($content['description']);
		
		if($content['tag']){
			$data['tag'] = array();
			$i = 0;
			foreach($content['tag'] as $value){
				$data['tag'][$i]['url'] = '/tag/?tag='.$value;
				$data['tag'][$i]['name'] = $value;
				$i++;
			}
		}
		 
        $content['table_name'] = 'content';
        
        $data['content'] =$content; 
        $data['pic_count'] = $count; 
        $data['folder_prefix'] = $this->folder_prefix; 

        if ($View->hasContext('location')) {
            $location_list = array();

            $result = $this->getLocation($channel_tree, $channel_tree['_id'], $channel_tree['name']);
            foreach ($result as $val) {
                $location_list[] = '<a href="' . $val[0] . '">' . $val[1] . '</a>';
            }
            $data['location'] = implode(' / ', $location_list);
        }

        //上一图集
        if ($View->hasContext('picture_prev')) {
            $picture_prev = $this->content->prev($content['channel'], $release_date);
            //print_r($picture_prev);
            $picture_prev['title'] = strip_tags(html_entity_decode($picture_prev['title']));
            if ($picture_prev['_id']) {
                $View->set(array('picture_prev' => '上一条：' . '<a href="' . $this->folder_prefix . '/detail/' . $picture_prev['_id'] . '.html">' . $picture_prev['title'] . '</a>', 'picture_prev_thumb' => '<a id="prevSet" href="' . $this->folder_prefix . '/detail/' . $picture_prev['_id'] . '.html"><img src="' . $picture_prev['thumb_name'] . '"/></a>'));
            }
        }

        //下一图集
        if ($View->hasContext('picture_next')) {
            $picture_next = $this->content->next($content['channel'], $release_date);
            //print_r($picture_next);
            $picture_next['title'] = strip_tags(html_entity_decode($picture_next['title']));
            if ($picture_next['_id']) {
                $View->set(array('picture_next' => '下一条：' . '<a href="' . $this->folder_prefix . '/detail/' . $picture_next['_id'] . '.html">' . $picture_next['title'] . '</a>', 'picture_next_thumb' => '<a id="nextSet" href="' . $this->folder_prefix . '/detail/' . $picture_next['_id'] . '.html" ><img src="' . $picture_next['thumb_name'] . '"/></a>'));
            }
        }
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //栏目列表
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
                } elseif ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $channel_id = $parent_id;
                    }

                    $menu_list = $this->getMenu($channel_id, $limit, $offset, $length);
                    $i = 0;
                    foreach ($menu_list as $key => $menu) {
                        $item_list[$i]['_id'] = $key;
                        $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
                        $i++;
                    }
                } elseif ($action == 'feedback') {
                    list($type_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->feedbackList($type_id, $limit, $offset, $length, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $View->display($data);
    }

    public function download() {
        $this->load->model('site_attach_model', 'site_attach');
        $_id = $this->input->get('_id');
        $attachment = $this->site_attach->find(array('_id' => $_id, 'removed' => false), 1, 0);
		//var_dump($attachment);
        if (empty($attachment)) {
            header("Content-type: text/html; charset=utf-8");
            show_error('错误：记录不存在。');
        }
        $subdir = substr($attachment['saved_name'], 0, 8);
        $full_file = $this->vals['setting']['upload_url'] . $subdir . '/' . $attachment['saved_name'];
        $attachment['saved_name'] = basename($attachment['saved_name']);
        header("Content-Type:" . $attachment['file_type']);
        header('Content-Disposition: attachment; filename="' . mb_convert_encoding($attachment['real_name'], 'GBK', 'UTF-8') . '"');
        header('Content-Length:' . $attachment['file_size']);
        ob_clean();
        flush();
        readfile($full_file);
    }

}

?>
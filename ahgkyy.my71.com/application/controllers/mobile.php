<?php

class mobile extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('site_channel_model', 'site_channel');
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
        $this->load->model('content_model', 'content');
    }

  protected function getLocation($channel_tree, $current_id, $current_name) {
        $result = array();
        $result[] = array('/', '网站首页');

        if (count($channel_tree['parent'])) {
            array_shift($channel_tree['parent']);
        }
        foreach ($channel_tree['parent'] as $key => $value) {
            $result[] = array('/mobile/content/' . $key . '/', $value);
        }

        $result[] = array('/mobile/content/' . $current_id . '/', $current_name);

        return $result;
    }
    
    protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type','body');
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

            $item['body'] = str_replace(Chr(32), " ", strip_tags($item['body']));
            if (mb_strlen($item['body']) > $description_length) {
                $item_list[$key]['body'] = mb_substr($item['description'], 0, $description_length) . '...';
            }

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] :  '/mobile/detail/' . $item['_id'] . '.html';
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
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['name'] = $item['name'];
            }

            $item_list[$key]['url'] = "/mobileLeader/detail/?_id=" . $item['_id'];
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
	
	
	   protected function serviceTypeList($parent_id, $limit = 50, $offset = 0, $length = 60) {
        $this->load->model('service_type_model', 'service_type');
        $filter = array('parent_id' => $parent_id, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');
        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/mobileServer/content/?type=' . $parent_id . '&_id=' . $item['_id'];
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
        }
        return $item_list;
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


    
    public function index() {
    	$data=array();
        $View = new Blitz('template/mobile/index.html');
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
                        $item_list[$i]['url'] = '/mobile/content/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
                        $i++;
                    }
                }elseif ($action == 'product') {
                    //信箱列表
                    list($product_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->itemSupervision(array("product_id" =>(int)$product_id), $limit, $offset, $length, $sort_by, $date_format);
                }elseif ($action == 'leader') {
                    list($type_id, $limit, $offset, $length) = explode('_', $matches[2]);

                    $item_list = $this->leaderList($type_id, $limit, $offset, $length);
                }elseif ($action == 'servicetype') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->serviceTypeList($parent_id, $limit, $offset, $length);
                } elseif ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }

        $View->set(array('folder_prefix' => $this->folder_prefix));
        $View->display($data);
    }
	
	

	
    
	public function content() {
        $channel_id = (string) $this->input->get('_id');
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
            $parent_channel = array('_id' => $array_keys[0], 'name' => $channel_tree['parent'][$array_keys[0]]);
        } else {
            $parent_channel = array('_id' => (string) $channel_tree['_id'], 'name' => $channel_tree['name']);
        }

        $total_row = $this->content->listCount($_id_list, NULL, array('status' => True, 'removed' => false));
      
        if($total_row==1){
			$View = new Blitz('template/mobile/list-one.html');
		}else{
			$View = new Blitz('template/mobile/list.html');
		}
        
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

              	if ($action == 'list') {
                    list($channelid, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    if ($channelid != 'current') {
                        $_id_array = explode('-', $channelid);
                    } else {
                        $_id_array = $_id_list;
                    }

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->contentList($_id_array, $limit, $offset, $length, $date_format);
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
                        $item_list[$i]['url'] = '/mobile/content/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
                        $i++;
                    }
                }
                $data[$struct_val] = $item_list;
            }
        }

        $data['channel_id'] = $parent_channel['_id'];
        $data['channel_name'] = $parent_channel['name'];
        $data['menu_id'] = $channel_tree['_id'];
        $data['menu_name'] = $channel_tree['name'];


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
            $content = $this->content->find(array('channel' => (string) $channel_id, 'status' => True, 'removed' => False), 1);
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
		
		$content['channel']=array_reverse($content['channel']);
        $channel_id = $content['channel'][0];
        $channel_tree = $this->site_channel_tree->find(array('_id' => $channel_id), 1);

        if (empty($content)) {
            show_404();
        }
        if (!isset($content['channel'][0]) || empty($content['channel'][0]) || empty($channel_tree)) {
            show_error('栏目不存在');
        }

        if (!empty($content['link_url'])) {
            header("Location: " . $content['link_url']);
        }

        $release_date = $content['release_date'];
        $content['body'] = htmlspecialchars_decode($content['body']);
		/*if(!strstr($content['body'],$content['thumb_name'])){
			$content['thumb']=$content['thumb_name'];
		}*/
        $content['release_date'] = ($content['release_date']) ? date('Y-m-d', $content['release_date']) : '';
        if (!empty($content['author'])) {
            $content['author'] = '作者： ' . $content['author'];
        }

        if (!empty($content['copy_from'])) {
            $content['copy_from'] = '信息来源： ' . $content['copy_from'];
        }

        $View = new Blitz('template/mobile/detail.html');

        $struct_list = $View->getStruct();


        //上一条新闻
        if ($View->hasContext('content_prev')) {
            $content_prev = $this->content->prev($content['channel'], $release_date);
            $content_prev['title'] = strip_tags(html_entity_decode($content_prev['title']));
            if ($content_prev['_id']) {
                $View->set(array('content_prev' => '上一条： <a href="' . $this->folder_prefix . '/detail/' . $content_prev['_id'] . '.html">' . $content_prev['title'] . '</a>'));
            }
        }

        //下一条新闻
        if ($View->hasContext('content_next')) {
            $content_next = $this->content->next($content['channel'], $release_date);
            $content_next['title'] = strip_tags(html_entity_decode($content_next['title']));
            if ($content_next['_id']) {
                $View->set(array('content_next' => '下一条： <a href="' . $this->folder_prefix . '/detail/' . $content_next['_id'] . '.html">' . $content_next['title'] . '</a>'));
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
                }
                $data[$struct_val] = $item_list;
            }
        }

        //当前位置
        
        $content['title_br'] = str_replace("\n", '<br/>', $content['title']);
        $content['description'] = nl2br($content['description']);

        $content['table_name'] = 'content';
		
        $data = array(
            'content' => $content,
            'pic_count' => $count,
            'folder_prefix' => $this->folder_prefix,
        );

        if ($View->hasContext('location')) {
            $location_list = array();

            $result = $this->getLocation($channel_tree, $channel_tree['_id'], $channel_tree['name']);
            foreach ($result as $val) {
                $location_list[] = '<a href="' . $val[0] . '">' . $val[1] . '</a>';
            }
            $data['location'] = implode(' / ', $location_list);
        }
        $View->display($data);
    }

}

?>
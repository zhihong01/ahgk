<?php

class mobileContent extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('content_model', 'content');
        $this->load->model('content_picture_model', 'content_picture');
        $this->load->model("site_channel_tree_model", "site_channel_tree");
    }
	
    protected function getLocation($channel_tree, $current_id, $current_name) {
        $result = array();
        $result[] = array('/', '网站首页');

        if (count($channel_tree['parent'])) {
            array_shift($channel_tree['parent']);
        }
        foreach ($channel_tree['parent'] as $key => $value) {
        	if($value=='English'){
				 $result[0] = array('/english/', 'Home');
			}else{
            	$result[] = array('/index.php?c=mobileContent&m=index&channel='.$key, $value);
			}
        }

        $result[] = array('/index.php?c=mobileContent&m=index&channel='.$current_id, $current_name);

        return $result;
    }

    protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false,$keyword=NULL) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type','title_color');
		if ($is_pic) {
            $filter = array('status' => true, 'thumb_name' => array("\$ne" => ''), 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        }
        $item_list = $this->content->findList($_id_list, $keyword, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

        	if($item['title_color']){
            	 $item_list[$key]['short_title'] = "<font style='color:".$item['title_color']."'>".$item_list[$key]['short_title']."</font>";
            }
            
            $item['description'] = str_replace(Chr(32), " ", $item['description']);
            $item['description'] = nl2br($item['description']);
        	if (mb_strlen($item['description']) > $description_length) { 
                $item_list[$key]['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }else{
            	$item_list[$key]['description']=$item['description'];
            }

           // $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
		    $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : "/index.php?c=mobileContent&m=detail&_id=".$item['_id'];
			
            $item_list[$key]['thumb'] = $item['type'] == 1 ? $item['thumb_name'] : $item['thumb_large'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
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

    // 广告
    protected function advertList($location_id, $limit = 10, $offset = 0, $length = 60) {

        $this->load->model('advert_resource_model', 'advert_resource');
        $this->load->model('advert_size_model', 'advert_size');

        $filter = array('location_id' => $location_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name', 'media_path', 'target_url', 'start_date', 'end_date', 'size_id');
        $sort_by = array('sort' => 'DESC');

        $item_list = $this->advert_resource->find($filter, $limit, $offset, $select, $sort_by);
        if ($limit == 1) {
            $item_list = array(0 => $item_list);
        }

        foreach ($item_list as $key => $item) {
            if ($item['start_date'] != $item['end_date'] && (time() < $item['start_date'] || time() > $item['end_date'])) {
                unset($item_list);
                continue;
            }
            $item_list[$key]['_id'] = (string) $item['_id'];
            $item_list[$key]['url'] = $item['target_url'];
            $item_list[$key]['thumb'] = $item['media_path'];
        }

        return $item_list;
    }

    public function index() {
		
		
        $channel_id = (string) $this->input->get('channel'); 
        $page = (int) $this->input->get('page');

        $channel_tree = $this->site_channel_tree->find(array('_id' => $channel_id), 1);
       // if ($channel_tree['link_url']) {
          //  header("Location: " . $channel_tree['link_url']);
      //  }
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

    	$View = new Blitz('template/mobile/list.html');
		
        $struct_list = $View->getStruct();

        $keyword=null;
        if($this->input->get('leader')){
        	$keyword=(string)$this->input->get('leader');
        }
        
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //列表
                if ($action == 'list') {
                    list($channelid, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);

                    if ($channelid != 'current') {
                        $_id_array = explode('-', $channelid);
                    } else {
                        $_id_array = $_id_list;
                    }

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->contentList($_id_array, $limit, $offset, $length, $date_format, $description_length,false,$keyword);
                } elseif ($action == 'slider') {
                    list($channelid, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    $this_channel = $this->site_channel_tree->find(array('_id' => $channelid));
                    if (!empty($this_channel['child'])) {
                        unset($_id_list);
                        foreach ($this_channel['child'] as $key => $val)
                            $_id_list[] = $key;
                    } else {
                        $_id_list = explode('-', $channelid);
                    }
                    $item_list = $this->contentList($_id_list, $limit, $offset, $length, $date_format, $description_length, true,$keyword);
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
                        $i++;
                    }
                } elseif ($action == 'advert') {
                    list($location_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->advertList($location_id, $limit, $offset, $length);
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
        $content['release_date'] = ($content['release_date']) ? date('Y-m-d', $content['release_date']) : '';
        if (!empty($content['author'])) {
            $content['author'] = '编辑： ' . $content['author'];
        }

    	if (!empty($content['copy_from'])) {
			if(!empty($content['copysource_url'])){
				$content['copy_from'] = '信息来源： <a href="'.$content['copysource_url'].'" target="_blank" >' . $content['copy_from'].'</a>';
			}else{
				$content['copy_from'] = '信息来源： ' . $content['copy_from'];
			}
        }
        $content['title']=$content['title'];
        
		//图片集
		$content_picture = $this->content_picture->find(array('content_id' => $_id), 1);
		
	
		$View = new Blitz('template/mobile/detail.html');

		

        $struct_list = $View->getStruct();

        
    	if ($View->hasContext('tag')) {
            foreach ($content['tag'] as $tag) {
                $View->block('/tag', array('url' => $this->folder_prefix . '/tag/' . $tag . '/', 'name' => $tag));
            }
        }
        

        //picture
        if ($View->hasContext('picture')) {
            $item_list = $this->picList($_id);
            $View->set(array('conut' => count($item_list)));
            $count = count($item_list);
            $i = 0;
            foreach ($item_list as $item) {
                $j = $i + 1;
                $View->block('/picture', array('_id' => $item['_id'], 'i' => $i, 'j' => $j,
                    'small_thumb' => $this->upload_pic_url . substr($item['small_thumb'], 0, 6) . '/' . $item['small_thumb'],
                    'medium_thumb' => $this->upload_pic_url . substr($item['medium_thumb'], 0, 6) . '/' . $item['medium_thumb'],
                    'large_thumb' => $this->upload_pic_url . substr($item['large_thumb'], 0, 6) . '/' . $item['large_thumb'],
                    'xlarge_thumb' => $this->upload_pic_url . substr($item['xlarge_thumb'], 0, 6) . '/' . $item['xlarge_thumb'],
                    'xxlarge_thumb' => $this->upload_pic_url . substr($item['xxlarge_thumb'], 0, 6) . '/' . $item['xxlarge_thumb'],
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
            $View->set(array('_id' => $item_list['_id'], 'video_thumb' => $content['thumb_large'],'upload_url'=>$this->upload_url, 'video' => $this->upload_url . substr($item_list['medium_name'], 0, 8) . '/' . $item_list['medium_name']));
        }


        //上一条新闻
        if ($View->hasContext('content_prev')) {
            $content_prev = $this->content->prev($content['channel'], $release_date);
            $content_prev['title'] = strip_tags(html_entity_decode($content_prev['title']));
            if ($content_prev['_id']) {
            	if(in_array('English',$channel_tree['parent'])){
            		$View->set(array('content_prev' => ' <a href="' . $this->folder_prefix . '/detail/' . $content_prev['_id'] . '.html" class="ym-gl" title="'.$content_prev['title'].'">PREV: ' . $content_prev['title'] . '</a>'));
            	}else{
                	$View->set(array('content_prev' => ' <a href="' . $this->folder_prefix . '/detail/' . $content_prev['_id'] . '.html" class="ym-gl" title="'.$content_prev['title'].'">上一条：' . mb_substr($content_prev['title'],0,28) . '</a>'));
            	}
            }
        }

        //下一条新闻
        if ($View->hasContext('content_next')) {
            $content_next = $this->content->next($content['channel'], $release_date);
            $content_next['title'] = strip_tags(html_entity_decode($content_next['title']));
            if ($content_next['_id']) {
            	if(in_array('English',$channel_tree['parent'])){
            		$View->set(array('content_next' => ' <a href="' . $this->folder_prefix . '/detail/' . $content_next['_id'] . '.html" class="ym-gr" title="'.$content_next['title'].'">NEXT: ' . $content_next['title'] . '</a>'));
            	}else{
                	$View->set(array('content_next' => ' <a href="' . $this->folder_prefix . '/detail/' . $content_next['_id'] . '.html" class="ym-gr" title="'.$content_next['title'].'">下一条：' . mb_substr($content_next['title'],0,28) . '</a>'));
            	}
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
        $item_list = $this->picList($_id);
        $count = count($item_list);
        $content['title_br'] = str_replace("\n", '<br/>', $content['title']);
        $content['description'] = nl2br($content['description']);
        
        $content['table_name']='content'; 

        $data = array(
            'content' => $content,
            'pic_count' => $count,
            'folder_prefix' => $this->folder_prefix,
        	'upload_url' => $this->upload_url
        );

        if ($View->hasContext('location')) {
            $location_list = array();

            $result = $this->getLocation($channel_tree, $channel_tree['_id'], $channel_tree['name']);
            foreach ($result as $val) {
                $location_list[] = '<a href="' . $val[0] . '">' . $val[1] . '</a>';
            }
            $data['location'] = implode('/', $location_list);
        }

        //上一图集
        if ($View->hasContext('picture_prev')) {
            $picture_prev = $this->content->prev($content['channel'], $release_date);
            //print_r($picture_prev);
            $picture_prev['title'] = strip_tags(html_entity_decode($picture_prev['title']));
            if ($picture_prev['_id']) {
            	if(in_array('English',$channel_tree['parent'])){
            		$View->set(array('picture_prev' => 'PREV: ' . '<a href="' . $this->folder_prefix . '/detail/' . $picture_prev['_id'] . '.html">' . $picture_prev['title'] . '</a>', 'picture_prev_thumb' => '<a id="prevSet" href="' . $this->folder_prefix . '/detail/' . $picture_prev['_id'] . '.html"><img src="' . $picture_prev['thumb_name'] . '"/></a>'));
            	}else{
                	$View->set(array('picture_prev' => '上一条：' . '<a href="' . $this->folder_prefix . '/detail/' . $picture_prev['_id'] . '.html">' . $picture_prev['title'] . '</a>', 'picture_prev_thumb' => '<a id="prevSet" href="' . $this->folder_prefix . '/detail/' . $picture_prev['_id'] . '.html"><img src="' . $picture_prev['thumb_name'] . '"/></a>'));
            	}
            }
        }

        //下一图集
        if ($View->hasContext('picture_next')) {
            $picture_next = $this->content->next($content['channel'], $release_date);
            //print_r($picture_next);
            $picture_next['title'] = strip_tags(html_entity_decode($picture_next['title']));
            if ($picture_next['_id']) {
            	if(in_array('English',$channel_tree['parent'])){
            		$View->set(array('picture_next' => 'NEXT: ' . '<a href="' . $this->folder_prefix . '/detail/' . $picture_next['_id'] . '.html">' . $picture_next['title'] . '</a>', 'picture_next_thumb' => '<a id="nextSet" href="' . $this->folder_prefix . '/detail/' . $picture_next['_id'] . '.html" ><img src="' . $picture_next['thumb_name'] . '"/></a>'));
            	}else{
                	$View->set(array('picture_next' => '下一条：' . '<a href="' . $this->folder_prefix . '/detail/' . $picture_next['_id'] . '.html">' . $picture_next['title'] . '</a>', 'picture_next_thumb' => '<a id="nextSet" href="' . $this->folder_prefix . '/detail/' . $picture_next['_id'] . '.html" ><img src="' . $picture_next['thumb_name'] . '"/></a>'));
            	}
            }
        }

        $View->display($data);
    }


}

?>
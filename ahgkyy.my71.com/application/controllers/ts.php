<?php

class ts extends MY_Controller {

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


    public function save() {

        $captcha_chars = $_SESSION['captcha_chars'];
        if ((strlen(captcha_chars) != 4) && (strcasecmp($captcha_chars, $this->input->post('vcode')) != 0)) {
            $this->resultJson('验证码不正确');
        }

        $data = array();

        $data['name'] = htmlspecialchars($this->input->post('name'));
        $data['email'] = htmlspecialchars($this->input->post('email'));
        $data['phone'] = htmlspecialchars($this->input->post('phone'));
        $data['title'] = htmlspecialchars($this->input->post('title'));
        $data['body'] = htmlspecialchars($this->input->post('body'));
        $data['type_id'] = htmlspecialchars($this->input->post('type_id'));
        $data['no'] = time();


        if (!$this->valid_email($data["email"])) {
            $this->resultJson('邮件地址不正确');
        }

        if($data['phone']){
            if(!$this->valid_mobile_number($data['phone'])){
                $this->resultJson('请输入正确手机号码', 4);
            }
        }

        if(empty($data['title'])){
            $this->resultJson('请输入标题', 4);
        }

        if(empty($data['type_id'])){
            $this->resultJson('请选择留言类型', 4);
        }

        if (strlen($data['body']) < 10) {
            $this->resultJson('信息正文太短', 4);
        }

        $data['site_id'] = $this->site_id;
        $data['create_date'] = time();
        $data['client_ip'] = $this->client_ip;

        $result = $this->site_feedback->create($data);

        if ($result) {
            $_SESSION['captcha_chars'] = '';
            $this->resultJson('恭喜，信息提交成功！', '2');
        } else {
            $this->resultJson('抱歉，信息提交失败！');
        }
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

        $View = new Blitz('template/'.$channel_tree['detail_template']);

        $struct_list = $View->getStruct();

        if ($View->hasContext('video')) {

            $this->load->model('content_video_model', 'content_video');

            $item_list = $this->content_video->find(array('content_id' => $_id));

            if (empty($item_list)) {
                $item_list['_id'] = '';
                $item_list['medium_thumb'] = '';
                $item_list['medium_name'] = '';
            }

            $View->set(array('_id' => $item_list['_id'], 'video_player' => '/media/player/player.swf?v1.3.5', 'video_skin' => '/media/player/skins/mySkin.swf', 'video_thumb' => $content['thumb_large'], 'video' => $this->vals['setting']['upload_url'] .'/'.$this->site_id.'/'. substr($item_list['medium_name'], 0, 6) . '/' . $item_list['medium_name']));
        }

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
                        'url' => '/index.php?c=download&mod=site_attach&_id=' . $item['_id'],
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
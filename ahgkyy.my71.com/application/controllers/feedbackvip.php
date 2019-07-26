<?php

class feedback extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('site_feedback_model', 'site_feedback');
        $this->load->model('site_feedback_type_model', 'site_feedback_type');
		
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

    protected function feedbackList($type_id, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {
        $type_id=$this->input->get('type_id');
        $this->load->model('site_feedback_model', 'feedback');

        $arr_sort = array('create_date' => 'desc');
        if (!empty($type_id)) {
            $filter = array('type_id' => $type_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        }

        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'name', 'client_ip', 'create_date', 'replied', 'reply_name', 'body', 'reply_content', 'reply_date');

        $item_list = $this->feedback->find($filter, $limit, $offset, $select, $arr_sort);
        //var_dump($item_list);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);

            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
$item_list[$key]['url'] = '/feedback/detail/' . $item['_id'] . '.html';
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
            $item_list[$key]['reply_date'] = ($item['reply_date']) ? date($date_format, $item['reply_date']) : '';
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
    protected function feedbackTypeList($limit = 10, $offset = 0, $length = 60) {

        $select = array('_id', 'name');

        $item_list = $this->site_feedback_type->find(array('site_id' => $this->site_id), $limit, $offset, $select);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);

            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['name'] = $item['name'];
            }

            $item_list[$key]['url'] = '/feedback/' .'?type_id'.'='. $item['_id'];
        }

        return $item_list;
    }

    public function index() {
        $page = (int) $this->input->get('page');
        $type_id =(string) $this->input->get('type_id') ? (string) $this->input->get('type_id') : null;
        $class=$this->input->get('class');

        if ($page == 0) {
            $page = 1;
        }

        if (!empty($type_id)) {
            $total_row = $this->site_feedback->count(array('type_id' => $type_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        } else {
            $total_row = $this->site_feedback->count(array('status' => true, 'removed' => false, 'site_id' => $this->site_id));
        }
        if($class){
            $View = new Blitz('template/openness/openness-feedback.html');
        }else{
            $View = new Blitz('template/feedback.html');  
        }
        

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

                    $_id_array = $type_id;

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->feedbackList($_id_array, $limit, $offset, $length, $date_format);
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
                        $item_list=array(
                        0=>array('url'=>'/interactionColl/','_id'=>1,'name'=>'民意征集'),
                        1=>array('url'=>'/interactVote/','_id'=>2,'name'=>'在线调查'),
                        2=>array('url'=>'/feedback/','_id'=>2,'name'=>'在线咨询')
                    );
                    }
                }

                $data[$struct_val] = $item_list;
            }
        }



        $View->set(array('rand' => rand(1, 9999999)));

        $current_type = $this->site_feedback_type->find(array('_id' => $type_id), 1, 0, array('name'));

        $data['channel_name'] = '公众互动';
		$data['menu_name'] = $current_type['name'];
        $data['type_id'] = $type_id;
        $data['location'] = "<a href='/'>网站首页</a>"."</a> / <a href='/feedback/".$type_id."/'>" . $current_type['name'] . "</a> ";

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

        if (strlen($data['body']) < 20) {
            $this->resultJson('信息正文太短');
        }
        $data['site_id'] = $this->site_id;
        $data['create_date'] = time();
        $data['client_ip'] = $this->input->ip_address();

        $result = $this->site_feedback->create($data);

        if ($result) {
            $_SESSION['captcha_chars'] = '';
            $this->resultJson('恭喜，信息提交成功！', '2');
        } else {
            $this->resultJson('抱歉，信息提交失败！');
        }
    }

    public function detail() {
        $_id = (string) $this->input->get('_id');
        $View = new Blitz('template/detail-feedback.html');

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

        $data = array(
            'content' => $content,
        );
 $data['location'] = "<a href='/'>网站首页</a>"."</a> / <a href='/feedback/'>互动交流</a> / <a href='/feedback/?type_id=$type_id'>" . $current_type['name'] . '</a> ';



        $View->display($data);
    }

}

?>
<?php

class wzjy extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('site_feedback_model', 'site_feedback');
        $this->load->model('site_feedback_type_model', 'site_feedback_type');
    }

     protected function feedbackList($type_id, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {

        $this->load->model('site_feedback_model', 'feedback');

        $arr_sort = array('sort' => 'desc');
        if (!empty($type_id)) {
            $filter = array('type_id' => $type_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        }

        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'name', 'client_ip', 'create_date', 'replied', 'reply_name', 'body', 'reply_content', 'reply_date');

        $item_list = $this->feedback->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);

            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

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

            $item_list[$key]['url'] = '/feedback/?type_id=' . $item['_id'];
        }

        return $item_list;
    }

    public function index() {
        $page = (int) $this->input->get('page');
        $type_id = $this->input->get('type_id') ? (string) $this->input->get('type_id') : null;

        if ($page == 0) {
            $page = 1;
        }

        if (!empty($type_id)) {
            $total_row = $this->site_feedback->count(array('type_id' => $type_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        } else {
            $total_row = $this->site_feedback->count(array('status' => true, 'removed' => false, 'site_id' => $this->site_id));
        }

        $View = new Blitz('template/wzjy.html');

        $struct_list = $View->getStruct();
        $data = array();
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
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count,false);
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
                    } else {
                        $item_list = $this->feedbackTypeList($limit, $offset, $length);
                    }
                }

                $data[$struct_val] = $item_list;
            }
        }



        $View->set(array('rand' => rand(1, 9999999)));

        $current_type = $this->site_feedback_type->find(array('_id' => $type_id), 1, 0, array('name'));

        $data['channel_name'] = '其他栏目';
        $data['type_id'] = $type_id;
        $data['location'] = "<a href='/'>网站首页</a> / <a href='#'>其他栏目</a> / <a href='/feedback/?type_id=$type_id'>" . $current_type['name'] . '</a> ';

        $View->display($data);
    }

    public function save() {

        $captcha_chars = $_SESSION['captcha_chars'];
        if ((strlen($captcha_chars) != 4) || (strcasecmp($captcha_chars, $this->input->post('vcode')) != 0)) {
            $this->resultJson('验证码不正确');
        }

        $data = array();

        $data['name'] = htmlspecialchars($this->input->post('name'));
        $data['email'] = htmlspecialchars($this->input->post('email'));
        $data['phone'] = htmlspecialchars($this->input->post('phone'));
        $data['title'] = htmlspecialchars($this->input->post('title'));
        $data['body'] = htmlspecialchars($this->input->post('body'));
        $data['type_id'] = htmlspecialchars($this->input->post('type_id'));
		$data['no']=time();
        if (strlen($data['body']) < 20) {
            $this->resultJson('信息正文太短');
        }
        $data['site_id'] = $this->site_id;
        $data['create_date'] = time();
        $data['client_ip'] = $this->input->ip_address();

        $result = $this->site_feedback->create($data);

        if ($result) {
            $_SESSION['captcha_chars'] = '';
            $this->resultJson('恭喜，信息提交成功！', '+OK');
        } else {
            $this->resultJson('抱歉，信息提交失败！');
        }
    }

    public function detail() {
        $_id = (string) $this->input->get('_id');
        $View = new Blitz('template/detail-feedback.html');

        $content = $this->site_feedback->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), 1, 0);

        $content['date'] = ($content['confirm_date']) ? date('Y-m-d H:i', $content['confirm_date']) : '';

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



        $View->display($data);
    }

}

?>
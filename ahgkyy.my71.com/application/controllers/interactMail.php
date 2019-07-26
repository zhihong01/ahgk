<?php

class interactMail extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('supervision_model', 'supervision');
        $this->load->model('site_leader_model', 'site_leader');
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('supervision_info_type_model', 'supervision_info_type');
        $this->load->model('supervision_question_model', 'supervision_question');
        $this->load->model('supervision_counter_model', 'supervision_counter');
        $this->vals['user_profile']["site_id"] = $this->site_id;
        session_start();
    }

    protected function itemList($branch_id, $filter, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'status', 'subject', 'create_date', 'hit');
        $item_list = $this->supervision->findList(NULL, $branch_id, NULL, NULL, $filter, NULL, $limit, $offset, $arr_sort, $select);
        foreach ($item_list as $key => $item) {

            switch ($item['status']) {
                case '1':
                    $item_list[$key]['status'] = '<font color=blue>新留言</font>';
                    break;
                case '2':
                    $item_list[$key]['status'] = '<font color=blue>已分配</font>';
                    break;
                case '3':
                    $item_list[$key]['status'] = '<font color=red>已回复</font>';
                    break;
                case '4':
                    $item_list[$key]['status'] = '<font color=red>再追问</font>';
                    break;
                case '5':
                    $item_list[$key]['status'] = '<font color=red>已处理</font>';
                    break;
                default :
                    $item_list[$key]['status'] = '<font color=blue>新留言</font>';
                    break;
            }

            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/interactMail/detail/?_id=' . $item['_id'];
            if (mb_strlen($item['subject']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['subject'], 0, $length) . '...';
            } else {
                $item_list[$key]['title'] = $item['subject'];
            }
            $item_list[$key]['date'] = date("m-d", $item['create_date']);
        }
        return $item_list;
    }

    protected function itemBranchList($channel_id, $limit = 20, $offset = 0, $current_id = '') {

        $arr_sort = array('sort' => 'DESC');
        $select = array('_id', 'name', 'id');
        $item_list = $this->site_branch->find(array('parent_id' => $channel_id, 'status' => true, 'supervision_on' => true, 'removed' => false), $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            if ($item['_id'] == $current_id) {
                $item_list[$key]['aon'] = 'class="aon"';
            } else {
                $item_list[$key]['aon'] = '';
            }
            if (empty($item['linkurl'])) {
                $item_list[$key]['url'] = '/interactMail/leaderList/?_id=' . $item['_id'];
            } else {
                $item_list[$key]['url'] = $item['linkurl'];
            }
        }
        return $item_list;
    }

    protected function itemLeaderList($bid, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'job_title', 'name', 'bid');
        $this->load->model('site_leader_model', 'site_leader');
        $item_list = $this->site_leader->find(array('type_id' => '524f5e1d28c39417c45e9fa9', 'bid' => array("\$ne" => $bid), 'status' => TRUE, 'removed' => False), $limit, $offset, $select, $arr_sort); //print_r($item_list);
        foreach ($item_list as $key => $item) {
            $leader = $this->site_branch->find(array('name' => $item['name']), 1, 0, array('_id'));
            if (empty($leader)) {
                continue;
            }
            $item_list[$key]['url'] = '/interactMail/leaderList/?_id=' . $leader['_id'];
        }
        return $item_list;
    }

    protected function branchTypeList($channel_id, $limit = 80, $offset = 0, $current_id = '') {

        $arr_sort = array('sort' => 'DESC', 'id' => 'ASC');
        $select = array('_id', 'name');

        $item_list = $this->site_branch->find(array('parent_id' => $channel_id, 'supervision_on' => true, 'removed' => false, 'status' => true, 'site_id' => $this->site_id), $limit, $offset, array('_id', 'name'), $arr_sort);
        foreach ($item_list as $key => $item) {
            if ($item['_id'] == $current_id) {
                $item_list[$key]['aon'] = 'class="aon"';
            } else {
                $item_list[$key]['aon'] = '';
            }
            if (mb_strlen($item['name']) > 8) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, 8);
            }
            $item_list[$key]['_id'] = $item['_id'];
        }
        return $item_list;
    }

    protected function questionTypeList() {

        $this->load->model('supervision_question_model', 'supervision_question');
        $item_list = $this->supervision_question->find(array('site_id' => $this->site_id, 'removed' => False), 10);
        return $item_list;
    }

    // 按信件类型
    public function typeList() {

        $question_id = (string) $this->input->get('_id');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $data = array();
        $total_row = $this->supervision->count(array('question_id' => $question_id, 'removed' => False, 'confirmed' => true, 'share_on' => true, 'cancelled' => false));
        // 统计
        $data['total'] = $this->supervision->count(array('removed' => False));
        $data['total_reply'] = $this->supervision->count(array('removed' => False, 'status' => array("\$gt" => 2)));

        $question = $this->supervision_question->find(array("_id" => $question_id), 1, 0, array('name'));
        $View = new Blitz('template/list-mail.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //列表
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $_id_array = array('question_id' => $question_id, 'removed' => False, 'confirmed' => true, 'share_on' => true, 'cancelled' => false);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemList(NULL, $_id_array, $limit, $offset, $length, $sort_by, $date_format);
                    foreach ($item_list as $key => $item) {
                        $item_list[$key]['question_name'] = $question['name'];
                    }
                }

                // 查询部门
                if ($action == 'branchbox') {
                    list($channel_id, $limit, $offset) = explode('_', $matches[2]);
                    $item_list = $this->branchTypeList($channel_id, $limit, $offset);
                }

                // 查询类别
                if ($action == 'type') {
                    $item_list = $this->questionTypeList();
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['question_name'] = $question['name'];
        $data['question_id'] = $question_id;
        $data['location'] = '<a href="/">中国池州</a> &gt; <a href="/interaction/">政民互动</a> &gt; <span>' . $question['name'] . '</span>';

        // 统计信件
        $supervision_counter = $this->supervision_counter->find();

        $data['today_total'] = $supervision_counter['today_counter'][1] + $supervision_counter['today_counter'][2] + $supervision_counter['today_counter'][3] + $supervision_counter['today_counter'][4] + $supervision_counter['today_counter'][5];
        $data['today_reply'] = $supervision_counter['today_counter'][3] + $supervision_counter['today_counter'][4] + $supervision_counter['today_counter'][5];

        $View->display($data);
    }

    // 按部门区分，但是都是领导
    public function leaderList() {

        $branch_id = (string) $this->input->get('_id');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $total_row = $this->supervision->count(array('branch_id' => $branch_id, 'removed' => False, 'status' => 1, 'confirmed' => true, 'share_on' => true, 'cancelled' => false));
        $branch = $this->site_branch->find(array('_id' => $branch_id), 1, 0, array('id', 'parent_id', 'name'));

        $View = new Blitz('template/mayor-mail.html');
        $struct_list = $View->getStruct();
        $data = array();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                // 领导列表
                if ($action == 'leader') {
                    list($channel_id, $limit, $offset) = explode('_', $matches[2]);

                    $_id_array = $branch['id'];

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemLeaderList($_id_array, $limit, $offset, $branch_id);
                }
                //列表
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    $_id_array = $branch_id;

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemList($_id_array, array('removed' => False, 'confirmed' => true, 'share_on' => true, 'cancelled' => false), $limit, $offset, $length, $sort_by, $date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
                }
                $data[$struct_val] = $item_list;
            }
        }

        if ($View->hasContext('current_leader')) {
            if (!empty($branch['id'])) {
                $leader = $this->site_leader->find(array('bid' => $branch['id']), 1, 0, array('name', 'duty', 'bid', 'photo', 'job_title'));
            } else {
                $leader = $this->site_leader->find(array('name' => $branch['name']), 1, 0, array('name', 'duty', 'bid', 'photo', 'job_title'));
            }

            if (mb_strlen($leader['duty']) > 180) {
                $leader['duty'] = mb_substr($leader['duty'], 0, 180) . '...';
            }
            $leader['photo'] = $leader['photo'] ? $leader['photo'] : '/media/images/nopic.gif';
            $View->block('/current_leader', array('url' => '/interactMail/writeMail/?type=3&branch_id=' . $branch_id, 'name' => $leader['name'], 'thumb' => $leader['photo'], 'job_title' => $leader['job_title'], 'duty' => $leader['duty']));
        }

        $data['location'] = '<a href="/">中国池州</a> &gt; <a href="/interaction/">政民互动</a> &gt; <span>市长信箱</span>';

        $View->display($data);
    }

    // 按部门区分
    public function branchList() {

        /*
         * _id 部门类别的id， branch_id 部门id
         */
        $_id = (string) $this->input->get('_id');
        $branch_id = (string) $this->input->get('branch_id');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $branch_type = $this->site_branch->find(array('_id' => $_id), 1, 0, array('_id', 'name'));
        // 统计
        $total_row = $this->supervision->count(array('removed' => False));
        $total_reply = $this->supervision->count(array('removed' => False, 'status' => array("\$gt" => 2)));
        $data = array();
        if (empty($branch_id)) {
            $branch_ids = $this->site_branch->find(array('parent_id' => $_id, 'supervision_on' => true, 'status' => 1), null, 0, array('_id', 'name'), array('sort' => 'DESC'));
            foreach ($branch_ids as $key => $item) {
                $branch_id[] = (string) $item['_id'];
            }

            // 没有当前部门，则根据部门类别查询
            $total_row_now = count($this->supervision->findList(NULL, $branch_id, NULL, NULL, array('product_id' => 3, 'removed' => False, 'confirmed' => true, 'share_on' => true, 'cancelled' => false), NULL, 5000));
        } else {
            $data['current_id'] = $branch_id;
            $total_row_now = $this->supervision->count(array('product_id' => 3, 'removed' => False, 'branch_id' => $branch_id, 'confirmed' => true, 'share_on' => true, 'cancelled' => false));
        }

        $View = new Blitz('template/branch-mail.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                // 部门
                if ($action == 'branch') {

                    list($channel_id, $limit, $offset) = explode('_', $matches[2]);
                    $item_list = $this->branchTypeList($channel_id, $limit, $offset, $branch_id);
                    foreach ($item_list as $key => $item) {
                        $item_list[$key]['url'] = '/interactMail/branchList/?_id=' . $channel_id . '&branch_id=' . $item['_id'];
                    }
                }

                //列表
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    $_id_array = $branch_id;

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemList($_id_array, array('product_id' => 3, 'share_on' => true, 'removed' => False, 'cancelled' => false), $limit, $offset, $length, $sort_by, $date_format);
                }

                // 查询部门
                if ($action == 'branchbox') {
                    list($channel_id, $limit, $offset) = explode('_', $matches[2]);
                    $item_list = $this->branchTypeList($channel_id, $limit, $offset, $branch_id);
                }

                // 查询类别
                if ($action == 'type') {
                    $item_list = $this->questionTypeList();
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row_now, $page, $per_count, False));
                }
                $data[$struct_val] = $item_list;
            }
        }
        //Feedback
        if ($View->hasContext('current_leader')) {

            $leader = $this->site_leader->find(array('bid' => $bid), 1, 0, array('name', 'duty', 'bid', 'photo'));
            $View->block('/current_leader', array('bid' => $leader['bid'], 'url' => '/mayorMail/writeMail/?_bid=' . $bid, 'name' => $leader['name'], 'thumb' => $leader['photo'], 'duty' => $leader['duty']));
        }
        $data['total_reply'] = $total_reply;
        $data['total'] = $total_row;
        $data['location'] = '<a href="/">中国池州</a> &gt; <a href="/interaction/">政民互动</a> &gt; <span>部门信箱</span>';
        $data['branch_id'] = $_id;

        // 统计信件
        $supervision_counter = $this->supervision_counter->find();

        $data['today_total'] = $supervision_counter['today_counter'][1] + $supervision_counter['today_counter'][2] + $supervision_counter['today_counter'][3] + $supervision_counter['today_counter'][4] + $supervision_counter['today_counter'][5];
        $data['today_reply'] = $supervision_counter['today_counter'][3] + $supervision_counter['today_counter'][4] + $supervision_counter['today_counter'][5];

        $View->display($data);
    }

    // 按县区区分
    public function countyList() {

        $_id = (string) $this->input->get('_id');
        $branch_id = (string) $this->input->get('branch_id');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $branch_type = $this->site_branch->find(array('_id' => $_id), 1, 0, array('_id', 'name'));
        if (empty($branch_id)) {
            $branch_id = null;
            $total_row_now = $this->supervision->count(array('product_id' => 4, 'share_on' => true, 'removed' => False, 'cancelled' => false));
        } else {
            $total_row_now = $this->supervision->count(array('branch_id' => $branch_id, 'product_id' => 4, 'share_on' => true, 'removed' => False, 'cancelled' => false));
        }


        $total_row = $this->supervision->count(array('removed' => False));
        $total_reply = $this->supervision->count(array('removed' => False, 'status' => array("\$gt" => 2)));

        $View = new Blitz('template/county-mail.html');
        $struct_list = $View->getStruct();
        $data = array();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');

                // 部门
                if ($action == 'branch') {
                    list($channel_id, $limit, $offset) = explode('_', $matches[2]);
                    $item_list = $this->branchTypeList($channel_id, $limit, $offset, $branch_id);
                    foreach ($item_list as $key => $item) {
                        $item_list[$key]['url'] = '/interactMail/countyList/?_id=' . $_id . '&branch_id=' . $item['_id'];
                    }
                }

                // 查询部门
                if ($action == 'branchbox') {
                    list($channel_id, $limit, $offset) = explode('_', $matches[2]);
                    $item_list = $this->branchTypeList($channel_id, $limit, $offset, $branch_id);
                }

                // 查询类别
                if ($action == 'type') {
                    $item_list = $this->questionTypeList();
                }

                //列表
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $_id_array = $branch_id;
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemList($_id_array, array('product_id' => 4, 'share_on' => true, 'removed' => False, 'cancelled' => false), $limit, $offset, $length, $sort_by, $date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row_now, $page, $per_count, False));
                }
                $data[$struct_val] = $item_list;
            }
        }

        $data['current_id'] = $branch_id;

        $data['total'] = $total_row;
        $data['total_reply'] = $total_reply;
        $data['location'] = '<a href="/">中国池州</a> &gt; <a href="/interaction/">政民互动</a> &gt; <span>县区信箱</span>';

        // 统计信件
        $supervision_counter = $this->supervision_counter->find();

        $data['today_total'] = $supervision_counter['today_counter'][1] + $supervision_counter['today_counter'][2] + $supervision_counter['today_counter'][3] + $supervision_counter['today_counter'][4] + $supervision_counter['today_counter'][5];
        $data['today_reply'] = $supervision_counter['today_counter'][3] + $supervision_counter['today_counter'][4] + $supervision_counter['today_counter'][5];

        $View->display($data);
    }

    public function writeMail() {

        $type = (int) $this->input->get('type');
        $branch_id = (string) $this->input->get('branch_id');
        $question_id = (string) $this->input->get('question_id');

        $this->load->model('supervision_question_model', 'supervision_question');
        //$data['question_list']  = $this->supervision_question->find(array("site_id" => $this->site_id),null);

        $data = array();
        if ($type == 1) {
            $data['type'] = '部门';
            $View = new Blitz('template/write-branch-mail.html');
        } else if ($type == 2) {
            $data['type'] = '县区';
            $data['branch_id'] = $branch_id;
            $View = new Blitz('template/write-county-mail.html');
        } else if ($type == 3) {
            $data['type'] = '市长信箱';
            $View = new Blitz('template/write-mayor-mail.html');
            $data['branch_id'] = $branch_id;
        } else {
            $data['question_id'] = $question_id;
            $View = new Blitz('template/write-mail.html');
        }

        $account_id = $_SESSION['account_id'];
        $nickname = $_SESSION['nickname'];
        if (!empty($account_id)) {
            $this->load->model('site_account_model', 'site_account');
            $account = $this->site_account->find(array('_id' => $account_id));

            $data['islogin'] = 1;
            $data['member'] = $account;
        }

        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');

                // 部门
                if ($action == 'branchbox') {
                    list($channel_id, $limit, $offset) = explode('_', $matches[2]);
                    $item_list = $this->branchTypeList($channel_id, $limit, $offset);
                    foreach ($item_list as $key => $item) {
                        if ($item['_id'] == $branch_id) {
                            $item_list[$key]['select'] = 'selected';
                        } else {
                            $item_list[$key]['select'] = '';
                        }
                    }
                }

                // 类别
                if ($action == 'type') {
                    $item_list = $this->questionTypeList();
                    foreach ($item_list as $key => $item) {
                        if ($item['_id'] == $question_id) {
                            $item_list[$key]['select'] = 'selected';
                        } else {
                            $item_list[$key]['select'] = '';
                        }
                    }
                }

                $data[$struct_val] = $item_list;
            }
        }


        $data['location'] = '<a href="/">中国池州</a> &gt; <a href="/interaction/">政民互动</a> &gt; <span>写信</span>';
        $View->display($data);
    }

    public function create() {

        $account_id = $_SESSION['account_id'];
        $nickname = $_SESSION['nickname'];
        if (empty($nickname)) {
            $this->resultJson('请登录后再写信.');
        }

        $captcha_chars = $_SESSION['captcha_chars'];
        if ($this->vals['setting']['captcha_on'] && strcasecmp($captcha_chars, $this->input->post('vcode'))) {
            $this->resultJson('错误的验证码');
        }

        $customer = $this->input->post('customer');
        $supervision = $this->input->post('supervision');

        if (empty($supervision["branch_id"])) {
            $this->resultJson('请选择部门！');
        }

        if (empty($supervision["question_id"])) {
            $this->resultJson('请选择问题类型！');
        }

        if (!isset($supervision["submitter_share_on"])) {
            $this->resultJson('请选择是否公开！');
        }

        if ((strlen($customer["email"]) < 5) || (strlen($supervision["subject"]) < 5) || (strlen($supervision["message"]) < 5)) {
            $this->resultJson('标有 * 字段是必填项,长度必须大于5');
        }

        if (!$this->valid_email($customer["email"])) {
            $this->resultJson('邮件地址不正确');
        }

        $supervision["subject"] = htmlspecialchars($supervision["subject"]);

        $this->load->model('site_account_model', 'site_account');

        //$result = $this->site_account->find(array("nickname" => $nickname));
        $this->site_account->update(array("_id" => $account_id), array('email' => $customer["email"], 'name' => $customer['name'], 'phone' => $customer['phone'], 'address' => $customer['address']));

        $customer_id = $account_id;
        $customer_name = $nickname;

        $this->load->model('supervision_model', 'supervision');
        // 判断信箱类型，如果是县区信箱下的部门，则属于县区信箱，反之则属于部门信箱
        if (empty($supervision["product_id"])) {
            $branch = $this->site_branch->find(array('_id' => $supervision["branch_id"]), 1, 0, array('parent_id'));
            if ($branch['parent_id'] == '52843f818b9226217e1be43f') {
                $supervision["product_id"] = 4;
            } else {
                $supervision["product_id"] = 3;
            }
        } else {
            $supervision["product_id"] = (int) $supervision["product_id"];
        }
        if ($supervision["submitter_share_on"] == 1) {
            $supervision["submitter_share_on"] == true;
        } else {
            $supervision["submitter_share_on"] == false;
        }
        $supervision["client_ip"] = $this->input->ip_address();
        $supervision["site_id"] = $this->site_id;
        $supervision["cancelled"] = false;
        $supervision["removed"] = false;
        $supervision["create_date"] = time();
        $supervision["update_date"] = time();
        $supervision["status"] = 1;
        $supervision["is_public"] = false;
        $supervision["assigned_user"] = array();
        $supervision["creator"] = array(
            "id" => $customer_id,
            "name" => $customer_name
        );

        $supervision["member_id"] = $customer_id;

        //增加 自增长的 ID 值
        $this->load->model('sequence_model', 'sequence');
        $supervision_no = $this->sequence->getSeq("supervision");
        $supervision['no'] = $supervision_no;
        //print_r($supervision);exit();
        $supervision_id = $this->supervision->create($supervision);

        if (empty($supervision_id)) {
            $this->resultJson('留言创建失败.');
        }

        $this->resultJson('留言创建成功.', "+OK");
    }

    public function detail() {

        $_id = (string) $this->input->get('_id');
        $mail = $this->supervision->find(array("_id" => "$_id"));
        $this->supervision->update(array("_id" => "$_id"), array('hit' => $mail['hit'] + 1));
        $mail['date'] = date("Y-m-d H:i:s", $mail['create_date']);
        if ($mail['status'] == 1) {
            $mail['status'] = '<span class="red">新留言</span>';
        }
        if ($mail['status'] == 2) {
            $mail['status'] = '<span class="red">已分配</span>';
        }
        if ($mail['status'] == 3) {
            $mail['status'] = '<span class="red">已回复</span>';
        }
        if ($mail['status'] == 4) {
            $mail['status'] = '<span class="red">再追问</span>';
        }
        if ($mail['status'] == 5) {
            $mail['status'] = '<span class="red">已解决</span>';
        }

        $this->load->model('supervision_reply_model', 'supervision_reply');
        $reply = $this->supervision_reply->find(array("supervision_id" => $_id, 'reply_open' => 1));

        $View = new Blitz('template/detail-mail.html');

        $data['mail'] = $mail;
        $data['reply'] = $reply;
        $reply_branch = $this->site_branch->find(array("_id" => $mail['branch_id']));
        $data['reply']['reply_name'] = $reply_branch['name'];
        if ($reply_branch['parent_id'] == '52493ed8161d7aa2bf6c7f6f') {
            $data['reply']['reply_name'] = '市政府办公室';
        } else {
            $data['mail']['branch'] = '部门：' . $reply_branch['name'];
        }

        //信箱类别
        $question_branch_type = $this->site_branch->find(array("_id" => $mail['branch_id']));
        if ($question_branch_type['parent_id'] == '52493ed8161d7aa2bf6c7f6f') {
            $branch_type = '市长信箱';
        } elseif ($question_branch_type['parent_id'] == '52493ed8161d7aa2bf6c7f3e') {
            $branch_type = '部门信箱';
        } else {
            $branch_type = '县区信箱';
        }


        $data['location'] = '<a href="/">中国池州</a> &gt; <a href="/interaction/">政民互动</a> &gt; <span>' . $branch_type . '</span>';
        $View->display($data);
    }

}

?>
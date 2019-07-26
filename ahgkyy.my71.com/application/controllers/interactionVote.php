<?php

class interactionVote extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('interaction_vote_model', 'interaction_vote');
        $this->load->model('interaction_vote_type_model', 'interaction_vote_type');
        session_start();
    }

    protected function itemList($channel_list = array(), $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'name', 'startdate', 'overdate', 'linkurl');
        //$item_list = $this->interaction_vote->find(array('type_id' => $channel_list, 'status' => 1), $limit, $offset, $select, $arr_sort);
        $item_list = $this->interaction_vote->find(array('removed' => false, 'status' => true, "site_id" => $this->site_id), $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
            if (empty($item['linkurl'])) {
                $item_list[$key]['url'] = '/interactionVote/detail/?_id=' . $item['_id'];
            } else {
                $item_list[$key]['url'] = $item['linkurl'];
            }
            $item_list[$key]['date'] = ($item['startdate']) ? date($date_format, $item['startdate']) : '';
        }
        return $item_list;
    }

    protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_column_model', 'openness_column');
        $this->load->model('site_branch_model', 'site_branch');

        if (is_string($branch_id) && strlen($branch_id) == 1) {
            $branch_id = (int) $branch_id;
            $branch_list = $this->site_branch->find(array('type_id' => $branch_id), 20, 0);

            $branch_id = array();
            foreach ($branch_list as $val) {
                $branch_id[] = (string) $val['_id'];
            }
        }
        $where_array = array_merge($where_array, array('openness_date' => array("\$ne" => 'None'), 'status' => true));
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'confirm_date', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id');

        $item_list = $this->openness_content->findList($branch_id, $where_array, null, null, null, $limit, $offset, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            $current_branch = $this->site_branch->find(array('_id' => $item['branch_id']), 1, 0);
            $item_list[$key]['branch'] = $current_branch['name'];
            if (!empty($item['column_id'])) {
                $current_column = $this->openness_column->find(array('_id' => $item['column_id']), 1, 0);
                $item_list[$key]['column'] = $current_column['name'];
            }

            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }
            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
            $item_list[$key]['url'] = "/openness/detail/content/" . $item['_id'] . '.html';
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

    // 友情链接
    protected function linkList($_id_list, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $arr_sort = array('sort' => 'DESC', 'id' => 'ASC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'link_url', 'file_path');
        $this->load->model('friend_link_model', 'friend_link');
        $item_list = $this->friend_link->find(array("type_id" => $_id_list, 'status' => True, 'removed' => False), $limit, $offset, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = (string) ($item['link_url']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }
        }
        return $item_list;
    }

    public function index() {
        $page = (int) $this->input->get('page');

        if ($page == 0) {
            $page = 1;
        }
		$vote_type = $this->interaction_vote_type->find(array('site_id' => $this->site_id ));
        $total_row = $this->interaction_vote->count(array('site_id' => $this->site_id,'status' => true, 'removed' => false));
        //print_r($total_row);
        $View = new Blitz('template/interaction/list-interactionvote.html');
        $struct_list = $View->getStruct();
        $data = array();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';
                //列表
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    $_id_array = $type_id;

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemList($_id_array, $limit, $offset, $length, $sort_by, $date_format);
                }
                //菜单
                if ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $current_id = $parent_id;
                    }
                    $menu_list = $this->getMenu($current_id, $limit, $offset, $length);
                    $s = 0;
                    foreach ($menu_list as $key => $menu) {
                        $item_list[$s]['_id'] = $key;
                        $item_list[$s]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$s]['name'] = $menu;
                        $s++;
                    }
					$item_list=array(
						0=>array('url'=>'/interactionColl/','_id'=>1,'name'=>'民意征集'),
						1=>array('url'=>'/interactVote/','_id'=>2,'name'=>'在线调查')
                        // 2=>array('url'=>'/feedback/','_id'=>2,'name'=>'在线咨询')  
					);
                }
                // 友情链接
                if ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $_id_list = $channel_id;
                    $item_list = $this->linkList($_id_list, $limit, $offset, $length, $sort_by, $date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
                }

                //通过部门调取信息
                if ($action == 'conbybranch') {
                    list($branch_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $where_array = array();
                    $item_list = $this->opennessList($branch_id, $where_array, $limit, $offset, $length, $sort_by, $date_format);
                }

                $data[$struct_val] = $item_list;
            }
        }
        $data['location'] = '<a href="/">网站首页</a> / <a href="/interaction/">调查征集</a>';
        $data['channel_name'] = "调查征集";
		$data['menu_name']=$vote_type['name'];
        $View->display($data);
    }

    public function detail() {
        $_id = (string) $this->input->get('_id');
        $View = new Blitz('template/interaction/detail-interactionvote.html');
        $struct_list = $View->getStruct();
        $content = $this->interaction_vote->find(array("_id" => $_id), 1, 0);
        $content['startdate_c'] = $content['startdate'];
        $content['overdate_c'] = $content['overdate'];
        $content['create_date'] = date('Y-m-d H:i:s', $content['create_date']);
        $content['startdate'] = date("Y-m-d H:i:s", $content['startdate']);
        $content['overdate'] = date("Y-m-d H:i:s", $content['overdate']);
        if ($content['startdate_c'] < time()) {
            if ($content['overdate_c'] > time()) {
                $data['status_type'] = "【征集时间" . $content['overdate'] . " 正在进行】";
            } else {
                $data['is_end'] = '1';
                $data['status_type'] = "【征集时间" . $content['overdate'] . " 已结束】";
            }
        } else {
            $data['status_type'] = "【开始时间" . $content['startdate'] . " 未开始】";
            $data['not_start'] = '1';
        }
        // 是否记名投票
        if ($content['is_realname'] == true) {
            $data['is_realname'] = 1;

            $account_id = $_SESSION['account_id'];
            if (!empty($account_id)) {
                $this->load->model('site_account_model', 'site_account');
                $member = $this->site_account->find(array('account_id' => $account_id), 1);
                $member['address'] = $member['address']['province'] . '' . $member['address']['city'] . '' . $member['address']['area'] . '' . $member['address']['street'];
                $data['member'] = $member;
            }
        }
        // 是否实时显示投票结果
        if ($content['is_syncshow'] == true) {
            $data['is_syncshow'] = 1;
        }

        $content['description'] = str_replace("\n", "<br/>", str_replace(Chr(32), " ", $content['description']));
        if (isset($content['content']) && !empty($content['content'])) {
            $content['content'] = preg_replace('/<form([^>]*)">/i', "", $content['content']);
            $content['content'] = preg_replace('/<\/form>/i', "", $content['content']);
        }

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //菜单
                if ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $current_id = $parent_id;
                    }
                    $menu_list = $this->getMenu($current_id, $limit, $offset, $length);
                    $s = 0;
                    foreach ($menu_list as $key => $menu) {
                        $item_list[$s]['_id'] = $key;
                        $item_list[$s]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$s]['name'] = $menu;
                        $s++;
                    }
                }

                // 友情链接
                if ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $_id_list = $channel_id;
                    $item_list = $this->linkList($_id_list, $limit, $offset, $length, $sort_by, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['content'] = str_replace("index.php?c=utility&amp;m=createCaptcha", "/index.php?c=utility&amp;m=createCaptcha", $content); 
        $data['location'] = '<a href="/">网站首页</a> / <a href="/interaction/">调查征集</a> / <a href="/interactionVote/">网上调查</a> / <span>' . $content['name'] . '</span>';

        $data['channel_name'] = "调查征集";
        $View->display($data);
    }

    public function vote() {
        if (($this->input->server("REQUEST_METHOD") != 'POST')) {
            $_id = (string) $this->input->get("_id");
            $this->load->model('interaction_vote_model', 'interaction_vote');
            $this_form = $this->interaction_vote->find(array("_id" => $_id, "site_id" => $this->site_id), 1);
            if (empty($this_form))
                show_error('投票表单 不存在！');
            $this_form['startdate_c'] = $this_form['startdate'];
            $this_form['overdate_c'] = $this_form['overdate'];
            $this_form['create_date'] = date('Y-m-d H:i:s', $this_form['create_date']);
            $this_form['startdate'] = date('Y-m-d H:i:s', $this_form['startdate']);
            $this_form['overdate'] = date('Y-m-d H:i:s', $this_form['overdate']);
            $this_form['can_vote'] = false;
            $this_form['can_view'] = false;
            if ($this_form['startdate_c'] < time()) {
                $this_form['can_view'] = true;
                if ($this_form['overdate_c'] > time()) {
                    $this_form['status_type'] = "【征集时间" . $this_form['overdate'] . " 正在进行】";
                    $this_form['can_vote'] = true;
                } else {
                    $this_form['status_type'] = "【征集时间" . $this_form['overdate'] . " 已结束】";
                }
            } else {
                $this_form['status_type'] = "【开始时间" . $this_form['startdate'] . " 未开始】";
            }
            if ($this_form['is_closed']) {
                $this_form['can_vote'] = false;
            }
            $this->vals["this_form"] = $this_form;
            $this->load->model('interaction_vote_form_model', 'interaction_vote_form');
            $this->vals["form_list"] = $this->interaction_vote_form->find(array("vote_id" => $_id, "site_id" => $this->site_id, 'status' => true, 'removed' => false), null, 0, array(), array('sort' => "DESC"));
            $this->load->view(__CLASS__.'/vote', $this->vals);
        } else {
            $data = (array) $this->input->post('data');
            $form_data = (array) $this->input->post('form_data');

            $this->load->model('interaction_vote_model', 'interaction_vote');
            $this_form = $this->interaction_vote->find(array("_id" => $data['vote_id'], "site_id" => $this->site_id), 1);
            if (empty($this_form)) {
                $this->resultJson('投票表单不存在！');
            }
            // 检查必选项
            $this->load->model('interaction_vote_form_model', 'interaction_vote_form');
            $form_list = $this->interact_vote_form->find(array("vote_id" => $data['vote_id']), null, 0, array('name', 'requried'), array('sort' => "DESC"));
            foreach ($form_list as $key => $value) {
                if ($value['requried']) {
                    $v_id = (string) $value['_id'];
                    // 输入框检测
                    if ($form_data[$v_id]['type'] == 'input' || $form_data[$v_id]['type'] == 'textarea') {
                        if (!isset($form_data[$v_id]['data'][0]) || empty($form_data[$v_id]['data'][0])) {
                            $this->resultJson($value['name'] . ' 为必选项');
                        }
                    } else {
                        if (!isset($form_data[$v_id]['data']) || empty($form_data[$v_id]['data'])) {
                            $this->resultJson($value['name'] . ' 为必选项');
                        }
                    }
                }
            }
            $this->createVoteData($form_data, $data['vote_id']);
            // 更新结果表，自增投票次数
            $this->interact_vote->incCounter(array("_id" => $data['vote_id']), array("voter_count" => 1));
            $this->resultJson("投票数据创建成功！", 2);
        }
    }


	
    public function create() {
        $data = (array) $this->input->post('data');
        $this->load->model('interaction_vote_model', 'interaction_vote');
        $this_form = $this->interaction_vote->find(array("_id" => $data['form_id'], "site_id" => $this->site_id), 1);

        if (empty($this_form)) {
            $this->resultJson('自定义表单 不存在！');
        }
        if ($this_form['startdate'] > time()) {
            $this->resultJson('投票还没有开始');
        }
        if ($this_form['overdate'] < time()) {
            $this->resultJson('投票已经结束');
        }
        // 验证码
        if (strstr($this_form['content'], 'index.php?c=utility&amp;m=createCaptcha')) {
            $captcha_chars = $_SESSION['captcha_chars'];
            $vcode = $this->input->post('vcode');

            if (empty($vcode)) {
                $this->resultJson('验证码不可为空');
            }
            if (strcasecmp($captcha_chars, $vcode)) {
                $this->resultJson('验证码不正确');
            }
        }
        
        if ($this_form['is_realname']) {

            $data['name'] = htmlspecialchars($this->input->post('name'));
            $data['voter_addr'] = htmlspecialchars($this->input->post('voter_addr'));

            if (empty($data['name']) || empty($data['voter_paper_id']) || empty($data['voter_tel']) || empty($data['voter_addr'])) {
                $this->resultJson('带*号的为必填项');
            }
            if (!is_numeric($data['voter_tel']) || mb_strlen($data['voter_tel']) < 7) {
                $this->resultJson('请输入有效的电话号码');
            }
            if (mb_strlen($data['voter_paper_id']) != 15 && mb_strlen($data['voter_paper_id']) != 18) {
                $this->resultJson('请输入正确的身份证号码');
            }
            //if(checkIdCard($data['voter_paper_id']) == false){
            //    $this->resultJson('请输入正确的身份证号码');
            // }
            list($old, $voter_info) = $this->findVoteRecord($data['form_id'], array("voter_paper_id" => $data['voter_paper_id']));
            if (!empty($old))
                $this->resultJson('一个身份证号只能投一次票，你已经投过票了。');
            $voter_info ["name"] = $data['name'];

            $voter_info ["voter_paper_id"] = $data['voter_paper_id'];
            $voter_info ["form_id"] = $data['form_id'];
            $voter_info ["voter_tel"] = $data['voter_tel'];
            $voter_info ["voter_addr"] = $data['voter_addr'];
        } else {
            list($old, $voter_info) = $this->findVoteRecord($data['form_id'], array("ipaddress" => $this->getIP()));
            if (!empty($old))
            //$this->resultJson('一个IP地址只能投一次票，你已经投过票了。');
            // $this->resultJson('一个IP地址只能投一次票，你已经投过票了。');
                $voter_info ["name"] = '热心网友';
        }

        $this->load->model('interaction_vote_data_model', 'interaction_vote_data');

        $cust_data = $this->getCustomData($_POST);
        //投票信息不能为空
        if (empty($cust_data)) {
            $this->resultJson("请选择内容！");
        }
        $data_type = explode(',', $_POST['data']['form_type']);
        foreach ($data_type as $key => $val) {
            if ($val == 'checkbox') {
                if (empty($_POST['field_' . $key])) {
                    $this->resultJson('每一个选项都要选择，请检查！');
                }
            }
        }

        $voter_info ["ipaddress"] = $this->getIP();
        $voter_info ['create_date'] = time();
        //$voter_info ['creator'] = array('id' => $this->vals['user_profile']['account_id'], 'name' => $this->vals['user_profile']['name']);
        $voter_info ['creator'] = array('id' => '5271bf7d763b49088261d0d8', 'name' => 'fyldbz');
        $this->load->model('interaction_vote_log_model', 'interaction_vote_log');
        $voter_id = $this->interaction_vote_log->create($voter_info);
        if (!$voter_id) {
            $this->resultJson('投票信息创建失败，请重试。');
        }

        $default_field_data = array(
            "form_id" => $data['form_id'],
            "voter_id" => (string) $voter_id,
            "field_id" => '',
            "field_value" => '',
            "field_type" => '',
            "site_id" => $this->site_id,
            "status" => true,
            'create_date' => time(),
            'creator' => array('id' => '5271bf7d763b49088261d0d8', 'name' => 'fyldbz')
        );

        if (isset($data['form_type']))
            $data['form_type'] = explode(",", $data['form_type']);


        foreach ($cust_data as $key => $val) {
            $field_data = $default_field_data;
            $field_data["field_id"] = $key;
            $field_data["field_value"] = $val;
            if (isset($data['form_type'][$key]))
                $field_data["field_type"] = $data['form_type'][$key];

            $ret = $this->interaction_vote_data->create($field_data);
            if (!$ret) {
                $ret = $this->interaction_vote_log->update(array("_id" => $voter_id), array("status" => true));
                $this->resultJson("投票数据创建失败！");
            }
        }
        $this->addVoteStatData($this->site_id, $data['form_id'], (string) $voter_id);
        $referer = "/interactionVote/detail/?_id=" . $data['form_id'];
        $this->resultJson("投票成功！", "+OK", array('referer' => $referer));
    }

    public function viewResult() {

        $form_id = $this->input->get('_id');
        $this->load->model('interaction_vote_model', 'interaction_vote');
        $this_form = $this->interaction_vote->find(array("_id" => $form_id, "site_id" => $this->site_id), 1);

        if (empty($this_form))
            show_error('本次调查不存在或不允许查看');
        $this_form['startdate'] = date('Y-m-d H:i:s', $this_form['startdate']);
        $this_form['overdate'] = date('Y-m-d H:i:s', $this_form['overdate']);

        $this->load->model('interaction_vote_data_model', 'interaction_vote_data');
        $this_data = $this->interaction_vote_data->find(array("form_id" => $form_id, "site_id" => $this->site_id, "status" => TRUE), null, 0, '*', array("voter_id" => "DESC", "field_id" => "DESC"));
        if (empty($this_data)) {
            show_error('111！');
        }

        $this->load->model('interaction_vote_log_model', 'interaction_vote_log');
        $voter_count = $this->interaction_vote_log->count(array("form_id" => $form_id, "site_id" => $this->site_id), 1);

        //开始 统计 数据
        $default_data = array('total' => 0);
        $stat_data = array();
        foreach ($this_data as $k => $v) {

            $value_field_id = (string) $v['field_id'];
            $value_field_type = (string) $v['field_type'];
            $value_data_arr = (array) explode(",", $v['field_value']);

            if (!isset($stat_data[$value_field_id])) {
                $stat_data[$value_field_id] = array(
                    "field_type" => $value_field_type,
                    "field_data" => array()
                );
            }

            if (($value_field_type == "text") || ($value_field_type == "textarea")) {
                if (!isset($stat_data[$value_field_id]["field_data"][0])) {
                    $stat_data[$value_field_id]["field_data"][0] = array('total' => 0);
                }

                $stat_data[$value_field_id]["field_data"][0]['total']
                        = (int) $stat_data[$value_field_id]["field_data"][0]['total'] + count($value_data_arr);
            } else {
                foreach ($value_data_arr as $value_data) {
                    if (!isset($stat_data[$value_field_id]["field_data"][$value_data])) {
                        $stat_data[$value_field_id]["field_data"][$value_data] =
                                array('total' => 0);
                    }

                    $stat_data[$value_field_id]["field_data"][$value_data]['total']
                            = (int) $stat_data[$value_field_id]["field_data"][$value_data]['total'] + 1;
                }
            }
        }

        foreach ($stat_data as $k => $v) {
            $myTotal = 0;
            foreach ($v["field_data"] as $ck => $cv) {
                $myTotal = $myTotal + $cv['total'];
                if ($voter_count > 0)
                    $stat_data[$k]["field_data"][$ck]['rate'] = round($cv['total'] / $voter_count * 100, 2);
            }
            $stat_data[$k]['field_total'] = $myTotal;
        }
        $this_form['content'] = str_replace("index.php?c=utility&amp;m=createCaptcha", "/index.php?c=utility&amp;m=createCaptcha", $this_form['content']);
        $this_form['description'] = str_replace("\n", "<br/>", str_replace(Chr(32), " ", $this_form['description']));
        $data["this_form"] = $this_form;
        $data["this_data"] = $this_data;
        $data["voter_count"] = $voter_count;
        $data["stat_data"] = $stat_data;
        
        $data['location'] = '<a href="/">网站首页</a> / <a href="/interaction/">民政互动</a> / <a href="/interactionVote/">在线调查</a> /  <span>' . $this_form["name"] . '</span>';
        $this->load->view('vote-result', $data);
    }

    // 查询是否已有投票记录
    public function findVoteRecord($_id, $add_info = array()) {
        $voter_info = array(
            "form_id" => (string) $_id,
            "site_id" => $this->site_id,
            "account_id" => time(),
            // "account_id" =>  (string)$this->vals['user_profile']['account_id'],
            //"name" => $this->vals['user_profile']['name'],
            "name" => '热心网友',
            "ipaddress" => $this->input->ip_address(),
            "status" => false
        );
        $voter_info = array_merge($voter_info, $add_info);
        $this->load->model('interaction_vote_log_model', 'interaction_vote_log');
        $old = $this->interaction_vote_log->find($voter_info, 1);
        return array($old, $voter_info);
    }

    protected function getCustomData($_POST) {
        $this->load->helper("ikode");

        $data = array();
        if (empty($_POST)) {
            return $data;
        }
        $i = 0;
        foreach ($_POST as $key => $val) {
            $keys = explode("_", $key);
            if (strcasecmp($keys[0], "field") != 0) {
                continue;
            }
            if (!is_numeric($keys[1])) {
                continue;
            }
            if (is_array($val)) {
                $val = implode(",", $val);
            } else {
                $val = cleanString($val);
            }
            if ($val === "") { //if (empty($val)) { $val 从0 开始
                $val = "-";
            }

            $data[$keys[1]] = $val;
        }
        return $data;
    }

    protected function checkCaptcha($input_chars) {
        $this->load->library('session');

        if (empty($input_chars)) {
            return false;
        }

        $captcha_chars = $this->session->userdata("captcha_chars");

        if (strcasecmp($captcha_chars, $input_chars) == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function addVoteStatData($site_id, $form_id, $voter_id) {
        $this->load->model('interaction_vote_model', 'interaction_vote');
        $ret = $this->interaction_vote->incCounter(array("_id" => $form_id, "site_id" => $site_id), array("voter_count" => 1), 1);

        $this->load->model('interaction_vote_data_model', 'interaction_vote_data');
        $this->load->model('interaction_vote_result_model', 'interaction_vote_result');
        $data_list = $this->interaction_vote_data->find(array("site_id" => $site_id, "form_id" => $form_id, "voter_id" => $voter_id), null);

        $filter_list = array("site_id" => $site_id, "form_id" => $form_id);
        foreach ($data_list as $key => $val) {
            $value_field_type = $val['field_type'];
            $field_value = $val['field_value'];

            $filter_list['field_id'] = $val['field_id'];
            $filter_list['field_type'] = $val['field_type'];
            if (($value_field_type == "text") || ($value_field_type == "textarea")) {
                $filter_list['field_value_index'] = 0;
                $stat_data = $this->interaction_vote_result->find($filter_list, 1);
                if (empty($stat_data)) {
                    $my_data = array(
                        "site_id" => $site_id,
                        "form_id" => $form_id,
                        "field_id" => $val['field_id'],
                        "field_type" => $val['field_type'],
                        "field_value_index" => 0,
                        "field_value_total" => 1,
                        "remark" => "",
                        "is_final_choice" => false,
                        "create_date" => time(),
                        'creator' => array('id' => $this->vals['user_profile']['account_id'], 'name' => $this->vals['user_profile']['name'])
                    );
                    $ret = $this->interaction_vote_result->create($my_data);
                } else {
                    $ret = $this->interaction_vote_result->incCounter(array("_id" => $stat_data['_id']), array("field_value_total" => 1), 1);
                }
            } else {
                $field_value_arr = (array) explode(",", $field_value);
                foreach ($field_value_arr as $v) {
                    $filter_list['field_value_index'] = $v;
                    $stat_data = $this->interaction_vote_result->find($filter_list, 1);
                    if (empty($stat_data)) {
                        $my_data = array(
                            "site_id" => $site_id,
                            "form_id" => $form_id,
                            "field_id" => $val['field_id'],
                            "field_type" => $val['field_type'],
                            "field_value_index" => $v,
                            "field_value_total" => 1,
                            "remark" => "",
                            "is_final_choice" => false,
                            "create_date" => time(),
                            'creator' => array('id' => $this->vals['user_profile']['account_id'], 'name' => $this->vals['user_profile']['name'])
                        );
                        $ret = $this->interaction_vote_result->create($my_data);
                    } else {
                        $ret = $this->interaction_vote_result->incCounter(array("_id" => $stat_data['_id']), array("field_value_total" => 1), 1);
                    }
                }
            }
        }
    }

    // 身份证号验证
    function checkIdCard($idcard) {
        if (empty($idcard)) {
            return false;
        }
        $City = array(11 => "北京", 12 => "天津", 13 => "河北", 14 => "山西", 15 => "内蒙古", 21 => "辽宁", 22 => "吉林", 23 => "黑龙江", 31 => "上海", 32 => "江苏", 33 => "浙江", 34 => "安徽", 35 => "福建", 36 => "江西", 37 => "山东", 41 => "河南", 42 => "湖北", 43 => "湖南", 44 => "广东", 45 => "广西", 46 => "海南", 50 => "重庆", 51 => "四川", 52 => "贵州", 53 => "云南", 54 => "西藏", 61 => "陕西", 62 => "甘肃", 63 => "青海", 64 => "宁夏", 65 => "新疆", 71 => "台湾", 81 => "香港", 82 => "澳门", 91 => "国外");
        $iSum = 0;
        $idCardLength = strlen($idcard);
        //长度验证
        if (!preg_match('/^\d{17}(\d|x)$/i', $idcard) and !preg_match('/^\d{15}$/i', $idcard)) {
            return false;
        }
        //地区验证
        if (!array_key_exists(intval(substr($idcard, 0, 2)), $City)) {
            return false;
        }
        // 15位身份证验证生日，转换为18位
        if ($idCardLength == 15) {
            $sBirthday = '19' . substr($idcard, 6, 2) . '-' . substr($idcard, 8, 2) . '-' . substr($idcard, 10, 2);
            $d = new DateTime($sBirthday);
            $dd = $d->format('Y-m-d');
            if ($sBirthday != $dd) {
                return false;
            }
            $idcard = substr($idcard, 0, 6) . "19" . substr($idcard, 6, 9); //15to18
            $Bit18 = getVerifyBit($idcard); //算出第18位校验码
            $idcard = $idcard . $Bit18;
        }
        // 判断是否大于2078年，小于1900年
        $year = substr($idcard, 6, 4);
        if ($year < 1900 || $year > 2078) {
            return false;
        }

        //18位身份证处理
        $sBirthday = substr($idcard, 6, 4) . '-' . substr($idcard, 10, 2) . '-' . substr($idcard, 12, 2);
        $d = new DateTime($sBirthday);
        $dd = $d->format('Y-m-d');
        if ($sBirthday != $dd) {
            return false;
        }
        //身份证编码规范验证
        $idcard_base = substr($idcard, 0, 17);
        if (strtoupper(substr($idcard, 17, 1)) != getVerifyBit($idcard_base)) {
            return false;
        } else {
            return true;
        }
    }

    function getIP() {
        $ip = '';
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else
            $ip = "Unknow";
        return $ip;
    }

}

?>

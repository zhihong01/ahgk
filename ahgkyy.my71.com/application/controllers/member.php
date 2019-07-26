<?php

session_start();

class member extends MY_Controller {
 protected $supervision_status = array(
        '[未审核]',
        '[未受理]',
        '[受理中]',
        '[已处理]',
        '[再追问]',
        '[已解决]'
    ); 
	/*  protected $supervision_status = array('<font color="#936216">[未审核]</font>', '<font color="#52ADF2">[未处理]</font>', '<font color="#ff0000">[处理中]</font>', '<font color="#36BD53">[已回复]</font>', '<font color="#D3C01C">[再追问]</font>', '<font color="#999">[已解决]</font>'); */

    public function __construct() {
        parent::__construct();
        $this->load->model('site_account_model', 'site_account');
    }

    public function index() {
        $account_id = $_SESSION['account_id'];
        $nickname = $_SESSION['nickname'];
        if (empty($account_id)) {
            header("Location: /nocache/login/");
        }
        $member = $this->site_account->find(array('_id' => $account_id), 1);
        $member['last_time'] = ($member['last_time']) ? date('Y-m-d H:i', $member['last_time']) : '';
        $member['login_count'] = $member['login_count'];
        if ($member['gender'] == 1) {
            $member['sex'] = '男';
        } else if ($member['gender'] == 2) {
            $member['sex'] = '女';
        } else {
            $member['sex'] = '保密';
        }
        $member['address'] = $member['address']['province'] . '' . $member['address']['city'] . '' . $member['address']['area'] . '' . $member['address']['street'];
        $data['member'] = $member;
        $admin_type = $_SESSION['type'];
        if (!empty($account_id)) {
            if ($admin_type == 2) {
                $data['location'] = '<a href="/">首页</a> ><span>用户中心</span>';
				// $data['location'] = '<a href="/">首页</a> > <a href="/interaction/">政民互动</a> > <span>用户中心</span>';
                $View = new Blitz('template/member/member.html');
            } else {
                $data['location'] = '<a href="/">网站首页</a> &gt; <span>部门管理员</span>';
                $View = new Blitz('template/admin/admin.html');
            }
        }
        $View->display($data);
    }

    public function register() {
        if ($this->input->server('REQUEST_METHOD') != 'POST') {
            $View = new Blitz('template/member/register.html');
            $data['rand'] = rand(0, 9);
            $data['location'] = '<a href="/">首页</a> ><span>用户注册</span>';
			 // $data['location'] = '<a href="/">首页</a> > <a href="/interaction/">政民互动</a> > <span>用户注册</span>';
            $View->display($data);
        } else {
            $member = $this->security->xss_clean($this->input->post('member'));
            $nickname = trim($member['nickname']);
            $password = $member['password'];
            $repassword = $member['confirm'];
            $vcode = $this->input->post('vcode');
            $email = strtolower(trim($member['email']));
            $phone = $member['phone'];
           
            if (strcasecmp($_SESSION['captcha_chars'], $vcode)) {
                $this->resultJson("验证码不正确", 3);
            }
            if ($password != $repassword) {
                $this->resultJson('密码和重复密码不一致！', 3);
            }
            if (mb_strlen($nickname) < 2) {
                $this->resultJson('昵称不能小于 2个字符', 3);
            }
            if (mb_strlen($password) < 8) {
                $this->resultJson('密码不能小于 6个字符', 3);
            }
            if (!$this->valid_email($email)) {
                //$this->resultJson('您输入的邮件地址无效', 3);
            }
            if (!is_numeric($phone)) {
                $this->resultJson('请输入有效的手机号码', 3);
            }
            $account_nickname = $this->site_account->find(array('nickname' => $nickname), 1);
            if ($account_nickname) {
                $this->resultJson('您输入的名称已经被使用,请重新输入', 3);
            }
            $account_email = $this->site_account->find(array('email' => $email), 1);
            if ($email&&$account_email) {
                $this->resultJson('您输入的邮箱已经被使用,请重新输入', 3);
            }
            $rand_key = $this->randomkeys(6);
            $data = array(
                'nickname' => $nickname,
                'password' => $this->encryptPass($password,''),
                'name' => $member['name'],
                'email' => $email,
                'site_id' => $this->site_id,
                'client_ip' => $this->input->ip_address(),
                'create_date' => time(),
                'rand_key' => $rand_key,
                'status' => true
            );
            $this->load->model('member_register_tmp_model', 'member_register_tmp');
            $this->member_register_tmp->update(array('email' => $email, 'site_id' => $this->site_id), $data, array('upsert' => TRUE, 'status' => true));
            //导入会员信息        
            $datas = array(
                'password' => $data['password'],
                'activated' => true,
                'email' => $data['email'],
                'site_id' => $data['site_id'],
                'create_date' => $data['create_date'],
                'nickname' => $data['nickname'],
                'name' => $data['name'],
				'IDno'=>$member['IDno'],
                'phone' => $phone,
				 'address' => array('province' => '', 'city' => '', 'area' => '', 'street' => $member['address']['street']),
                'gender' => (int) $member['gender'],
                'rand_key' => $data['rand_key'],
                'removed' => False,
                'status' => true,
                'type' => 2,
            );
            $account_id = (string) $this->site_account->create($datas);
            if ($account_id) {
                $this->load->model("site_member_model", "site_member");
                $this->site_member->create(array('account_id' => $account_id, 'site_id' => $this->site_id));
                $this->resultJson('恭喜您,用户注册成功！', 2);
            } else {
                $this->resultJson('非常抱歉！网络出问题了,请稍后再试', 3);
            }
        }
    }

    public function profile() {
        $account_id = $_SESSION['account_id'];
        $nickname = $_SESSION['nickname'];
        if (empty($account_id)) {
            header("Location: /");
            exit();
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $email = strtolower(trim($this->input->post('email')));
            $name = trim($this->input->post('name'));
            $nickname = $nickname;
            $address = array('province' => '', 'city' => '', 'area' => '', 'street' => $this->input->post('address'));
            $phone = $this->input->post('phone');
            $gender = $this->input->post('gender');
			$IDno = $this->input->post('IDno');
            if (!$this->valid_email($email)) {
                $this->resultJson('您输入的邮件地址无效');
            }
            if ($per_count == 0) {
                $per_count = 20;
            }
            $data = array(
                'name' => $name,
                'email' => $email,
                'nickname' => $nickname,
                'update_date' => time(),
                'address' => $address,
                'phone' => $phone,
                'gender' => $gender,
				'IDno'=>$IDno
            );

            $this->load->model('site_account_model', 'site_account');
            $site_account = $this->site_account->find(array('email' => $email), 1);

            if (($site_account && $site_account['_id'] != $account_id)) {
                $this->resultJson('您输入的邮件地址已经存在', 3);
            }

            $result = $this->site_account->update(array('nickname' => $nickname, 'site_id' => $this->site_id), $data);
            if (empty($result)) {
                $this->resultJson('错误，更新个人信息失败', 3);
            }

            $data = array(
                'time_zone' => $this->input->post('time_zone'),
                'per_count' => $per_count,
            );

            $this->load->model('site_member_model', 'site_member');
            $result = $this->site_member->update(array('account_id' => $account_id), $data);

            if ($result) {
                $this->resultJson('个人信息已经成功更新', 2);
            } else {
                $this->resultJson('您的个人信息修改失败', 3);
            }
        } else {

            $member = $this->site_account->find(array('_id' => $account_id, 'site_id' => $this->site_id), 1);

            if ($member['gender'] == 1) {
                $member['sex1'] = 1;
            } else if ($member['gender'] == 2) {
                $member['sex2'] = 1;
            } else {
                $member['sex0'] = 1;
            }
            $member['last_time'] = ($member['last_time']) ? date('Y-m-d H:i', $member['last_time']) : '';
            $member['login_count'] = $member['login_count'] + 1;

            $member['address'] = $member['address']['street'];

            $data['member'] = $member;
            $data['location'] = '<a href="/">首页</a> > <a href="/nocache/member/">用户中心</a> > <span>个人资料修改</span>';
			 // $data['location'] = '<a href="/">首页</a> > <a href="/interaction/">政民互动</a> > <a href="/nocache/member/">用户中心</a> > <span>个人资料修改</span>';
            $admin_type = $_SESSION['type'];
            if ($admin_type == 1) {
                $View = new Blitz('template/admin/member-profile.html');
            } else {
                $View = new Blitz('template/member/member-profile.html');
            }
            $View->display($data);
        }
    }

    public function password() {
        $account_id = $_SESSION['account_id'];
        $nickname = $_SESSION['nickname'];
        if (empty($account_id)) {
            header("Location: /");
            exit();
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $old_password = $this->input->post('oldpassword');
            $new_password = $this->input->post('newpassword');
            $comfirm_password = $this->input->post('repassword');
            if ($new_password != $comfirm_password) {
                $this->resultJson('新输入的密码和重复密码不一致');
            }
            $this->load->model('site_account_model', 'site_account');
            $account = $this->site_account->find(array('_id' => $account_id, 'site_id' => $this->site_id), 1);
            if (empty($account)) {
                $this->resultJson('用户不存在', 3);
            }
            if ($account['password'] !== $this->encryptPass($old_password,'')) {
                $this->resultJson('旧密码输入错误', 3);
            }
            $rand_key = $account['rand_key']; 
            $result = $this->site_account->update(array('_id' => $account_id), array('password' => $this->encryptPass($new_password,'')));
            if ($result) {
                $this->resultJson('密码已经被成功修改', 2);
            } else {
                $this->resultJson('密码修改错误', 3);
            }
        }else {
            $member = $this->site_account->find(array('_id' => $account_id, 'site_id' => $this->site_id), 1);
            $member['last_time'] = ($member['last_time']) ? date('Y-m-d H:i', $member['last_time']) : '';
            $member['login_count'] = $member['login_count'] + 1;
            $data['member'] = $member;
            $admin_type = $_SESSION['type'];
            if ($admin_type == 1) {
                $View = new Blitz('template/admin/member-password.html');
            } else {
                $View = new Blitz('template/member/member-password.html');
            }
			$data['location'] = '<a href="/">首页</a> ><a href="/nocache/member/">用户中心</a> > <span>密码修改</span>';
			// $data['location'] = '<a href="/">首页</a> > <a href="/interaction/">政民互动</a> > <a href="/nocache/member/">用户中心</a> > <span>密码修改</span>';
            $View->display($data);
        }
    }

    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $rand_key = $this->input->post('key');
            $password = $this->input->post('password');
            $repeat_password = $this->input->post('repassword');

            if ($password !== $repeat_password) {
                $this->resultJson('密码必须等于重复密码.');
            }

            if (mb_strlen($password) < 6) {
                $this->resultJson('密码不能小于 6个字符');
            }

            $this->load->model('reset_password_model', 'reset_password');
            $reset = $this->reset_password->find(array('rand_key' => $rand_key, 'site_id' => $this->site_id), 1);

            if (empty($reset)) {
                show_error('您的密匙无效，请到邮箱中核实！');
            }

            if ($reset['expire_date'] < time()) {
                $this->resultJson('密钥过期了');
            }

            $this->load->model('site_account_model', 'site_account');
            $result = $this->site_account->update(array('_id' => $reset["account_id"]), array('password' => $this->encryptPass($password)));

            if ($result) {
                $this->reset_password->update(array('_id' => $result['_id']), array('status' => true));

                $this->resultJson('成功', "+OK");
            } else {
                $this->resultJson('失败.');
            }
        } else {

            $rand_key = trim($this->input->get("key"));

            $this->load->model('reset_password_model', 'reset_password');
            $reset = $this->reset_password->find(array('rand_key' => $rand_key, 'site_id' => $this->site_id), 1);

            if (empty($reset)) {
                show_error('您的密匙无效，请到邮箱中核实！');
            }

            //读取版块信息
            $group_info = $this->getGroupInfo();
            $this->vals['group_top_list'] = $group_info['group_top_list'];
            $this->vals['current_group'] = $group_info['current_group'];

            $this->vals['rand_key'] = $rand_key;

            $View = new Blitz('template/member/memberResetPassword.html');
            $View->display($this->vals);
        }
    }

    public function lostPassword() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $nickname = trim($this->input->post("nickname"));
            $email = strtolower(trim($this->input->post("email")));

            if ($nickname == '' && $email == '') {
                $this->resultJson('昵称和电子邮件地址必须填写一项！');
            }

            $this->load->model('site_account_model', 'site_account');
            if ($nickname != '') {
                $account = $this->site_account->find(array('nickname' => $nickname), 1);
                if (empty($account)) {
                    $this->resultJson('昵称不存在！');
                }
            } else {
                $account = $this->site_account->find(array('email' => $email), 1);
                if (empty($account)) {
                    $this->resultJson('电子邮件地址不存在！');
                }
            }

            $account_id = (string) $account['_id'];

            $this->load->model('reset_password_model', 'reset_password');
            $reset_key = $this->randKey();
            while ($this->reset_password->find(array('rand_key' => $reset_key), 1)) {
                $reset_key = $this->randKey();
            }

            $data = array(
                'rand_key' => $reset_key,
                'expire_date' => time() + 24 * 3600,
                'account_id' => $account_id,
                'status' => 0,
            );

            $this->reset_password->update(array('account_id' => $account_id, 'site_id' => $this->site_id), $data, array('upsert' => TRUE));

            // send mail to submitter

            $this->load->model('email_template_model', 'email_template');
            $template = $this->email_template->find(array('key_word' => "reset_password"), 1);

            if ($template) {
                $email_tags = array(
                    "MEMBER_NAME",
                    "RESET_PASSWORD_URL",
                );

                $replace_str = array(
                    $account["name"],
                    $this->vals['setting']['soft_url'] . "index.php?c=member&m=setPassword&reset_key=" . $reset_key,
                );

                $template["content"] = str_replace($email_tags, $replace_str, $template["content"]);
                $this->load->library('email');
                $this->email->from('noreply@ishang.net', '系统管理员');
                $this->email->to($email);

                $this->email->subject($template["subject"]);
                $this->email->message($template["content"]);

                $this->email->send();
            }

            $this->resultJson('成功：验证密钥已经发送到您的邮箱。 ', "+OK");
        } else {
            //读取版块信息
            $group_info = $this->getGroupInfo();
            $this->vals['group_top_list'] = $group_info['group_top_list'];
            $this->vals['current_group'] = $group_info['current_group'];

            $View = new Blitz('template/member/memberLostPassword.html');
            $View->display($this->vals);
        }
    }

    public function logout() {
        unset($_SESSION['nickname']);
        unset($_SESSION['account_id']);
        unset($_SESSION['email']);
        unset($_SESSION['avatar']);
        unset($_SESSION['logged']);
        header("Location: /nocache/login/");
    }

    public function interaction() {
        $account_id = $_SESSION['account_id'];
        $nickname = $_SESSION['nickname'];
        if (empty($account_id)) {
            header("Location: /");
            exit();
        }
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $this->load->model('supervision_model', 'supervision');
        $this->load->model('supervision_question_model', 'supervision_question');
        $total_row = $this->supervision->count(array("member_id" => $account_id, "site_id" => $this->site_id, "removed" => False));

        $View = new Blitz('template/member/member-interaction.html');
        $struct_list = $View->getStruct();
        $data = array();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');

                //列表
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $_id_array = $branch_id;
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    //$item_list = $this->supervision->find(array('create.name' => $nickname, 'site_id' => $this->site_id), $limit, $offset, $arr_sort, $select);
                    $item_list = $this->supervision->find(array("member_id" => $account_id, 'site_id' => $this->site_id, "removed" => False), $limit, $offset);
                    foreach ($item_list as $key => $item) {
                        if (!empty($item['question_id'])) {
                            $question = $this->supervision_question->find(array('_id' => (string) $item['question_id']));
                            $item_list[$key]['question_name'] = $question['name'];
                        }
                        switch ($item['process_status']) {
                            case '1':
                                $item_list[$key]['process_status'] = '<font color=#52ADF2>未处理</font>';
                                break;
                            case '2':
                                $item_list[$key]['process_status'] = '<font color=#ff0000>处理中</font>';
                                break;
                            case '3':
                                $item_list[$key]['process_status'] = '<font color=#36BD53>已回复</font>';
                                break;
                            case '4':
                                $item_list[$key]['process_status'] = '<font color=#D3C01C>再追问</font>';
                                break;
                            case '5':
                                $item_list[$key]['process_status'] = '<font color=#999>已解决</font>';
                                break;
                            default :
                                $item_list[$key]['process_status'] = '<font color=#936216>未审核</font>';
                                break;
                        }
						
						
						

                        if ($item['confirmed'] == false && !empty($item['confirm_remark'])) {
                            $item_list[$key]['status'] = '<span style="color:gray;">未通过</span>';
                        }

                        $item_list[$key]['_id'] = (string) ($item['_id']);
                        $item_list[$key]['url'] = '/supervision/detail/' . $item['_id'] . ".html";
                        if (mb_strlen($item['subject']) > $length) {
                            $item_list[$key]['short_title'] = mb_substr($item['subject'], 0, $length) . '...';
                        } else {
                            $item_list[$key]['short_title'] = $item['subject'];
                        }
                        $item_list[$key]['title'] = $item['subject'];
                        $item_list[$key]['date'] = date("Y-m-d", $item['create_date']);
                    }
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

        $member = $this->site_account->find(array('nickname' => $nickname, 'site_id' => $this->site_id), 1);

        if ($member['gender'] == 1) {
            $member['sex1'] = 1;
        } else if ($member['gender'] == 2) {
            $member['sex2'] = 1;
        } else {
            $member['sex0'] = 1;
        }
        $member['last_time'] = ($member['last_time']) ? date('Y-m-d H:i', $member['last_time']) : '';
        $member['login_count'] = $member['login_count'] + 1;
        $member['addredd'] = $member['address']->province . ' ' . $member['address']->city . ' ' . $member['address']->area . ' ' . $member['address']->street;
        //print_r($member);
        $data['member'] = $member;

        $data['location'] = '<a href="/">首页</a> > <a href="/nocache/member/">用户中心</a> > <span>我的互动信息</span>';
		 // $data['location'] = '<a href="/">首页</a> > <a href="/interaction/">政民互动</a> > <a href="/nocache/member/">用户中心</a> > <span>我的互动信息</span>';
        $View->display($data);
    }

    public function interactionDetail() {
        $_id = (string) $this->input->get('_id');
        $this->load->model('supervision_model', 'supervision');
        $supervision = $this->supervision->find(array("_id" => $_id, 'removed' => false));

        if (empty($supervision)) {
            show_404();
        }
        $account_id = $_SESSION['account_id'];
        $login_status = FALSE;
        if ($account_id) {
            $login_status = TRUE;
            //获取网站会员信息
            $this->load->model('site_account_model', 'site_account');
            $account = $this->site_account->find(array('_id' => $account_id, 'site_id' => $this->site_id), 1);
            $data['account'] = $account;
        }
        $data['login_status'] = $login_status;
        $data['rand'] = rand(0, 9);
        $this->supervision->update(array("_id" => $_id), array("hit" => $supervision['hit'] + 1));
        $supervision['date'] = date("Y-m-d H:i:s", $supervision['create_date']);
		//移动时间
        $supervision['change_branch_time'] = $supervision['change_branch_time']?date("Y-m-d H:i:s", $supervision['change_branch_time']):"";
        //审核时间
        $supervision['confirm_date'] = $supervision['confirm_date']?date("Y-m-d H:i:s", $supervision['confirm_date']):"";
        //受理时间
        $supervision['reply_date'] = $supervision['reply_date']?date("Y-m-d H:i:s", $supervision['reply_date']):"";
        if ($supervision['process_status'] == 1) {
            $supervision['process_status'] = '<span class="red">未受理</span>';
        } elseif ($supervision['process_status'] == 2) {
            $supervision['process_status'] = '<span class="red">受理中</span>';
        } elseif ($supervision['process_status'] == 3) {
            $supervision['process_status'] = '<span class="red">已处理</span>';
        } elseif ($supervision['process_status'] == 4) {
            $supervision['process_status'] = '<span class="red">再追问</span>';
        } elseif ($supervision['process_status'] == 5) {
            $supervision['process_status'] = '<span class="red">已解决</span>';
        } else {
            $supervision['process_status'] = '<span class="red">未知</span>';
        }
        $supervision['message'] = str_replace("[", "<", $supervision['message']);
        $supervision['message'] = str_replace("]", ">", $supervision['message']);
        $data['supervision'] = $supervision;
        $View = new Blitz('template/detail-supervision.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $this->load->model('supervision_reply_model', 'supervision_reply');
        $reply = $this->supervision_reply->find(array("supervision_id" => $_id, 'status' => true), NULL, NULL, "*", array("create_date" => "ASC"));

        if ($View->hasContext('reply')) {
            if (!empty($reply)) {
                foreach ($reply as $key => $item) {
                    //预定义是否有满意度评论为false;
                    $reply[$key]['rating'] = $reply[$key]['israting'] = FALSE;
                    //回复人
                    $user = $this->getUser($item['user_id']);
                    if (!empty($user)) {
                        if ($user['type'] == 1) {
                            // 回复单位
                            if (isset($this->branch_list[$supervision['branch_id']])) {
                                $reply[$key]['user'] = "回复单位：" . $this->branch_list[$supervision['branch_id']];
                            } else {
                                $reply[$key]['user'] = '回复单位：宣城市人民政府网站';
                            }
                            //回复模式
                            $reply[$key]['manner'] = "回复：";
                            $reply[$key]['branch_id'] = $supervision['branch_id'];
                            $reply[$key]['supervision_id'] = $supervision['_id'];
                            if ($account_id && $account_id == $supervision['member_id']) {
                                if ((int) $supervision['rating'] > 0) {
                                    //判断用户是否已评论满意度
                                    $reply[$key]['israting'] = TRUE;
                                    $reply[$key]['rating_str'] = $this->supervision_rating[$supervision['rating']];
                                }
                                //当前会员存在，并且该信件是该会员的信件（会员具有评论回复是否满意）
                                $reply[$key]['rating'] = TRUE;
                            }
                        } else {
                            if ($user['nickname']) {
                                $reply[$key]['user'] = "回复用户：" . $user['nickname'];
                            } else {
                                $reply[$key]['user'] = "回复用户：" . $user['name'];
                            }
                            //回复模式
                            $reply[$key]['manner'] = "再追问：";
                        }
                    } else {
                        $reply[$key]['user'] = '回复单位：宣城市人民政府网站';
                    }
                    $reply[$key]['message'] = str_replace("[", "<", $item['message']);
                    $reply[$key]['message'] = str_replace("]", ">", $reply[$key]['message']);
                    $View->block('/reply', array(
                        '_id' => $item['_id'],
                        'date' => date("Y-m-d H:i:s", $item['create_date']),
                        'branch' => $reply[$key]['branch'],
                        'branch_id' => $reply[$key]['branch_id'],
                        'supervision_id' => $reply[$key]['supervision_id'],
                        'rating' => $reply[$key]['rating'],
                        'israting' => $reply[$key]['israting'],
                        'rating_str' => $reply[$key]['rating_str'],
                        'user' => $reply[$key]['user'],
                        'manner' => $reply[$key]['manner'],
                        'message' => $reply[$key]['message'],
                    ));
                }
            }
        }
        $data['location'] = '<a href="/">宣城</a> / <a href="/member/">用户中心</a> / <span>互动信件</span>';

        $View->display($data);
    }

    public function interactionReply() {

        $this->load->model('supervision_reply_model', 'supervision_reply');
        $this->load->model('supervision_model', 'supervision');

        $account_id = $_SESSION['account_id'];
        $nickname = $_SESSION['nickname'];
        if (empty($account_id)) {
            $this->resultJson('请登录');
        }

        $message = htmlspecialchars($this->input->post('message'));
        $mail_id = $this->input->post('mail_id');

        if (mb_strlen($message) < 20) {
            $this->resultJson('追问内容不得少于20个字');
        }

        $supervision_reply = array(
            'supervision_id' => $mail_id,
            'message' => $message,
            'type' => 2,
            'user_id' => $account_id,
            'rand_key' => $this->randKey(),
            'create_date' => time(),
            'update_date' => ''
        );

        $supervision_reply_id = $this->supervision_reply->create($supervision_reply);
        // 更新留言状态
        $this->load->model('supervision_model', 'supervision');
        $supervision_id = $this->supervision->update(array('_id' => $mail_id), array('status' => 4));

        if (empty($supervision_reply_id)) {
            $this->resultJson('留言创建失败.');
        }

        $this->resultJson('留言追问成功.', "+OK");
    }

    public function interactionEnd() {

        $account_id = $_SESSION['account_id'];
        $nickname = $_SESSION['nickname'];
        if (empty($account_id)) {
            $this->resultJson('请登录');
        }

        $this->load->model('supervision_rating_stat_model', 'supervision_rating_stat');
        $this->load->model('supervision_model', 'supervision');

        $mail_id = $this->input->post('mail_id');
        $branch_id = $this->input->post('branch_id');
        $current_rating = (int) $this->input->post('current_rating');
        $rating = (int) $this->input->post('rating');

        $supervision_id = $this->supervision->update(array('_id' => $mail_id), array('status' => 5, 'rating' => $rating + $current_rating));

        $result = $this->supervision_rating_stat->find(array('branch_id' => $branch_id));
        if ($rating == 2) {
            $good = 1;
            $notbad = 0;
            $bad = 0;
        } else if ($rating == 1) {
            $good = 0;
            $notbad = 1;
            $bad = 0;
        } else {
            $good = 0;
            $notbad = 0;
            $bad = 1;
        }

        /* if(!empty($result)){
          $result = $this->supervision_rating_stat->update(array('_id' => $result['_id']), array('type3' => $result['type3'] + $good, 'type2' => $result['type2'] + $notbad, 'type1' => $bad['type1'] + $bad));
          }else{
          $this->supervision_rating_stat->create(array('branch_id' => $branch_id, 'type3' => $good, 'type2' => $notbad, 'type1' => $bad));
          } */
        $this->supervision_rating_stat->create(array('branch_id' => $branch_id, 'type3' => $good, 'type2' => $notbad, 'type1' => $bad));
        $this->resultJson('操作成功，此次互动已结束.', "+OK");
    }

    private function getUser($user_id) {
        if (empty($user_id)) {
            return FALSE;
        }
        $this->load->model('site_account_model', 'site_account');
        $user = $this->site_account->find(array('_id' => $user_id, 'site_id' => $this->site_id), 1);
        if (!empty($user)) {
            return $user;
        } else {
            return FALSE;
        }
    }

    public function admin() {
        $account_id = $_SESSION['account_id'];
        $account_type = $_SESSION['type'];
        $nickname = $_SESSION['nickname'];
        if (empty($account_id) || $account_type != 1) {
            $View = new Blitz('template/member/login.html');
            $this->data['location'] = '<a href="/">网站首页</a> / <span>用户登录</span>';
        } else {
            $View = new Blitz('template/admin/admin.html');
            $member = $this->site_account->find(array('_id' => $account_id), 1);
            $member['last_time'] = ($member['last_time']) ? date('Y-m-d H:i', $member['last_time']) : '';
            $member['login_count'] = $member['login_count'];
            if ($member['gender'] == 1) {
                $member['sex'] = '男';
            } else if ($member['gender'] == 2) {
                $member['sex'] = '女';
            } else {
                $member['sex'] = '保密';
            }
            $member['address'] = $member['address']['province'] . '' . $member['address']['city'] . '' . $member['address']['area'] . '' . $member['address']['street'];
            $this->data['member'] = $member;
            $this->data['location'] = '<a href="/">网站首页</a> / <span>部门管理员</span>';
        }
        $View->display($this->data);
    }

    // 信箱信息类别
    protected function itemQuestion() {
        $this->load->model('supervision_question_model', 'supervision_question');
        $item_list = $this->supervision_question->find(array('removed' => false, 'site_id' => $this->site_id), null, NULL, "*", array("create_date" => "DESC"));
        return $item_list;
    }

    // 获取互动信件列表
    protected function itemSupervision($filter, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('supervision_model', 'supervision');
        $select = array('_id', 'process_status', 'subject', 'create_date', 'branch_id', 'no', 'hit', 'question_id');
        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $item_list = $this->supervision->find($filter, $limit, $offset, $select, $arr_sort);
        $question_list = array();
        $question = $this->itemQuestion();
        foreach ($question as $item) {
            $question_list[(string) $item['_id']] = $item['name'];
        }
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
            switch ($item['process_status']) {
                case '1':
                    $item_list[$key]['action'] = '<span class="action-audit" style="cursor: pointer; color: red;" data-url="/member/audit/' . $item['_id'] . '/">[受理]</span>';
                    break;
                case '2':
                    $item_list[$key]['action'] = '<a style="cursor: pointer; color: green;" href="/member/reply/' . $item['_id'] . '/">[回复]</a>';
                    break;
                case '3':
                    $item_list[$key]['action'] = '<a style="cursor: pointer; color: green;" href="/member/reply/' . $item['_id'] . '/">[追加回复]</a>';
                    break;
                case '4':
                    $item_list[$key]['action'] = '<span style="cursor: pointer; color: #00a1e9;">[再追问]</span>';
                    break;
                case '5':
                    $item_list[$key]['action'] = '<span style="cursor: pointer; color: #3a3a3a;">[已解决]</span>';
                    break;
            }
            $item_list[$key]['question_name'] = $question_list[(string) $item['question_id']];
            // 取部门
            if (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['branch_id']];
            } else {
                $item_list[$key]['branch'] = '';
            }
            $item['subject'] = strip_tags(html_entity_decode($item['subject']));
            if (mb_strlen($item['subject']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['subject'], 0, $length);
            } else {
                $item_list[$key]['short_title'] = $item['subject'];
            }
            $item_list[$key]['title'] = $item['subject'];
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
        }
        return $item_list;
    }

    public function supervision() {
        $account_id = $_SESSION['account_id'];
        $branch_id = $_SESSION['branch_id'];
        $account_type = $_SESSION['type'];
        $nickname = $_SESSION['nickname'];
        $page = (int) $this->input->get('page');
        if ($page < 1) {
            $page = 1;
        }
        if (empty($account_id) && $account_type != 1) {
            $View = new Blitz('template/member/login.html');
            $this->data['location'] = '<a href="/">网站首页</a> / <span>用户登录</span>';
        } else {
            $member = $this->site_account->find(array('nickname' => $nickname), 1);
            $member['last_time'] = ($member['last_time']) ? date('Y-m-d H:i', $member['last_time']) : '';
            $member['login_count'] = $member['login_count'];
            if ($member['gender'] == 1) {
                $member['sex'] = '男';
            } else if ($member['gender'] == 2) {
                $member['sex'] = '女';
            } else {
                $member['sex'] = '保密';
            }
            $member['address'] = $member['address']['province'] . '' . $member['address']['city'] . '' . $member['address']['area'] . '' . $member['address']['street'];
            $this->data['member'] = $member;
            $View = new Blitz('template/admin/supervision.html');
            $struct_list = $View->getStruct();
            //判断用户身份(管理员)
            $this->load->model('site_user_model', 'site_user');
            $site_user = $this->site_user->find(array("account_id" => $account_id), 1);
            if ($site_user['privilege_id'] == "53d1c7d59a05c20f4015125f") {
                //网站管理员
                $filter = array('process_status' => array("\$lte" => 5), 'cancelled' => false, 'status' => TRUE, 'removed' => False, 'site_id' => $this->site_id);
            } else {
                $filter = array("branch_id" => $branch_id, 'process_status' => array("\$lte" => 3), 'cancelled' => false, 'status' => TRUE, 'removed' => False, 'site_id' => $this->site_id);
            }
            $this->load->model('supervision_model', 'supervision');
            $total_row = $this->supervision->listCount(NULL, $filter);
            foreach ($struct_list as $struct) {
                $matches = array();
                if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                    $action = $matches[1];
                    $struct_val = trim($matches[0], '/');
                    $item_list = '';
                    //列表
                    if ($action == 'supervision') {
                        list($supervision_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                        if ($offset == 'page') {
                            $offset = $limit * ($page - 1);
                        }
                        if ($site_user['privilege_id'] !== "53d1c7d59a05c20f4015125f") {
                            $filter["branch_id"] = $branch_id;
                        }
                        $item_list = $this->itemSupervision($filter, $limit, $offset, $length, $sort_by, $date_format);
                    } elseif ($action == 'list') {
						list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
						$_id_list = explode('-', $channel_id);
						$item_list = $this->newList($_id_list, $limit, $offset, $length, $date_format, $description_length);
					}  elseif ($action == 'page') {
                        $per_count = (int) $matches[2];
                        if ($per_count == 0) {
                            $per_count = 20;
                        }
                        $link = $this->getPagination($total_row, $page, $per_count);
                        $item_list['page'] = $link;
                    }
                    $this->data[$struct_val] = $item_list;
                }
            }
        }
        $View->display($this->data);
    }

    public function audit() {
        $account_id = $_SESSION['account_id'];
        $branch_id = $_SESSION['branch_id'];
        $account_type = $_SESSION['type'];
        $nickname = $_SESSION['nickname'];
        if (empty($account_id) || $account_type != 1 || empty($branch_id)) {
            $this->resultJson("非法身份，禁止操作", 3);
        }
        $id = $this->security->xss_clean($this->input->get('_id'));
        $this->load->model('supervision_model', 'supervision');
        $supervision = $this->supervision->find(array("_id" => $id), 1);
        if (empty($id) || empty($supervision)) {
            $this->resultJson("信息有误，操作失败", 3);
        }

        $confirmer = array("id" => $account_id, "name" => $nickname);
        $system_time = time();
        $data = array(
            "process_status" => 2,
            "confirm_date" => empty($supervision['confirm_date'])?$system_time:$supervision['confirm_date'],
			"reply_date" => $system_time,
            "update_date" => $system_time,
            "confirm_remark" => "受理操作 审核时间" . date("Y-m-d H:i:s"),
            "confirmer" => $confirmer,
            "status" => true,
        );
        if ($this->supervision->update(array("_id" => $id), $data)) {
            $this->resultJson("受理成功", 2);
        } else {
            $this->resultJson("操作失败", 3);
        }
    }

    public function reply() {
        $account_id = $_SESSION['account_id'];
        $branch_id = $_SESSION['branch_id'];
        $account_type = $_SESSION['type'];
        $nickname = $_SESSION['nickname'];
        if (empty($account_id) || $account_type != 1) {
            header("Location: /	");
        }
        $id = $this->security->xss_clean($this->input->get('_id'));
        $this->load->model('supervision_model', 'supervision');
        $supervision = $this->supervision->find(array("_id" => $id), 1);
        if (empty($id) || empty($supervision)) {
            //show_error("非法身份，禁止操作");
			header("Location: /	");
        }
        $View = new Blitz('template/admin/reply.html');
        $member = $this->site_account->find(array('nickname' => $nickname), 1);
        $member['last_time'] = ($member['last_time']) ? date('Y-m-d H:i', $member['last_time']) : '';
        $member['login_count'] = $member['login_count'];
        if ($member['gender'] == 1) {
            $member['sex'] = '男';
        } else if ($member['gender'] == 2) {
            $member['sex'] = '女';
        } else {
            $member['sex'] = '保密';
        }
        $member['address'] = $member['address']['province'] . '' . $member['address']['city'] . '' . $member['address']['area'] . '' . $member['address']['street'];
        $this->data['member'] = $member;
        $supervision['_id'] = (string) $supervision['_id'];
        $supervision['message'] = strip_tags(htmlspecialchars_decode($supervision['message']));
        //是否有附件
        $is_attach = FALSE;
        $attach_record = $this->getFileFSAttach($supervision['supervision_attach_id']);
        if (!empty($attach_record)) {
            $is_attach = TRUE;
            $image_type = array(
                "image/gif",
                "image/pgif",
                "image/x-gif",
                "image/jpeg",
                "image/pjpeg",
                "image/png",
                "image/x-png",
            );
            $is_image = FALSE;
            if (in_array($attach_record->file['contentType'], $image_type)) {
                $is_image = TRUE;
            }
            $this->data['is_image'] = $is_image;
            $this->data['attach'] = array(
                'filename' => $attach_record->file['filename'],
                'size' => (int) ($attach_record->file['size'] / 1024),
                'contentType' => $attach_record->file['contentType'],
                'downloadUrl' => '/index.php?c=member&m=downloadFS&attach_id=' . $attach_record->file['_id'],
                'picUrl' => '/index.php?c=member&m=getImage&_id=' . $attach_record->file['_id'],
            );
        }
        $this->data['is_attach'] = $is_attach;
        //回复内容
        $this->load->model('supervision_reply_model', 'supervision_reply');
        $reply = $this->supervision_reply->find(array("supervision_id" => $id, 'status' => true), NULL, NULL, "*", array("create_date" => "ASC"));

        if ($View->hasContext('reply')) {
            if (!empty($reply)) {
                foreach ($reply as $key => $item) {
                    //回复内容中是否有附件
                    $is_attach_reply = FALSE;
                    $attach_record_reply = $this->getFileFSAttach($item['supervision_attach_id']);
                    if (!empty($attach_record_reply)) {
                        $is_attach_reply = TRUE;
                        $image_type = array(
                            "image/gif",
                            "image/pgif",
                            "image/x-gif",
                            "image/jpeg",
                            "image/pjpeg",
                            "image/png",
                            "image/x-png",
                        );
                        $is_image_reply = FALSE;
                        if (in_array($attach_record_reply->file['contentType'], $image_type)) {
                            $is_image_reply = TRUE;
                        }
                        $reply[$key]['is_image_reply'] = $is_image_reply;
                        $reply[$key]['attach_reply'] = array(
                            'filename' => $attach_record_reply->file['filename'],
                            'size' => (int) ($attach_record_reply->file['size'] / 1024),
                            'contentType' => $attach_record_reply->file['contentType'],
                            'downloadUrl' => '/index.php?c=member&m=downloadFS&attach_id=' . $attach_record_reply->file['_id'],
                            'picUrl' => '/index.php?c=member&m=getImage&_id=' . $attach_record_reply->file['_id'],
                        );
                    }
                    $reply[$key]['is_attach_reply'] = $is_attach_reply;

                    $user = $this->getUser($item['user_id']);
                    $reply[$key]['user'] = "回复人：" . $user['nickname'];
                    $reply['message'] = str_replace('&lt;?xml:namespace prefix = o ns = "urn:schemas-microsoft-com:office:office" />', "", $reply['message']);
                    $reply[$key]['message'] = str_replace("[", "<", $item['message']);
                    $reply[$key]['message'] = str_replace("]", ">", $reply[$key]['message']);
                    $View->block('/reply', array(
                        '_id' => $item['_id'],
                        'date' => date("Y-m-d H:i:s", $item['create_date']),
                        'branch' => $reply[$key]['branch'],
                        'branch_id' => $reply[$key]['branch_id'],
                        'supervision_id' => $reply[$key]['supervision_id'],
                        'rating' => $reply[$key]['rating'],
                        'israting' => $reply[$key]['israting'],
                        'rating_str' => $reply[$key]['rating_str'],
                        'user' => $reply[$key]['user'],
                        'manner' => $reply[$key]['manner'],
                        'message' => $reply[$key]['message'],
                        'is_image_reply' => $reply[$key]['is_image_reply'],
                        'attach_reply' => $reply[$key]['attach_reply'],
                        'is_attach_reply' => $reply[$key]['is_attach_reply'],
                    ));
                }
            }
        }

        $this->data['supervision'] = $supervision;
        $View->display($this->data);
    }

    public function replyTudo() {
        $account_id = $_SESSION['account_id'];
        $branch_id = $_SESSION['branch_id'];
        $account_type = $_SESSION['type'];
        $nickname = $_SESSION['nickname'];
        if (empty($account_id) && $account_type != 1 && empty($branch_id)) {
            $this->resultJson("非法身份，禁止操作", 3);
        }
        $message = $this->security->xss_clean($this->input->post("message"));
        $id = $this->security->xss_clean($this->input->post("_id"));
        if (empty($message)) {
            $this->resultJson("信息有误，操作失败", 3);
        }
        //剔除标签
        $message = strip_tags($message);
        $this->load->model('supervision_model', 'supervision');
        $supervision = $this->supervision->find(array("_id" => $id), 1);
        if (empty($id) || empty($supervision)) {
            $this->resultJson("信息有误，操作失败", 3);
        }
        $system_time = time();
        $data = array(
            "supervision_id" => $id,
            "user_id" => $account_id,
            "message" => $message,
            "rand_key" => $this->randomkeys(12),
            "create_date" => $system_time,
            "update_date" => $system_time,
            "reply_open" => 1,
            "status" => TRUE,
            "confirm_date" => $system_time,
            "confirmer" => array(
                "id" => $account_id,
                "name" => $nickname
            )
        );
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
                    "image/x-png",
                    "application/powerpoint",
                    "text/plain",
                    "text/xml",
                    "application/msword",
                    "application/vnd.ms-powerpoint",
                    "application/octet-stream",
                    "application/kswps",
                    "application/x-zip",
                    "application/zip",
                    "application/x-zip-compressed",
                    '"application/vnd.openxmlformats-officedocument.wordprocessingml.document"',
                    '\"application/vnd.openxmlformats-officedocument.wordprocessingml.document\"',
                    "application/vnd.ms-excel",
                );
                //检查上传文件是否在允许上传的类型  
                if (!in_array($_FILES["attach"]["type"], $tp)) {
                    $this->resultJson('上传格式不对！', 4);
                }
                // mongodb 文件上传
                $fileFS = $this->supervision->gridFS();
                $size = $_FILES["attach"]["size"];
                //限制附件上传大小
                if ($size > $supervision_setting['supervision_attach_size'] * 1024) {
                    $this->resultJson('您上传的附件太大，请上传' . $supervision_setting['supervision_attach_size'] . 'KB以内文件！', 4);
                }
                $md5 = md5_file($_FILES['attach']['tmp_name']);
                // 查找文件是否已存在(查找出来的是个对象)
                $exists = $fileFS->findOne(array('md5' => $md5, 'length' => $size), array('md5'));

                if (empty($exists->file['md5'])) {
                    $data['supervision_attach_id'] = (string) $fileFS->storeFile($_FILES['attach']['tmp_name'], array('filename' => $_FILES['attach']['name'], 'contentType' => $_FILES["attach"]["type"], 'size' => $size, 'resoure' => 'attach'));
                } else {
                    $data['supervision_attach_id'] = (string) $exists->file['_id'];
                }
            }
        }

        $supervision_data = array(
            "process_status" => 3,
            "replies" => 1,
            "reply_confirmed" => true,
            //"reply_date" => $system_time,
            "update_date" => $system_time,
        );
        //针对市长信箱部门回复不自动审核
        if ($supervision['product_id'] == 2) {
            $data['status'] = FALSE;
            $supervision_data['reply_confirmed'] = FALSE;
        }
        $this->load->model('supervision_reply_model', 'supervision_reply');
        if ($this->supervision_reply->create($data)) {
            if ($this->supervision->update(array("_id" => $id), $supervision_data)) {
				//信件处理后并与是审核通过的给问政网友发送短信
                if ($data['status'] && $supervision_data['reply_confirmed']) {
                    //获取网站会员信息
                    $this->load->model('site_account_model', 'site_account');
                    $account = $this->site_account->find(array('_id' => $supervision['member_id'], 'site_id' => $this->site_id), 1);
                    if ($account['phone']) {
                        $content = $account['name'] . "（网名：" . $account["nickname"] . "）您好，您提交的主题为:“" . $supervision['subject'] . "”的问政信息己办理";
                        $this->send_sms_message($account['phone'], $content);
                    }
                }
                $this->resultJson("回复成功", 2);
            } else {
                $this->resultJson("操作失败", 3);
            }
        } else {
            $this->resultJson("操作失败", 3);
        }
    }

    protected function getFileFSAttach($attachId) {
        $this->load->model('supervision_model', 'supervision');
        try {
            $_id = new MongoId($attachId);
        } catch (MongoException $ex) {
            $_id = new MongoId();
        }
        $fileFS = $this->supervision->gridFS();
        $attach = $fileFS->findOne(array('_id' => $_id));
        return $attach;
    }

    public function getImage() {
        $_id = (string) $this->input->get('_id');
        $this->load->model('supervision_model', 'supervision');
        $attach = $this->getFileFSAttach($_id);
        // $attach = $fileFS->findOne(array('_id' => new MongoId($_id))); 
        header('Content-type: ' . $attach->file['contentType']); //输出图片头 
        echo $attach->getBytes(); //输出数据流 
    }

    public function downloadFS() {
        $_id = (string) $this->input->get('attach_id');
        $this->load->model('supervision_model', 'supervision');
        $attach = $this->getFileFSAttach($_id);
        if (empty($attach) || empty($attach->file)) {
            exit('没有找到文件内容');
        }
        $mime = $attach->file['contentType'];
        $filename = $attach->file['filename'];
        $data = $attach->getBytes();

        // Generate the server headers 
        if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE) {
            header('Content-Type: "' . $mime . '"');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header("Content-Transfer-Encoding: binary");
            header('Pragma: public');
            header("Content-Length: " . strlen($data));
        } else {
            header('Content-Type: "' . $mime . '"');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header("Content-Transfer-Encoding: binary");
            header('Expires: 0');
            header('Pragma: no-cache');
            header("Content-Length: " . strlen($data));
        }
        exit($data);
        // $this->load->helper('download'); 
        // force_download($attach->file['contentType'], $data); 
        exit();
    }

    //网络发言人
    public function content() {
        $this->load->model('content_model', 'content');
        $this->load->model("site_channel_tree_model", "site_channel_tree");
        $account_id = $_SESSION['account_id'];
        $branch_id = $_SESSION['branch_id'];
        $account_type = $_SESSION['type'];
        $nickname = $_SESSION['nickname'];
        if (empty($account_id) && $account_type != 1 && empty($branch_id)) {
            show_error("非法身份，禁止操作");
        }
        $member = $this->site_account->find(array('nickname' => $nickname), 1);
        $member['last_time'] = ($member['last_time']) ? date('Y-m-d H:i', $member['last_time']) : '';
        $member['login_count'] = $member['login_count'];
        if ($member['gender'] == 1) {
            $member['sex'] = '男';
        } else if ($member['gender'] == 2) {
            $member['sex'] = '女';
        } else {
            $member['sex'] = '保密';
        }
        $member['address'] = $member['address']['province'] . '' . $member['address']['city'] . '' . $member['address']['area'] . '' . $member['address']['street'];
        $this->data['member'] = $member;
        
        $channel_id = $this->input->get("_id");
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

        $total_row = $this->content->listCount($_id_list, NULL, array('status' => True, 'removed' => false ,"branch_id"=>$branch_id));
        $View = new Blitz('template/admin/content.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //列表
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
                }
                $this->data[$struct_val] = $item_list;
            }
        }
        $data['channel_id'] = $parent_channel['_id'];
        $data['channel_name'] = $parent_channel['name'];
        $data['menu_id'] = $channel_tree['_id'];
        $data['menu_name'] = $channel_tree['name'];
        $View->display($this->data);
    }

    protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {
        $this->load->model('content_model', 'content');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type','body');
        $branch_id = $_SESSION['branch_id'];
        if ($is_pic) {
            $filter = array('status' => true, 'thumb_name' => array("\$ne" => ''), 'removed' => false,"branch_id"=>$branch_id, 'site_id' => $this->site_id);
        } else {
            $filter = array('status' => true, 'removed' => false,"branch_id"=>$branch_id, 'site_id' => $this->site_id);
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
	
	protected function newList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {
		$this->load->model('content_model', 'content');
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
	
	public function notice(){
		$View = new Blitz('template/member/register-notice.html');
        $this->load->model('content_model', 'content');

		$notice=$this->content->find(array("channel"=>'55c02a1fd60b88a805928602',"removed"=>false,"status"=>true));//注册协议
		
		$data=array(
			"location"=>'<a href="/">首页</a> ><span>注册协议</span>',
			// "location"=>'<a href="/">首页</a> > <a href="/interaction/">政民互动</a> > <span>注册协议</span>',
			"notice"=>$notice
		);
		$View->display($data);
	}

}

/* End of file member.php */
/* Location: ./application/controllers/member.php */
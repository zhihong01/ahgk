<?php

class login extends MY_Controller {

    public function __construct() {
        parent::__construct();
        session_start();
    }

    public function index() {
        if ($this->input->server('REQUEST_METHOD') != 'POST') {
            $account_id = $_SESSION['account_id'];
            if (!empty($account_id)) {
                header("Location: /nocache/member/");
            }
			
            $View = new Blitz('template/member/login.html');
            $data['location'] = '<a href="/">首页</a> ><span>用户登录</span>';
			 // $data['location'] = '<a href="/">首页</a> > <a href="/interaction/">政民互动</a> > <span>用户登录</span>';
            $View->display($data);
        } else {
            $login_account = $this->security->xss_clean($this->input->post('account'));
            $password = $this->security->xss_clean($this->input->post('password'));
            if (empty($login_account)) {
                $this->resultJson('帐号是必填项目！', 3);
            }
            if (empty($password)) {
                $this->resultJson('密码是必填项目！', 3);
            }
            $vcode = $this->input->post('vcode');
            if (!empty($vcode) && strcasecmp($_SESSION['captcha_chars'], $vcode)) {
                $this->resultJson("验证码不正确", 3);
            }
            $client_ip = $this->input->ip_address();
            $this->load->model('site_account_model', 'site_account');
            $account = $this->site_account->find(array('nickname' => $login_account, 'site_id' => $this->site_id), 1);
            if (empty($account)) {
                if ($this->valid_email($login_account)) {
                    $login_account = strtolower($login_account);
                    $account = $this->site_account->find(array('email' => $login_account, 'site_id' => $this->site_id), 1);
                } else if ($this->valid_mobile_number($login_account)) {
                    $account = $this->site_account->find(array('phone' => $login_account, 'site_id' => $this->site_id), 1);
                }
            }
            if (empty($account)) {
                $this->resultJson("该账户不存在,请查证后再试", 3);
            }
            $login_data = array(
                'account_id' => (string) $account['_id'],
                'email' => $account['email'],
                'create_date' => time(),
                'client_ip' => $client_ip,
                'referer' => $this->input->server('REQUEST_URI'),
                'site_id' => $this->site_id,
                'status' => TRUE,
            );
            $this->load->model('site_login_log_model', 'site_login_log');
            if ($account['password'] !== $this->encryptPass($password,'')) {
                $login_data['status'] = FALSE;
                $login_id = $this->site_login_log->create($login_data);
                $this->resultJson('用户名和密码不匹配', 3);
            }
            if (!$account['status']) {
                $login_data['status'] = FALSE;
                $login_id = $this->site_login_log->create($login_data);
                $this->resultJson('该帐号还没有被管理员审核!', 3);
            }
            if (!$account['activated']) {
                $login_data['status'] = FALSE;
                $login_id = $this->site_login_log->create($login_data);
                $this->resultJson('该帐号未激活或已被冻结', 3);
            }
            //登录成功纪录登录信息
            $login_id = $this->site_login_log->create($login_data);
            $account_id = (string) $account['_id'];
            $this->load->model('site_member_model', 'site_member');
            $member = $this->site_member->find(array('account_id' => $account_id), 1);
            if (empty($member)) {
                $member['account_id'] = $account_id;
                $member['site_id'] = $this->site_id;
                $member['row_per_page'] = 20;
                $member['forum_group_id'] = '';
                $this->site_member->create($member);
            }
            $_SESSION['account_id'] = $account_id;
            $_SESSION['site_id'] = $this->site_id;
            $_SESSION['email'] = $account['email'];
            $_SESSION['nickname'] = $account['nickname'];
            $_SESSION['logged'] = true;
            $_SESSION['type'] = $account['type'];
            if ($account['type'] == 1) {
                $this->load->model('site_user_model', 'site_user');
                $user = $this->site_user->find(array('account_id' => $account_id), 1);
                if ($user) {
                    $_SESSION['branch_id'] = $user['branch_id'];
                }
            }
            $_SESSION['expiration_date'] = time() + 15 * 24 * 3600;
            $_SESSION['update_date'] = time();

            $this->load->model('site_login_session_model', 'login_session');
            $_SESSION['session_key'] = $this->session_key = md5($this->client_ip . $this->randKey() . time());

            $login_session_data = array(
                'account_id' => $account_id,
                'site_id' => $this->site_id,
                'email' => $account['email'],
                'avatar' => $account['avatar'],
                'nickname' => $account['nickname'],
                'logged' => true,
                'client_ip' => $client_ip,
                'is_user' => false,
                'phone_confirmed' => $account['phone_confirmed'],
                'expiration_date' => time() + 15 * 24 * 3600,
                'update_date' => time(),
            );
            $this->login_session->update(array('session_key' => $this->session_key), $login_session_data);
            $data = array(
                'last_ip' => $client_ip,
                'last_time' => time(),
                'login_count' => $account['login_count'] + 1,
            );
            $this->site_account->update(array('_id' => $account_id), $data);
            if ($account['type'] == 1) {
                $this->resultJson('登录成功',2, array('url' => '/member/admin/'));
            } else {
                $this->resultJson('登录成功',2,array('url' => '/member/'));
            }
        }
    }

    public function loginTudo() {
        $login_account = $this->security->xss_clean($this->input->post('account'));
        $password = $this->security->xss_clean($this->input->post('password'));
        if (empty($login_account)) {
            $this->resultJson('帐号是必填项目！', 3);
        }
        if (empty($password)) {
            $this->resultJson('密码是必填项目！', 3);
        }
        $client_ip = $this->input->ip_address();
        $this->load->model('site_account_model', 'site_account');
        $account = $this->site_account->find(array('nickname' => $login_account, 'site_id' => $this->site_id), 1);
        if (empty($account)) {
            if ($this->valid_email($login_account)) {
                $login_account = strtolower($login_account);
                $account = $this->site_account->find(array('email' => $login_account, 'site_id' => $this->site_id), 1);
            } else if ($this->valid_mobile_number($login_account)) {
                $account = $this->site_account->find(array('phone' => $login_account, 'site_id' => $this->site_id), 1);
            }
        }
        if (empty($account)) {
            $this->resultJson("该账户不存在,请查证后再试", 3);
        }
        $login_data = array(
            'account_id' => (string) $account['_id'],
            'email' => $account['email'],
            'create_date' => time(),
            'client_ip' => $client_ip,
            'referer' => $this->input->server('REQUEST_URI'),
            'site_id' => $this->site_id,
            'status' => TRUE,
        );
        $this->load->model('site_login_log_model', 'site_login_log');
        if ($account['password'] !== md5(md5($password) . $account['rand_key'])) {
            $login_data['status'] = FALSE;
            $login_id = $this->site_login_log->create($login_data);
            $this->resultJson('用户名和密码不匹配', 3);
        }
        if (!$account['status']) {
            $login_data['status'] = FALSE;
            $login_id = $this->site_login_log->create($login_data);
            $this->resultJson('该帐号还没有被管理员审核!', 3);
        }
        if (!$account['activated']) {
            $login_data['status'] = FALSE;
            $login_id = $this->site_login_log->create($login_data);
            $this->resultJson('该帐号未激活或已被冻结', 3);
        }
        //登录成功纪录登录信息
        $login_id = $this->site_login_log->create($login_data);
        $account_id = (string) $account['_id'];
        $this->load->model('site_member_model', 'site_member');
        $member = $this->site_member->find(array('account_id' => $account_id), 1);
        if (empty($member)) {
            $member['account_id'] = $account_id;
            $member['site_id'] = $this->site_id;
            $member['row_per_page'] = 20;
            $member['forum_group_id'] = '';
            $this->site_member->create($member);
        }
        $_SESSION['account_id'] = $account_id;
        $_SESSION['site_id'] = $this->site_id;
        $_SESSION['email'] = $account['email'];
        $_SESSION['nickname'] = $account['nickname'];
        $_SESSION['logged'] = true;
        $_SESSION['type'] = $account['type'];
        if ($account['type'] == 1) {
            $this->load->model('site_user_model', 'site_user');
            $user = $this->site_user->find(array('account_id' => $account_id), 1);
            if ($user) {
                $_SESSION['branch_id'] = $user['branch_id'];
            }
        }
        $_SESSION['expiration_date'] = time() + 15 * 24 * 3600;
        $_SESSION['update_date'] = time();

        $this->load->model('site_login_session_model', 'login_session');
        $_SESSION['session_key'] = $this->session_key = md5($this->client_ip . $this->randKey() . time());

        $login_session_data = array(
            'account_id' => $account_id,
            'site_id' => $this->site_id,
            'email' => $account['email'],
            'avatar' => $account['avatar'],
            'nickname' => $account['nickname'],
            'logged' => true,
            'client_ip' => $client_ip,
            'is_user' => false,
            'phone_confirmed' => $account['phone_confirmed'],
            'expiration_date' => time() + 15 * 24 * 3600,
            'update_date' => time(),
        );
        $this->login_session->update(array('session_key' => $this->session_key), $login_session_data);
        $data = array(
            'last_ip' => $client_ip,
            'last_time' => time(),
            'login_count' => $account['login_count'] + 1,
        );
        $this->site_account->update(array('_id' => $account_id), $data);
        $this->resultJson('登录成功', 2, array('url' => "/nocache/interaction/"));
    }

    public function logout() {
        unset($_SESSION['nickname']);
        unset($_SESSION['account_id']);
        unset($_SESSION['email']);
        unset($_SESSION['avatar']);
        unset($_SESSION['logged']);
		header( 'Pragma: no-cache' );
        header("Location: /");
    }

}

/* End of file login.php */
/* Location: ./application/controllers/login.php */
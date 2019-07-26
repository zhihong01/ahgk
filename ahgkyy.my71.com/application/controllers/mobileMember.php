<?php

/**
 * 宣城手机用户 mobileMember
 *
 * @author liufeiyue
 */
class mobileMember extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_account_model', 'site_account');
    }

    public function index() {
        
    }

    public function login() {
        $View = new Blitz('template/mobile/login.html');
        $data['rand'] = rand(0, 9);
        $View->display($data);
    }

}

/* End of file mobileMember.php */
/* Location: ./application/controllers/mobileMember.php */
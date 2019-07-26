<?php

@session_start();

class MY_Controller extends CI_Controller {
    
    /* 下面三个必须根据网站群中的实际配置信息，进行替换 */
    protected $site_id = '5bc861157f8b9a7a34f96885';
    protected $api_key = '';
    protected $upload_url = 'http://file.i.my71.com/';

    /* 下面值是系统初始化值，不需要更改 */
    protected $date_foramt = array('m-d', 'Y-m-d', 'm-d H:i', 'Y-m-d H:i');
    protected $sort_by = array('release_date', 'sort', 'create_date', 'views', 'downloads');
    protected $folder_prefix = '/content';

    protected $member = false;
    protected $oprn_member = false;

    public function __construct() {
        parent::__construct();
		$this->vals['setting'] = $this->getSiteSetting($this->site_id);
    }

    protected function getSiteSetting($site_id) {
		$this->load->model('group_model', 'group');
		$group = $this->group->find(array("_id"=>$this->site_id,"removed"=>false,"status"=>true),1);
		// if(empty($group)){
			// $View = new Blitz('template/site-close.html');
			// $View->display();
			// exit();
		// }
        $this->load->model('site_setting_model', 'site_setting');
        return $this->site_setting->find(array('site_id' => $site_id), 1);
    }

    // make pagination
    protected function getPagination($total_row, $page = 1, $per_count = 20, $rewrite = TRUE) {
        $pagination = '';

        if ($total_row > $per_count) {
            $cur_uri = ($this->input->server("REQUEST_URI") == $this->input->server("PHP_SELF")) ? $this->input->server("PHP_SELF") . "?" : $this->input->server("REQUEST_URI");
            $this->load->library('ipagination');
            $this->ipagination->initialize($cur_uri, $total_row, $per_count, $rewrite);
            $this->ipagination->getCurrentStartRecordNo($page);
            $pagination = $this->ipagination->displayPageLinks();
        }

        return $pagination;
    }

    protected function resultJson($msg, $status = "-ERR", $append = array()) {
        $data = array("msg" => $msg, "status" => $status);
        if ($append) {
            $data = array_merge($data, $append);
        }

        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . date("D, d M Y H:i:s") . "GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: text/html; charset=utf-8");
        exit(json_encode($data));
    }

    protected function valid_email($address) {
        return (preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? TRUE : FALSE;
    }

    protected function valid_mobile_number($phone) {
        return ((strlen($phone) == 11) && (preg_match("/13[12356789]{1}\d{8}|15[12356789]\d{8}|18\d{9}/", $phone))) ? TRUE : FALSE;
    }

    protected function randKey($len = 12) {
        return substr(str_shuffle("abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ23456789"), rand(0, 57 - $len), $len);
    }

    protected function getChineseWeek() {
        $week = array("星期天", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六");
        $nums = date("w");
        return $week[$nums];
    }


    //获取信件问题类别
    protected function questionList() {
        $this->load->model('supervision_question_model', 'supervision_question');
        return $this->supervision_question->findName(array("site_id" => $this->site_id), NULL);
    }

    // 根据部门_id 获取部门名称
    protected function getBranchName() {
        $this->load->model('site_branch_model', 'site_branch');
        return $this->site_branch->findName(array('site_id' => $this->parent_site_id));
        //        return $this->site_branch->findName(array('site_id' => $this->site_id));
    }

//信箱随机查询码
    protected function randomkeys($length) {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        $key = null;

        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern{mt_rand(0, 35)};
        }
        return $key;
    }

}

?>
<?php
@session_start();
class contentError extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }
	
	 protected function contentErrorList($limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0) {
        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = "*";
        $this->load->model("content_error_report_model", "content_error_report");
        $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $item_list = $this->content_error_report->findList(NULL, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
            $item_list[$key]['reply_date'] = ($item['reply_date']) ? date($date_format, $item['reply_date']) : '';
        }
        return $item_list;
    }

    // public function index() {
         // $View = new Blitz('template/content-error.html');
		 
		
         // $View->display($data);
    // }
	public function index() {
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $data = array(
            'location' => '<a href="/">首页</a> > <span>网站纠错</span>'
        );
        $this->load->model("content_error_report_model", "content_error_report");
        $total_row = $this->content_error_report->count(array('status' => True, 'removed' => false));
        $View = new Blitz('template/content-error.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //列表
                if ($action == 'list') {
                    list($channelid, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->contentErrorList($limit, $offset, $length, $date_format, $description_length);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $item_list['page'] = $link;
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['error_url'] = $_SERVER['HTTP_REFERER'];
        $View->display($data);
    }

    public function doSave() {
        $send = (int)$this->input->post("send");
        if ($send) {
            $data = $this->input->post('data');
            if (empty($data['error_type']) || $data['error_type'] == '') {
                $this->resultJson('请选择错误类型', 'error');
            }
            if (empty($data['error_url']) || $data['error_url'] == '') {
                $this->resultJson('出错页面地址不可为空', 'error');
            }

            session_start();
            $captcha_chars = $_SESSION['captcha_chars'];
            if (strnatcasecmp($captcha_chars, $this->input->post('vcode'))) {
                $this->resultJson('请输入验证码', 'error');
            }


            $error_type = "";
            for ($i = 0; $i < count($data['error_type']); $i++) {
                $error_type.=$data['error_type'][$i] . "、";
            }

            $data['error_type'] = $error_type;

            $data['create_date'] = time();
            $data['site_id'] = $this->site_id;
            $data['client_ip'] = $this->input->ip_address();

            //添加记录:
            $this->load->model("content_error_report_model", "content_error_report");
            $result = $this->content_error_report->create($data);

            $referer = '/';

            if ($result) {
                $this->resultJson('感谢您积极发现错误，我们一定及时处理！', '+OK', array('referer' => $referer));
            }
        }
    }

}

?>
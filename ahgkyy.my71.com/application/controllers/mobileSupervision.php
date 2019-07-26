<?php

/**
 * 用户写信 mobileSupervision
 *
 * @author liufeiyue
 */
class mobileSupervision extends MY_Controller{
    
    public function __construct() {
        parent::__construct();
        $this->load->model('supervision_model', 'supervision');
        $this->branch_list = $this->getBranchName();
    }
    
    // 部门列表
    protected function itemBranch($channel_id, $limit = 20, $offset = 0, $length = 10, $current_id = '') {

        $this->load->model('site_branch_model', 'site_branch');

        $filter = array('parent_id' => $channel_id, 'status' => true, 'supervision_on' => true, 'removed' => False);

        $select = array('_id', 'name', 'id');
        $arr_sort = array('sort' => 'DESC');

        $item_list = $this->site_branch->find($filter, $limit, $offset, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            if ($item['_id'] == $current_id) {
                $item_list[$key]['aon'] = 'class="aon"';
                $item_list[$key]['selected'] = 'selected';
            } else {
                $item_list[$key]['aon'] = '';
            }
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_name'] = $item['name'];
            }
            $item_list[$key]['url'] = '/supervision/branch/' . $item['_id'] . "/";
        }
        return $item_list;
    }
    
    // 信箱信息类别
    protected function itemQuestion() {
        $this->load->model('supervision_question_model', 'supervision_question');
        $item_list = $this->supervision_question->find(array('removed' => false, 'site_id' => $this->site_id), null, NULL, "*", array("create_date" => "DESC"));
        return $item_list;
    }
    
    public function write(){
        $data['product_id'] = (string)$this->input->get("product_id");
        $View = new Blitz('template/mobile/write-email.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                // 部门
                if ($action == 'branch') {
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->itemBranch($channel_id, $limit, $offset, $length, $_id);
                } elseif ($action == 'question') {
                    $item_list = $this->itemQuestion();
                }
                $data[$struct_val] = $item_list;
            }
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
        $View->display($data);
    }
}

?>
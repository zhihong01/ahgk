<?php 
class xzpy extends MY_Controller {
	
    public function __construct() {
        parent::__construct();
        $this->load->model('interaction_vote_model', 'interaction_vote');
        $this->load->model('interaction_vote_type_model', 'interaction_vote_type');
		$this->load->model('interaction_comment_log_model', 'interaction_comment_log');
        $this->load->model('interaction_comment_model', 'interaction_comment');
    }
	
	
    public function create() {

        $data = $this->input->post('data');
        //当前投票的id
        $_id = '589bd8ed7f8b9a8a251faad3';
		if(empty($_id)){
			$this->resultJson('该评议不存在或已结束');
		}
		$item_list = $this->interaction_comment->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), 1, 0, 'branch,vote_count,ismember');
		
		if(empty($item_list)){
			$this->resultJson('该评议不存在或已结束');
		}

		// 投票限制，如果要求会员才能参与，限制会员_id，否者限制ip
		$this->load->model('interaction_comment_log_model', 'interaction_comment_log');
		if($item_list['ismember']){
			if (!$this->member['logged']) {
				$this->resultJson('请登录！');
			}
			$filter = array('comments_id' => $_id, 'username' => $this->member['nickname'], 'removed' => false, 'site_id' => $this->site_id);
		}else{
			$filter = array('comments_id' => $_id, 'client_ip' => $this->client_ip, 'removed' => false, 'site_id' => $this->site_id);
		}
		
		if(count($data['score']) == 0){
			$this->resultJson('至少要选一个部门！');
		}
		
		$commentLog = $this->interaction_comment_log->find($filter);
		if(!empty($commentLog)){
			$this->resultJson('此次评议您已经参与过了，谢谢您的支持。');
		}
		
		// 每一个部门都要选择
		/* if(count($data['score']) != count($item_list['branch'])){
			$this->resultJson('请对每一个部门进行评价');
		} */
		// 用户选择的部门与分数
		foreach ($data['score'] as $keys => $val) {
			// 总分数
			foreach ($item_list['branch'] as $key => $item) {
				if ($key == $keys) {
					// 因为后台生产的分数和显示的不一致，在这里手动减掉3分
					$item_list['branch'][$key] = $item + $val;
				}
			}
			
			// 投票人数
			foreach ($item_list['vote_count'] as $key => $item) {
				if ($key == $keys) {
					$item_list['vote_count'][$key] = $item + 1;
				}
			}
		}
		
		foreach ($data['fenshus'] as $keys => $val) {
			
			// 总分数
			foreach ($item_list['custom_score'] as $key => $item) {
				if ($key == $keys) {
					$item_list['custom_score'][$key] = $item + $val;
				}
			}

		}
		//print_r($item_list['branch']);die();
		$this->interaction_comment->update(array('_id' => $_id), array('branch' => $item_list['branch'], 'vote_count' => $item_list['vote_count'],'custom_score'=>$item_list['custom_score']));

		// 如果网友是登录状态，就取他的昵称
		if(empty($this->member['nickname'])){
			$nickname = '热心网友';
			$creator = array("id" => time(), "name" => '热心网友');
		}else{
			$nickname = $this->member['nickname'];
			$creator = array("id" => $this->member['account_id'], "name" => $this->member['nickname']);
		}
		$data_log = array(
			"client_ip" => $this->client_ip,
			"comments_id" => $_id,
			"create_date" => time(),
			"ismember" => $item_list['ismember'],
			"isstat" => 0,
			"site_id" => $this->site_id,
			"username" => $nickname,
			"vote_data" => $data['score'],
			"custom_score" => $data['fenshus'],
			"creator" => $creator
		);
		
		$ret = $this->interaction_comment_log->create($data_log);
		
		if(!$ret) {
            $this->resultJson('保存评议人信息 出错！');
        }

        $this->load->model('interaction_comment_result_model', 'interaction_comment_result');
        $filter_list = array(
            "comments_id" => $_id,
            "site_id" => $this->site_id,
        );
        // 用户选择的部门与分数
        foreach ($data['score'] as $branchId => $val) {
            $filter_list['branch_id'] = $branchId;
            $filter_list['filed_id'] = $val;
            $filter_list['removed'] = false;

            $old = $this->interaction_comment_result->find($filter_list,1);
            if(empty($old)) {
                $filter_list['total'] = 1;
                $ret = $this->interaction_comment_result->create($filter_list);
            } else {
                $ret = $this->interaction_comment_result->incCounter($filter_list, array("total"=>1));
            }
        }

		$referer = "/interactionComment/detail/?_id=".$_id;	
		$this->resultJson('提交成功', 2, array('referer' => $referer));
    }
		
		
	    public function index() {
			$View = new Blitz('template/xzpy.html');
			
			$View->display($this->data);
		}
}
?>
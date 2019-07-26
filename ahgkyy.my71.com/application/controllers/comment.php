<?php

class comment extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('interaction_comments_members_model', 'interaction_comments_members');
        $this->load->model('interaction_comment_model', 'interaction_comment');
        $this->load->model("site_branch_model", "site_branch");
        session_start();
    }

    // 网上评议
    protected function itemComment($limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $this->load->model('interaction_comment_model', 'interaction_comment');

        $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'create_date');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->interaction_comment->find($filter, $limit, $offset, $select, $arr_sort);
        foreach ($item_list as $key => $item) {

            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/interactionComment/detail/nocache/' . $item['_id'] . '.html';
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
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
                $result[$key] = $value;
                $i++;
            }
        }

        return $result;
    }

    public function index() {

        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $total_row = $this->interaction_comment->count(array('status' => true, 'removed' => false, 'site_id' => $this->site_id));

        $View = new Blitz('template/interaction/comment.html');
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
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemComment($limit, $offset, $length, $sort_by, $date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, True));
                }
				
				if ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $channel_id = $parent_id;
                    } else {
                        $channel_id = $parent_channel['_id'];
                    }

                    $menu_list = $this->getMenu($channel_id, $limit, $offset, $length);
                    $i = 0;
                    foreach ($menu_list as $key => $menu) {
                        $item_list[$i]['_id'] = $key;
                        $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
                        $i++;
                    }
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['location'] = '<a href="/">首页</a> > <a href="/interaction/">政民互动</a> > <span>网上评议</span>';
		$data['channel_name']='网上评议';
		
		//县市区（第一组）
		$content = $this->interaction_comment->find(array('_id' => '56a0586a7f8b9a1711cf873c', 'status' => true, 'removed' => false), 1, 0, array('_id', 'title', 'body', 'ismember', 'is_syncshow', 'startdate', 'overdate', 'member_list'));
        if (empty($content)) {
            show_404();
        }
        if ($content['startdate']->sec > time() || $content['overdate']->sec < time()) {
            $data['is_over'] = true;
        }
        $content['startdate'] = date('Y-m-d h:m:s', $content['startdate']->sec);
        $content['overdate'] = date('Y-m-d h:m:s', $content['overdate']->sec);
        // 是否只允许会员评议
        if ($content['ismember'] == true) {
            if ($this->member['logged']) {
                $data['logged'] = true;
                if (!empty($content['member_list'])) {
                    // 是否在可投票名单中
                    if (in_array($this->member['nickname'], $content['member_list'])) {
                        // 在会员名单中
                        $data['can_comment'] = true;
                    } else {
                        $data['not_list'] = true;
                    }
                } else {
                    $data['can_comment'] = true;
                }
            }
        } else {
            $data['can_comment'] = true;
        }

        if (!empty($content['member_list'])) {
            // 有投票名单
            $data['have_list'] = true;
        }
		$data['content']=$content;
		
		
        $View->display($data);
    }

    public function detail() {
        $_id = (string) $this->input->get('_id');
        $content = $this->interaction_comment->find(array('_id' => $_id, 'status' => true, 'removed' => false), 1, 0, array('_id', 'title', 'body', 'ismember', 'is_syncshow', 'startdate', 'overdate', 'member_list'));
        if (empty($content)) {
            show_404();
        }
        if ($content['startdate']->sec > time() || $content['overdate']->sec < time()) {
            $data['is_over'] = true;
        }
        $content['startdate'] = date('Y-m-d h:m:s', $content['startdate']->sec);
        $content['overdate'] = date('Y-m-d h:m:s', $content['overdate']->sec);
        // 是否只允许会员评议
        if ($content['ismember'] == true) {
            if ($this->member['logged']) {
                $data['logged'] = true;
                if (!empty($content['member_list'])) {
                    // 是否在可投票名单中
                    if (in_array($this->member['nickname'], $content['member_list'])) {
                        // 在会员名单中
                        $data['can_comment'] = true;
                    } else {
                        $data['not_list'] = true;
                    }
                } else {
                    $data['can_comment'] = true;
                }
            }
        } else {
            $data['can_comment'] = true;
        }

        if (!empty($content['member_list'])) {
            // 有投票名单
            $data['have_list'] = true;
        }

        $data['content'] = $content;
        $View = new Blitz('template/interaction/comment.html');
        $data['location'] = '<a href="/">首页</a> > <a href="/interaction/">政民互动</a> > <a href="/interactionComment/">网上评议</a> > <span>' . $content['title'] . '</span>';
        $View->display($data);
    }

    public function create() {
        $data = $this->input->post('data');
        //当前投票的id
        $_id = (string) $this->input->post('comments_id');
        if (empty($_id)) {
            $this->resultJson('该评议不存在或已结束', 3);
        }
        $item_list = $this->interaction_comment->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), 1, 0, 'branch,vote_count,ismember');

        if (empty($item_list)) {
            $this->resultJson('该评议不存在或已结束', 3);
        }

        // 投票限制，如果要求会员才能参与，限制会员_id，否者限制ip
        $this->load->model('interaction_comment_log_model', 'interaction_comment_log');
        if ($item_list['ismember']) {
            if (!$this->member['logged']) {
                $this->resultJson('请登录！', 3);
            }
            $filter = array('comments_id' => $_id, 'username' => $this->member['nickname'], 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('comments_id' => $_id, 'addip' => $this->client_ip, 'removed' => false, 'site_id' => $this->site_id);
        }

		$captcha_chars = $_SESSION['captcha_chars'];
        if ($this->input->post('vcode') == '') {
            $this->resultJson('验证码不可为空');
        }
        if (strcasecmp($captcha_chars, $this->input->post('vcode')) != 0) {
            $this->resultJson('验证码不正确');
        }
		
        if (count($data['score']) == 0) {
            $this->resultJson('至少要选一个部门！', 4);
        }

        $commentLog = $this->interaction_comment_log->find($filter);
        if (!empty($commentLog)) {
            $this->resultJson('此次评议您已经参与过了，谢谢您的支持。', 4);
        }

        // 每一个部门都要选择
        //if(count($data['score']) != count($item_list['branch'])){
        //$this->resultJson('请对每一个部门进行评价');
        //}
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
        $this->interaction_comment->update(array('_id' => $_id), array('branch' => $item_list['branch'], 'vote_count' => $item_list['vote_count']));

        // 如果网友是登录状态，就取他的昵称
        if (empty($this->member['nickname'])) {
            $nickname = '热心网友';
            $creator = array("id" => time(), "name" => '热心网友');
        } else {
            $nickname = $this->member['nickname'];
            $creator = array("id" => $this->member['account_id'], "name" => $this->member['nickname']);
        }
        $data_log = array(
            "addip" => $this->client_ip,
            "comments_id" => $_id,
            "create_date" => time(),
            "ismember" => $item_list['ismember'],
            "isstat" => 0,
            "site_id" => $this->site_id,
            "username" => $nickname,
            "vote_data" => $data['score'],
            "creator" => $creator,
            'removed' => false
        );

        $ret = $this->interaction_comment_log->create($data_log);

        if (!$ret) {
            $this->resultJson('保存评议人信息 出错！', 3);
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

            $old = $this->interaction_comment_result->find($filter_list, 1);
            if (empty($old)) {
                $filter_list['total'] = 1;
                $ret = $this->interaction_comment_result->create($filter_list);
            } else {
                $ret = $this->interaction_comment_result->incCounter($filter_list, array("total" => 1));
            }
        }
		$advise=trim($this->input->post('advise'));
		if(!empty($advise)){
			$data_advise = array();
			$data_advise['title'] = htmlspecialchars($advise);
			$data_advise['body'] = htmlspecialchars($advise);
			$data_advise['type_id'] = '569f48477f8b9acb10cb5936';
			$data_advise['no']=time();
			$data_advise['site_id'] = $this->site_id;
			$data_advise['create_date'] = time();
			$data_advise['client_ip'] = $this->client_ip;
			
			$this->load->model('site_feedback_model', 'site_feedback');
			$result = $this->site_feedback->create($data_advise);
		}
		
        $url = "/interactionComment/detail/?_id=" . $_id;
        $this->resultJson('提交成功', 2, array('url' => $url));
    }

    public function viewResult() {
        $_id = (string) $this->input->get('_id');
        $item_list = $this->interaction_comment->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), 1, 0, 'branch,vote_count');

        if (empty($item_list)) {
            show_404();
        }
        $i = 0;
        $branchs = array();
        foreach ($item_list['branch'] as $key => $item) {
            $branch = $this->site_branch->find(array('_id' => $key), 1, 0, 'name');
            $branchs[$i]['name'] = $branch['name'];
            // 投票数（因为部门是对应的，所以这里直接取）
            $branchs[$i]['vote_count'] = $item_list['vote_count'][$key];
            $branchs[$i]['point_count'] = $item;
            $i++;
        }
        $data['title'] = $item_list['title'];
        $data['result'] = $branchs;
        $data['location'] = '<a href="/">首页</a> > <a href="/interaction/">政民互动</a> > <a href="/interactionComment/">网上评议</a> > <span>评议结果（' . $item_list['title'] . '）</span>';

        $View = new Blitz('template/interaction/result-interactioncomment.html');
        $View->display($data);
    }

    public function memberList() {

        $_id = (string) $this->input->get('_id');
        $content = $this->interaction_comment->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'ismember', 'title', 'startdate', 'overdate', 'member_list'));

        if (empty($content)) {
            show_404();
        }

        foreach ($content['member_list'] as $key => $item) {
            $member_list[]['name'] = $item;
        }
        //if($content['ismember']){
        //$content['title'] = $content['title']."<font color='#0000ff'>(会员参与)</font>";
        //}
        $content['startdate'] = date("Y-m-d h:m:s", $content['startdate']->sec);
        $content['overdate'] = date("Y-m-d h:m:s", $content['overdate']->sec);

        $data['content'] = $content;
        $data['member_list'] = $member_list;

        $data['location'] = '<a href="/">首页</a>/<a href="/interaction/">民政互动</a>/<a href="/interactionComment/">网上评议</a>/<span>查看评议会员（' . $content['title'] . '）</span>';

        $View = new Blitz('template/comment-member-list.html');
        $View->display($data);
    }

}

?>
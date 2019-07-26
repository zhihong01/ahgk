<?php

class interactionColl extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('interaction_coll_model', 'interaction_coll');
        $this->load->model('interaction_coll_type_model', 'interaction_coll_type');
        $this->load->model('interaction_coll_feedback_model', 'interaction_coll_feedback');
    }

    // 民意征集
    protected function itemColl($type, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $this->load->model('interaction_coll_model', 'interaction_coll');

        //$filter = array('type_id' => $type, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
		
		$now = time();	
        $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'confirm_date','link_url','release_date','overdate');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->interaction_coll->find($filter, $limit, $offset, $select, $arr_sort);
		
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] :'/interactionColl/detail/' . $item['_id'] . '.html';
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
         
		   	if($now<$item['overdate']){ 
				$item_list[$key]['coll_state'] ='<font color="#1E5F1E">【征集中】</font>';
			}else{ 
				$item_list[$key]['coll_state'] ='<font color="#9EADB6">【已截止】</font>';
			}
			$item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

    // 友情链接
    protected function friendLinkList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {

        $this->load->model('friend_link_model', 'friend_link');

        $filter = array("type_id" => $_id_list, 'status' => true, 'removed' => False, 'site_id' => $this->site_id);
        $arr_sort = array('sort' => 'ASC');

        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'link_url', 'file_path', 'width', 'height', 'target', 'confirm_date');

        $item_list = $this->friend_link->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = (string) ($item['link_url']);
            $item_list[$key]['thumb'] = (string) ($item['file_path']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }

    protected function itemCollFeedback($_id, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $filter = array('collection_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('name', 'create_date', 'body');

        $item_list = $this->interaction_coll_feedback->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['body'] = htmlspecialchars_decode($item['body']);
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
        $id=$this->input->get('_id');
		
        $interaction_type=$this->interaction_coll_type->find(array('site_id'=>$this->site_id));

        $total_row = $this->interaction_coll->count(array('status' => true, 'removed' => false, 'site_id' => $this->site_id));
        $View = new Blitz('template/interaction/list-interactionColl.html');
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
                    list($type_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemColl($type_id, $limit, $offset, $length, $sort_by, $date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, True));
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
						0=>array('url'=>'/content/channel/5bce800220f7fede2449b1e9/','_id'=>1,'name'=>'国土访谈'),
						1=>array('url'=>'/content/channel/5bce7f5d20f7fee02427ec36/','_id'=>2,'name'=>'联系我们'),
                        2=>array('url'=>'/interactionColl/','_id'=>3,'name'=>'调查征集'),
                        3=>array('url'=>'http://www.xuancheng.gov.cn/supervision/branch/53d8e27db1a64ce7ce426fbd/','_id'=>4,'name'=>'部门信箱'),
					);
                }

                if ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['channel_name'] = "政民互动";
        //$data['menu_id'] = "566002bab45ca00814000008";
        $data['location'] = '<a href="/">网站首页</a> > <a href="/supervision/?product_id=3">政民互动</a> > <span>网上调查</span>';
        $data['menu_name']=$interaction_type['name'];
        $View->display($data);
    }
	
	protected function attachList($content_id) {
        $this->load->model('site_attach_model', 'site_attach');
        $item_list = $this->site_attach->find(array('module_id' => $content_id), NULL);

        return $item_list;
    }

    public function detail() {
        
        $_id = (string) $this->input->get('_id');
        $View = new Blitz('template/interaction/detail-interactioncoll.html');
		

        $content = $this->interaction_coll->find(array("_id" => $_id, 'status' => true, 'removed' => false), 1, 0);
       

        if (empty($content)) {
            show_404();
        }
        if (time() < $content['startdate']) {
            $data['not_start'] = 1;
        }
        if (time() > $content['overdate']) {
            $data['is_end'] = 1;
        }
       
        $content['startdate'] = $content['startdate'] ? date("Y-m-d H:i:s", $content['startdate']) : '';
        $content['overdate'] = $content['overdate'] ? date("Y-m-d H:i:s", $content['overdate']) : '';
        $content['body'] = str_replace("\n", "<br/>", str_replace(Chr(32), " ", $content['body']));
        $content['summarys'] = str_replace("\n", "<br/>", str_replace(Chr(32), " ", $content['summarys']));

        $data['content'] = $content;

       // $member = $this->is_login();
        $data['member'] = $member;

        $struct_list = $View->getStruct();
		
		if ($View->hasContext('attach')) {
            $item_list = $this->attachList($_id);

            $this->load->helper('number');
            foreach ($item_list as $item) {
                $View->block('/attach', array('_id' => $item['_id'],
                    'downloads' => $item['downloads'],
                    'file_size' => byte_format($item['file_size']),
                    'name' => "附件：" . $item['real_name'],
                    'url' => 'http://file.dongzhi.gov.cn:9000/mserver/download/?_id=' . $item['_id'].'&SiteId='.$item['site_id'],
                    'file_type' => $item['file_type'],
                        )
                );
            }
        }
		
		
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                //列表
                if ($action == 'feedback') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->itemCollFeedback($_id, $limit, $offset, $length, $sort_by, $date_format);
                }

                if ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }

        $this->interaction_coll->update(array('_id' => $_id), array('views' => $content['views'] + 1));
        $content['views'] = $content['views'] + 1;

        $type_id=$content['type_id'];
        //$type=$this->interaction_coll_type->find(array('_id'=>$type_id));
        $data['location'] = '<a href="/">网站首页</a> > <a href="/interactionColl/">调查征集</a> ';
        $View->display($data);
    }

    public function create() {

        $data = $this->input->post('data');
        if ((empty($data["name"])) || (empty($data["email"])) || (empty($data["body"]))) {
            $this->resultJson('标有 * 字段是必填项');
        }
        if (!$this->valid_email($data["email"])) {
            $this->resultJson('邮件地址不正确');
        }
        $captcha_chars = $_SESSION['captcha_chars'];
        if ($this->input->post('vcode') == '') {
            $this->resultJson('验证码不可为空');
        }
        if (strcasecmp($captcha_chars, $this->input->post('vcode')) != 0) {
            $this->resultJson('验证码不正确');
        }
        //当前投票的id
        $list_id = $data['collection_id'];
        if (!empty($list_id)) {
            $creator = array('id' => '', 'name' => $data['name']);
            $confirmer = array('id' => '', 'name' => '');
            $data = array(
                "client_ip" => $this->client_ip,
                "collection_id" => $list_id,
                "create_date" => time(),
                "creator" => $creator,
                "site_id" => $this->site_id,
                "removed" => false,
                "status" => false,
                "body" => htmlspecialchars($data['body']),
                "phone" => htmlspecialchars($data['phone']),
                "email" => htmlspecialchars($data['email']),
                "name" => htmlspecialchars($data['name']),
                "confirmer" => $confirmer,
            );
            $this->interaction_coll_feedback->create($data);
        }
        $referer = "/interactionColl/detail/?_id=" . $list_id;
        $this->resultJson('提交成功', '+OK', array('referer' => $referer));
    }

    public function feedback() {

        $_id = (string) $this->input->get('_id');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }

        $content = $this->interaction_coll->find(array("_id" => $_id, 'status' => true, 'removed' => false), 1, 0, array('title'));
		
        if (empty($content)) {
            show_404();
        }

        $total_row = $this->interaction_coll_feedback->count(array('collection_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));

        $View = new Blitz('template/interaction/feedback-interactioncoll.html');
        $struct_list = $View->getStruct();
        $data = array();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                //列表
                if ($action == 'feedback') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemCollFeedback($_id, $limit, $offset, $length, $sort_by, $date_format);
                }
                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, True));
                }

                if ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }

                $data[$struct_val] = $item_list;
            }
        }

        $data['title'] = $content['title'];
        $data['location'] = '<a href="/">网站首页</a> > <a href="/interactionColl/">调查征集</a> >  <span>征集留言</span>';

        $View->display($data);
    }

}

/* End of file interactionColl.php */
/* Location: ./application/controllers/interactionColl.php */
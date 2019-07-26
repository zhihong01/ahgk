<?php

class mobileInteraction extends MY_Controller {

    protected $supervision_status = array('<font color=blue>[未审核]</font>', '<font color=blue>[未受理]</font>', '<font color=blue>[受理中]</font>', '<font color="red">[已处理]</font>', '<font color="red">[再追问]</font>', '<font color="red">[已解决]</font>');
    protected $product_name = array("未知信箱", "书记信箱", "市长信箱", "问政部门", "县(市、区)长信箱");
    protected $supervision_rating = array("未评论", "不满意", "", "基本满意", "", "满意");

    public function __construct() {
        parent::__construct();
        $this->load->model('supervision_model', 'supervision');
        $this->branch_list = $this->getBranchName();
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

    // 获取互动信件列表
    protected function itemSupervision($filter, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('supervision_model', 'supervision');
        $filter = array_merge($filter, array('status' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
        $select = array('_id', 'process_status', 'subject', 'create_date', 'confirm_date', 'branch_id', 'no', 'hit');
        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $item_list = $this->supervision->find($filter, $limit, $offset, $select, $arr_sort);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
            $item_list[$key]['url'] = '/mobileInteraction/detail/' . $item['_id'] . '.html';
            $item_list[$key]['_id'] = (string) ($item['_id']);
            // 留言的状态
            if (isset($this->supervision_status[$item['process_status']])) {
                $item_list[$key]['process_status'] = $this->supervision_status[$item['process_status']];
            } else {
                $item_list[$key]['process_status'] = $this->supervision_status[0];
            }
            // 取部门
            if (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['branch_id']];
            } else {
                $item_list[$key]['branch'] = '';
            }
			$item_list[$key]['branch_long'] = mb_substr($item_list[$key]['branch'], 0, 6);
            $item_list[$key]['branch'] = mb_substr($item_list[$key]['branch'], 0, 4);
            $item['subject'] = strip_tags(html_entity_decode($item['subject']));
            if (mb_strlen($item['subject']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['subject'], 0, $length);
            } else {
                $item_list[$key]['short_title'] = $item['subject'];
            }
            $item_list[$key]['title'] = $item['subject'];
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
            $item_list[$key]['confirm_date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }

    // 服务指南
    protected function itemServiceContent($_id, $limit = 50, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $this->load->model('service_content_model', 'service_content');

        $filter = array('type' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'branch_id', 'confirm_date', 'download', 'policy');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->service_content->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/service/contentDetail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            // 取部门
            if (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['branch_id']];
                $item_list[$key]['branch_url'] = '/supervision/branch/?_id=' . $item['branch_id'];
            } else {
                $item_list[$key]['branch'] = '';
            }
            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }

    // 获取互动信件列表
    protected function itemCommonSupervision($filter, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $this->load->model('supervision_model', 'supervision');

        $filter = array_merge($filter, array('status' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
        $select = array('_id', 'process_status', 'subject', 'create_date', 'branch_id', 'no');
        $arr_sort = array('hit' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->supervision->find($filter, $limit, $offset, $select, $arr_sort);

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
            // 取部门
            if (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['branch_id']];
//				if (mb_strlen($item_list[$key]['branch']) > 4) {
//					$item_list[$key]['short_branch'] = mb_substr($item_list[$key]['branch'], 0, 4);
//				}else{
//					$item_list[$key]['short_branch'] = $item_list[$key]['branch'];
//				}
            } else {
                $item_list[$key]['branch'] = '';
            }
            $item['subject'] = strip_tags(html_entity_decode($item['subject']));
            if (mb_strlen($item['subject']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['subject'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['subject'];
            }
            $item_list[$key]['title'] = $item['subject'];
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
        }
        return $item_list;
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

    /*
     * 统计部门信件列表 $supervision_type： 
     * 0 =>问政市四大班子、市直党群部门、检察机关、审判机关等
     * 1 =>公共服务、行政执法单位
     * 2 =>县(市、区)长信箱
     * 3 =>综合管理部门
     * 4 =>市长信箱
     * */

    public function supervisionBranch($branch_id = NULL, $supervision_type = NULL, $limit = NULL, $offset = NULL, $length = 6) {
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('supervision_branch_counter_model', 'supervision_branch_counter');
        $filter = array('supervision_type' => (int) $supervision_type, 'status' => true, 'removed' => False);
        $select = array('_id', 'name', 'id');
        $arr_sort = array('sort' => 'DESC');
        $record = $this->site_branch->find($filter, $limit, $offset, $select, $arr_sort);
        if (!empty($record) && $limit == 1) {
            $item_list[] = $record;
        } else {
            $item_list = $record;
        }
        foreach ($item_list as $key => $item) {
            if ($item['_id'] == $branch_id) {
                $item_list[$key]['aon'] = 'class="aon"';
                $item_list[$key]['selected'] = 'selected';
            } else {
                $item_list[$key]['aon'] = '';
            }
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '';
            } else {
                $item_list[$key]['short_name'] = $item['name'];
            }
            $item_list[$key]['url'] = '/supervision/branch/' . (string) $item['_id'] . "/";
        }
        return $item_list;
    }

    // 网上评议
    protected function itemComment($limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $this->load->model('interaction_comment_list_model', 'interaction_comment_list');

        $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'create_date');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->interaction_comment_list->find($filter, $limit, $offset, $select, $arr_sort);
        foreach ($item_list as $key => $item) {

            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/interactionComment/detail/' . $item['_id'] . '.html';
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

    // 信箱信息类别
    protected function itemQuestion() {
        $this->load->model('supervision_question_model', 'supervision_question');
        $item_list = $this->supervision_question->find(array('removed' => false, 'site_id' => $this->site_id), null, NULL, "*", array("create_date" => "DESC"));
        return $item_list;
    }

    protected function itemList($_id_list, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0, $description_length = 100) {

        $this->load->model('content_model', 'content');

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date');
        $filter = array('channel' => $_id_list, 'status' => True, 'removed' => false, 'site_id' => $this->site_id);
        $item_list = $this->content->find($filter, $limit, $offset, $select, $arr_sort);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
            $item_list[$key]['url'] = '/content/detail/' . $item['_id'] . '.html';
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            $item['description'] = strip_tags(html_entity_decode($item['description']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            if (mb_strlen($item['description']) > $description_length) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }
            $item_list[$key]['thumb'] = $item['thumb_name'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

    public function index() {
		$page = (int) $this->input->get('page');
        // 信件统计
        $data['total_supervision'] = $this->supervision->count(array('status' => true, 'removed' => False, 'cancelled' => false, 'site_id' => $this->site_id));
        $data['processed_supervision'] = $this->supervision->count(array('status' => true, 'removed' => False, 'process_status' => 5, 'cancelled' => false, 'site_id' => $this->site_id));
        $data['processing_supervision'] = $data['total_supervision'] - $data['processed_supervision'];
        $View = new Blitz('template/mobile/list-interaction.html');
		if ($page == 0) {
            $page = 1;
        }
        $data = array();
        if (empty($branch_id)) {
            $total_row = $this->supervision->count(array('status' => true, 'removed' => False, 'cancelled' => false, 'site_id' => $this->site_id));
        } else {
            $total_row = $this->supervision->count(array('branch_id' => $branch_id, 'status' => true, 'removed' => False, 'cancelled' => false, 'site_id' => $this->site_id));
        }
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //列表
                if ($action == 'supervision') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
					if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemSupervision(array(), $limit, $offset, $length, $sort_by, $date_format);
                } elseif ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->itemList($channel_id, $limit, $offset, $length, $sort_by, $date_format);
                } elseif ($action == 'reply') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->itemSupervision(array('process_status' => array("\$gte" => 3)), $limit, $offset, $length, $sort_by, $date_format);
                } elseif ($action == 'common') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->itemCommonSupervision(array(), $limit, $offset, $length, $sort_by, $date_format);
                } elseif ($action == 'comment') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->itemComment($limit, $offset, $length, $sort_by, $date_format);
                } elseif ($action == 'branch') {
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->itemBranch($channel_id, $limit, $offset, $length);
                } elseif ($action == 'service') {
                    // 办事指南
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id == 'all') {
                        $parent_id = null;
                    }
                    $item_list = $this->itemServiceContent($parent_id, $limit, $offset, $length);
                }elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, TRUE));
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['supervision_total'] = $this->supervision->count(array('status' => true, 'removed' => false, 'site_id' => $this->site_id));
        $data['supervision_processed'] = $this->supervision->count(array('process_status' => 5, 'status' => true, 'removed' => false, 'site_id' => $this->site_id)) + $this->supervision->count(array('process_status' => 4, 'status' => true, 'removed' => false, 'site_id' => $this->site_id)) + $this->supervision->count(array('process_status' => 3, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        $data['supervision_processing'] = $data['supervision_total'] - $data['supervision_processed'];
        $data['location'] = '<a href="/">网站首页</a> &gt; <a href="/nocache/interaction/">互动交流</a> &gt; <span>部门信箱</span>';

        $View->display($data);
    }

    public function branch() {
        $branch_id = $this->security->xss_clean($this->input->get('_id'));
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $data = array();
        if (empty($branch_id)) {
            $total_row = $this->supervision->count(array('status' => true, 'removed' => False, 'cancelled' => false, 'site_id' => $this->site_id));
        } else {
            $total_row = $this->supervision->count(array('branch_id' => $branch_id, 'status' => true, 'removed' => False, 'cancelled' => false, 'site_id' => $this->site_id));
        }
        $View = new Blitz('template/list-supervision-branch.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //列表
                if ($action == 'supervision') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    if (empty($branch_id)) {
                        $filter = array();
                    } else {
                        $filter = array('branch_id' => $branch_id);
                    }
                    $item_list = $this->itemSupervision($filter, $limit, $offset, $length, $sort_by, $date_format);
                } elseif ($action == 'branch') {
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->itemBranch($channel_id, $limit, $offset, $length, $branch_id);
                } elseif ($action == 'question') {
                    $item_list = $this->itemQuestion();
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, TRUE));
                } elseif ($action == 'supervisionbranch') {
                    /*
                     * 统计部门信件列表 $supervision_type： 
                     * 0 =>问政市四大班子、市直党群部门、检察机关、审判机关等
                     * 1 =>公共服务、行政执法单位
                     * 2 =>县(市、区)长信箱
                     * 3 =>综合管理部门
                     * 4 =>市长信箱
                     * */
                    list($supervision_type, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->supervisionBranch($branch_id, $supervision_type, $limit, $offset, $length, $sort_by, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['channel_name'] = "政民互动";
        $data['branch_name'] = $this->branch_list[$branch_id];
        $data['write_url'] = '/supervision/write/product_id=3/branch_id=' . $branch_id . '/';
        $data['service_url'] = '/serviceBranch/?type=53d8e27db1a64ce7ce426f7d&_id=' . $branch_id;
        $data['location'] = '<a href="/">网站首页</a> / <a href="/nocache/interaction/">政民互动</a> / <span>问政部门</span>';
        $View->display($data);
    }

    public function write() {
        $product_id = $this->security->xss_clean($this->input->get('product_id'));
        if ($product_id < 0 || $product_id > 4) {
            show_error("信箱类别有误");
        }
        $question_id = $this->security->xss_clean($this->input->get('question_id'));
        $branch_id = $this->security->xss_clean($this->input->get('branch_id'));
        if ($product_id == "2") {
            $View = new Blitz('template/write-supervision-mayor.html');
        } elseif ($product_id == "4") {
            $View = new Blitz('template/write-supervision-county.html');
        } elseif ($product_id == "3") {
            $View = new Blitz('template/write-supervision-branch.html');
        } else {
            $View = new Blitz('template/write-supervision-branch.html');
        }
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
                } elseif ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $channel_id = $parent_id;
                    }
                    $menu_list = $this->getMenu($channel_id, $limit, $offset, $length);
                    $i = 0;
                    foreach ($menu_list as $key => $menu) {
                        $item_list[$i]['_id'] = $key;
                        $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
                        $i++;
                    }
                } elseif ($action == 'question') {
                    $item_list = $this->itemQuestion();
                } elseif ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['product_id'] = $product_id;
        $data['question_id'] = $question_id;
        $data['branch_id'] = $branch_id;
        $data['channel_name'] = "政民互动";
        $data['location'] = '<a href="/">网站首页</a> / <a href="/nocache/interaction/">政民互动</a> / <a href="/supervision/write/product_id=' . $product_id . '/">' . (string) $this->product_name[$product_id] . '</a>';
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

    //创建信件
    public function create() {
        $account_id = $_SESSION['account_id'];
        $this->load->model('site_account_model', 'site_account');
        $account = $this->site_account->find(array('_id' => $account_id, 'site_id' => $this->site_id), 1);
        if (!$account) {
            $this->resultJson('请登录网站会员后再写信', 3);
        }
        if ($account['type'] == 1) {
            $this->resultJson('请勿使用管理员身份发信', 3);
        }
        $captcha_chars = $_SESSION['captcha_chars'];
        if (strcasecmp($captcha_chars, $this->input->post('vcode'))) {
            $this->resultJson('验证码不正确', 4);
        }
        $customer = $this->security->xss_clean($this->input->post('customer'));
        $supervision = $this->security->xss_clean($this->input->post('supervision'));
//        if (empty($supervision["branch_id"])) {
//            $this->resultJson('请选择部门！', 4);
//        }
        if (empty($supervision["question_id"])) {
            $this->resultJson('请选择类型！', 4);
        }
        // if (!$this->valid_email($customer["email"])) {
        // $this->resultJson('邮件地址不正确', 4);
        // }
        if (empty($supervision["subject"])) {
            $this->resultJson('留言主题不能为空', 4);
        }
        if (empty($supervision["message"])) {
            $this->resultJson('留言内容不能为空', 4);
        }
//        if (mb_strlen($supervision["message"]) < 20) {
//            $this->resultJson('留言内容不能少于20个字', 4);
//        }
        if ($supervision['share_on'] == 1) {
            $supervision['share_on'] = TRUE;
        } else {
            $supervision['share_on'] = FALSE;
        }
        $creator = array("id" => (string) $account['_id'], "name" => $account['nickname']);
        $supervision["member_id"] = (string) $account['_id'];
        $supervision["client_ip"] = $this->input->ip_address();
        $supervision["site_id"] = $this->site_id;
        $supervision["cancelled"] = false;
        $supervision["removed"] = false;
        $supervision["status"] = false;
        $supervision["reply_date"] = 0;
        $supervision["create_date"] = time();
        $supervision["update_date"] = time();
        $supervision["process_status"] = 1;
        $supervision["is_public"] = false;
        $supervision["product_id"] = (int) $supervision["product_id"];
        $supervision["assigned_user"] = array();
        $supervision["creator"] = $creator;
        $supervision["reply_confirmed"] = false;
        $supervision["priority"] = (int)$supervision["priority"];
        if (!empty($supervision["branch_id"])) {
            $result = $this->autoAudit($supervision["product_id"]);
            $supervision = array_merge($supervision, $result);
        }

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
                    $supervision['supervision_attach_id'] = (string) $fileFS->storeFile($_FILES['attach']['tmp_name'], array('filename' => $_FILES['attach']['name'], 'contentType' => $_FILES["attach"]["type"], 'size' => $size, 'resoure' => 'attach'));
                } else {
                    $supervision['supervision_attach_id'] = (string) $exists->file['_id'];
                }
            }
        }

        //判断是否自动审核
        //增加 自增长的 no 值 
        $supervision['no'] = $this->getSeqNo($supervision['create_date']);

        //剔除标签
        $supervision['message'] = str_replace('&lt;?xml:namespace prefix = o ns = "urn:schemas-microsoft-com:office:office" />', "", $supervision['message']);
        //$supervision['message'] = strip_tags($supervision['message']);

        //创建信件
        $this->load->model('supervision_model', 'supervision');
        $supervision_id = $this->supervision->create($supervision);

		//发送短信
        if ($supervision['branch_id'] && $supervision['status'] ) {
            $this->audit_message($supervision_id);
        }

        if (empty($supervision_id)) {
            $this->resultJson('留言创建失败', 3);
        }
        $referer = "/interaction/";
        $this->resultJson('提交成功', 2, array('url' => $referer));
    }

    //信件是否自动审核
    private function autoAudit($product_id) {
        //判断是否是周末
        $this->load->model("site_holiday_model", "site_holiday");
        $holiday = $this->site_holiday->find(array("site_id" => $this->site_id), 1);
        if (in_array(date("Y-m-d"), $holiday['date'])) {
            return FALSE;
        }
        $data = array();
        $this->load->model("supervision_setting_model", "supervision_setting");
        $supervision_setting = $this->supervision_setting->find(array("site_id" => $this->site_id), 1);
        //array('未知信箱','书记信箱','市长信箱','问政部门','县(区)长信箱')
        //当前信箱自动审核时间
        $auto_time = $supervision_setting['auto_confirm_time_arr'][$product_id];
        if (!empty($auto_time)) {
            //当前时间偏移量
            $time_limit = time() - strtotime(date('Y-m-d', time()));
            if (($auto_time[0][0] < $time_limit) && ($time_limit < $auto_time[0][1]) || ($auto_time[1][0] < $time_limit) && ($time_limit < $auto_time[1][1])) {
                $confirmer = array("id" => "", "name" => "");
                $system_time = time();
                $data = array(
                    "confirm_date" => $system_time,
                    "update_date" => $system_time,
                    "confirm_remark" => "自动审核" . date("Y-m-d H:i:s"),
                    "confirmer" => $confirmer,
                    "status" => true,
                );
            }
        }
        return $data;
    }

    //查看信件
    public function detail() {
        $_id = (string) $this->input->get('_id');
        $supervision = $this->supervision->find(array("_id" => $_id, 'removed' => false));
        if (empty($supervision)) {
            show_error("信件有误！");
        }
        if (!empty($supervision['question_id'])) {
            $this->load->model('supervision_question_model', 'supervision_question');
            $question = $this->supervision_question->find(array("_id" => $supervision['question_id']), 1);
            if (empty($question)) {
                show_error("信箱类型有误");
            } else {
                $question_name = '<span style="color:red;">(' . $question['name'] . ')<span>';
                unset($question);
            }
        }
        $account_id = $_SESSION['account_id'];
        $login_status = FALSE;
        if ($account_id) {
            $login_status = TRUE;
            //获取网站会员信息
            $this->load->model('site_account_model', 'site_account');
            $account = $this->site_account->find(array('_id' => $account_id, 'site_id' => $this->site_id), 1);
            if ($account['type'] == 1) {
                $login_status = FALSE;
            }
            //判断用户身份(管理员)
            $this->load->model('site_user_model', 'site_user');
            $site_user = $this->site_user->find(array("account_id" => $account_id), 1);
            $data['is_admin'] = FALSE;
            if ($site_user['privilege_id'] == "53d1c7d59a05c20f4015125f") {
                $data['is_admin'] = TRUE;
                $data['supervision_update_url'] = "/admin/supervisionUpdate/" . $_id . "/";
                $data['supervision_delete_url'] = "/admin/supervisionDelete/" . $_id . "/";
            }
            $data['account'] = $account;
        }
        if (!$supervision['share_on']) {
            if ($supervision['member_id'] === $account_id) {
                $supervision['share_on'] = TRUE;
            }
			if($account['type'] == 1){
				$supervision['share_on'] = TRUE;
				if($site_user['branch_id'] != $supervision['branch_id']){
					$supervision['share_on'] = false;
				}
			}
        }
        $data['share_on'] = $supervision['share_on'];
        $data['login_status'] = $login_status;
        $data['rand'] = rand(0, 9);
        $this->supervision->update(array("_id" => $_id), array("hit" => $supervision['hit'] + 1));
        $supervision['date'] = date("Y-m-d H:i:s", $supervision['create_date']);
        //受理未回复的信件做提示
        $data['prompt'] = FALSE;
        if ($supervision['process_status'] == 2) {
            $data['prompt'] = TRUE;
            //咨询类是一个工作日，建议和投诉都是5个工作日。县市区长信箱所有都是5个工作日
            if ($supervision['question_id'] == "51a57d99df85a70d545ec0ea") {
                //咨询
                $day = 1;
            } else {
                $day = 5;
            }
            $data['prompt_content'] = "--受理部门尚未回复--<br>" . $this->branch_list[$supervision['branch_id']] . "已于" . date("Y-m-d H:i", $supervision['confirm_date']) . "成功受理你的问题，将于" . $day . "个工作日内答复您！";
        }
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
        $supervision['message'] = str_replace('&lt;?xml:namespace prefix = o ns = "urn:schemas-microsoft-com:office:office" />', "", $supervision['message']);
        $supervision['message'] = str_replace("[", "<", $supervision['message']);
        $supervision['message'] = str_replace("]", ">", $supervision['message']);
        $data['supervision'] = $supervision;
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
            $data['is_image'] = $is_image;
            $data['attach'] = array(
                'filename' => $attach_record->file['filename'],
                'size' => (int) ($attach_record->file['size'] / 1024),
                'contentType' => $attach_record->file['contentType'],
                'downloadUrl' => '/index.php?c=member&m=downloadFS&attach_id=' . $attach_record->file['_id'],
                'picUrl' => '/index.php?c=member&m=getImage&_id=' . $attach_record->file['_id'],
            );
        }
        $data['is_attach'] = $is_attach;

        $View = new Blitz('template/mobile/detail-interaction.html');
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
                    $reply[$key]['message'] = str_replace('&lt;?xml:namespace prefix = o ns = "urn:schemas-microsoft-com:office:office" />', "", $item['message']);
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
                            //如果是管理员具有修改回复功能
                            $reply[$key]['is_admin_reply'] = FALSE;
                            if ($site_user['privilege_id'] == "53d1c7d59a05c20f4015125f") {
                                $reply[$key]['is_admin_reply'] = TRUE;
                                $reply[$key]['supervision_update_url'] = "/admin/replyUpdate/" . $item['_id'] . "/";
                                $reply[$key]['supervision_delete_url'] = "/admin/replyDelete/" . $item['_id'] . "/";
                            }
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
                            //如果是管理员具有修改回复功能
                            $reply[$key]['is_admin_reply'] = FALSE;
                            if ($site_user['privilege_id'] == "53d1c7d59a05c20f4015125f") {
                                $reply[$key]['is_admin_reply'] = TRUE;
                                $reply[$key]['supervision_update_url'] = "/admin/replyUpdate/" . $item['_id'] . "/";
                                $reply[$key]['supervision_delete_url'] = "/admin/replyDelete/" . $item['_id'] . "/";
                            }
                        }
                    } else {
                        //$reply[$key]['user'] = '回复单位：宣城市人民政府网站';
                        unset($reply[$key]);
						continue;
                    }

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
                        'is_admin_reply' => $reply[$key]['is_admin_reply'],
                        'supervision_update_url' => $reply[$key]['supervision_update_url'],
                        'supervision_delete_url' => $reply[$key]['supervision_delete_url'],
                    ));
                }
            }
        }
        $data['location'] = '<a href="/">网站首页</a> / <a href="/nocache/interaction/">政民互动</a> / <span>互动信件</span>' . $question_name;

        $View->display($data);
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

    //信件再回复
    public function reply() {
        $account_id = $_SESSION['account_id'];
        $nickname = $_SESSION['nickname'];
        $this->load->model('site_account_model', 'site_account');
        $account = $this->site_account->find(array('_id' => $account_id, 'site_id' => $this->site_id), 1);
        if (!$account) {
            $this->resultJson('请登录网站会员后再回复', 3);
        }
        $captcha_chars = $_SESSION['captcha_chars'];
        if (strcasecmp($captcha_chars, $this->input->post('vcode'))) {
            $this->resultJson('验证码不正确', 4);
        }
        $data['message'] = $this->security->xss_clean($this->input->post('message'));
        $data['supervision_id'] = $this->security->xss_clean($this->input->post('supervision_id'));
        if (empty($data['message'])) {
            $this->resultJson('请填写回复内容', 4);
        }
        $data['create_date'] = $data['update_date'] = time();
        $data['user_id'] = (string) $account_id;
        $data["rand_key"] = $this->randomkeys(12);
        //剔除标签
        $data['message'] = str_replace('&lt;?xml:namespace prefix = o ns = "urn:schemas-microsoft-com:office:office" />', "", $data['message']);
        //$data['message'] = strip_tags($data['message']);
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

        //判断是不是自己的信件，如果是自己的在追加的审核通过
        $supervision = $this->supervision->find(array("_id" => $data['supervision_id']));
        
        //在追问是否审核
        $result = $this->autoAudit($supervision["product_id"]);
        $data['status'] = $result['status'];
        $system_time = time();
        if ($supervision['member_id'] == $account_id) {
            $data['reply_open'] = 1;
            $data['status'] = TRUE;
            $data['confirm_date'] = $system_time;
            $data['confirmer'] = array(
                "id" => "",
                "name" => "",
            );
        }
        //判断是不是管理员回复
        if ($account['type'] == 1) {
            $supervision_data = array(
                "status" => true,
                "process_status" => 3,
                "replies" => 1,
                "reply_confirmed" => true,
                "reply_date" => $system_time,
                "update_date" => $system_time,
            );
            $data['status'] = TRUE;
            $data['confirmer'] = array(
                "id" => $account_id,
                "name" => $nickname,
            );
            //针对市长信箱部门回复不自动审核
            if ($supervision['product_id'] == 2) {
                $data['status'] = FALSE;
                $supervision_data['reply_confirmed'] = FALSE;
            }
        }
        $this->load->model("supervision_reply_model", "supervision_reply");
        if ($this->supervision_reply->create($data)) {   
            //$this->supervision->update(array("_id" => $data['supervision_id']), $supervision_data);
            $this->resultJson('回复成功', 2);
        } else {
            $this->resultJson('网络出问题啦！回复失败', 3);
        }
    }

    //评论回复是否满意
    public function rating() {
        $account_id = $_SESSION['account_id'];
        $this->load->model('site_account_model', 'site_account');
        $account = $this->site_account->find(array('_id' => $account_id, 'site_id' => $this->site_id), 1);
        if (!$account) {
            $this->resultJson('请登录网站会员后再评论满意度', 3);
        }
        $data = $this->security->xss_clean($this->input->post('data'));
        $rating = (int) $data['rating'];
        $supervision_id = $data['supervision_id'];
        $this->load->model('supervision_model', 'supervision');
        if ($this->supervision->update(array("_id" => $supervision_id), array("rating" => $rating))) {
            $this->resultJson('评论成功', 2);
        } else {
            $this->resultJson('网络出问题啦！评论失败', 3);
        }
    }

    //信件问题类型划分列表
    public function supervisionList() {
        $question_id = (string) $this->input->get("question_id");
        $page = (int) $this->input->get('page');
        $this->load->model('supervision_question_model', 'supervision_question');
        $question = $this->supervision_question->find(array("_id" => $question_id), 1);
        if (empty($question)) {
            die("问题类型不存在");
        }
        $View = new Blitz('template/list-supervision.html');
        $struct_list = $View->getStruct();
        $filter = (array("question_id" => $question_id, 'status' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
        $total_row = $this->supervision->listCount(NULL, $filter);
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //列表
                if ($action == 'supervision') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = $question_id;
                    }

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemSupervision(array("question_id" => $_id_array), $limit, $offset, $length, $sort_by, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $item_list['page'] = $link;
                } elseif ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                } elseif ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $channel_id = $parent_id;
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
        $data['channel_name'] = "政民互动";
        $data['menu_id'] = $question_id;
        $data['question'] = $question;
        $data['location'] = '<a href="/">网站首页</a> / <a href="/nocache/interaction/">政民互动</a> / <a href="/supervision/supervisionList/question_id=' . (string) $question['_id'] . '/">' . $question['name'] . '</a>';
        $View->display($data);
    }

    //信箱类型
    public function productList() {
        $product_id = (int) $this->input->get("product_id");
        $branch_id = $this->security->xss_clean($this->input->get('branch_id'));
        if (!empty($branch_id)) {
            $this->load->model('site_branch_model', 'site_branch');
            $branch = $this->site_branch->find(array('_id' => $branch_id), 1, 0, array('id', 'parent_id', 'name'));
            if (empty($branch)) {
                show_error("部门不存在");
            }
        }
        if ($product_id < 0 || $product_id > 4) {
            show_error("信箱类型有误");
        }
        $page = (int) $this->input->get('page');
        if ($page < 1) {
            $page = 1;
        }
        if ($product_id == 2) {
            $View = new Blitz('template/list-supervision-sz.html');
        } elseif ($product_id == 3) {
            $View = new Blitz('template/list-supervision-branch.html');
        } elseif ($product_id == 4) {
            $View = new Blitz('template/list-supervision-xz.html');
        } else {
            $View = new Blitz('template/list-supervision.html');
        }

		if($product_id == 4){
			$product_id = array("\$gt"=>0);
		}
		
        $struct_list = $View->getStruct();
        if (!empty($branch)) {
            $filter = (array("product_id" => $product_id, "branch_id" => $branch_id, 'status' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
        } else {
            $filter = (array("product_id" => $product_id, 'status' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
        }
        $total_row = $this->supervision->listCount(NULL, $filter);
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';
                //列表
                if ($action == 'supervision') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = $product_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    if (!empty($branch_id)) {
                        $filter = array("product_id" => $product_id, "branch_id" => $branch_id);
                    } else {
                        $filter = array("product_id" => $product_id);
                    }
                    $item_list = $this->itemSupervision($filter, $limit, $offset, $length, $sort_by, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $item_list['page'] = $link;
                } elseif ($action == 'branch') {
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->itemBranch($channel_id, $limit, $offset, $length, $branch_id);
                } elseif ($action == 'question') {
                    $item_list = $this->itemQuestion();
                } elseif ($action == 'supervisionbranch') {
                    /*
                     * 统计部门信件列表 $supervision_type： 
                     * 0 =>问政市四大班子、市直党群部门、检察机关、审判机关等
                     * 1 =>公共服务、行政执法单位
                     * 2 =>县(市、区)长信箱
                     * 3 =>综合管理部门
                     * 4 =>市长信箱
                     * */
                    list($supervision_type, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->supervisionBranch($branch_id, $supervision_type, $limit, $offset, $length, $sort_by, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['channel_name'] = "政民互动";
        if ($branch_id) {
            $branch_name = $this->branch_list[$branch_id];
        } else {
            $branch_name = "问政部门";
        }
        $data['branch_name'] = $branch_name;
        if ($branch_id) {
            $data['write_url'] = '/supervision/write/product_id=' . $product_id . '/branch_id=' . $branch_id . '/';
        } else {
            $data['write_url'] = '/supervision/write/product_id=' . $product_id . '/';
        }
        $data['service_url'] = '/serviceBranch/?type=53d8e27db1a64ce7ce426f7d&_id=' . $branch_id;
        $data['location'] = '<a href="/">网站首页</a> / <a href="/nocache/interaction/">政民互动</a> / <a href="/supervision/productList/product_id=/' . (string) $product_id . '/">' . (string) $this->product_name[$product_id] . '</a>';
        $View->display($data);
    }

    /*
      获取信件的编号
      $curTime 当前时间
     */

    protected function getSeqNo($curTime) {

        $default_no_rule = "%YEAR%%MONTH%%DAY%%SERIAL_NO%";
        if (isset($this->vals['setting'] ["supervision_no_rule"]) && !empty($this->vals['setting'] ["supervision_no_rule"])) {
            $default_no_rule = $this->vals['setting'] ["supervision_no_rule"];
        }

        $y = date("Y", $curTime);
        $m = date("m", $curTime);
        $d = date("d", $curTime);
        $numberTags = array(
            '%YEAR%' => $y,
            "%MONTH%" => $m,
            "%DAY%" => $d,
            "%SERIAL_NO%" => "0000",
            "%SERIAL_NO_5%" => "00000",
            "%SERIAL_NO_6%" => "000000",
        );

        $number_patern = $this->replaceTag($default_no_rule, $numberTags);

        $this->load->model('sequence_model', 'sequence');
        $supervision_no = (int) $this->sequence->getNoSeq($number_patern, "supervision");

        $no_4 = str_pad($supervision_no, 4, "0", STR_PAD_LEFT);
        $no_5 = str_pad($supervision_no, 5, "0", STR_PAD_LEFT);
        $no_6 = str_pad($supervision_no, 6, "0", STR_PAD_LEFT);
        $numberTags = array(
            '%YEAR%' => $y,
            "%MONTH%" => $m,
            "%DAY%" => $d,
            "%SERIAL_NO%" => $no_4,
            "%SERIAL_NO_5%" => $no_5,
            "%SERIAL_NO_6%" => $no_6,
        );

        $supervision_number = $this->replaceTag($default_no_rule, $numberTags);

        return $supervision_number;
    }

    private function replaceTag($message, $serialTags) {
        if (is_array($serialTags)) {
            $keys = array_keys($serialTags);
            $values = array_values($serialTags);
            $message = str_replace($keys, $values, $message);
            // foreach ($serialTags as $key => $val) { 
            // $message = str_replace('%' . $key . '%', $val, $message); 
            // } 
        }

        return $message;
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

    //不满意办贴
    public function bantie() {
        $action = $this->input->get("action");
        $page = (int) $this->input->get('page');
        if ($page < 1) {
            $page = 1;
        }
        $View = new Blitz('template/list-supervision-bantie.html');
        $struct_list = $View->getStruct();
        if ($action === "red") {
            $action_name = "红牌督办";
            $filter = array('status' => true, 'removed' => False, 'priority' => 1, 'process_status' => 1, 'site_id' => $this->site_id);
        } elseif ($action === "yellow") {
            $action_name = "黄牌督办";
            $filter = array('status' => true, 'removed' => False, 'priority' => 2, 'process_status' => 1, 'site_id' => $this->site_id);
        } else {
            $action_name = "不满意办贴";
            $filter = array('status' => true, 'removed' => False, 'rating' => 1, 'process_status' => 3, 'site_id' => $this->site_id);
        }
        $total_row = $this->supervision->listCount(NULL, $filter);
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';
                //列表
                if ($action == 'supervision') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemSupervision($filter, $limit, $offset, $length, $sort_by, $date_format);
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
        $data['channel_name'] = "政民互动";
        $data['location'] = '<a href="/">网站首页</a> / <a href="/nocache/interaction/">政民互动</a> / '.$action_name;
        $View->display($data);
    }

	//信件自动审核后给该部门的每个管理员发送一条短信提醒
	public function audit_message($supervision_id = NULL) {
        if (empty($supervision_id)) {
            return false;
        }
        //获取信件信息
        $this->load->model('supervision_model', 'supervision');
        $supervision = $this->supervision->find(array("_id" => $supervision_id, 'removed' => false));
        //获取会员信息
        $this->load->model('site_account_model', 'site_account');
        $account = $this->site_account->find(array('_id' => $supervision['member_id']), 1);
        //获取部门ID及名称
        $this->load->model('site_branch_model', 'site_branch');
        $branch = $this->site_branch->find(array('_id' => $supervision['branch_id']), 1);
        //获取需要发送短信的所有管理员
        $this->load->model("site_user_model", "site_user");
        $admin = $this->site_user->find(array("branch_id" => $supervision['branch_id']), NULL);
        $content = $branch['name'] . "部门管理员您好，会员名为：" . $account["name"] . "（网名：" . $account["nickname"] . "）于" . date("Y年m月d日H时i分", $supervision['create_date']) . "提交了主题为：“" . $supervision['subject'] . "”的问政信息 ，请及时办理";
        //发送短信
        foreach ($admin as $item) {
            $admin_info = $this->site_account->find(array('_id' => (string) $item['account_id'], 'phone' => array("\$ne" => ''), "type" => 1, "activated" => true, "status" => true, "removed" => false), 1);
            if (!empty($admin_info)) {
                $this->send_sms_message($admin_info['phone'], $content);
            }
        }
    }

}

/* End of file supervision.php */
/* Location: ./application/controllers/supervision.php */
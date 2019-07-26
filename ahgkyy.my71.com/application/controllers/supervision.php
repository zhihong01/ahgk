<?php

@session_start();

class supervision extends MY_Controller {

    protected $supervision_rating = array("未评论", "不满意", "", "基本满意", "", "满意");

    private $channel_id = "5720870ad09491e8c76d48de";
    private $channel_tree = array("name" => "问政", "link_url" => "/interaction/");
    private $question_list=array('投诉举报','建言献策','问题反映','咨询求助');

    public function __construct() {
        parent::__construct();
        $this->load->model('supervision_model', 'supervision');
        $this->load->model('content_model', 'content');
        $this->question_list = $this->questionList();
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
        $this->load->model('site_branch_model', 'site_branch');
        if ($this->channel_id) {
            $channel_tree = $this->site_channel_tree->find(array('_id' => $this->channel_id), 1);
            if ($channel_tree) {
                $this->channel_tree = $channel_tree;
            }
        }
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
                $result[$key] = mb_substr($value, 0, $length);
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
        $filter = array_merge($filter, array('share_on' => true,'status' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id, 'submitter_share_on' => true));
        $select = array('_id', 'process_status', 'subject', 'create_date', 'update_date', 'branch_id', 'no', 'question_id', 'hit','views');
        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $item_list = $this->supervision->find($filter, $limit, $offset, $select, $arr_sort);
        //print_r($item_list);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
            if ($key % 2 !== 0) {
                $item_list[$key]['class'] = "bgcolor";
            }
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
            } else {
                $item_list[$key]['branch'] = '';
            }
            //信件问题类别
            if (isset($this->question_list[$item['question_id']])) {
                $item_list[$key]['question_name'] = $this->question_list[$item['question_id']];
            } else {
                $item_list[$key]['question_name'] = '';
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

        $filter = array_merge($filter, array('status' => true, 'share_on' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
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
        $arr_sort = array('sort' => 'desc');

        $item_list = $this->site_branch->find($filter, $limit, $offset, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            if ($item['_id'] == $current_id) {
                $item_list[$key]['aon'] = 'class="aon"';
                $item_list[$key]['selected'] = 'selected';
            } else {
                $item_list[$key]['aon'] = '';
            }
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length);
                $item_list[$key]['short_towns'] = mb_substr($item['name'], 0, $length);
            } else {
                $item_list[$key]['short_towns'] = $item['name'];
                $item_list[$key]['short_name'] = $item['name'];

            }
            $item_list[$key]['url'] = '/supervision/branch/' . $item['_id'] . '/';
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

    // 信箱问题信息类别
    protected function itemQuestion() {
        $this->load->model('supervision_question_model', 'supervision_question');
        $item_list = $this->supervision_question->find(array('removed' => false, 'site_id' => $this->site_id), null, NULL, "*", array("create_date" => "ASC"));
        return $item_list;
    }

    // 信箱类别
    protected function itemProduct() {
        $this->load->model("site_dictionary_model", "site_dictionary");
        $item_list = $this->site_dictionary->find(array("key_word" => "supervision_product_name", "status" => TRUE), NULL, NULL, "*", array("sort" => "DESC"));
        if (!empty($item_list)) {
            foreach ($item_list as $key => $item) {
                $result = array_search("未知信箱", $item, true);
                if ($result) {
                    unset($item_list[$key]);
                }
            }
        }
        return $item_list;
    }

    protected function itemList($_id_list, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0, $description_length = 100) {
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

    protected function itemLeaderList($leader, $limit = 10, $offset = 0, $product_id = 2, $date_format = 0) {
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'job_title', 'name', 'bid');
        $this->load->model('site_leader_model', 'site_leader');
        $item_list = $this->site_leader->find(array('type_id' => $leader['type_id'], 'status' => TRUE, 'removed' => False, 'site_id' => $this->site_id), $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $leader_list = $this->site_branch->find(array('name' => $item['name']), 1, 0, array('_id'));
            if (empty($leader_list)) {
                continue;
            }
            $item_list[$key]['url'] = '/supervision/productList/product_id=' . $product_id . '/branch_id=' . $leader_list['_id'] . '/';
        }
        return $item_list;
    }

    //不满意办贴
    public function bantie() {

        $action = $this->input->get("action");
        $page = (int) $this->security->xss_clean(htmlentities($this->input->get('page'),ENT_COMPAT,'UTF-8'));
        if ($page < 1) {
            $page = 1;
        }
        $View = new Blitz('template/supervision/list-supervision-bantie.html');
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
        $filter['create_date'] = array('$gte' => strtotime(date("Y-m-01")));
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
        $data['channel_name'] = "互动咨询";
        $data['location'] = '<a href="/">网站首页</a> / <a href="/nocache/interaction/">互动咨询</a> / ' . $action_name;
        $View->display($data);
    }

    public function index() {

        $product_id = (int) $this->security->xss_clean(htmlentities($this->input->get('product_id'),ENT_COMPAT,'UTF-8'));
        $page = (int) $this->security->xss_clean(htmlentities($this->input->get('page'),ENT_COMPAT,'UTF-8'));
        if ($page == 0) {
            $page = 1;
        }
        $total_row = $this->supervision->count(array('status' => true, 'removed' => False,'submitter_share_on' => true, 'share_on' => true, 'cancelled' => false, 'site_id' => $this->site_id, 'product_id' => $product_id));

        $View = new Blitz('template/supervision/list-supervision.html');

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
                    $item_list = $this->itemSupervision(array('product_id' => $product_id), $limit, $offset, $length, $sort_by, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, false));
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
                } elseif ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }elseif ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $channelid = $parent_id;
                    } else {
                        $channelid = $parent_channel['_id'];
                    }

                    $menu_list = $this->getMenu($channelid, $limit, $offset, $length);
                    $i = 0;
                    foreach ($menu_list as $key => $menu) {
                        $item_list[$i]['_id'] = $key;
                        $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
                        $has_child=$this->getMenu($key, 10, 0, 10);
                        $item_list[$i]['child_menu_html']='';
                        foreach($has_child as $k=>$v){
                            $item_list[$i]['child_menu_html']=$item_list[$i]['child_menu_html'].'<a href="/content/channel/'.$k.'/">'.$v.'</a>';
                        }
                        $i++;
                    }

                }
                $data[$struct_val] = $item_list;
            }
        }
        // 信件统计
        $data['total_supervision'] = $this->supervision->count(array('status' => true, 'removed' => False, 'share_on' => true, 'cancelled' => false, 'site_id' => $this->site_id));
        $data['processed_supervision'] = $this->supervision->count(array('status' => true, 'removed' => False, 'process_status' => 5, 'share_on' => true, 'cancelled' => false, 'site_id' => $this->site_id));
        $data['processing_supervision'] = $data['total_supervision'] - $data['processed_supervision'];
        $data['supervision_total'] = $this->supervision->count(array('status' => true, 'removed' => false, 'site_id' => $this->site_id));
        $data['supervision_processed'] = $this->supervision->count(array('process_status' => 5, 'status' => true, 'removed' => false, 'site_id' => $this->site_id)) + $this->supervision->count(array('process_status' => 4, 'status' => true, 'removed' => false, 'site_id' => $this->site_id)) + $this->supervision->count(array('process_status' => 3, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        $data['supervision_processing'] = $data['supervision_total'] - $data['supervision_processed'];


        if($product_id == 3){
            $data['location'] = '<a href="/">网站首页</a> > <a href="/content/channel/5bac372e7f8b9a343963e2fd/">互动交流</a> > <span>部长信箱</span>';
            $data['menu_name'] = "部长信箱";
        }elseif($product_id == 4){
            $data['location'] = '<a href="/">网站首页</a> > <a href="/content/channel/5bac372e7f8b9a343963e2fd/">互动交流</a> > <span>部门信箱</span>';
            $data['menu_name'] = "部门信箱";
        }elseif($product_id == 5){
            $data['location'] = '<a href="/">网站首页</a> > <a href="/content/channel/5bac372e7f8b9a343963e2fd/">互动交流</a> > <span>信件查询</span>';
            $data['menu_name'] = "信件查询";
        }

        $data['product_id'] = $product_id;
        $data['channel_name'] = "互动交流";
        $View->display($data);
    }

    public function branch() {
        $branch_id = $this->security->xss_clean(htmlentities($this->input->get('_id'),ENT_COMPAT,'UTF-8'));
        $product_id = 3;
        $page = (int) $this->security->xss_clean(htmlentities($this->input->get('page'),ENT_COMPAT,'UTF-8'));
        if ($page == 0) {
            $page = 1;
        }
        $data = array();
        if (empty($branch_id)) {
            $total_row = $this->supervision->count(array('status' => true, 'removed' => False, 'share_on' => true, 'cancelled' => false, 'site_id' => $this->site_id, 'product_id' => $product_id));
        } else {
            $total_row = $this->supervision->count(array('branch_id' => $branch_id, 'status' => true, 'removed' => False, 'share_on' => true, 'cancelled' => false, 'site_id' => $this->site_id, 'product_id' => $product_id));
        }
        $View = new Blitz('template/' . __CLASS__ . '/list-supervision-branch.html');
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
                        $filter = array('product_id' => $product_id);
                    } else {
                        $filter = array('branch_id' => $branch_id, 'product_id' => $product_id);
                    }
                    $item_list = $this->itemSupervision($filter, $limit, $offset, $length, $sort_by, $date_format);
                }
                // 部门
                if ($action == 'branch') {
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->itemBranch($channel_id, $limit, $offset, $length, $branch_id);
                }
                // 部门
                if ($action == 'question') {
                    $item_list = $this->itemQuestion();
                }
                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, TRUE));
                }
                if ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $total_where = array("branch_id" => $branch_id, "site_id" => $this->site_id, 'cancelled' => false, 'removed' => False);
        $total_reply_where = array("branch_id" => $branch_id, 'process_status' => array("\$gt" => 2), "site_id" => $this->site_id, 'cancelled' => false, 'removed' => False);
        $today_time = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $today_total_where = array("branch_id" => $branch_id, 'create_date' => array("\$gt" => $today_time), "site_id" => $this->site_id, 'cancelled' => false, 'removed' => False);
        $today_reply_where = array("branch_id" => $branch_id, 'create_date' => array("\$gt" => $today_time), 'process_status' => array("\$gt" => 2), "site_id" => $this->site_id, 'cancelled' => false, 'removed' => False);
        // 按部门统计信件回复总数
        $data['total'] = $this->supervision->count($total_where);
        $data['total_reply'] = $this->supervision->count($total_reply_where);
        //今日信件与今日回复
        $data['today_total'] = $this->supervision->count($today_total_where);
        $data['today_reply'] = $this->supervision->count($today_reply_where);

        $data['channel_name'] = $this->channel_tree['name'];
        $data['channel_id'] = (string) $this->channel_tree['_id'];
        $data['product_id'] = $product_id;
        $data['product_name'] = (string) $this->product_name[$product_id];
        $data['write_url'] = '/nocache/supervision/write/?product_id=3&branch_id=' . $branch_id;
        $data['location'] = '<a href="/">首页</a> > <a href="/content/channel/5bac372e7f8b9a343963e2fd/">互动咨询</a> > <span>部门信箱</span>';
        $View->display($data);
    }

    public function hotline() {
        $product_id = 5;
        $page = (int) $this->security->xss_clean(htmlentities($this->input->get('page'),ENT_COMPAT,'UTF-8'));
        if ($page == 0) {
            $page = 1;
        }
        $total_row = $this->supervision->count(array('status' => true, 'removed' => False, 'share_on' => true, 'cancelled' => false, 'site_id' => $this->site_id, 'product_id' => $product_id));
        $View = new Blitz('template/supervision/list-supervision-12345hotline.html');
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
                    $item_list = $this->itemSupervision(array('product_id' => $product_id), $limit, $offset, $length, $sort_by, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, TRUE));
                } elseif ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->itemList($channel_id, $limit, $offset, $length, $sort_by, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['location'] = '<a href="/">首页</a> > <a href="/content/channel/5bac372e7f8b9a343963e2fd/">互动咨询</a> > <span>12345县长热线</span>';

        $View->display($data);
    }

    public function write() {
        $product_id = (int) $this->security->xss_clean($this->input->get('product_id'));
        $branch_id = $this->security->xss_clean($this->input->get('branch_id'));
        //$branch_id = '552493ed8161d7aa2bf6c80a7';
        //print_r($product_id);
        $zxts = $this->security->xss_clean($this->input->get('zxts'));
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        if (!empty($product_id)) {
            $this->load->model("site_dictionary_model", "site_dictionary");
            $site_dictionary = $this->site_dictionary->find(array("key_word" => "supervision_product_name", "id" => $product_id, "status" => TRUE));
            if (empty($site_dictionary)) {
                show_error("信箱类型有误");
            }
        } else {
            $product_id = 3;
        }
        $question_id = $this->security->xss_clean($this->input->get('question_id'));

        //当前部门
        //$current_branch = $this->site_branch->find(array('_id'=>$branch_id,'removed' => false, 'status' => true), 1, 0);
        //print_r($current_branch);
        if (!empty($question_id)) {
            $this->load->model('supervision_question_model', 'supervision_question');
            $question = $this->supervision_question->find(array("_id" => $question_id), 1);
            if (empty($question)) {
                show_error("信箱类型有误");
            } else {
                $this->question_name = '<span style="color:red;">(' . $question['name'] . ')<span>';
                unset($question);
            }
        }
           

            $View = new Blitz('template/supervision/write-supervision.html');

        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                // 部门
//                if ($action == 'branch') {
//                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
//                    $item_list = $this->branchList($channel_id, $limit, $offset, $length,$branch_id,$product_id);
//                    //print_r($item_list);
//                } else
                    if ($action == 'menu') {

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
                    echo 1;
                    $item_list = $this->itemQuestion();
                } elseif ($action == 'product') {
                    $item_list = $this->itemProduct();
                } elseif ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);

                } elseif ($action == 'supervision') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $filter_list = array('status' => true, 'removed' => False, 'share_on' => true, 'cancelled' => false,'product_id'=>$product_id);
                    $total_row = $this->supervision->count($filter_list);
                    $item_list = $this->supervisionList($filter_list, $limit, $offset, $length, $sort_by, $date_format);
                }elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, false));
                }
                $data[$struct_val] = $item_list;
            }
        };
        $data['product_id'] = $product_id;
        $data['product_name'] = $this->product_name[$product_id];
        $data['question_id'] = $question_id;
        $data['branch_id'] = $branch_id;
        $data['channel_name'] = $this->channel_tree['name'];
        $data['channel_id'] = (string) $this->channel_tree['_id'];
        $data['location'] = '<a href="/">首页</a> > <a href="/interaction/">互动交流</a> > 我要写信';
        $data['rand'] = rand(0, 9);
        $data['current_branch'] = $current_branch;
        //判断是否有会员

        $this->load->model('site_account_model', 'site_account');
        $account = $this->site_account->find(array('_id' => $this->member['account_id']), 1);

        $this->load->model('site_member_model', 'site_member');
        $member = $this->site_member->find(array('account_id' => $this->member['account_id']), 1);
        if (empty($member)) {
            $member = $this->site_member->field();
        }

        $data['account'] = array_merge($account, $member);
        $data['is_login'] = empty($account) ? FALSE : TRUE;//print_r($account);
        //print_r($data);
        $View->display($data);
    }

    //创建信件
    public function create() {
        //写信是否要求是会员，在MY_Controller中 $oprn_member 对象设置开启与关闭
        if ($this->oprn_member) {
            $account_id = $this->member['account_id'];
            $this->load->model('site_account_model', 'site_account');
            $account = $this->site_account->find(array('_id' => $account_id, 'site_id' => $this->site_id), 1);
            if (!$account) {
                $this->resultJson('请登录网站会员后再写信', 3);
            }
        }
        $captcha_chars = $_SESSION['captcha_chars'];
        if (strcasecmp($captcha_chars, $this->input->post('vcode'))) {
            $this->resultJson('验证码不正确', 4);
        }
        unset($_SESSION['captcha_chars']);
        $customer = $this->security->xss_clean($this->input->post('customer'));
        $supervision = $this->security->xss_clean($this->input->post('supervision'));
//        if (empty($supervision["branch_id"])) {
//            $this->resultJson('请选择部门！', 4);
//        }
        if (!$this->valid_email($customer["email"])) {
            $this->resultJson('邮件地址不正确', 4);
        }
        if (empty($supervision["subject"])) {
            $this->resultJson('留言主题不能为空', 4);
        }
        if (empty($supervision["message"])) {
            $this->resultJson('留言内容不能为空', 4);
        }
        if ($supervision['share_on'] == 1) {
            $supervision['share_on'] = TRUE;
        } else {
            $supervision['share_on'] = FALSE;
        }
        //当前系统时间
        $system_time = time();
        //如果会员开启
        if ($this->oprn_member) {
            $creator = array("id" => (string) $account['_id'], "name" => $account['nickname']);
            $supervision["member_id"] = (string) $account['_id'];
            echo 2;
        } else {
            //创建会员
            $this->load->model('site_account_model', 'site_account');
            $account_email = $this->site_account->find(array('email' => $customer["email"], 'type' => 2), 1);
            if ($account_email) {
                $account = $account_email;
                $creator = array("id" => (string) $account['_id'], "name" => $account['nickname']);
                $supervision["member_id"] = (string) $account['_id'];
            } else {
                $rand_key = $this->randKey(6);
                $data = array(
                    'nickname' => $customer["name"],
                    'password' => md5(md5("i20s14") . $rand_key),
                    'email' => $customer["email"],
                    'site_id' => $this->site_id,
                    'client_ip' => $this->client_ip,
                    'create_date' => $system_time,
                    'rand_key' => $rand_key,
                    'status' => true,
                );
                $this->load->model('member_register_tmp_model', 'member_register_tmp');
                $this->member_register_tmp->update(array('email' => $customer["email"], 'site_id' => $this->site_id), $data, array('upsert' => TRUE, 'status' => true));
                //导入会员信息
                $datas = array(
                    'address' => array('province' => '', 'city' => '', 'area' => '', 'street' => $customer['address']),
                    'password' => $data['password'],
                    'activated' => FALSE,
                    'email' => $customer["email"],
                    'site_id' => $this->site_id,
                    'create_date' => $system_time,
                    'nickname' => $customer["name"],
                    'name' => $customer["name"],
                    'phone' => $customer["phone"],
                    'rand_key' => $rand_key,
                    'removed' => False,
                    'status' => FALSE,
                    'type' => 2,
                );
                $account_id = (string) $this->site_account->create($datas);
                if ($account_id) {
                    $this->load->model("site_member_model", "site_member");
                    $this->site_member->create(array('account_id' => $account_id, 'site_id' => $this->site_id));
                    $creator = array("id" => $account_id, "name" => $customer["name"]);
                    $supervision["member_id"] = $account_id;
                } else {
                    $this->resultJson('非常抱歉！网络出问题了,请稍后再试', 3);
                }
            }
        }

        $current_branch=$this->site_branch->find(array("_id"=>$supervision["branch_id"]));
        $supervision["client_ip"] = $this->client_ip;
        $supervision["site_id"] = $this->site_id;
        $supervision["cancelled"] = false;
        $supervision["removed"] = false;
        $supervision["status"] = false;
        $supervision["reply_date"] = 0;
        $supervision["create_date"] = $system_time;
        $supervision["update_date"] = $system_time;
        $supervision["process_status"] = 1;
        $supervision["product_id"] = (int)$supervision["product_id"];
        $supervision["submitter_share_on"] = (int) $supervision["submitter_share_on"] == 1 ? TRUE : FALSE;
        $supervision["share_on"] = (int)$supervision["share_on"]==1?true:false;
        $supervision["is_public"] = false;
        $supervision["assigned_user"] = array();
        $supervision["creator"] = $creator;
        $supervision["reply_confirmed"] = false;
        //剔除标签
        $supervision["subject"]=strip_tags(html_entity_decode($supervision['subject']));
        $supervision["message"]=strip_tags(html_entity_decode($supervision['message']));
        //自动增长的 信件编号 值
        $supervision['no'] = (string) $this->getSeqNo($supervision['create_date']);
        //信件查询码
        $supervision['no_password'] = $this->randomkeys(8);
        $this->load->model('supervision_model', 'supervision');
        $supervision_id = $this->supervision->create($supervision);
        if (empty($supervision_id)) {
            $this->resultJson('网络出问题了,请稍后再试!', 3);
        }
        $url = "/supervision/productList/product_id=" . $supervision["product_id"] . "/";
        //$this->resultJson('信件提交成功!我们会尽快处理您的信件!信件查询编号为：'.$supervision['no'].'，信件查询码为：'. $supervision['no_password'] , 2, array('url' => $url));
        $this->resultJson('信件提交成功!我们会尽快处理您的信件!');
    }



    //查看信件
    public function detail() {
        $_id = (string) $this->security->xss_clean(htmlentities($this->input->get('_id'),ENT_COMPAT,'UTF-8'));

            $supervision = $this->supervision->find(array("_id" => $_id, 'removed' => false));

        if (empty($supervision)) {
            show_404("信件不存在");
        }
        //满意度
       $data['is_rating'] = FALSE;
        //$member = $this->is_login();
        if ($supervision['process_status'] == "3" && $supervision['member_id'] == (string) $member['_id']) {//已处理
            $data['is_rating'] = TRUE;
            $this->load->model('supervision_rating_mean_model', 'supervision_rating_mean');
            $data['rating'] = $this->supervision_rating_mean->find(array("supervision_id" => $_id), 1);
            //var_dump($data['rating']);
            if (empty($data['rating'])) {
                $data['rating'] = array(
                    "mean" => array(
                        "stat_0" => 0,
                        "stat_1" => 0,
                        "stat_2" => 0,
                        "stat_3" => 0,
                        "stat_4" => 0,
                        "stat_5" => 0,
                    ));
            }
        }
        $this->supervision->update(array("_id" => $_id), array("hit" => $supervision['hit'] + 1));
        $supervision['date'] = date("Y-m-d", $supervision['create_date']);
        $supervision['date_ymd'] = date("Y-m-d", $supervision['create_date']);
        $supervision['process_status'] = $this->supervision_status[$supervision['process_status']];
        $supervision['hit'] ++;
        $supervision['title'] = $supervision['subject'];
        //$account=$this->is_login();
        //if($supervision['member_id']==$account['_id']){
           $supervision['share_on'] = True;
        //}else{
        //    $supervision['share_on'] = $supervision['share_on'];
        //}

        $supervision['creator']['name'] = mb_substr($supervision['creator']['name'], 0, 1) . '**';

        $this->question_list = $this->questionList();
        $supervision['question_name'] = $this->question_list[$supervision['question_id']];
        $data['supervision'] = $supervision;
        $View = new Blitz('template/supervision/detail-supervision.html');
        $this->load->model('supervision_reply_model', 'supervision_reply');
        $reply = $this->supervision_reply->find(array("supervision_id" => $_id, 'status' => true, 'type' => 1),1,0);

        if (!empty($reply)) {
            $reply['have'] = true;

            $reply['date'] = date("Y-m-d", $reply['create_date']);
            // 回复单位

            $reply['have'] = true;
        }
        //$this->is_login();
        $struct_list = $View->getStruct();
        if ($View->hasContext('remessage')) {
            // $this->load->helper('number');
            $reply_list = $this->supervision_reply->find(array("supervision_id" => $_id, 'status' => true, 'type' => 1), null);
            //var_dump($reply_list);
            foreach ($reply_list as $item) {

                $View->block('/remessage',array('_id' => (string)$item['_id'],
                    'branch_name' => $this->branch_list[$supervision['branch_id']],
                    'message' => $item['message'],
                    'date' => date("Y-m-d H:i:s", $item['create_date'])));
            }

        }


        $data['reply'] = $reply;
        $data['channel_name'] = $this->channel_tree['name'];
        $data['channel_id'] = (string) $this->channel_tree['_id'];
        $data['location'] = '<a href="/">首页</a> > <a href="/content/channel/5bac372e7f8b9a343963e2fd/">互动咨询</a> > 信件详情';



        $View->display($data);
    }

    //信件再回复
    public function reply() {
        $account_id = $_SESSION['account_id'];
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
        $this->load->model("supervision_reply_model", "supervision_reply");
        if ($this->supervision_reply->create($data)) {
            $this->resultJson('回复成功', 2);
        } else {
            $this->resultJson('网络出问题啦！回复失败', 3);
        }
    }

    //评论回复是否满意
    public function rating() {
        $rating = (int) $this->security->xss_clean($this->input->post('rating'));
        $supervision_id = $this->security->xss_clean($this->input->post('supervision_id'));
        $this->load->model("supervision_rating_ip_model", "supervision_rating_ip");
        $ip = $this->input->ip_address();
        $rating_ip = $this->supervision_rating_ip->find(array("supervision_id" => $supervision_id, "ip" => $ip), 1);
        if ($rating_ip) {
            $this->resultJson('您已经评论过该信件！', 3);
        }
        $this->supervision_rating_ip->create(array("supervision_id" => $supervision_id, "ip" => $ip, "create_date" => time()));
        $this->load->model('supervision_rating_mean_model', 'supervision_rating_mean');
        $rating_mean = $this->supervision_rating_mean->find(array("supervision_id" => $supervision_id));
        $this->load->model('supervision_model', 'supervision');
        if ($rating_mean) {
            $rating_mean['rating'][$rating] ++;
            $sum = array_sum($rating_mean['rating']);
            $max = max($rating_mean['rating']);
            foreach ($rating_mean['rating'] as $key => $item) {
                if ($item == $max) {
                    $max_key = $key;
                }
            }
            $data = array(
                'stat_0' => sprintf("%.2f", substr(sprintf("%.3f", ($rating_mean['rating'][0] / $sum) * 100), 0, -2)),
                'stat_1' => sprintf("%.2f", substr(sprintf("%.3f", ($rating_mean['rating'][1] / $sum) * 100), 0, -2)),
                'stat_2' => sprintf("%.2f", substr(sprintf("%.3f", ($rating_mean['rating'][2] / $sum) * 100), 0, -2)),
                'stat_3' => sprintf("%.2f", substr(sprintf("%.3f", ($rating_mean['rating'][3] / $sum) * 100), 0, -2)),
                'stat_4' => sprintf("%.2f", substr(sprintf("%.3f", ($rating_mean['rating'][4] / $sum) * 100), 0, -2)),
                'stat_5' => sprintf("%.2f", substr(sprintf("%.3f", ($rating_mean['rating'][5] / $sum) * 100), 0, -2)),
            );
            $this->supervision_rating_mean->update(array("supervision_id" => $supervision_id), array("rating" => $rating_mean['rating'], "mean" => $data));
            $this->supervision->update(array("_id" => $supervision_id), array("rating" => $max_key));
        } else {
            $this->supervision_rating_mean->create(array("supervision_id" => (string) $supervision_id, "mean" => array("stat_" . $rating => 100)));
            $this->supervision->update(array("_id" => $supervision_id), array("rating" => $rating));
        }
        $this->resultJson('评论成功！', 2);
    }

    //信件问题类型划分列表
    public function supervisionList() {
        $question_id = (string) $this->security->xss_clean(htmlentities($this->input->get("question_id"),ENT_COMPAT,'UTF-8'));
        $page = (int) $this->security->xss_clean(htmlentities($this->input->get('page'),ENT_COMPAT,'UTF-8'));
        $this->load->model('supervision_question_model', 'supervision_question');
        $question = $this->supervision_question->find(array("_id" => $question_id), 1);
        if (empty($question)) {
            show_error("问题类型不存在");
        }
        $View = new Blitz('template/list-supervision.html');
        $struct_list = $View->getStruct();
        $filter = (array("question_id" => $question_id, 'status' => true, 'share_on' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
        $total_row = count($this->supervision->find($filter, NULL));
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
        $data['channel_name'] = "互动咨询";
        $data['menu_id'] = $question_id;
        $data['question'] = $question;
        $data['write_url'] = '/nocache/supervision/write/question_id=' . $question_id . '/';
        $data['location'] = '<a href="/">网站首页</a> > <a href="/interaction/">互动咨询</a> > <a href="/supervision/supervisionList/question_id=' . (string) $question['_id'] . '/">' . $question['name'] . '</a>';

        $View->display($data);
    }

    //信箱类型
    public function productList() {
        $product_id = (int) $this->security->xss_clean(htmlentities($this->input->get("product_id"),ENT_COMPAT,'UTF-8'));
        $this->load->model("site_dictionary_model", "site_dictionary");
        $product = $this->site_dictionary->find(array("key_word" => "supervision_product_name", "id" => $product_id, "status" => TRUE));
        if (empty($product)) {
            show_error("信箱类型有误");
        }
        $branch_id = $this->security->xss_clean(htmlentities($this->input->get('branch_id'),ENT_COMPAT,'UTF-8'));
        if (!empty($branch_id)) {
            $this->load->model('site_branch_model', 'site_branch');
            $branch = $this->site_branch->find(array('_id' => $branch_id), 1, 0, array('id', 'parent_id', 'name'));
            if (empty($product)) {
                show_error("部门不存在");
            }
            $data['branch_id'] = $branch_id;
            $total_where = array("branch_id" => $branch_id, "product_id" => $product_id, "site_id" => $this->site_id, 'cancelled' => false, 'removed' => False);
            $total_reply_where = array("branch_id" => $branch_id, "product_id" => $product_id, 'process_status' => array("\$gt" => 2), "site_id" => $this->site_id, 'cancelled' => false, 'removed' => False);
            $today_time = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
            $today_total_where = array("branch_id" => $branch_id, "product_id" => $product_id, 'create_date' => array("\$gt" => $today_time), "site_id" => $this->site_id, 'cancelled' => false, 'removed' => False);
            $today_reply_where = array("branch_id" => $branch_id, "product_id" => $product_id, 'create_date' => array("\$gt" => $today_time), 'process_status' => array("\$gt" => 2), "site_id" => $this->site_id, 'cancelled' => false, 'removed' => False);
        } else {
            $total_where = array("product_id" => $product_id, "site_id" => $this->site_id, 'cancelled' => false, 'removed' => False);
            $total_reply_where = array("product_id" => $product_id, 'process_status' => array("\$gt" => 2), "site_id" => $this->site_id, 'cancelled' => false, 'removed' => False);
            $today_time = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
            $today_total_where = array("product_id" => $product_id, 'create_date' => array("\$gt" => $today_time), "site_id" => $this->site_id, 'cancelled' => false, 'removed' => False);
            $today_reply_where = array("product_id" => $product_id, 'create_date' => array("\$gt" => $today_time), 'process_status' => array("\$gt" => 2), "site_id" => $this->site_id, 'cancelled' => false, 'removed' => False);
        }
        //按部门统计信件回复总数
        $data['total'] = $this->supervision->count($total_where);
        $data['total_reply'] = $this->supervision->count($total_reply_where);
        //今日信件与今日回复
        $data['today_total'] = $this->supervision->count($today_total_where);
        $data['today_reply'] = $this->supervision->count($today_reply_where);
        $page = (int) $this->security->xss_clean(htmlentities($this->input->get('page'),ENT_COMPAT,'UTF-8'));
        if ($page < 1) {
            $page = 1;
        }

        $View = new Blitz('template/leader/list-supervision.html');

        if ($View->tpl == "22" || empty($View->tpl)) {
            show_error("模板不存在");
        }
        $struct_list = $View->getStruct();
        if (!empty($branch)) {
            $filter = (array("product_id" => $product_id, "branch_id" => $branch_id, 'status' => true, 'share_on' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
        } else {
            $filter = (array("product_id" => $product_id, 'status' => true, 'share_on' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
        }
        $total_row = $this->supervision->listCount(NULL, $filter);
        //获取领导信息
        if ($View->hasContext('current_leader')) {
            if (!empty($branch)) {
                $this->load->model('site_leader_model', 'site_leader');
                $leader = $this->site_leader->find(array('name' => $branch['name']), 1, 0, array('name', 'duty', 'bid', 'photo', 'job_title', 'type_id'));
            }
            if (mb_strlen($leader['duty']) > 180) {
                $leader['duty'] = mb_substr($leader['duty'], 0, 180) . '...';
            }
            $leader['photo'] = $leader['photo'] ? $leader['photo'] : '/media/images/nopic.gif';
            $View->block('/current_leader', array('url' => '/nocache/supervision/write/product_id=' . $product_id . '/branch_id=' . $branch_id . '/', 'name' => $leader['name'], 'thumb' => $leader['photo'], 'job_title' => $leader['job_title'], 'duty' => $leader['duty']));
        }
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
                    } else if (!empty($product_id)) {
                        $filter = array("product_id" => $product_id);
                    } else {
                        $filter = array();
                    }
                    $item_list = $this->itemSupervision($filter, $limit, $offset, $length, $sort_by, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $item_list['page'] = $link;
                } elseif ($action == 'product') {
                    $item_list = $this->itemProduct();
                } elseif ($action == 'question') {
                    $item_list = $this->itemQuestion();
                } elseif ($action == 'branch') {
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->itemBranch($channel_id, $limit, $offset, $length, $branch_id);
                }if ($action == 'leader') {
                    list($channel_id, $limit, $offset) = explode('_', $matches[2]);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemLeaderList($leader, $limit, $offset, $product_id);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['channel_name'] = $this->channel_tree['name'];
        $data['channel_id'] = (string) $this->channel_tree['_id'];
        $data['product_id'] = $product_id;
        $data['product_name'] = (string) $this->product_name[$product_id];
        $data['write_url'] = '/nocache/supervision/write/product_id=' . $product_id . '/';
        $data['location'] = '<a href="/">网站首页</a> > <a href="' . $this->channel_tree['link_url'] . '">' . $this->channel_tree['name'] . '</a> > <a href="/nocache/supervision/write/product_id=' . $product_id . '/">' . (string) $this->product_name[$product_id] . '</a>';

        //判断是否有会员
        $account = $this->is_login();
        $data['account'] = $account;
        $data['is_login'] = empty($account) ? FALSE : TRUE;
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

    //写信须知
    public function notice() {
        $View = new Blitz('template/' . __CLASS__ . '/supervision-mayor-notice.html');
        $product_id = (int) $this->input->get("product_id");
        if ($product_id == 2) {//县长信箱写信须知
            $notice = $this->content->find(array("channel" => '55c0261cd60b88cd0259d07c', "removed" => false, "status" => true));
            $data = array(
                "location" => '<a href="/">首页</a> > <a href="/content/channel/5bac372e7f8b9a343963e2fd/">互动咨询</a> > <a href="/nocache/supervision/?product_id=2"> 部长信箱</a> > <span>写信须知</span>',
                "notice" => $notice,
                "menu_index" => 0
            );
        } else {
            $notice = $this->content->find(array("channel" => '55c08ba3d60b8805547a3c6e', "removed" => false, "status" => true));
            $data = array(
                "location" => '<a href="/">首页</a> > <a href="/content/channel/5bac372e7f8b9a343963e2fd/">互动咨询</a> > <a href="/nocache/supervision/branch/">部门信箱</a> > <span>写信须知</span>',
                "notice" => $notice,
                "menu_index" => 2
            );
        }

        $View->display($data);
    }



    // public function search() {
    // if ($this->input->server('REQUEST_METHOD') == 'POST') {
    // $filter['no'] = addslashes($this->input->post('no'));

    // if ($filter['no'] == '') {
    // $this->resultJson('请输入信件编号！');
    // exit;
    // }
    // /* $msg = $this->supervision->find(array("no" => $filter['no'],'status' => true, 'share_on' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id)); */
    // $msg = $this->supervision->find(array("no" => $filter['no'],'status' => true, 'removed' => False, 'share_on' => true,'cancelled' => false,'site_id' => $this->site_id));
    // //var_dump($msg);
    // if (!empty($msg)) {
    // $_SESSION['rating_status'] = true;
    // $referer = "/supervision/detail/" . $msg['_id'] . ".html";
    // $this->resultJson('正在跳转信件详情页面！', '+OK', array('referer' => $referer));
    // } else {
    // $this->resultJson('信件编号错误，请重新输入进行查询！');
    // exit;
    // }
    // }
    // }


    public function search(){
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $filter['no']=addslashes($this->input->post('no'));
            $filter['no_password']=addslashes($this->input->post('no_password'));
            if ($filter['no']=='') {
                $this->resultJson('请输入信件编号！');exit;
            }
            if ($filter['no_password']=='') {
                $this->resultJson('请输入查询码！');exit;
            }

            $msg= $this->supervision->find(array("no"=>$filter['no'],"no_password"=>$filter['no_password']));
            if(!empty($msg)){
                $_SESSION['rating_status']=true;
                $referer = "/supervision/detail/" .$msg['_id'] . ".html";
                $this->resultJson('正在跳转信件详情页面！', '+OK', array('referer' => $referer));
            }else{
                $this->resultJson('信件编号或查询码错误，请重新输入进行查询！');exit;
            }
        }
    }


    // public function sear(){
    // if ($this->input->server('REQUEST_METHOD') == 'POST') {
    // $filter['no']=addslashes($this->input->post('no'));

    // if ($filter['no']=='') {
    // $this->resultJson('请输入信件编号！');exit;
    // }


    // $msg= $this->supervision->find(array("no"=>$filter['no']));


    // if(!empty($msg)){
    // $_SESSION['rating_status']=true;
    // $referer = "/supervision/detail/" .$msg['_id'] . ".html";
    // $this->resultJson('正在跳转信件详情页面！', '+OK', array('referer' => $referer));
    // }else{
    // $this->resultJson('信件编号或查询码错误，请重新输入进行查询！');exit;
    // }
    // }
    // }

    //县长信箱统计
    public function replyMayor() {

        $View = new Blitz('template/supervision/supervision-reply-mayor-counter.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    // 信箱回复排行
                    list($limit, $offset) = explode('_', $matches[2]);
                    $item_list = $this->itemSupervisionMayorCounter();
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['location'] = '<a href="/">首页</a> &gt; <a href="/content/channel/5bac372e7f8b9a343963e2fd/">互动咨询</a> &gt; <span>县长信箱回复排行</span>';

        $View->display($data);
    }

    protected function itemSupervisionMayorCounter() {
        $this->load->model('supervision_type_counter_model', 'supervision_type_counter');

        $filter = array('_id.site_id' => $this->site_id, '_id.product_id' => 2);
        $select = '*';
        $arr_sort = $sort_by;

        $this_branch = $this->site_branch->find(array("_id" => $item['branch_id'], 'removed' => false));

        $item_list = $this->supervision_type_counter->find($filter, $limit, 0, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['key'] = $key + 1;
            $question_id = $item['_id']['question_id'];
            $item_list[$key]['question_name'] = $this->question_list[$question_id];
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['totalReply'] = $this->supervision->count(array('question_id' => $question_id, "product_id" => 2, 'removed' => false, 'reply_confirmed' => true));
            $item_list[$key]['replyRate'] = round(($item_list[$key]['totalReply'] / $item_list[$key]['total']) * 100, 2) . '%';
        }
        return $item_list;
    }

    //部门回复排行
    public function replyCounter() {

        $order = $this->input->get('order');
        if (empty($order)) {
            $order = 'replyRate';
        }

        $View = new Blitz('template/supervision/supervision-reply-counter.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    // 信箱回复排行
                    list($limit, $offset) = explode('_', $matches[2]);
                    $item_list = $this->itemSupervisionBranchCounter($limit, array($order => 'DESC', 'replyRate' => 'DESC', 'total' => 'DESC', 'totalReply' => 'DESC'));
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['location'] = '<a href="/">首页</a> &gt; <a href="/content/channel/5bac372e7f8b9a343963e2fd/">互动咨询</a> &gt; <span>部门信箱回复排行</span>';

        $View->display($data);
    }

    // 信件排行
    protected function itemSupervisionBranchCounter($limit, $sort_by) {
        $this->load->model('supervision_branch_counter_model', 'supervision_branch_counter');

        $filter = array('site_id' => $this->site_id, 'removed' => False, 'is_sys' => true, 'branch_id' => array("\$ne" => ""));
        $select = array('branch_id', 'total', 'replyRate', 'totalReply');
        $arr_sort = $sort_by;

        $item_list = $this->supervision_branch_counter->find($filter, $limit, 0, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['key'] = $key + 1;
            if (!empty($item['branch_id'])) {
                $this_branch = $this->site_branch->find(array("_id" => $item['branch_id'], 'removed' => false));
            }
            $item_list[$key]['url'] = '/supervision/branch/?_id=' . $item['branch_id'];
            $item_list[$key]['replyRate'] = mb_substr($item['replyRate'], 0, 5) . '%';

            if ($this_branch['parent_id'] == '55c057bdd60b88e02d07a4a7') {
                //$item_list[$key]['branch'] = '县长信箱';
                unset($item_list[$key]);
            } elseif (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['branch_id']];
            } else {
                $item_list[$key]['branch'] = '';
                unset($item_list[$key]);
            }
        }
        return $item_list;
    }

    public function replyRating() {

        $order = $this->input->get('order');
        if (empty($order)) {
            $order = 'replyRate';
        }

        $View = new Blitz('template/supervision/supervision-rating.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    // 信箱满意度排行
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->ratingBranch($limit, $offset, $length);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['location'] = '<a href="/">首页</a> &gt; <a href="/content/channel/5bac372e7f8b9a343963e2fd/">互动咨询</a> &gt; <span>部门信箱满意度排行</span>';

        $View->display($data);
    }

    //部门信件满意度排行
    protected function ratingBranch($limit, $offset, $length) {
        $this->load->model('supervision_rating_counter_model', 'supervision_rating_counter');
        $arr_sort = array("total" => "DESC");
        $record = $this->supervision_rating_counter->find(NULL, $limit, $offset, "*", $arr_sort);
        if (!empty($record) && $limit == 1) {
            $item_list[] = $record;
        } else {
            $item_list = $record;
        }
        foreach ($item_list as $key => $item) {
            /* $branch_name = $this->branch_list[$item['branch_id']];
              if (mb_strlen($branch_name) > $length) {
              $item_list[$key]['branch_name'] = mb_substr($branch_name, 0, $length) . '';
              } else {
              $item_list[$key]['branch_name'] = $branch_name;
              } */
            $item_list[$key]['url'] = '/supervision/branch/' . $item['branch_id'] . "/";

            if (!empty($item['branch_id'])) {
                $this_branch = $this->site_branch->find(array("_id" => $item['branch_id'], 'removed' => false));
            }
            if ($this_branch['parent_id'] == '55c057bdd60b88e02d07a4a7') {
                unset($item_list[$key]);
            } elseif (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch_name'] = $this->branch_list[$item['branch_id']];
            } else {
                unset($item_list[$key]);
            }
        }
        return $item_list;
    }

}

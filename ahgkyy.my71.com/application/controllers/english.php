<?php

class english extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('site_channel_model', 'site_channel');
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
        $this->load->model('content_model', 'content');
    }

    protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type');
        if ($is_pic) {
            $filter = array('status' => true, 'thumb_name' => array("\$ne" => ''), 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        }
        $item_list = $this->content->findList($_id_list, NULL, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);
		$total_row=count($item_list);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item['description'] = str_replace(Chr(32), " ", $item['description']);
            if (mb_strlen($item['description']) > $description_length) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }
			$item_list[$key]['description']=nl2br($item_list[$key]['description']);
			
            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            $item_list[$key]['thumb'] = $item['type'] == 1 ? $item['thumb_name'] : $item['thumb_large'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
            $item_list[$key]['i']=$key; 
            $item_list[$key]['total']=$total_row; 
        }

        return $item_list;
    }

    protected function newsList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type');
        if ($limit == 1) {
            $item_list = $this->content->findList($_id_list, NULL, array('status' => true, 'removed' => false, 'site_id' => $this->site_id, 'thumb_name' => array('$ne' => '')), NULL, NULL, $limit, $offset, $select, $arr_sort);
        } else {
            $limit = $limit + 1;
            $item_list = $this->content->findList($_id_list, NULL, array('status' => true, 'removed' => false, 'site_id' => $this->site_id), NULL, NULL, $limit, $offset, $select, $arr_sort);
        }

        $first_thumb = false;
        foreach ($item_list as $key => $item) {
            if (!$first_thumb && $limit != 1 && !empty($item['thumb_name'])) {
                $first_thumb = true;
                unset($item_list[$key]);
                continue;
            }
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item['description'] = str_replace(Chr(32), " ", $item['description']);
            if (mb_strlen($item['description']) > $description_length) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            $item_list[$key]['thumb'] = $item['type'] == 1 ? $item['thumb_name'] : $item['thumb_large'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        if (!$first_thumb && $limit != 1 && count($item_list) == $limit) {//不包含图片移除最后一条
            array_pop($item_list);
        }
        return $item_list;
    }

    protected function hotList($channel_id, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0) {

        $date_format = $this->date_foramt[$date_format];

        $this->load->model('content_hot_model', 'content_hot');
        if ($channel_id) {
            $filter_list = array('channel_id' => $channel_id, 'status' => true, 'site_id' => $this->site_id);
        } else {
            $filter_list = array('status' => true, 'site_id' => $this->site_id);
        }

        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date');

        $item_list = $this->content_hot->find($filter_list, $limit, $offset);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['content_id'] . '.html';
            $item_list[$key]['thumb'] = $item['thumb_name'];
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            if (mb_strlen($item['description']) > $description_length) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
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

    // 留言反馈
    protected function feedbackList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {

        $this->load->model('site_feedback_model', 'feedback');

        $filter = array("type_id" => $_id_list, 'status' => true, 'removed' => False, 'site_id' => $this->site_id);
        $arr_sort = array('sort' => 'ASC');

        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'confirm_date', 'replied', 'reply_name');

        $item_list = $this->feedback->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);

            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            if ($item['replied']) {
                $item_list[$key]['replied'] = "已回复";
            } else {
                $item_list[$key]['replied'] = "未回复";
            }
            $item_list[$key]['url'] = "/feedback/detail/?_id=" . $item['_id'];
            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }

    // 专题
    protected function specialList($limit = 10, $offset = 0, $length = 60, $date_format = 0) {

        $this->load->model('special_model', 'special');

        $filter = array('status' => true, 'removed' => False, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'create_date', 'cover', 'link_url');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->special->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {

            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : '/special/channel/?_id=' . $item['_id'];
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
            $item_list[$key]['thumb'] = $item['cover'];
        }
        return $item_list;
    }

        // 广告
    protected function advertList($location_id, $limit = 10, $offset = 0, $length = 60) {

        $this->load->model('advert_resource_model', 'advert_resource');
        $this->load->model('advert_size_model', 'advert_size');

        $filter = array('location_id' => $location_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name', 'media_path', 'target_url', 'start_date', 'end_date', 'size_id');
        $sort_by = array('sort' => 'DESC');

        $item_list = $this->advert_resource->find($filter, $limit, $offset, $select, $sort_by);
        if ($limit == 1&&$item_list) {
            $item_list = array(0 => $item_list);
        }

        foreach ($item_list as $key => $item) {
            if ($item['start_date'] != $item['end_date'] && (time() < $item['start_date'] || time() > $item['end_date'])) {
                unset($item_list);
                continue;
            }
            // 判断有没有位置信息
            if (!empty($item['size_id'])) {
                $size_list = $this->advert_size->find(array('_id' => (string) $item['size_id'], 'site_id' => $this->site_id, 'removed' => false), 1, 0, array('width', 'height'));
                if (empty($size_list['width'])) {
                    $item_list[$key]['width'] = '300';
                } else {
                    $item_list[$key]['width'] = $size_list['width'];
                }
                if (empty($size_list['height'])) {
                    $item_list[$key]['height'] = '200';
                } else {
                    $item_list[$key]['height'] = $size_list['height'];
                }
            } else {
                $item_list[$key]['width'] = '300';
                $item_list[$key]['height'] = '200';
            }
            $item_list[$key]['_id'] = (string) $item['_id'];
            $item_list[$key]['url'] = $item['target_url'];
            $item_list[$key]['thumb'] = $item['media_path'];
        }

        return $item_list;
    }

    // 在线调查
    protected function interactionVoteList($type_id, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {

        $this->load->model('interaction_vote_model', 'interaction_vote');

        $filter = array('type_id' => $type_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name', 'confirm_date');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->interaction_vote->find($filter, $limit, $offset, $select, $arr_sort);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }

        foreach ($item_list as $key => $item) {

            $item_list[$key]['url'] = '/interactionVote/detail/' . $item['_id'] . '.html';
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['name'] = strip_tags(html_entity_decode($item['name']));
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
            $item_list[$key]['title'] = $item['name'];
            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }

    // 服务类型
    protected function serviceTypeList($parent_id, $limit = 50, $offset = 0, $length = 60) {

        $this->load->model('service_type_model', 'service_type');

        $filter = array('parent_id' => $parent_id, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');

        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/service/type/?type=' . $parent_id . '&_id=' . $item['_id'];
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
        }
        return $item_list;
    }
    
    // 服务类型
    protected function itemServiceType($_id, $limit = 50, $offset = 0, $length = 60) {

        $this->load->model('service_type_model', 'service_type');

        $filter = array('parent_id' => $_id, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');

        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/serviceSubject/' . $item['_id']."/";
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_name'] = $item['name'];
            }
        }
        return $item_list;
    }

    //民意征集
    protected function interactionCollList($type_id, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $this->load->model('interaction_coll_model', 'interaction_coll');

        $filter = array('type_id' => $type_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'confirm_date');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->interaction_coll->find($filter, $limit, $offset, $select, $arr_sort);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {

            $item_list[$key]['url'] = '/interactionColl/detail/' . $item['_id'] . '.html';
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }
    
    // 在线访谈
    protected function interactionLiveList($limit = 10, $offset = 0, $length = 60, $time_length = 12, $description_length = 100) {

        $this->load->model('interaction_live_model', 'interaction_live');

        $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'photo', 'time', 'addr', 'guests', 'sponsor', 'intro', 'confirm_date');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[1];

        $item_list = $this->interaction_live->find($filter, $limit, $offset, $select, $arr_sort);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {

            $item_list[$key]['url'] = '/interactionLive/detail/nocache/' . $item['_id'] . '.html?r=' . time();
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            if (mb_strlen($item['intro']) > $description_length) {
                $item_list[$key]['intro'] = mb_substr(strip_tags($item['intro']), 0, $description_length) . '...';
            }
            if (mb_strlen($item['time']) > $time_length) {
                $item_list[$key]['time'] = mb_substr(strip_tags($item['time']), 0, $time_length) . '...';
            }else{
                $item_list[$key]['time'] = strip_tags($item['time']);
            }
            $item_list[$key]['thumb'] = $item['photo'];
            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }
	
	protected function vodList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {
		$this->load->model('vod_model', 'vod');
        $arr_sort = array('release_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'release_date', 'thumb_name');
        $item_list = $this->vod->find(array("channel" => $_id_list,'status' => true, 'removed' => false), $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = 'http://v.ahhuoshan.gov.cn/vod/detail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }
	
	protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('openness_column_model', 'openness_column');

        $arr_sort = array('sort' => 'DESC', 'openness_date' => 'DESC', 'create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $where_array['status'] = True;
        $where_array['removed'] = False;

        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'column_code', 'openness_date', 'id');

        $item_list = $this->openness_content->findList($branch_id, $where_array, $limit, $offset, $select, $arr_sort, $code);


        foreach ($item_list as $key => $item) {
            if ($item['branch_id']) {
                $this_branch = $this->site_branch->find(array('_id' => $item['branch_id']));
                $item_list[$key]['branch'] = $this_branch['name'];
            }
            if ($item['column_code'] && $item['branch_id']) {
                $this_column = $this->openness_column->find(array('code' => (string) $item['column_code'], 'branch_id' => $item['branch_id']));
                $item_list[$key]['column'] = $this_column['name'];
            }
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }

            $item_list[$key]['date'] = $item['openness_date'];
            $item_list[$key]['url'] = "http://www.ahhuoshan.gov.cn/openness/detail/content/" . $item['_id'] . '.html';
        }

        return $item_list;
    }
	
	/* 论坛排行 */
	protected function bbsCountList($sort_by, $limit = 10, $offset = 0, $length = 60) {
	
        $this->load->model('forum_branch_counter_model', 'forum_branch_counter');

        $filter = array('site_id' => $this->site_id);
        $select = array('branch_id', 'branch_name', 'total', 'rate');
		$arr_sort = array('total' => 'DESC');	

        $item_list = $this->forum_branch_counter->find($filter, $limit, $offset, $select, $arr_sort);

        return $item_list;
    }
    
  // 领导列表
    protected function leaderList($type_id, $limit = 10, $offset = 0, $length = 60) {
    
        $this->load->model('site_leader_model', 'site_leader');

        $filter = array("type_id" => $type_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $arr_sort = array('sort' => 'DESC');

        $select = array('_id', 'name', 'job_title','photo');
        $item_list = $this->site_leader->find($filter, $limit, $offset, $select, $arr_sort);
     	if ($limit == 1&&$item_list) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {	
            $item_list[$key]['_id'] = (string)($item['_id']);

            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['name'] = $item['name'];
            }

            $item_list[$key]['url'] = "/leader/english/?type=".$type_id."&_id=" . $item['_id'];
        }
        return $item_list;
    }
    

    public function index() {
        $View = new Blitz('template/english.html');
        $struct_list = $View->getStruct();
		$data=array();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'hot') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    if ($channel_id == 'all') {
                        $channel_id = NULL;
                    }
                    $item_list = $this->hotList($channel_id, $limit, $offset, $length, $date_format, $description_length);

                    foreach ($item_list as $key => $item) {
                        $item_list[$key]['txt'] = urlencode($item['short_title']);
                        $item_list[$key]['key'] = md5($this->api_key . $item_list[$key]['txt']);
                    }
                } elseif ($action == 'slider') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    $this_channel = $this->site_channel_tree->find(array('_id' => $channel_id));
                    if (!empty($this_channel['child'])) {
                        unset($_id_list);
                        foreach ($this_channel['child'] as $key => $val)
                            $_id_list[] = $key;
                    } else {
                        $_id_list = explode('-', $channel_id);
                    }
                    $item_list = $this->contentList($_id_list, $limit, $offset, $length, $date_format, $description_length, true);
                } elseif ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    $this_channel = $this->site_channel_tree->find(array('_id' => $channel_id));
                    if (!empty($this_channel['child'])) {
                        unset($_id_list);
                        foreach ($this_channel['child'] as $key => $val)
                            $_id_list[] = $key;
                    } else {
                        $_id_list = explode('-', $channel_id);
                    }
                    $item_list = $this->contentList($_id_list, $limit, $offset, $length, $date_format, $description_length);
                } elseif ($action == 'news') {//取标题图片不为空的信息
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    $this_channel = $this->site_channel_tree->find(array('_id' => $channel_id));
                    if (!empty($this_channel['child'])) {
                        unset($_id_list);
                        foreach ($this_channel['child'] as $key => $val)
                            $_id_list[] = $key;
                    } else {
                        $_id_list = explode('-', $channel_id);
                    }
                    $item_list = $this->newsList($_id_list, $limit, $offset, $length, $date_format, $description_length);
                } elseif ($action == 'special') {

                    list($limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->specialList($limit, $offset, $length, $date_format);
                } elseif ($action == 'vote') {
                    list($type_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->interactionVoteList($type_id, $limit, $offset, $length, $date_format);
                } elseif ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                } elseif ($action == 'feedback') {
                    list($type_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->feedbackList($type_id, $limit, $offset, $length, $date_format);
                } elseif ($action == 'menu') {
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
                } elseif ($action == 'advert') {
                    list($location_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->advertList($location_id, $limit, $offset, $length);
                } elseif ($action == 'servicetype') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->serviceTypeList($parent_id, $limit, $offset, $length);
                } elseif ($action == 'coll') {
                    list($type_id, $limit, $offset, $length, $date_format ) = explode('_', $matches[2]);
                    $item_list = $this->interactionCollList($type_id, $limit, $offset, $length, $date_format);
                } elseif ($action == 'live') {
                    list( $limit, $offset, $length,  $time_length, $description_length) = explode('_', $matches[2]);
                    $item_list = $this->interactionLiveList($limit, $offset, $length, $time_length, $description_length);
                }elseif ($action == 'vod') {

                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    $_id_list = explode('-', $channel_id);
                    $item_list = $this->vodList($_id_list, $limit, $offset, $length, $date_format);
                }elseif ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                }elseif ($action == 'conbybranch') {
                    list($branch_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if (strlen($branch_id) == 1) {
                        $where_array = array();
                        $where_array = array('branch_type' => (int) $branch_id);
                        $branch_id = null;
                    }elseif ($branch_id == 'all') {
                        $branch_id = null;
                    }
                    $item_list = $this->opennessList($branch_id, $where_array, $limit, $offset, $length, $date_format);
                }elseif ($action == 'conbycolumn') {
                    list($column_code, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->opennessList($this->gov_branch, null, $limit, $offset, $length, $date_format, (int) $column_code);
                }elseif ($action == 'bbscount') {
                    list($sort_by, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->bbsCountList($sort_by, $limit, $offset, $length);
                }elseif ($action == 'leader') {
                    list($type_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->leaderList($type_id, $limit, $offset, $length);
                }
                $data[$struct_val] = $item_list;
            }
        }

        $View->set(array('folder_prefix' => $this->folder_prefix));
        $View->display($data);
    }

}

?>
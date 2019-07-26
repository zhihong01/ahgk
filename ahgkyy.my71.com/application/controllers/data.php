<?php

class data extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('site_channel_model', 'site_channel');
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
        $this->load->model('content_model', 'content');
    }

    protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type','body');
		if ($is_pic) {
            $filter = array('status' => true, 'thumb_name' => array("\$ne" => ''), 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        }
        $item_list = $this->content->findList($_id_list, NULL, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            
            $item_list[$key]['short_title'] = $this->msubstr($item['title'], $length);

            $item['description'] = str_replace(Chr(32), " ", $item['description']);
            if (mb_strlen($item['description']) > $description_length) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }
			$item_list[$key]['body'] = strip_tags($item['body']);
            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            $item_list[$key]['thumb'] = $item['type'] == 1 ? $item['thumb_name'] : $item['thumb_large'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
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
        if(!$first_thumb && $limit != 1&&count($item_list)==$limit){//不包含图片移除最后一条
        	array_pop($item_list); 
        }
        return $item_list;
    }

    protected function hotList($channel_id, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0) {

        $date_format = $this->date_foramt[$date_format];

        $this->load->model('content_hot_model', 'content_hot');
		if($channel_id){
			$filter_list = array('channel_id' => $channel_id, 'status' => true, 'site_id' => $this->site_id);
		}else{
			$filter_list = array('status' => true, 'site_id' => $this->site_id);
		}
        
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date');

        $item_list = $this->content_hot->find($filter_list, $limit, $offset);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['content_id'] . '.html';
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
        if ($limit == 1) {
            $item_list = array(0 => $item_list);
        }

        foreach ($item_list as $key => $item) {
            if ($item['start_date'] != $item['end_date'] && (time() < $item['start_date'] || time() > $item['end_date'])) {
                unset($item_list);
                continue;
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
    
 	protected function leaderList($_id_list, $limit = 10, $offset = 0 ,$joblength=0,$namelength=0, $sort_by = 0, $date_format = 0) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'job_title', 'name', 'bid', 'photo','type_id');
        $this->load->model('site_leader_model', 'site_leader');
        $item_list = $this->site_leader->find(array('type_id' => $_id_list, 'status' => True, 'removed' => False, 'site_id' => $this->site_id), $limit, $offset, $select, $arr_sort);
        if ($limit == 1&&$item_list) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
			if (mb_strlen($item['name']) > $namelength) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $namelength);
            } else {
                $item_list[$key]['short_name'] = $item['name'];
            }
			if (mb_strlen($item['job_title']) > $joblength) {
                $item_list[$key]['short_job'] = mb_substr($item['job_title'], 0, $joblength);
            } else {
                $item_list[$key]['short_job'] = $item['job_title'];
            }
            $item_list[$key]['url'] = '/leader/?type='.$item['type_id'].'&_id='. $item['_id'];
        }
        return $item_list;
    }

    public function index() {
        $View = new Blitz('template/sj.html');
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
                        $item_list[$key]['txt'] = urlencode($item['title']);
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
                }elseif ($action == 'leader') {
                    list($channel_id, $limit, $offset,$joblength,$namelength) = explode('_', $matches[2]);
                    $item_list = $this->leaderList((string) $channel_id, $limit, $offset,$joblength,$namelength);
                }
                $data[$struct_val] = $item_list;
            }
        }

        $View->set(array('folder_prefix' => $this->folder_prefix));
        $View->display($data);
    }

}

?>
<?php

class home extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('site_channel_model', 'site_channel');
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
        $this->load->model('content_model', 'content');
    }

    protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {
        if($_id_list==null){
			$arr_sort = array('release_date' => 'DESC');
		}else{
			$arr_sort = array('sort' => 'DESC');
		}
        //$arr_sort = array('release_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type','channel');
        if ($is_pic) {
            $filter = array('status' => true, 'thumb_name' => array("\$ne" => ''), 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        }
        $item_list = $this->content->findList($_id_list, NULL, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);
		//print_r($item_list);die();

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

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            if (strstr($item['thumb_name'],'@')) {
                $item_list[$key]['thumb'] = str_replace('@/upload', '/data/upload', $item['thumb_name']);
            } else {
                $item_list[$key]['thumb'] = $item['thumb_name'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
			$channel_name = $this->site_channel->find(array('_id' => $item['channel'][0]),1,0,array('name'));
			$item_list[$key]['channel_name']=$channel_name['name'];
			$item_list[$key]['no'] = $key+1;
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
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, ($length-2)) . '...';
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

        $item_list = $this->content_hot->find($filter_list, $limit, $offset);//print_r($item_list);die();
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
        $select = array('_id', 'title', 'confirm_date', 'replied', 'reply_name','no');

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
            $item_list[$key]['url'] = '/special/column/?_id=' . $item['_id'];
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
			$size = $this->advert_size->find(array('_id'=>$item['size_id'],'status'=>true,'removed'=>false, 'site_id' => $this->site_id),1,0);
            $item_list[$key]['_id'] = (string) $item['_id'];
            $item_list[$key]['url'] = $item['target_url'];
            $item_list[$key]['thumb'] = $item['media_path'];
			$item_list[$key]['width'] = $size['width'];
            $item_list[$key]['height'] = $size['height'];
        }

        return $item_list;
    }

    // 在线调查
    protected function interactionVoteList($type_id, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {

        $this->load->model('interaction_vote_model', 'interaction_vote');

        $filter = array('type_id' => $type_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name', 'confirm_date','content');
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
            $item_list[$key]['content'] = str_replace('<form class="form-horizontal">', "", $item_list[$key]['content']); 
			$item_list[$key]['content'] = str_replace('</form>', "", $item_list[$key]['content']); 
			$matches = array(); 
			preg_match_all("/<legend class[^>]+>\s*([^<]+)<\/legend>/", $item_list[$key]['content'], $matches); 
			$item_list[$key]['content'] = str_replace($matches[0][0], "", $item_list[$key]['content']);
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
            $item_list[$key]['url'] = '/service/type/?type=' . $_id . '&_id=' . $item['_id'];
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
        }
        return $item_list;
    }

    //民意征集
    protected function interactionCollList($type_id, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $this->load->model('interaction_coll_model', 'interaction_coll');

        $filter = array( 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
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
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->interaction_live->find($filter, $limit, $offset, $select, $arr_sort);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
            $item_list[$key]['url'] = '/interactionLive/detail/' . $item['_id'] . '.html?r=' . time();
            $item_list[$key]['photo_url'] = '/interactionLive/livePhoto/?_id=' . $item['_id'] . '&r=' . time();
            $item_list[$key]['record_url'] = '/interactionLive/liveRecode/?_id=' . $item['_id'] . '&r=' . time();
            $item_list[$key]['video_url'] = '/interactionLive/liveVideo/?_id=' . $item['_id'] . '&r=' . time();
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            $item['intro'] = strip_tags($item['intro']);
            $item['time'] = strip_tags($item['time']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            if (mb_strlen($item['intro']) > $description_length) {
                $item_list[$key]['intro'] = mb_substr($item['intro'], 0, $description_length) . '...';
            } else {
                $item_list[$key]['intro'] = $item['intro'];
            }
            if (mb_strlen($item['time']) > 30) {
                $item_list[$key]['time'] = mb_substr($item['time'], 0, 30) . '...';
            } else {
                $item_list[$key]['time'] = $item['time'];
            }
            if (empty($item['photo'])) {
                $item_list[$key]['photo'] = '/media/images/ft/zxft.png';
            }elseif(strstr($item['photo'],'data')){
                $item_list[$key]['photo']='http://www.hngov.cn'.$item['photo'];
            }
            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }

	// 视频
	protected function videoList($_id_list, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
		
		$this->load->model('content_video_model', 'content_video');
		
        $content = $this->content->find(array('channel' => $_id_list, 'status' => true, 'removed' => false), 1, 0, array('_id'), array('sort' => 'DESC'));
        $item_list = $this->content_video->find(array('content_id' => (string)$content['_id']));
		$video[0]['video_thumb'] = $this->vals['setting']['upload_url'].substr($item_list['medium_name'],0,8).'/'.$item_list['medium_thumb'];
		$video[0]['video_file'] = $this->vals['setting']['upload_url'].substr($item_list['medium_name'],0,8).'/'.$item_list['medium_name'];
		$video[0]['video_player'] = $this->vals['setting']['upload_url']."/media/player/player.swf?v1.3.5";
		$video[0]['video_skin'] = $this->vals['setting']['upload_url']."/media/player/skins/mySkin.swf";
        return $video;
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

    // 获取互动信件列表
    protected function itemSupervision($filter, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('supervision_model', 'supervision');
        $filter = array_merge($filter, array('status' => true, 'share_on' => true, 'cancelled' => false, 'removed' => False, 'reply_confirmed' => true));
        $select = array('_id', 'process_status', 'subject', 'create_date', 'update_date', 'branch_id', 'no', 'question_id', 'hit', 'deal_branch_id', 'leader_remark', 'product_id', 'reply_date');
        $arr_sort = array('reply_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $item_list = $this->supervision->find($filter, $limit, $offset, $select, $arr_sort);

        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
            if ($key % 2 !== 0) {
                $item_list[$key]['class'] = "bgcolor";
            }
            $item_list[$key]['url'] = 'http://www.xuancheng.gov.cn/supervision/detail/' . $item['_id'] . '.html';
            $item_list[$key]['_id'] = (string) ($item['_id']);
            // 留言的状态
//            var_dump($this->supervision_status[$item['process_status']]);die;
            if (isset($this->supervision_status[$item['process_status']])) {
                $item_list[$key]['process_status'] = $this->supervision_status[$item['process_status']];
            } else {
                $item_list[$key]['process_status'] = $this->supervision_status[0];
            }

            // 取部门
            if (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch_name'] = $this->branch_list[$item['branch_id']];
            } else {
                $item_list[$key]['branch_name'] = '区信访局';
            }
            //信箱问题类别
            if (isset($this->product_name[$item['product_id']])) {
                $item_list[$key]['product_name'] = $this->product_name[$item['product_id']];
            } else {
                $item_list[$key]['product_name'] = $this->product_name[0];
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
            $item_list[$key]['date'] = ($item['reply_date']) ? date($date_format, $item['reply_date']) : date($date_format, $item['create_date']);
            $item_list[$key]['deal_branch'] = $item['deal_branch_id'];
            if (mb_strlen($item['leader_remark']) > $length) {
                $item_list[$key]['leader_remark'] = mb_substr($item['leader_remark'], 0, $length) . '...';
            }
        }


        return $item_list;
    }
    //信息公开列表
    protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
        $this->load->model('openness_content_model', 'openness_content');

        $arr_sort = array('openness_date'=>'DESC');
        $where_array['status'] = True;
        $where_array['removed'] = False;
        $where_array['site_id']=$this->site_id;
        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'column_code', 'openness_date', 'id','link_url');
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
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }


            $item_list[$key]['date'] = $date_format==0?substr($item['openness_date'],5,5):$item['openness_date'];
            // $item_list[$key]['url'] = "/openness/detail/content/" . $item['_id'] . '.html';
            $item_list[$key]['url'] = $item['link_url']?$item['link_url']:"/openness/detail/content/" . $item['_id'] . '.html';
        }

        return $item_list;
    }

    public function index() {
		
    	$data=array();
        $View = new Blitz('template/home.html');
        $struct_list = $View->getStruct();

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
                        foreach ($this_channel['child'] as $key => $val){
                            $_id_list[] = $key;
						}
						$_id_list[] = $channel_id;
                    } else {
                        $_id_list = explode('-', $channel_id);
                    }
                    $item_list = $this->contentList($_id_list, $limit, $offset, $length, $date_format, $description_length, true);
                } elseif ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    $this_channel = $this->site_channel_tree->find(array('_id' => $channel_id));
                    if (!empty($this_channel['child'])) {
                        unset($_id_list);
                        foreach ($this_channel['child'] as $key => $val){
                            $_id_list[] = $key;
						}
						$_id_list[] = $channel_id;
                    }else {
                        $_id_list = explode('-', $channel_id);
                    }
                    $item_list = $this->contentList($_id_list, $limit, $offset, $length, $date_format, $description_length);
                } elseif ($action == 'listnew') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    if ($channel_id == 'all') {
                        $_id_list = NULL;
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
                }elseif ($action == 'openness') {
                    list($branch_id, $column_code, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $where_array = null;
                    if (strlen($branch_id) == 1) {
                        $where_array = array('branch_type' => (int) $branch_id);
                        $branch_id = null;
                    }elseif ($branch_id == 'all') {
                        $branch_id = null;
                    }elseif ($column_code == 'all') {
                        $column_code = null;
                    }
                    $item_list = $this->opennessList($branch_id, $where_array, $limit, $offset, $length, $date_format, (int) $column_code);
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
                }elseif($action == 'supervision') {
                    list($branch_id,$limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->itemSupervision(array('branch_id'=>$branch_id), $limit, $offset, $length, $sort_by, $date_format);

                } elseif ($action == 'video') {

                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->videoList($channel_id, $limit, $offset, $length);
                }elseif ($action == 'leader') {
                    list($channel_id, $limit, $offset,$joblength,$namelength) = explode('_', $matches[2]);
                    $item_list = $this->leaderList((string) $channel_id, $limit, $offset,$joblength,$namelength);
                }elseif ($action == 'float') {
                    list($location_id, $limit, $offset) = explode('_', $matches[2]);

                    $float_data = $this->advertList((string) $location_id, null, 0 ,null);//print_r($float_data);die();
					if(!empty($float_data)){
						$float_content="";
						foreach($float_data as $val){
							$float_content=$float_content.'<a href="'.$val['url'].'" target="_blank" title="'.$val['description'].'"><img src="'.$val['thumb'].'"></a><br/>';
						}
						$item_list[0]['content']=mb_substr($float_content,0,-5);
					}
					
                }
                $data[$struct_val] = $item_list;
            }
        }

        $View->set(array('folder_prefix' => $this->folder_prefix));
        $View->display($data);
    }

}

?>
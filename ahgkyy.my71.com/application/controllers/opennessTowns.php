<?php

class opennessTowns extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('site_leader_model', 'site_leader');
        $this->load->model('site_channel_model', 'site_channel');
        $this->load->model('openness_request_model', 'openness_request');
		$this->load->model('openness_column_model', 'openness_column');
    }

    public function jsonTree() {
		
		$target=$this->input->get('target')?$this->input->get('target'):null;
		$this->load->driver('cache');
		$branch_id = (string) $this->input->get('branch_id');
		
		$cache_key = md5('jsonTree'. $branch_id);
        // if ($cache_data = $this->cache->file->get($cache_key)) {
            // echo json_encode($cache_data);
       		// exit();
        // } else {
			
        $this->load->model('openness_column_model', 'openness_column');
        $filter_list = array();

        $current_branch_id = $this->input->get('branch_id') ? $this->input->get('branch_id') : $this->gov_branch;
        $filter_list['site_id'] = $this->site_id;
        $filter_list['branch_id'] = $current_branch_id;
        $filter_list['removed'] = false;
        $filter_list['status'] = True;

        $select = array('_id', 'parent_id', 'name', 'code', 'link_url', 'tree_counter');
        $data = $this->openness_column->find($filter_list, null, 0, $select, array("sort" => 'desc','code'=>'asc'));

        foreach ($data as $key => $value) {
            $data[$key]['_id'] = (string) $value['_id'];
			if($target){
				$data[$key]['code'] = $value['link_url'] ? $value['link_url'] : "/opennessTarget/?branch_id=$current_branch_id&column_code=" . $value['code'];
			}else{
				$data[$key]['code'] = $value['link_url'] ? $value['link_url'] : "/opennessContent/?type=towns&branch_id=$current_branch_id&column_code=" . $value['code'];
			}
            
        }

        $itemsByReference = array();

        foreach ($data as $key => &$item) {
            $itemsByReference[$item['_id']] = &$item;
            $itemsByReference[$item['_id']]['text'] = &$item['name'];
            $itemsByReference[$item['_id']]['classes'] = 'file';
            $itemsByReference[$item['_id']]['children'] = array();
            $itemsByReference[$item['_id']]['data'] = new StdClass();
        }

        foreach ($data as $key => &$item) {
            if ($item['parent_id'] && isset($itemsByReference[$item['parent_id']])) {
                $itemsByReference[$item['parent_id']]['children'][] = &$item;
                $itemsByReference[$item['parent_id']]['classes'] = 'folder';
            }
        }
        foreach ($data as $key => &$item) {
            if ($item['parent_id'] && isset($itemsByReference[$item['parent_id']])) {
                unset($data[$key]);
            }
        }
		
		$this->cache->file->save($cache_key, $data, 3600);
        echo json_encode($data);
        exit();
		// }
    }

    protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
        $this->load->model('openness_content_model', 'openness_content');

        $arr_sort = array('sort' => 'DESC');
        $where_array['status'] = True;
        $where_array['removed'] = False;

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
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }

            $item_list[$key]['date'] = $date_format==0?substr($item['openness_date'],5,5):$item['openness_date'];
             $item_list[$key]['url'] = $item['link_url']?$item['link_url']:"/openness/detail/content/" . $item['_id'] . '.html';
        }

        return $item_list;
    }

    protected function branchList($parent_id, $limit = 10, $offset = 0, $length = 60, $sort_by = 0) {

        $type_id = (int) $type_id;
        $arr_sort = array('sort' => 'DESC');

        $select = array('_id', 'name','website');
        $item_list = $this->site_branch->find(array('parent_id' => $parent_id, 'status' => true, 'openness_on' => true, 'removed' => False), $limit, $offset, $select, $arr_sort);


        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            }
			if($parent_id=="53fed0122f81a5843bd57125"){
				$item_list[$key]['url'] ="/opennessTowns/?branch_id=" . $item['_id'];
			}else{
				$item_list[$key]['url'] = $item['website']?$item['website']:"/opennessDepartment/?branch_id=" . $item['_id'];
			}
            
        }

        return $item_list;
    }

    protected function topicList($branch_id, $parent_id, $limit = 10, $offset = 0, $length = 60) {
        $this->load->model('openness_topic_model', 'openness_topic');

        $arr_sort = array('sort' => 'DESC');
        $where_array['status'] = True;

        $select = array('_id', 'name');
        $item_list = $this->openness_topic->findList($parent_id, null, $where_array, null, null, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            }
            $item_list[$key]['url'] = "/opennessContent/?type=towns&branch_id=" . $branch_id . "&topic_id=" . $item['_id'];
        }

        return $item_list;
    }

    protected function leaderList($_id_list, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'job_title', 'name', 'bid', 'photo');
        $this->load->model('site_leader_model', 'site_leader');
        $item_list = $this->site_leader->find(array('type_id' => $_id_list, 'status' => True, 'removed' => False), $limit, $offset, $select, $arr_sort);
        if ($limit == 1) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
            $this_channel = $this->site_channel->find(array('name' => $item['name']));
            $item_list[$key]['url'] = '/openness/leaders/' . $item['_id'] . ".html";
        }
        return $item_list;
    }

    protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {
		$this->load->model('content_model', 'content');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type');
        if ($is_pic) {
            $filter = array('status' => true, 'thumb_name' => array("\$ne" => ''), 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        }
        $item_list = $this->content->findList($_id_list, NULL, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);

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
            $item_list[$key]['description'] = nl2br($item_list[$key]['description']);

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            $item_list[$key]['thumb'] = $item['type'] == 1 ? $item['thumb_name'] : $item['thumb_large'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }

    // 图片新闻
    protected function sliderList($_id_list, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $this->load->model('content_model', 'content');

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_large', 'release_date', 'thumb_name');
        $filter = array('status' => True, 'removed' => false, 'thumb_name' => array("\$ne" => ""));
        $item_list = $this->content->findList($_id_list, NULL, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['url'] = $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['short_title'] = strip_tags(html_entity_decode($item_list[$key]['short_title']));
            $item_list[$key]['title'] = strip_tags(html_entity_decode($item_list[$key]['title']));

            if (mb_strlen($item['thumb_name']) == 20) {
                $item_list[$key]['thumb'] = "/data/upfile/" . substr($item['thumb_name'], 0, 1) . "/images/" . substr($item['thumb_name'], 2, 4) . "/" . $item['thumb_name'];
            } else {
                $item_list[$key]['thumb'] = $item['thumb_name'];
            }

            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }

    // 视频
    protected function videoList($_id_list, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('vod_model', 'vod');
        $arr_sort = array($this->sort_by[$sort_by] => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'release_date', 'thumb_name');
        $item_list = $this->vod->find(array('status' => True, 'removed' => false), $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = 'http://v.luan.gov.cn/vod/detail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

    protected function counterList($limit = 10, $offset = 0) {
        $this->load->model('openness_counter_model', 'openness_counter');
        $this->load->model('site_branch_model', 'site_branch');
        $arr_sort = array('value.total' => 'DESC');

        $select = array('_id', 'value');
        $item_list_all = $this->openness_counter->find(array('_id.site_id' => $this->site_id), $limit, $offset, $select, $arr_sort);
        foreach ($item_list_all as $key => $item) {
            $branch = $this->site_branch->find(array('_id' => $item['_id']['branch_id']));
            $item_list[$key]['branch'] = $branch['name'];
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
        }

        return $item_list;
    }
	
	protected function counterMonthList($limit = 10, $offset = 0) {
        $this->load->model('openness_counter_month_model', 'openness_counter_month');
        $this->load->model('site_branch_model', 'site_branch');
        $arr_sort = array('value.total' => 'DESC');

        $select = array('_id', 'value');
        $item_list_all = $this->openness_counter_month->find(array('_id.site_id' => $this->site_id), $limit, $offset, $select, $arr_sort);
        foreach ($item_list_all as $key => $item) {
            $branch = $this->site_branch->find(array('_id' => $item['_id']['branch_id']));
            $item_list[$key]['branch'] = $branch['name'];
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
        }

        return $item_list;
    }
	
	protected function counterYearList($limit = 10, $offset = 0) {
        $this->load->model('openness_counter_year_model', 'openness_year_month');
        $this->load->model('site_branch_model', 'site_branch');
        $arr_sort = array('value.total' => 'DESC');

        $select = array('_id', 'value');
        $item_list_all = $this->openness_year_month->find(array('_id.site_id' => $this->site_id), $limit, $offset, $select, $arr_sort);
        foreach ($item_list_all as $key => $item) {
            $branch = $this->site_branch->find(array('_id' => $item['_id']['branch_id']));
            $item_list[$key]['branch'] = $branch['name'];
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
        }

        return $item_list;
    }

    protected function attachList($content_id) {
        $this->load->model('openness_attach_model', 'openness_attach');

        $item_list = $this->openness_attach->find(array('content_id' => $content_id,'removed'=>false), NULL);
        return $item_list;
    }
	
	// 在线访谈
    protected function specialList($_id_list, $limit = 10, $offset = 0, $length = 60,$description_length = 0) {
		$this->load->model('special_model', 'special');
		$arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[1];

        $select = array('_id', 'title', 'link_url', 'create_date', 'description', 'cover');

        $item_list = $this->special->find(array('template_id' => 'press-conference', 'status' => true, 'removed' => false), $limit, $offset, $select,$arr_sort);
		if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
			$item_list[$key]['url'] = '/pressConference/channel/?_id='.$item['_id'];
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            }
			if (mb_strlen($item['description']) > $description_length) {
                $item['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }
			$item_list[$key]['description'] = str_replace("\n", "<br/>", str_replace(Chr(32), "&nbsp;", $item['description']));
			
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
            $item_list[$key]['thumb'] = $item['cover'];
        }
		
        return $item_list;
    }
	
	
	// 获取互动信件列表
    protected function itemSupervision($filter, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('supervision_model', 'supervision');
        $this->load->model('supervision_rep_model', 'supervision');
        $filter = array_merge($filter, array('status' => true, 'share_on' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
        $select = array('_id', 'process_status', 'subject', 'create_date', 'branch_id', 'hit');
        $arr_sort = array($this->sort_by[$sort_by] => "DESC");
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
	
	protected function itemLive($limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0, $time_length = 12, $description_length = 100) {

        $this->load->model('interaction_live_model', 'interaction_live');

        $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id, 'iscast' => array("\$ne" => '1'));
        $select = array('_id', 'title', 'photo', 'time', 'addr', 'guests', 'sponsor', 'intro', 'confirm_date');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

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
            // 访谈嘉宾，长度在此写死
            if (mb_strlen($item['guests']) > 25) {
                $item_list[$key]['guests'] = mb_substr(strip_tags($item['guests']), 0, 25) . '...';
            }
            if (mb_strlen($item['intro']) > $description_length) {
                $item_list[$key]['intro'] = mb_substr(strip_tags($item['intro']), 0, $description_length) . '...';
            }
            if (mb_strlen($item['time']) > $time_length) {
                $item_list[$key]['time'] = mb_substr(strip_tags($item['time']), 0, $time_length) . '...';
            }
            if (empty($item['photo'])) {
                $item_list[$key]['photo'] = '/media/images/default-live-pictrue.jpg';
            }
            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }

    public function index() {

        $current_branch_id = $this->input->get('branch_id');

        $data['branch'] = $this->site_branch->find(array('_id' => $current_branch_id));
		$View = new Blitz('template/openness-towns.html');
        
		
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');


                if ($action == 'slider') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $_id_list = explode('-', $channel_id);
                    $item_list = $this->sliderList($_id_list, $limit, $offset, $length, $sort_by, $date_format);
                }elseif ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format, $description_length) = explode('_', $matches[2]);
                    $_id_list = explode('-', $channel_id);
                    $item_list = $this->contentList($_id_list, $limit, $offset, $length, $sort_by, $date_format, $description_length);
                }elseif ($action == 'video') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $_id_list = explode('-', $channel_id);
                    $item_list = $this->videoList($_id_list, $limit, $offset, $length, $sort_by, $date_format);
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
                    $item_list = $this->opennessList($current_branch_id, $where_array, $limit, $offset, $length, $date_format, (int) $column_code);
                }elseif ($action == 'branch') {
                    list($type_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->branchList($type_id, $limit, $offset, $length, $sort_by, $date_format);
                }elseif ($action == 'topic') {
                    list($branch_id, $parent_id, $limit, $offset, $length, $sort_by) = explode('_', $matches[2]);
                    if ($branch_id != 'current') {
                        $branch_id = explode('-', $branch_id);
                    } else {
                        $branch_id = $current_branch_id;
                    }
                    $parent_id = explode('-', $parent_id);
                    $item_list = $this->topicList($branch_id, $parent_id, $limit, $offset, $length, $sort_by);
                }elseif ($action == 'leader') {
                    list($channel_id, $limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->leaderList((string) $channel_id, $limit, $offset);
                }elseif ($action == 'counter') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterList($limit, $offset);
                }elseif ($action == 'counterm') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterMonthList($limit, $offset);
                }elseif ($action == 'countery') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterYearList($limit, $offset);
                }elseif ($action == 'live') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format, $time_length, $description_length) = explode('_', $matches[2]);
                    $item_list = $this->itemLive($limit, $offset, $length, $sort_by, $date_format, $time_length, $description_length);
                }elseif ($action == 'newreply') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->itemSupervision(array("process_status" => 3), $limit, $offset, $length, $sort_by, $date_format);
                }


                $data[$struct_val] = $item_list;
            }
        }

        $View->display($data);
    }

    public function detail() {

        $this->load->model('openness_rules_model', 'openness_rules');
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_annual_report_model', 'openness_annual_report');
        $this->load->model('openness_topic_model', 'openness_topic');
        $this->load->model('openness_column_model', 'openness_column');


        $_id = $this->input->get('_id');
        $type = 'openness_' . $this->input->get('type');

        $View = new Blitz('template/openness-detail.html');
        $struct_list = $View->getStruct();
        $openness = $this->$type->find(array('_id' => $_id, 'status' => true, 'removed' => false), 1);
        if (empty($openness)) {
            show_404();
        }


        $current_branch = $this->site_branch->find(array('_id' => $openness['branch_id']), 1, 0);
        $openness['branch'] = $current_branch['name'];
        if (!empty($openness['column_code'])) {
            $current_column = $this->openness_column->find(array('code' => (int) $openness['column_code'], 'branch_id' => $openness['branch_id']));
            $openness['column'] = $current_column['name'];
        }
        if (!empty($openness['topic_id'])) {
            if (is_array($openness['topic_id'])) {
                $openness['topic'] = '';
                foreach ($openness['topic_id'] as $val) {
                    $current_topic = $this->openness_topic->find(array('_id' => (string) $val), 1, 0);
                    $openness['topic'] = !empty($current_topic) ? $current_topic['name'] . "&nbsp;&nbsp;" . $openness['topic'] : '';
                }
            }
        }

        $openness['title'] = !empty($openness['title']) ? $openness['title'] : $openness['name'];
        $openness['date'] = !empty($openness['openness_date']) ? $openness['openness_date'] : date('Y-m-d', $openness['openness_date']);


        $is_content = $type == 'openness_content' ? 1 : null;


        if ($openness['tag']) {
            foreach ($openness['tag'] as $val) {
                $openness['tags'] = $openness['tags'] . $val . "&nbsp;&nbsp;";
            }
        }


        $data = array(
            'openness' => $openness,
            'is_content' => $is_content,
            'folder_prefix' => $this->folder_prefix,
            'location' => "<a href='/'>首页</a> > <a href='/opennessContent/?branch_id=" . $openness["branch_id"] . "'>" . $openness['branch'] . "信息公开</a> > 信息浏览",
        );

        if ($View->hasContext('attach')) {
            $this->load->model('openness_attach_model', 'openness_attach');
            $item_list = $this->attachList($_id);
            $this->load->helper('number');
            foreach ($item_list as $item) {
                $View->block('/attach', array('_id' => $item['_id'],
                    'downloads' => $item['downloads'],
                    'file_size' => byte_format($item['file_size']),
                    'name' => "附件：" . $item['real_name'],
                    'url' => '/openness/?m=download&_id=' . $item['_id'],
                    'file_type' => $item['file_type'],
                        )
                );
            }
        }

        //print_r($data);die();
		$data['_id']=$_id;
        $View->display($data);
    }

    public function download() {

        $this->load->model('openness_attach_model', 'openness_attach');

        $_id = $this->input->get('_id');
        $attachment = $this->openness_attach->find(array('_id' => $_id, 'removed' => false), 1, 0);

        if (empty($attachment)) {
            header("Content-type: text/html; charset=utf-8");
            show_error('错误：记录不存在。');
        }

        $subdir = substr($attachment['saved_name'], 0, 8);
        $full_file = 'http://file.luan.gov.cn/' . $subdir . '/' . $attachment['saved_name'];

        header("Content-Type:" . $data['type']);

        header('Content-Disposition: attachment; filename="' . mb_convert_encoding($attachment['real_name'], 'GBK', 'UTF-8') . '"');

        header('Content-Length:' . $attachment['file_size']);

        ob_clean();
        //flush();

        readfile($full_file);
    }
	
	public function transRequest() {

        $send = $this->input->post("send");
        if ($send) {

            $data = array();
            $data = $this->input->post('data');
            if (empty($data['id']) || $data['id'] == '') {
                $this->resultJson('请输入查询码', 'error');
            }

            if ($this->input->post('vcode') == '' || $this->input->post('vcode') == NULL) {
                $this->resultJson('验证码不能为空！', 'error');
            }
            $this->load->library('Session');
            $captcha_chars = $this->session->userdata('captcha_chars');
            if (strnatcasecmp($captcha_chars, $this->input->post('vcode'))) {
                $this->resultJson('验证码错误', 'error');
            }
        }
    }

}

?>
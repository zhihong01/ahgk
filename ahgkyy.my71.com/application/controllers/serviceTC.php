<?php

class service extends MY_Controller {

    private $channel_tree = array("name" => "网上办事", "link_url" => "/service/");

    public function __construct() {
        parent::__construct();
        $this->load->model('service_content_model', 'service_content');
        $this->load->model('site_attach_model', 'site_attach');
        $this->load->model('service_type_model', 'service_type');
        $this->load->model('service_policy_model', 'service_policy');
		$this->load->model('site_channel_model', 'site_channel');
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
        $this->load->model('content_model', 'content');
		$this->load->model('site_branch_model', 'site_branch');
		$this->branch_list = $this->getBranchName();
		
		//echo "<pre>";print_r($this->branch_list);die();
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
	
	

    protected function serviceTypeList($parent_id, $limit = 50, $offset = 0, $length = 60) {
        $this->load->model('service_type_model', 'service_type');
        $filter = array('parent_id' => $parent_id, 'removed' => false, 'site_id' => $this->site_id,'status'=>true);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');
        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/service/type/?type=' . $parent_id . '&_id=' . $item['_id'];
            //$item_list[$key]['url'] = '/serviceSubject/' . $item['_id'].'/';
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
        }
        return $item_list;
    }

    protected function serviceDownloadList($_id,$service_type, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {
		
        $this->load->model('site_attach_model', 'site_attach');
        $select = array('_id', 'title', 'release_date');
        $sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        if ($service_type) {
            $filter = array('attach_type' => $service_type, 'removed' => false, 'status' => true, 'site_id' => $this->site_id, "module"=>"serviceDownload");
        } else {
            $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id,"module"=>"serviceDownload");
        }
        if(!empty($_id)){		
			 $item_list = $this->site_attach->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id', 'title'));
			 //echo '<pre>';var_dump($item_list);
		}else{
			$item_list = $this->site_attach->find($filter, $limit, $offset, $select, $sort);
		}
        

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            // $item_list[$key]['url'] = 'http://file.tianchang.gov.cn/mserver/download/?_id=' . $item['_id'].'&SiteId=574ffa70a7ad9898acfe6d58';
			  $item_list[$key]['url'] = '/download/?mod=site_attach&_id=' . $item['_id'];
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }
	
	protected function itemDownloadLists($download_array,$limit = 10, $offset = 0, $length = 60, $date_format = 0) {
		
        $this->load->model('site_attach_model', 'site_attach');
        $select = array('_id', 'title', 'release_date');
        $sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
		
        $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id,"module"=>"serviceDownload");
		foreach($download_array as $_id){
			$item_list[] = $this->site_attach->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'title'));
		}
		

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            // $item_list[$key]['url'] = 'http://file.tianchang.gov.cn/mserver/download/?_id=' . $item['_id'].'&SiteId=574ffa70a7ad9898acfe6d58';
			  // $item_list[$key]['url'] = '/download/?mod=site_attach&_id=' . $item['_id'];
			   $item_list[$key]['url'] = 'http://hexian.u.my71.com/download/?_id=' . $item['_id'].'&SiteId=59313631ceab063f2f611981';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }
	
	protected function itemPolicyLists($policy_array,$limit = 10, $offset = 0, $length = 60, $date_format = 0) {
		
        $this->load->model('site_attach_model', 'site_attach');
        $select = array('_id', 'title', 'release_date');
        $sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
		
        $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id,"module"=>"serviceDownload");
		foreach($policy_array as $_id){
			$item_list[] = $this->service_policy->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('title', '_id'));	
		}

        foreach ($item_list as $key => $item) {
            $item_list[$key]['url'] = '/service/policyDetail/?_id=' . $item['_id'];
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
	

    protected function serviceContentList($service_type, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {
		
        $this->load->model('service_content_model', 'service_content');

        $select = array('_id', 'title', 'release_date');
        $sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        if ($service_type) {
            $filter = array('type' => $service_type, 'removed' => false, 'status' => true, 'site_id' => $this->site_id);
        } else {
            $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id);
        }
        $item_list = $this->service_content->find($filter, $limit, $offset, $select, $sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/service/detail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }
	 // 获取互动信件列表
    protected function itemSupervision($filter, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('supervision_model', 'supervision');
		
        $filter = array_merge($filter, array('status' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
        $select = array('_id', 'process_status', 'subject', 'create_date', 'branch_id', 'question_id', 'hit','no');
        $arr_sort = array('create_date' => "DESC");
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
                $item_list[$key]['branch_name'] = $this->branch_list[$item['branch_id']];
            } else {
                $item_list[$key]['branch_name'] = '';
            }
			$item_list[$key]['short_branch_name'] = mb_substr($item_list[$key]['branch_name'], 0, 6);
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
	//信件满意度排行
	    protected function ratingBranch($filter, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('supervision_rating_mean_model', 'supervision_rating_mean');
        $filter = array_merge($filter, array('status' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
        $select = array('_id', 'rating', 'subject', 'create_date', 'branch_id', 'question_id', 'hit','no');
        $arr_sort = array('create_date' => "DESC");
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
                $item_list[$key]['branch_name'] = $this->branch_list[$item['branch_id']];
            } else {
                $item_list[$key]['branch_name'] = '';
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

 // 相关政策
    protected function policyList($_id,$type, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
		//var_dump($_id);
		if ($type) {
            $filter = array('service_type' => $type, 'site_id' => $this->site_id, 'status' => true, 'removed' => false);
        } else {
            $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id);
        }
        $select = array('_id', 'title', 'create_date');
        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt['1'];
		
		if(!empty($_id)){
		      $item_list = $this->service_policy->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('title', '_id'));	
		}else{
		      $item_list = $this->service_policy->find($filter, $limit, $offset, $select, $arr_sort);
        //echo'<pre>';var_dump($item_list);			  
		}

        foreach ($item_list as $key => $item) {
            $item_list[$key]['url'] = '/service/policyDetail/?_id=' . $item['_id'];
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
    
    // 服务指南
    protected function itemServiceContent($_id, $limit = 50, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('service_content_model', 'service_content');
		if ($_id) {
            $filter = array('type' => array("\$in" => $_id), 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id);
        }

        $select = array('_id', 'title', 'branch_id', 'release_date', 'download', 'policy', 'link_url','handle_branch','declare_url');
        $arr_sort = array('release_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->service_content->find($filter, $limit, $offset, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : '/service/contentDetail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            // 取部门
            if (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['branch_id']];
                $item_list[$key]['branch_url'] = '/supervision/branch/' . $item['branch_id'] . '/';
            } else {
                $item_list[$key]['branch'] = '';
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }
	
	 protected function itemSearchContent($_id, $limit = 50, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0,$keywords) {
        $this->load->model('service_content_model', 'service_content');
		if ($_id) {
            $filter = array('type' => array("\$in" => $_id), 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id);
        }

        $select = array('_id', 'title', 'branch_id', 'release_date', 'download', 'policy', 'link_url','handle_branch','declare_url');
        $arr_sort = array('release_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
         
        $item_list = $this->service_content->findlist($keywords,$filter, $limit, $offset, $select, $arr_sort);
		
		
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : '/service/contentDetail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            // 取部门
            if (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['branch_id']];
                $item_list[$key]['branch_url'] = '/supervision/branch/' . $item['branch_id'] . '/';
            } else {
                $item_list[$key]['branch'] = '';
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

 // 服务类型
    protected function itemServiceType($_id, $limit = 50, $offset = 0, $length = 60) {
        $this->load->model('service_type_model', 'service_type');
        $filter = array('parent_id' => $_id, 'removed' => false,'status'=>true, 'site_id' => $this->site_id);
		$select = array('_id','name');
        $sort = array('sort' => 'DESC');
        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);
        $count = count($item_list);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['key'] = $key;
            $item_list[$key]['count'] = $count;
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/service/type/?type=' . $_id . '&_id=' . $item['_id'];
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length);
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
            $child_list = $this->service_type->find(array('parent_id' => (string) $item['_id']), $limit, $offset, $select, $sort);
            if (!empty($child_list)) {
                foreach ($child_list as $key_val => $val) {
                    $child_list[$key_val]['_id'] = (string) $val['_id'];
                    $child_list[$key_val]['url'] = '/service/content/?type=' . $_id . '&_id=' . $item['_id'] . "&child_id=" . $val['_id'];
                    if (mb_strlen($val['name']) > $length) {
                        $child_list[$key_val]['short_title'] = mb_substr($val['name'], 0, $length);
                    } else {
                        $child_list[$key_val]['short_title'] = $val['name'];
                    }
                }
            }
            $item_list[$key]['child_list'] = $child_list;
        }
        return $item_list;
    }

    //调取当前服务下子栏目信息
    protected function childContentList($type_id, $_id, $limit = 50, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('service_type_model', 'service_type');
        $filter = array('parent_id' => $_id, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');
        $item_list = $this->service_type->find($filter, NULL, NULL, $select, $sort);
        if (!empty($item_list)) {
            foreach ($item_list as $key => $item) {
                $item_list[$key]['_id'] = (string) ($item['_id']);
                $item_list[$key]['url'] = '/wsbs/content/?type=' . $type_id . "&_id=" . $_id . '&child_id=' . $item['_id'];
                if (mb_strlen($item['name']) > $length) {
                    $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length);
                } else {
                    $item_list[$key]['short_title'] = $item['name'];
                }
                $item_list[$key]['child_content'] = $this->itemServiceContent($item['_id'], $limit, $offset, $length, $sort_by, $date_format);
            }
        } else {
            $filter = array('_id' => $_id, 'removed' => false, 'site_id' => $this->site_id);
            $item_list = $this->service_type->find($filter, NULL, NULL, $select, $sort);
            $item_list[0]['short_title'] = $item_list[0]['name'];
            $item_list[0]['url'] = '/wsbs/content/?type=' . $type_id . "&_id=" . $_id;
            $item_list[0]['child_content'] = $this->itemServiceContent($_id, $limit, $offset, $length, $sort_by, $date_format);
        }

        return $item_list;
    }
    protected function getMenu($channel_id, $limit = 50, $offset = 0, $length = 60) {
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
        $result = array();
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
	
	
	 protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type', 'title_color','declare_url');
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

           /* if ($item['title_color']) {
                $item_list[$key]['short_title'] = "<font style='color:" . $item['title_color'] . "'>" . $item_list[$key]['short_title'] . "</font>";
            }*/

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
	

    public function index() {
        $View = new Blitz('template/' . __CLASS__ . '/index.html');
		
			$account_id = $_SESSION['account_id'];
		
		
		$type_id = (string) $this->input->get('type');
		
        $struct_list = $View->getStruct();
        $data = array();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'servicedownload') {
                    list($service_type, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($service_type == 'all') {
                        $service_type = NULL;
                    }
                    $item_list = $this->serviceDownloadList($service_type, $limit, $offset, $length, $date_format);
                } elseif ($action == 'servicecontent') {
                    list($service_type, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($service_type == 'all') {
                        $service_type = NULL;
                    }
                    $item_list = $this->serviceContentList($service_type, $limit, $offset, $length, $date_format);
                }elseif ($action == 'newreply') {
                    //最新信件
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->itemSupervision(array("process_status" => 3), $limit, $offset, $length, $sort_by, $date_format);
					//print_r($channel_id);
					//echo'<pre>';print_r($item_list);die();
                }elseif ($action == 'branch') {
                    // 部门列表
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->itemBranch($channel_id, $limit, $offset, $length);
					//echo'<pre>';print_r($item_list);die();
                } elseif ($action == 'servicetype') {

                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->serviceTypeList($parent_id, $limit, $offset, $length);
                }elseif ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
					
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
                } elseif ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }elseif ($action == 'list') {
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
                }
                $data[$struct_val] = $item_list;
            }
        }
		$data['banshi'] = file_get_contents('./data/banshi.dat');
        $View->set(array('folder_prefix' => $this->folder_prefix));
        $data['channel_name'] = $this->channel_tree['name'];
		
		
		
		$account_id = $_SESSION['account_id'];
		$nickname = $_SESSION['nickname'];
		   $login_status = FALSE;
        if ($account_id) {
            $login_status = TRUE;
        }
        $data['login_status'] = $login_status; 
		$data['nickname'] = $nickname; 
		
		
		
		
        $View->display($data);
    }
	
        public function type() {
        $type_id = (string) $this->security->xss_clean($this->input->get('type'));
        $_id = (string) $this->security->xss_clean($this->input->get('_id'));
        $page = (int) $this->security->xss_clean($this->input->get('page'));
        if ($page == 0) {
            $page = 1;
        }

        $parent_type = $this->service_type->find(array('_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name','parent_id'));
        if (empty($parent_type)) {
            show_404();
        }
		
		if($parent_type['parent_id']!='/'){//父类不是根目录
			$parent_type = $this->service_type->find(array('_id' => $parent_type['parent_id'], 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name','parent_id'));

			$current_type_id=(string)$parent_type['_id'];
			
			$current_type = $this->service_type->find(array('_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'), array('sort' => 'DESC'));
			
			$current_id=$_id;
			
		}else{
			if (empty($_id)) {
				$current_type = $this->service_type->find(array('parent_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'), array('sort' => 'DESC'));
				$p_id= $parent_id;
			} else {
				$current_type = $this->service_type->find(array('_id' => $_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'));
			}
			$current_type_id=$type_id;
			$current_id=(string)$current_type['_id'];
		}

        if (empty($current_type)) {
            show_404($current_type);
        }
        if (!empty($current_type['linkurl'])) {
            header("Location: " . $current_type['linkurl']);
            exit();
        }
		$service_ids = $this->service_type->find(array('parent_id' => $current_id), 40, 0, array('_id'));
		$service_id=array($current_id);
		foreach ($service_ids as $key => $item) {
			$service_id[] = (string)$item['_id'];
		}
        $total_row = $this->service_content->count(array('type' => array("\$in" => $service_id), 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id'));
		
		$View = new Blitz('template/' . __CLASS__ . '/work-list.html');
		
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = (string) $current_type['_id'];
                    }else if ($type == 'current') {
                        $type = $current_type_id;
                    }else if ($type == 'ty'){
                        $type = $p_id;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } elseif ($action == 'content') {
                    // 办事指南
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($parent_id == 'current') {
                        $type = $service_id;
                    } else {
                        $type = $parent_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemServiceContent($type , $limit, $offset, $length, $sort_by, $date_format);
					//echo "<pre>";print_r($item_list);
                    foreach ($item_list as $key => $item) {
                        $item_list[$key]['content'] = '<a href="/service/contentDetail/' . $item['_id'] . '.html" target="_blank">办事指南</a>';
                        if (!empty($item['download'])) {
                            $item_list[$key]['download'] = '<a href="/service/contentDownloadLists/?_id=' . $item['_id'].'" target="_blank">表格下载</a>';
                        } else {
                            $item_list[$key]['download'] = '';
                        }
                        if (!empty($item['policy'])) {
                            $item_list[$key]['policy'] = '<a href="/service/contentPolicyLists/?_id=' . $item['_id'].'" target="_blank">相关政策</a>';
                        } else {
                            $item_list[$key]['policy'] = '';
                        }
						if (!empty($item['declare_url'])) {
                            $item_list[$key]['declare_url'] = '<a href="'.$item['declare_url'].'" target="_blank">在线申报</a>';
                        } else {
                            $item_list[$key]['declare_url'] = '';
                        }
						if(!empty($item_list[$key]['policy'])||!empty($item_list[$key]['download'])){
							$item_list[$key]['has_line']=true;
						} 
						
                    }
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['parent_type'] = $parent_type;
        $data['current_type']=(string) $current_type['_id'];
        $data['type_name']=$current_type['name'];
		$data['current_id']=$current_id;
		
		$serviceTree = $this->serviceTree($current_id);
        $location = '<a href="/">网站首页</a> &gt; <a href="/service/">网上办事</a> &gt; <a href="/service/type/?type=' . $parent_type['_id'] . '">' . $parent_type['name'] . '</a>';
        if ($serviceTree['serviceTree']) {
            $array = array_reverse($serviceTree['serviceTree'], TRUE);
			$i=0;
             foreach ($array as $item) {
				if($i==0){
					$location.=' &gt; <a href="/service/type/?type='.$type_id.'&_id='.$item['_id'].'">' . $item['name'] . '</a>';
				}else{
					$location.=' &gt; <a href="/service/type/?type='.$type_id.'&_id='.$item['_id'].'">' . $item['name'] . '</a>';
				}
				$i++;
            }
        }
        $data['location'] = $location;
		
        $View->display($data);
    }
    
    
    
	public function priority() {
        $type_id = (string) $this->input->get('type');
        $_id = (string) $this->input->get('_id');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $parent_type = $this->service_type->find(array('_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name'));
        
        if (empty($parent_type)) {
            show_404();
        }

        if (empty($_id)) {
            $current_type = $this->service_type->find(array('parent_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'), array('sort' => 'DESC'));
        } else {
            $current_type = $this->service_type->find(array('_id' => $_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'));
        }

        if (!empty($current_type['linkurl'])) {
            header("Location: " . $current_type['linkurl']);
            exit();
        }
        $total_row = $this->service_content->count(array('type' => (string) $current_type['_id'], 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id'));
  
		
		$View = new Blitz('template/list-service-zdly.html');
		
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

            	if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    } else {
                        $type = $type_id;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } elseif ($action == 'childlist') {
                    list($limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->childContentList($type_id, (string) $current_type['_id'], $limit, $offset, $length, $sort_by, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['service_name'] = $parent_type['name'];
        $data['service_id'] = empty($_id) ? $current_type['_id'] : $_id;
        $data['type_id'] = $type_id;
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a> / <a href="/wsbs/type/?type=' . $parent_type['_id'] . '">' . $parent_type['name'] . '</a> / <span>' . $current_type['name'] . '</span>';
        $View->set(array('folder_prefix' => $this->folder_prefix, 'jstag' => 'service'));
        $View->display($data);
    }
    

    public function detail() {
        $_id = $this->input->get('_id');

        $content = $this->service_content->find(array('_id' => $_id, 'status' => true, 'removed' => false));

        if (!empty($content['link_url'])) {
            header("Location: " . $content['link_url']);
        }

        $content['body'] = htmlspecialchars_decode($content['content']);
        $content['release_date'] = ($content['release_date']) ? date('Y-m-d', $content['release_date']) : '';

        $content['table_name'] = 'service_content';
        $data = array(
            'content' => $content,
            'folder_prefix' => $this->folder_prefix,
        );
        $current_type = $this->service_type->find(array('_id' => (string) $content['type'][0]), 1, 0, array('name'));
        $data['location'] = "<a href='/'>网站首页</a> / <a href='/service/'>网上办事</a> / <a href='/service/type/?type=".$current_type['_id']."'>" . $current_type['name'] . '</a> ';

        $View = new Blitz('template/detail.html');
        $struct_list = $View->getStruct();

        $View->display($data);
    }

    public function download() {
    	$_id = (string) $this->input->get('_id');
		//var_dump($_id); 
    	if (empty($_id)) {
            $current_type = $this->service_type->find(array('parent_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'), array('sort' => 'DESC'));
			
        } else {
            $current_type = $this->service_type->find(array('_id' => $_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl','parent_id'));
			
        }
        $page = (string) $this->input->get('page');
		 if ($page == 0) {
            $page = 1;
		 }
		 if(!empty($_id)){
			 $total_row = 0;
		 }else{
			 $total_row = $this->site_attach->count(array('module' => 'serviceDownload','status' => True, 'removed' => false, 'site_id' => $this->site_id));
		 }
        //$total_row = $this->site_attach->count(array('module' => 'serviceDownload','status' => True, 'removed' => false, 'site_id' => $this->site_id));
        $View = new Blitz('template/service/list-down.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($service_type, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($service_type != 'current') {
                        $service_type = $explode('-', $service_type);
                    } else {
                        $service_type =(string) $current_type['_id'];
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->serviceDownloadList($_id,$service_type, $limit, $offset, $length, $date_format);
					
                }elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count,true);
                    $item_list['page'] = $link;
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
                }else if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    } else {
                        $type = (string) $current_type['parent_id'];;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } 
                $data[$struct_val] = $item_list;
            }
			
        }
        $data['channel_name'] = '表格下载';
        $data['menu_name'] = '表格下载';
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a>';
        $View->display($data);
    }
	
	public function contentDownloadLists() {
    	$_id = (string) $this->input->get('_id');
		$content=$this->service_content->find(array('_id' => (string)$_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0);
		
        $page = (string) $this->input->get('page');
		if ($page == 0) {
			$page = 1;
		}
		
		$total_row = count($content['download']);
		
        $View = new Blitz('template/service/list-down.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($service_type, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemDownloadLists($content['download'],$limit, $offset, $length, $date_format);
					
                }elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count,true);
                    $item_list['page'] = $link;
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
                }else if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    } else {
                        $type = (string) $current_type['parent_id'];;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } 
                $data[$struct_val] = $item_list;
            }
			
        }
        $data['channel_name'] = '表格下载';
        $data['menu_name'] = '表格下载';
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a>';
        $View->display($data);
    }
	
	public function contentPolicyLists() {
    	$_id = (string) $this->input->get('_id');
		$content=$this->service_content->find(array('_id' => (string)$_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0);
		
        $page = (string) $this->input->get('page');
		if ($page == 0) {
			$page = 1;
		}
		
		$total_row = count($content['policy']);
		
        $View = new Blitz('template/service/list-down.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($service_type, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemPolicyLists($content['policy'],$limit, $offset, $length, $date_format);
					
                }elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count,true);
                    $item_list['page'] = $link;
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
                }else if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    } else {
                        $type = (string) $current_type['parent_id'];;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } 
                $data[$struct_val] = $item_list;
            }
			
        }
        $data['channel_name'] = '相关政策';
        $data['menu_name'] = '相关政策';
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a>';
        $View->display($data);
    }
	
	public function policyLt() {
    	$_id = (string) $this->input->get('_id');
    	if (empty($_id)) {
            $current_type = $this->service_type->find(array('parent_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'), array('sort' => 'DESC'));
			
        } else {
            $current_type = $this->service_type->find(array('_id' => $_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl','parent_id'));
			
        }
        $page = (string) $this->input->get('page');
		 if ($page == 0) {
            $page = 1;
		 }
		 if(!empty($_id)){
			$total_row = 0;
		 }else{
			$total_row = $this->service_policy->count(array('status' => True, 'removed' => false, 'site_id' => $this->site_id)); 
		 }
        // $total_row = $this->service_policy->count(array('status' => True, 'removed' => false, 'site_id' => $this->site_id)); 
		//var_dump($total_row);
        $View = new Blitz('template/service/list-down.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($service_type, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($service_type != 'current') {
                        $service_type = $explode('-', $service_type);
                    } else{
                        $service_type =(string) $current_type['_id'];
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->policyList($_id,$service_type, $limit, $offset, $length, $date_format);
					
                }elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count,true);
                    $item_list['page'] = $link;
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
                }else if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    } else {
                        $type = (string) $current_type['parent_id'];;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } 
                $data[$struct_val] = $item_list;
            }
			
        }
        $data['channel_name'] = '相关政策';
        $data['menu_name'] = '相关政策';
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a>';
        $View->display($data);
    }
	

    public function content() {
        $page = (string) $this->input->get('page');
        $total_row = count($this->service_content->find(array('status' => True, 'removed' => false, 'site_id' => $this->site_id), 100, 0, array('_id')));
        $View = new Blitz('template/list.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($service_type, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($service_type != 'current') {
                        $service_type = $explode('-', $service_type);
                    } else {
                        $service_type = null;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->serviceContentList($service_type, $limit, $offset, $length, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $item_list['page'] = $link;
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
                }
                $data[$struct_val] = $item_list;
            }
        }

        $data['channel_name'] = '办事指引';
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a> / <a href="/service/content/">办事指引</a> ';

        $View->display($data);
    }

	//办事指南
	public function contentGuide(){
		$_id = (string) $this->input->get('_id');
        $content = $this->service_content->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        if ($content['link_url']) {
            header("Location: " . $content['link_url']);
        }
        if (empty($content)) {
            show_404();
        }
        
        $View = new Blitz('template/' . __CLASS__ . '/detail-service.html');
        
        $this->service_content->update(array('_id' => $_id), array("views" => $content['views'] + 1));
        $content['release_date'] = ($content['confirm_date']) ? date('Y-m-d H:i:s', $content['confirm_date']) : '';
        $content['content'] = htmlspecialchars_decode($content['guide_content']);
        $download_list = '';
        if (!empty($content['download'])) {
            $download_list = '<br/><br/><font size=4 color="red">表格下载</font>:<br/><font size=3>';
            foreach ($content['download'] as $_id) {
                $item_list = $this->site_attach->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id', 'title'));
                foreach ($item_list as $item) {
                    $title = $item['title'];
                    $url = '/service/downloadDetail/' . $item['_id'] . '.html';
                    $download_list.= '&nbsp;&nbsp;&nbsp;&nbsp;' . $title;
                    $download_list.= "[<a href='" . $url . "' style='color:#1565A6' target='_blank'>下载</a>]<br/>";
                }
            }
            $download_list.='</font>';
        }
        $policy_list = '';
        if (!empty($content['policy']) && !empty($content['policy'][0])) {
            $policy_list = '<br/><br/><font size=4 color="red">相关政策:</font><br/><font size=3>';
            foreach ($content['policy'] as $_id) {
                $item_list = $this->service_policy->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('title', '_id'));
                foreach ($item_list as $item) {
                    $url = '/service/policyDetail/' . $item['_id'] . '.html';
                    $title = $item['title'];
                    $policy_list .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href='" . $url . "' target='_blank'>" . $title . "</a><br/>";
                }
            }
            $policy_list.='</font>';
        }
        array_multisort($content['table_data'],SORT_ASC);
       
        $data = array(
            'download' => $download_list,
            'policy' => $policy_list,
            'content' => $content,
        	'table_data'=>$content['table_data']
        );
        
        $serviceTree = $this->serviceTree($content['type'][0]);
        $location = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a>';
        if ($serviceTree['serviceTree']) {
            $array = array_reverse($serviceTree['serviceTree'], TRUE);
            foreach ($array as $item) {
                $location.=' / <span>' . $item['name'] . '</span>';
            }
        }
        $data['location'] = $location;
      
        $View->display($data);
	}
    
	//查询
	public function search() {
		
        $keywords = $this->input->get('keywords');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
    	$View = new Blitz('template/service/work-list.html');
        if(!empty($channel_id)){
			$channel_tree = $this->site_channel_tree->find(array('_id' => $channel_id), 1);
			if ($channel_tree['link_url']) {
				header("Location: " . $channel_tree['link_url']);
			}
		}
        if ($this->input->get('version')=='English') {
        	$_id_list = array($channel_id);
	        if (count($channel_tree['child']) > 0) {
	            foreach ($channel_tree['child'] as $key => $val) {
	                $_id_list[] = (string) $key;
	                $channel_tree1=$this->site_channel_tree->find(array('_id' =>  (string) $key), 1);
			        if (count($channel_tree1['child']) > 0) {
			            foreach ($channel_tree1['child'] as $key1 => $val1) {
			             	$channel_tree2=$this->site_channel_tree->find(array('_id' =>  (string) $key1), 1);
					        if (count($channel_tree2['child']) > 0) {
					            foreach ($channel_tree2['child'] as $key2 => $val2) {
					                $_id_list[] = (string) $key2;
					            }
					        }
			                $_id_list[] = (string) $key1;
			            }
			        }
	            }
	        }
        }else{
	        $_id_list = array($channel_id);
	        if (count($channel_tree['child']) > 0) {
	            foreach ($channel_tree['child'] as $key => $val) {
	                $_id_list[] = (string) $key;
	                $channel_tree1=$this->site_channel_tree->find(array('_id' =>  (string) $key), 1);
			        if (count($channel_tree1['child']) > 0) {
			            foreach ($channel_tree1['child'] as $key1 => $val1) {
			             	$channel_tree2=$this->site_channel_tree->find(array('_id' =>  (string) $key1), 1);
					        if (count($channel_tree2['child']) > 0) {
					            foreach ($channel_tree2['child'] as $key2 => $val2) {
					                $_id_list[] = (string) $key2;
					            }
					        }
			                $_id_list[] = (string) $key1;
			            }
			        }
	            }
	        }
	        //去除英文栏目
			$channel_tree_english = $this->site_channel_tree->find(array('_id' => '541410749a05c2671df09eb0'), 1);
			if ($channel_tree_english['link_url']) {
				header("Location: " . $channel_tree_english['link_url']);
			}
			
        	$_id_list_english = array($channel_id);
	        if (count($channel_tree_english['child']) > 0) {
	            foreach ($channel_tree_english['child'] as $key => $val) {
	                $_id_list_english[] = (string) $key;
	                $channel_tree1=$this->site_channel_tree->find(array('_id' =>  (string) $key), 1);
			        if (count($channel_tree1['child']) > 0) {
			            foreach ($channel_tree1['child'] as $key1 => $val1) {
			             	$channel_tree2=$this->site_channel_tree->find(array('_id' =>  (string) $key1), 1);
					        if (count($channel_tree2['child']) > 0) {
					            foreach ($channel_tree2['child'] as $key2 => $val2) {
					                $_id_list_english[] = (string) $key2;
					            }
					        }
			                $_id_list_english[] = (string) $key1;
			            }
			        }
	            }
	        }
	        
	        $_id_list=array_diff($_id_list,$_id_list_english);//去除英文栏目
        }
        
        
        $field = $this->input->get('field') ? (string) $this->input->get('field') : null;
        $total_row = $this->searchCount($_id_list,$keywords, $field);
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';
                //列表
                if ($action == 'content') {
                    // 办事指南
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($parent_id == 'current') {
                        $type = $_id_list;
                    } else {
                        $type = $_id_list;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemSearchContent($type , $limit, $offset, $length, $sort_by, $date_format,$keywords);
					foreach ($item_list as $key => $item) {
                        $item_list[$key]['content'] = '<a href="/service/contentDetail/' . $item['_id'] . '.html" target="_blank">办事指南</a>';
                       /*  if (!empty($item['download'])) {
                            $item_list[$key]['download'] = '<a href="/service/contentDetail/' . $item['_id'] . '.html#download" target="_blank">表格下载</a>';
                        } else {
                            $item_list[$key]['download'] = '';
                        }
                        if (!empty($item['policy'])) {
                            $item_list[$key]['policy'] = '<a href="/service/contentDetail/' . $item['_id'] . '.html#policy" target="_blank">相关政策</a>';
                        } else {
                            $item_list[$key]['policy'] = '';
                        }
						if(!empty($item_list[$key]['policy'])||!empty($item_list[$key]['download'])){
							$item_list[$key]['has_line']=true;
						}  */
						if (!empty($item['download'])) {
                            $item_list[$key]['download'] = '<a href="/service/contentDownloadLists/' . $item['_id'] . '.html" target="_blank">表格下载</a>';
                        } else {
                            $item_list[$key]['download'] = '';
                        }
                        if (!empty($item['policy'])) {
                            $item_list[$key]['policy'] = '<a href="/service/contentPolicyLists/' . $item['_id'] . '.html" target="_blank">相关政策</a>';
                        } else {
                            $item_list[$key]['policy'] = '';
                        }
						if (!empty($item['declare_url'])) {
                            $item_list[$key]['declare_url'] = '<a href="'.$item['declare_url'].'" target="_blank">在线申报</a>';
                        } else {
                            $item_list[$key]['declare_url'] = '';
                        }
						if(!empty($item_list[$key]['policy'])||!empty($item_list[$key]['download'])){
							$item_list[$key]['has_line']=true;
						} 
						
                    }
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count, 0);
                    $item_list['page'] = $link;
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
                }

                $data[$struct_val] = $item_list;
            }
        }
        //$data['parent_type']['name'] = $item_list['name'];
        $data = array_merge($data, array(
            'keywords' => $keywords,
            'total_row' => $total_row,
            'folder_prefix' => $this->folder_prefix,
       	 	'location'=>'<a href="/">网站首页</a> / <a href="/service/">网上办事</a> / 查询'	
        ));

        $View->display($data);
       } 
       

    protected function searchCount($channel_id=null,$keywords = NULL, $field = null) {
        $count = $this->service_content->listCount($keywords, array('status' => True, 'removed' => false, 'site_id' => $this->site_id), null, null, $field);
        return $count;
    }

	
    public function contentDetail() {
        $_id = (string) $this->input->get('_id');
        $content = $this->service_content->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
		//echo '<pre>';var_dump($content);
        if ($content['link_url']) {
            header("Location: " . $content['link_url']);
        }
        if (empty($content)) {
            show_404();
        }
        if(!empty($content['is_table'])){
		 $View = new Blitz('template/' . __CLASS__ . '/work-detail.html');
		}else{
		 $View = new Blitz('template/' . __CLASS__ . '/detail-service.html');
       }
        $this->service_content->update(array('_id' => $_id), array("views" => $content['views'] + 1));
        $content['release_date'] = ($content['confirm_date']) ? date('Y-m-d H:i:s', $content['confirm_date']) : '';
        $content['content'] = htmlspecialchars_decode($content['content']);
        $download_list = '';
        if (!empty($content['download'])) {
            $download_list = '<br/><br/><font size=4 color="#FF7E00">表格下载</font>:<br/><font size=3>';
            foreach ($content['download'] as $_id) {
                $item_list = $this->site_attach->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id', 'title'));
                foreach ($item_list as $item) {
                    $title = $item['title'];
                    // $url = '/index.php?c=service&m=downloadDetail&_id=' . $item['_id'] . '&SiteId='.$item['site_id'];
					 $url = 'http://hexian.u.my71.com/download/?_id=' . $item['_id'].'&SiteId=59313631ceab063f2f611981';
                    $download_list.= '&nbsp;&nbsp;&nbsp;&nbsp;' . $title;
                    $download_list.= "[<a href='" . $url . "' style='color:#1C73BB' target='_blank'>下载</a>]<br/>";
                }
            }
            $download_list.='</font>';
        }
        $policy_list = '';
        if (!empty($content['policy']) && !empty($content['policy'][0])) {
            $policy_list = '<br/><br/><font size=4 color="#FF7E00">相关政策:</font><br/><font size=3>';
			//$url = "/service/policyLt/?_id=$item['_id']";
            foreach ($content['policy'] as $_id) {
                $item_list = $this->service_policy->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('title', '_id'));
                foreach ($item_list as $item) {
                    $url = '/service/policyDetail/' . $item['_id'] . '.html';
                    $title = $item['title'];
                    $policy_list .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href='" . $url . "' target='_blank'>" . $title . "</a><br/>";
                }
            }
            $policy_list.='</font>';
        }
		
        array_multisort($content['table_data'],SORT_ASC);
       
        $data = array(
            'download' => $download_list,
            'policy' => $policy_list,
            'content' => $content,
        	'table_data'=>$content['table_data']
        ); 
        
        $serviceTree = $this->serviceTree($content['type'][0]);
        // $location = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a>/';
		
		
		if ($serviceTree['serviceTree']) {
            $array = array_reverse($serviceTree['serviceTree'], TRUE);
            foreach ($array as $item) {
                $location.=' <a href="/">网站首页</a> / <a href="/service/">网上办事</a>/ ' . $item['name'] . '';
            }
        }
      
		
		
       
        $data['location'] = $location;
		
        //$data['sb_id'] = $item['_id'];
		$data['sb_id'] = $_id;
		$data['sb_id'] = $_id;
		//var_dump($item['_id']);
        $View->display($data);
    }
    
     //获取办事服务树关系返回深度和栏目信息
    protected function serviceTree($_id = NULL, $tree = -1, $serviceTree = array()) {
        $tree++;
        if (empty($_id)) {
            return array("tree" => $tree, "serviceTree" => $serviceTree);
        }
        $service = $this->service_type->find(array('_id' => $_id), 1, 0, array('name', 'parent_id'));

        if (empty($service)) {
            return array("tree" => $tree, "serviceTree" => $serviceTree);
        }
        if ($service['parent_id'] == "/") {
            return array("tree" => $tree, "serviceTree" => $serviceTree);
        }
        $serviceTree[] = array("_id" => (string) $service['_id'], "name" => $service['name']);
        return $this->serviceTree($service['parent_id'], $tree, $serviceTree);
    }
    

    public function policyDetail() {
        $_id = (string) $this->input->get('_id');
        $content = $this->service_policy->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        if (empty($content)) {
            show_404();
        }
        $View = new Blitz('template/service/detail-service.html');
		 $this->service_policy->update(array('_id' => $_id), array("views" => $content['views'] + 1));
        $content['content'] = $content['body'];
        $content['release_date'] = ($content['confirm_date']) ? date('Y-m-d H:i:s', $content['confirm_date']) : '';
        $data['content'] = $content;
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a>';
        $View->display($data);
    }

    public function downloadDetail() {
        $_id = (string) $this->input->get('_id');
        $attachment = $this->site_attach->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));//echo'<pre>';print_r($attachment);die();

         if (empty($attachment)) {
            header("Content-type: text/html; charset=utf-8");
            show_error('错误：记录不存在。');
        }

        $subdir = substr($attachment['saved_name'], 0, 6);
		//$full_file = $this->upload_url.$this->site_id.'/'.$subdir . '/' . $attachment['saved_name'];
        $full_file = $attachment['media_path'] . $attachment['saved_name'];
        $filename = mb_convert_encoding($attachment['real_name'], 'GBK', 'UTF-8');
        if (strrpos($filename, '.')) {
            $filename = mb_convert_encoding($attachment['real_name'], 'GBK', 'UTF-8');
        } else {
            $filename = $filename . '.' . $attachment['file_type'];
        }

        $filesize = $attachment['file_size'];
        //echo'<pre>';print_r($filesize);die();

        header("Content-Type:" . $data['type']);

        header('Content-Disposition: attachment; filename="' . $filename . '"');

        header('Content-Length:' . $filesize);

        ob_clean();
        //flush();

        readfile($full_file);
    }

    
      public function guide() {

        $service_id = (string) $this->input->get('_id');
        if (empty($service_id)) {
            $service_id = null;
        }
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }

        $current_service = $this->service_type->find(array('_id' => $service_id), 1, 0, array('_id', 'name', 'parent_id'));
		
		if($service_id){
        $total_row = $this->service_content->count(array('type' => $service_id, 'site_id' => $this->site_id, 'status' => true, 'removed' => false));
		}else{
			 $total_row = $this->service_content->count(array('site_id' => $this->site_id, 'status' => true, 'removed' => false));
			
			}
        $data = array();
        $View = new Blitz('template/list-service-xz.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {

                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //菜单(二级栏目)
                 if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    } else {
                        $type = (string) $current_service['parent_id'];;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } 

                //服务指南
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = $service_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemServiceContent($_id_array, $limit, $offset, $length, 0,$date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list['page'] = $this->getPagination($total_row, $page, $per_count, true);
                }

                $data[$struct_val] = $item_list;
            }
        }
        $data['channel_name'] = '办事指南';
        $data['location'] = '<a href="/">网站首页</a> / <a>办事指南</a>';
        $View->display($data);
    }
     
     public function policy() {

        $service_id = (string) $this->input->get('_id');
		//var_dump($service_id);
        if (empty($service_id)) {
            $service_id = null;
        }
        $page = (int) $this->input->get('page');
        if (empty($page)) {
            $page = 1;
        }

        $current_service = $this->service_type->find(array('_id' => $service_id), 1, 0, array('_id', 'name', 'parent_id'));
		
		if($service_id){
        $total_row = $this->service_policy->count(array('service_type' => $service_id, 'site_id' => $this->site_id, 'status' => true, 'removed' => false));
		}else{
			 $total_row = $this->service_policy->count(array('site_id' => $this->site_id, 'status' => true, 'removed' => false));
			}
        $data = array();

        $View = new Blitz('template/service/list-down.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {

                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //菜单(二级栏目)
                 if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    } else {
                        $type = (string) $current_service['parent_id'];;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } 

                //服务指南
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = $service_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->policyList($_id_array, $limit, $offset, $length, $sort_by, $date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list['page'] = $this->getPagination($total_row, $page, $per_count, true);
                }

                $data[$struct_val] = $item_list;
            }
        }
        $data['channel_name'] = '相关政策';
        $data['location'] = '<a href="/">首页</a> / <a>相关政策</a>';
        $View->display($data);
    }
    
       public function branchType() {
        $type_id = (string) $this->input->get('type');
        $_id = (string) $this->input->get('_id');
        $branch_name= (string) $this->input->get('branch_name');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
		
            $current_type = $this->service_content->find(array('branch_id' => $_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'));

        $total_row = $this->service_content->count(array('branch_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id'));
		$View = new Blitz('template/' . __CLASS__ . '/work-list.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    } else {
                        $type = $type_id;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
					//echo'<pre>';print_r($item_list);die();
                } elseif ($action == 'content') {
                    // 办事指南
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($parent_id == 'current') {
                        $type = (string) $_id;
                    } else {
                        $type = $parent_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->branchServiceContent($type, $limit, $offset, $length, $sort_by, $date_format);

                    foreach ($item_list as $key => $item) {
                        $item_list[$key]['content'] = '<a href="/service/contentDetail/' . $item['_id'] . '.html" target="_blank">办事指南</a>';
                        if (!empty($item['download'])) {
                            $item_list[$key]['download'] = '<a href="/service/contentDownloadLists/' . $item['_id'] . '.html#download" target="_blank">表格下载</a>';
                        } else {
                            $item_list[$key]['download'] = '';
                        }
                        if (!empty($item['policy'])) {
                            $item_list[$key]['policy'] = '<a href="/service/contentPolicyLists/' . $item['_id'] . '.html#policy" target="_blank">相关政策</a>';
                        } else {
                            $item_list[$key]['policy'] = '';
                        }
						if(!empty($item_list[$key]['policy'])||!empty($item_list[$key]['download'])){
							$item_list[$key]['has_line']=true;
						} 
						
                    } 
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
                }elseif ($action == 'list') {//办事指南
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = (string) $current_type['_id'];
                    }
                    $item_list = $this->itemServiceContent($_id_array, $limit, $offset, $length, $sort_by, $date_format);
                }else if ($action == 'download') {//表格下载
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array =(string) $current_type['_id'];
                    }
                    $item_list = $this->serviceDownloadList($_id_array, $limit, $offset, $length,$date_format);
                }else if ($action == 'policy') {//相关政策
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = (string) $current_type['_id'];
                    }
                    $item_list = $this->policyList($_id_array, $limit, $offset, $length);
                }elseif ($action == 'branch') {
                    // 部门列表
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->itemBranch($channel_id, $limit, $offset, $length);
					//echo'<pre>';print_r($item_list);die();
                } 
                $data[$struct_val] = $item_list;
            }
        }
		$data['menu_name'] = $branch_name;
        $data['parent_type'] = array(
		      'parent_type'=>$parent_type,
		      'name'=>'部门办事',
			  );
        $data['type']=(string) $current_type['_id'];
        $data['type_name']=$current_type['name'];
        $data['service_id'] = $_id;
		$data['location'] = $location = '<a href="/">网站首页</a> &gt; <a href="/service/">网上办事</a> &gt; 部门办事';
        $View->set(array('folder_prefix' => $this->folder_prefix, 'jstag' => 'service'));
        $View->display($data);
    }
	
	       // 服务指南
    protected function branchServiceContent($_id, $limit = 50, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('service_content_model', 'service_content');
		if ($_id) {
            $filter = array('branch_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id);
        }

        $select = array('_id', 'title', 'branch_id', 'release_date', 'download', 'policy', 'link_url','handle_branch');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->service_content->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : '/service/contentDetail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            // 取部门
            if (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['branch_id']];
                $item_list[$key]['branch_url'] = '/supervision/branch/' . $item['branch_id'] . '/';
            } else {
                $item_list[$key]['branch'] = '';
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }
   	// 部门列表
   protected function itemBranch($channel_id, $limit = 20, $offset = 0, $length = 10) {
        $this->load->model('site_branch_model', 'site_branch');
        $filter = array('parent_id' => $channel_id, 'status' => true, 'supervision_on' => true, 'removed' => False,'service_on'=>true);
        $select = array('_id', 'name', 'website');
        $arr_sort = array('sort' => 'DESC');
        $item_list = $this->site_branch->find($filter, $limit, $offset, $select, $arr_sort);
		
        foreach ($item_list as $key => $item) {
            if ($item['_id'] == $current_id) {
                $item_list[$key]['aon'] = 'class="aon"';
            } else {
                $item_list[$key]['aon'] = '';
            }
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
				
            } else {
                $item_list[$key]['short_name'] = $item['name'];
				
            }
            $item_list[$key]['url'] = '/service/branchType/?type=' . $channel_id . '&_id=' . $item['_id'].'&branch_name='.$item['name'];
        }
        return $item_list;
    }	
     	
}

?>
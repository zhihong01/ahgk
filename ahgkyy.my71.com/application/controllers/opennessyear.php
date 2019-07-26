<?php

class opennessyear extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('site_leader_model', 'site_leader');
        $this->load->model('site_channel_model', 'site_channel');
        $this->load->model('openness_request_model', 'openness_request');
		$this->load->model('openness_column_model', 'openness_column');
		$this->load->model('special_model', 'special');
    }

	   // 专题
    protected function specialLists($limit = 10, $offset = 0, $length = 60, $date_format = 0,$has_pic=false) {
		
        $this->load->model('special_model', 'special');
		if($has_pic){
			$filter = array('status' => true, 'removed' => False, 'site_id' => $this->site_id,'thumb'=>array("\$ne"=>''));
		}else{
			$filter = array('status' => true, 'removed' => False, 'site_id' => $this->site_id);
		}
        
        $select = array('_id', 'title', 'create_date', 'thumb', 'link_url');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->special->find($filter, $limit, $offset, $select, $arr_sort);//print_r($item_list);

        foreach ($item_list as $key => $item) {

            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : '/special/column/?_id=' . $item['_id'];
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
            $item_list[$key]['thumb'] = $item['thumb'];
			$item_list[$key]['i'] = $key+1;
        }
/* 		echo "<pre>";
		print_r($item_list);
		die(); */
        return $item_list;
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
				$data[$key]['code'] = $value['link_url'] ? $value['link_url'] : "/opennessContent/?branch_id=$current_branch_id&column_code=" . $value['code'];
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
		
		//$this->cache->file->save($cache_key, $data, 3600);
        echo json_encode($data);
        exit();
		// }
    }

     // protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
         // $this->load->model('openness_content_model', 'openness_content');

         // $arr_sort = array('create_date' => 'DESC');
         // $where_array['status'] = True;
         // $where_array['removed'] = False;
		 
		 // $where_array['site_id']=$this->site_id;
       // //  var_dump($where_array['site_id']);echo 'ds12da2s';
		 
        // $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'column_code', 'openness_date', 'id','foreign_id');
        // // $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'column_code', 'openness_date', 'id');

         // $item_list = $this->openness_content->findList($branch_id, $where_array, $limit, $offset, $select, $arr_sort, $code);


         // foreach ($item_list as $key => $item) {
             // if ($item['branch_id']) {
                 // $this_branch = $this->site_branch->find(array('_id' => $item['branch_id']));
                // $item_list[$key]['branch'] = $this_branch['name'];
            // }
            // if ($item['column_code'] && $item['branch_id']) {
                 // $this_column = $this->openness_column->find(array('code' => (string) $item['column_code'], 'branch_id' => $item['branch_id']));
                 // $item_list[$key]['column'] = $this_column['name'];
             // }
             // $item_list[$key]['_id'] = (string) ($item['_id']);
             // if (mb_strlen($item['title']) > $length) {
                 // $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            // }

             // $item_list[$key]['date'] = $date_format==0?substr($item['openness_date'],5,5):$item['openness_date'];
             // // $item_list[$key]['url'] = "/openness/detail/content/" . $item['_id'] . '.html';
			   // $item_list[$key]['url'] =!empty($item['foreign_id'])?"/openness/detail/content/" . $item['foreign_id'] . '.html' : "/openness/detail/content/" . $item['_id'] . '.html';
         // }

         // return $item_list;
     // }
	 
	 
	  protected function requestList($branch_id, $limit = 10, $offset = 0, $length = 50, $date_format = 0) {
        $where_array = array('site_id' => $this->site_id, 'status' => True, 'removed' => false);
        $select = array('_id', 'name', 'create_date', 'request_branch', 'content', 'as_type', 'unit_contact', 'reply_type');
        $date_format = $this->date_foramt[$date_format];
        $arr_sort = array('create_date' => 'DESC');
        $item_list = $this->openness_request->find($where_array, $limit, $offset, $select, $arr_sort);
        $i = 1;
        foreach ($item_list as $key => $item) {

            // 依申请公开的编号
            $item_list[$key]['key'] = $offset + $i;
            $item_list[$key]['url'] = '/openness/requestDetail/?_id=' . $item['_id'];
            if ($item['as_type'] == 1) {
                $item_list[$key]['name'] = $item['name'];
            } else {
                $item_list[$key]['name'] = $item['unit_contact'];
            }
            if (isset($this->branch_list[$item['request_branch']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['request_branch']];
            } else {
                $item_list[$key]['branch'] = '';
            }
            if ($item['reply_type'] == '0') {
                $item_list[$key]['reply_type'] = '尚未办理';
            } elseif ($item['reply_type'] == '1') {
                $item_list[$key]['reply_type'] = '同意公开';
            } elseif ($item['reply_type'] == '2') {
                $item_list[$key]['reply_type'] = '同意部分公开';
            } elseif ($item['reply_type'] == '3') {
                $item_list[$key]['reply_type'] = '信息不存在';
            } elseif ($item['reply_type'] == '4') {
                $item_list[$key]['reply_type'] = '非本部门掌握';
            } elseif ($item['reply_type'] == '5') {
                $item_list[$key]['reply_type'] = '申请信息不明确';
            } else {
                $item_list[$key]['reply_type'] = '状态不明';
            }
            if (mb_strlen($item['content']) > $length) {
                $item_list[$key]['content'] = mb_substr($item['content'], 0, $length) . '...';
            }
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
            $i++;
        }
        //  print_r($item_list);die;
        return $item_list;
    }
	 
	 
	   protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
         $this->load->model('openness_content_model', 'openness_content');

         $arr_sort = array('openness_date'=>'DESC','sort' => 'DESC');
        $where_array['status'] = True;
         $where_array['removed'] = False;
         $where_array['site_id']=$this->site_id;
        // var_dump($where_array['site_id']);echo 'ds12da2s';
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
              $item_list[$key]['url'] = $item['link_url']?$item['link_url']:"/openness/detail/content/" . $item['_id'] . '.html';
         }

        return $item_list;
    }

    protected function branchList($parent_id, $limit = 10, $offset = 0, $length = 60, $sort_by = 0) {

        $type_id = (int) $type_id;
        $arr_sort = array('sort' => 'DESC');

        $select = array('_id', 'name','website');
        $item_list = $this->site_branch->find(array('parent_id' => $parent_id,  'openness_on' => true, 'removed' => False), $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
            }else{
				$item_list[$key]['short_name'] = $item['name'];
			}
            // if (empty($item['website'])) {
                // $item_list[$key]['is_website']=true;
				// $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id'];
				// $item_list[$key]['url_guide'] = "opennessGuide/?branch_id=" . $item['_id'];
				// $item_list[$key]['url_annual_report'] = "/opennessAnnualReport/?branch_id=" . $item['_id'];
				
            // }else{
                // $item_list[$key]['url'] = $item['website'];
                // $item_list[$key]['target']="_blank";
            // }
			$item_list[$key]['is_website']=true;
			$item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id'];
			$item_list[$key]['url_guide'] = "opennessGuide/?branch_id=" . $item['_id'];
			$item_list[$key]['url_annual_report'] = "/opennessAnnualReport/?branch_id=" . $item['_id'];
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
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $branch_id . "&topic_id=" . $item['_id'];
        }

        return $item_list;
    }

    protected function leaderList($type_id, $limit = 10, $offset = 0, $length = 60) {

        $this->load->model('site_leader_model', 'site_leader');

        $filter = array("type_id" => $type_id, 'status' => true, 'removed' => False, 'site_id' => $this->site_id);
        $arr_sort = array('sort' => 'DESC');

        $select = array('_id', 'name', 'job_title','photo');

        $item_list = $this->site_leader->find($filter, $limit, $offset, $select, $arr_sort);
		
		if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);

            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['name'] = $item['name'];
            }

            $item_list[$key]['url'] = "/leader/?_id=" . $item['_id'];
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
            $item_list[$key]['url'] = '/vod/detail/' . $item['_id'] . '.html';
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
        $item_list_all = $this->openness_counter->find(array('_id.site_id' => $this->site_id,'_id.branch_id'=>array("\$ne"=>'')), $limit, $offset, $select, $arr_sort);
        foreach ($item_list_all as $key => $item) {
			$branch='';
			if(!empty($item['_id']['branch_id'])){
				$branch = $this->site_branch->find(array('_id' => $item['_id']['branch_id']));
			}
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
         $item_list_all = $this->openness_counter_month->find(array('_id.site_id' => $this->site_id,'_id.report_month'=>date('Y-m',strtotime("-1 month"))), $limit, $offset, $select, $arr_sort);
	
         foreach ($item_list_all as $key => $item) {
             $branch = $this->site_branch->find(array('_id' => $item['_id']['branch_id']));
             $item_list[$key]['branch'] = $branch['full_name'];
             $item_list[$key]['total'] = $item['value']['total'];
             $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
			$item_list[$key]['class'] = $key+1;
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
            $item_list[$key]['branch'] = $branch['full_name'];
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
			$item_list[$key]['class'] = $key+1;
        }

        return $item_list;
    }

    protected function attachList($content_id) {
        $this->load->model('site_attach_model', 'site_attach');

        $item_list = $this->site_attach->find(array('module_id' => $content_id), NULL);
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
	
	// 在线访谈
    protected function itemLive($limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0, $time_length = 12, $description_length = 100) {

        $this->load->model('interaction_live_model', 'interaction_live');

        $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id, 'type_id'=>2, 'iscast' => array("\$ne" => '1'));
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
            if (mb_strlen($item['guests']) > 11) {
                $item_list[$key]['guests'] = mb_substr(strip_tags($item['guests']), 0, 11) . '...';
            }
            if (mb_strlen($item['intro']) > 11) {
                $item_list[$key]['intro'] = mb_substr(strip_tags($item['intro']), 0, 11) . '...';
            }
            if (mb_strlen($item['time']) > 11) {
                $item_list[$key]['time'] = mb_substr(strip_tags($item['time']), 0, 11) . '...';
            }
            if (empty($item['photo'])) {
                $item_list[$key]['photo'] = '/media/images/default-live-pictrue.jpg';
            }elseif(strstr($item['photo'],'data')){
				 $item_list[$key]['photo']='http://www.hngov.cn'.$item['photo'];
			}
            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }
	
	 // 广告
    protected function advertList($location_id, $limit = 10, $offset = 0, $length = 60) {

        $this->load->model('advert_resource_model', 'advert_resource');
        $this->load->model('advert_size_model', 'advert_size');

        $filter = array('location_id' => $location_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name', 'media_path', 'target_url', 'start_date', 'end_date', 'size_id','media_type');
        $sort_by = array('sort' => 'DESC');

        $item_list = $this->advert_resource->find($filter, $limit, $offset, $select, $sort_by);
        if ($limit == 1 && $item_list) {
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
			$item_list[$key]['isflash'] = $item['media_type']==3?true:false;
        }

        return $item_list;
    }

    public function index() {

       
		$View = new Blitz('template/openness/opennessyear.html');
		

		
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
                }elseif ($action == 'special') {

                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->specialLists($limit, $offset, $length, $date_format);

                } elseif ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format, $description_length) = explode('_', $matches[2]);
                    $_id_list = explode('-', $channel_id);
                    $item_list = $this->contentList($_id_list, $limit, $offset, $length, $sort_by, $date_format, $description_length);
                }elseif ($action == 'video') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $_id_list = explode('-', $channel_id);
                    $item_list = $this->videoList($_id_list, $limit, $offset, $length, $sort_by, $date_format);
				} elseif ($action == 'request') {  //依申请公开列表
                    list($branch_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $branch_id = $current_branch_id;
                    $item_list = $this->requestList($branch_id, $limit, $offset, $length, $sort_by, $date_format);
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
                }elseif ($action == 'point') {
                    list($branch_id, $column_code, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
					$where_array = array("column_code"=>array(140900,141000,70000,140600,140300,80000,141100,141200,140700,141300,141400,140200,140100,140400,140500,141600));
					$column_code = null;
                    $item_list = $this->opennessList($branch_id, $where_array, $limit, $offset, $length, $date_format, (int) $column_code);
                }elseif ($action == 'branch') {
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->branchList($parent_id, $limit, $offset, $length, $sort_by, $date_format);
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
                }elseif ($action == 'livep') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format, $time_length,$sponsor_length, $description_length) = explode('_', $matches[2]);
                    $item_list = $this->itemLive(2,$limit, $offset, $length, $sort_by, $date_format, $time_length,$sponsor_length, $description_length);
                }elseif ($action == 'newreply') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->itemSupervision(array("process_status" => 3), $limit, $offset, $length, $sort_by, $date_format);
                }elseif ($action == 'advert') {
                    list($location_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->advertList($location_id, $limit, $offset, $length);
                }elseif ($action == 'live') {
                    // 在线访谈
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format, $time_length, $description_length) = explode('_', $matches[2]);
                    $item_list = $this->itemLive($limit, $offset, $length, $sort_by, $date_format, $time_length, $description_length);
                } 


                $data[$struct_val] = $item_list;
            }
        }

        $data['current_branch_id'] = $this->gov_branch; //政府办部门id
		
		//依申请公开统计
		$request=array();
		$request['all']=$this->openness_request->count(array('status'=>true,'removed'=>false));//收到申请
		$request['done']=$request['all']-$this->openness_request->count(array('reply_type'=>0,'status'=>true,'removed'=>false));//已经办理
		$request['type0']=$this->openness_request->count(array('reply_type'=>0,'status'=>true,'removed'=>false));//尚未办理
		$request['type1']=$this->openness_request->count(array('reply_type'=>1,'status'=>true,'removed'=>false));//同意公开
		$request['type2']=$this->openness_request->count(array('reply_type'=>2,'status'=>true,'removed'=>false));//同意部分公开
		$request['type3']=$this->openness_request->count(array('reply_type'=>3,'status'=>true,'removed'=>false));//信息不存在
		$request['type4']=$this->openness_request->count(array('reply_type'=>4,'status'=>true,'removed'=>false));//非本部门掌握
		$request['type5']=$this->openness_request->count(array('reply_type'=>5,'status'=>true,'removed'=>false));//申请信息不明确
		$data['request']=$request;
        $View->display($data);
    }
	

    public function detail() {

        $this->load->model('openness_rules_model', 'openness_rules');
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_annual_report_model', 'openness_annual_report');
        $this->load->model('openness_topic_model', 'openness_topic');
        $this->load->model('openness_request_dir_model', 'openness_request_dir');
        $this->load->model('openness_column_model', 'openness_column');


        $_id = $this->input->get('_id');
        $type = 'openness_' . $this->input->get('type');

        $View = new Blitz('template/openness/detail.html');
        $struct_list = $View->getStruct();
        $openness = $this->$type->find(array('_id' => $_id, 'status' => true, 'removed' => false), 1);
        if (empty($openness)) {
            show_404();
        }


        $current_branch = $this->site_branch->find(array('_id' => $openness['branch_id']), 1, 0);
        $openness['branch'] = $current_branch['full_name'];
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

        // $openness['title'] = !empty($openness['title']) ? $openness['title'] : $openness['name'];
        // $openness['body'] = str_replace(array('"/data','"data'),'"http://www.huainan.gov.cn/data',$openness['body']);
        // $openness['date'] = !empty($openness['openness_date']) ? $openness['openness_date'] : date('Y-m-d', $openness['openness_date']);


        // $is_content = $type == 'openness_content' ? 1 : null;
$openness['title'] = !empty($openness['title']) ? $openness['title'] : $openness['name'];
        $openness['body'] = str_replace(array('"/data','"data'),'"http://www.huainan.gov.cn/data',$openness['body']);
		$openness['body'] = preg_replace('/tcyg\/DownloadInfoAnnexServlet\?id=(\d+)&amp;type=(\w+)/i','index.php?c=getFile&id=$1&type=$2',$openness['body']);
        $openness['date'] = !empty($openness['openness_date']) ? $openness['openness_date'] : date('Y-m-d', $openness['openness_date']);
		$openness['effect_date']=$openness['validity']['effect_date'];
		$openness['effect_date'] = date('Y-m-d', $openness['effect_date']);
        $openness['abolition_date']=$openness['validity']['abolition_date'];
		$openness['abolition_date'] = date('Y-m-d', $openness['abolition_date']);
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
            $item_list = $this->attachList($_id);
            $this->load->helper('number');
            foreach ($item_list as $item) {
                $View->block('/attach', array('_id' => $item['_id'],
                    'downloads' => $item['downloads'],
                    'file_size' => byte_format($item['file_size']),
                    'name' => "附件：" . $item['real_name'],
                    'url' => '/download/?mod=site_attach&_id=' . $item['_id'],
                    'file_type' => $item['file_type'],
                        )
                );
            }
        }

        //print_r($data);die();
		$data['_id']=$_id;
        $View->display($data);
    }
	 public function requestDetail() {

        $_id = (string) $this->input->get('_id');

        $View = new Blitz('template/openness/requestdetail.html');
        $struct_list = $View->getStruct();
        $content = $this->openness_request->find(array('_id' => $_id), 1);

        if (empty($content)) {
            show_error('抱歉，此内容不存在或已被删除！');
        }
        if ($content['as_type'] == '1') {
            $data['is_people'] = 1;
            $content['as_type'] = '公民';
        } else {
            $content['as_type'] = '法人/其他组织';
        }

        if ($content['author_open'] == '1') {
            $content['author_open'] = '公开';
        } else {
            $content['author_open'] = '不公开';
        }
		
		$content['name'] = mb_substr($content['name'], 0, 1) . '**';
        $content['paper_id'] = mb_substr($content['paper_id'], 0, 6) . '************';
        $content['email'] = mb_substr($content['email'], 0, 3) . '********';
        $content['phone'] = mb_substr($content['phone'], 0, 3) . '********';
        $content['workunit'] = mb_substr($content['workunit'], 0, 3) . '********';
        $content['addr'] = mb_substr($content['addr'], 0, 3) . '********';

        if (!empty($content['offer_type'])) {
            foreach ($content['offer_type'] as $key => $item) {
                if ($item == '1') {
                    $data['offer_one'] = 1;
                } elseif ($item == '2') {
                    $data['offer_two'] = 1;
                } elseif ($item == '3') {
                    $data['offer_three'] = 1;
                } elseif ($item == '4') {
                    $data['offer_four'] = 1;
                }
            }
        }

        if (!empty($content['for_type'])) {
            foreach ($content['for_type'] as $key => $item) {
                if ($item == '1') {
                    $data['for_one'] = 1;
                } elseif ($item == '2') {
                    $data['for_two'] = 1;
                } elseif ($item == '3') {
                    $data['for_three'] = 1;
                } elseif ($item == '4') {
                    $data['for_four'] = 1;
                } elseif ($item == '5') {
                    $data['for_five'] = 1;
                }
            }
        }
        if ($content['reply_type'] == '0') {
            $content['reply_type'] = '尚未办理';
        } elseif ($content['reply_type'] == '1') {
            $content['reply_type'] = '同意公开';
        } elseif ($content['reply_type'] == '2') {
            $content['reply_type'] = '同意部分公开';
        } elseif ($content['reply_type'] == '3') {
            $content['reply_type'] = '信息不存在';
        } elseif ($content['reply_type'] == '4') {
            $content['reply_type'] = '非本部门掌握';
        } elseif ($content['reply_type'] == '5') {
            $content['reply_type'] = '申请信息不明确';
        } else {
            $content['reply_type'] = '状态不明';
        }

        $content['date'] = ($content['create_date']) ? date('Y-m-d', $content['create_date']) : '';

        $data['data'] = $content;

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
       
$full_file = $attachment['media_path'] . $attachment['saved_name'];
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
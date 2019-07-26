<?php

class xctj extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('site_channel_model', 'site_channel');
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
        $this->load->model('content_model', 'content');
        $this->load->model('openness_content_model', 'openness_content');
		$this->load->library('user_agent'); 
		$this->branch_list = $this->getBranchName();
		if ($this->agent->is_mobile()){ 
		header("http://www.guangde.gov.cn/mobile/");exit; 
		}
    }

    protected function welcomeList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false,$current_channel) {

        $arr_sort = array('views' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'release_date', 'views','channel');
        $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id,'release_date'=>array("\$gt"=>1420041600));
        $item_list = $this->content->findList(null, NULL, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

			if(count($item['channel'])>1){
				$l=array_search($current_channel,$item['channel']);
				if($l){
					$item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html?l='.$l;
				}else{
					$item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
				}
				
			}else{
				$item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
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
        $arr_sort = array('sort' => 'DESC');

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

    
    /* 论坛排行 */

    protected function bbsCountList($sort_by, $limit = 10, $offset = 0, $length = 60) {

        $this->load->model('forum_branch_counter_model', 'forum_branch_counter');

        $filter = array('site_id' => $this->site_id);
        $select = array('branch_id', 'branch_name', 'total', 'rate');
        $arr_sort = array('total' => 'DESC');

        $item_list = $this->forum_branch_counter->find($filter, $limit, $offset, $select, $arr_sort);

        return $item_list;
    }

    // 获取互动信件列表
    protected function itemSupervision($filter, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('supervision_model', 'supervision');
        $filter = array_merge($filter, array('status' => true,  'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
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

	//信件统计
      protected function counterSupervision() {
        $this->load->model('supervision_counter_model', 'supervision_counter');
        $supervision_counter = $this->supervision_counter->find(array("site_id" => $this->site_id));
        if (!empty($supervision_counter)) {
            $supervision_status = array("unknown", "newmessage", "assigned", "replied", "thenask", "resolved", "total");
            //本日
            foreach ($supervision_counter['today_counter'] as $key => $item) {
                $today_counter[$supervision_status[$key]] = $item;
            }
			$today_counter['total'] = array_sum($today_counter);
            //本月
            foreach ($supervision_counter['this_month_counter'] as $key => $item) {
                $this_month_counter[$supervision_status[$key]] = $item;
            }
			$this_month_counter['total'] = array_sum($this_month_counter);
            //本年
            foreach ($supervision_counter['this_year_counter'] as $key => $item) {
                $this_year_counter[$supervision_status[$key]] = $item;
            }
			$this_year_counter['total'] = array_sum($this_year_counter);
        }
        unset($supervision_counter);
        $supervision_counter = array(
            "today_counter" => $today_counter,
            "this_month_counter" => $this_month_counter,
            "this_year_counter" => $this_year_counter,
        );  
        $this->load->model('supervision_model', 'supervision');
        //当前年份开始时间
		$year_time = date("Y-m-d",mktime(0, 0, 0, 01, 01, date("Y")));

        //咨询
        $supervision_counter['consult'] = $this->supervision->count(array("question_id" => "593a7d8cceab06c92361197f", 'site_id' => $this->site_id, 'removed' => false));
        //建议
        $supervision_counter['suggest'] = $this->supervision->count(array("question_id" => "593a7d9aceab06b82561197f", 'site_id' => $this->site_id, 'removed' => false));
        //投诉
        $supervision_counter['complaint'] = $this->supervision->count(array("question_id" => "593a7daaceab066b2161197f", 'site_id' => $this->site_id,  'removed' => false));
        //举报
        $supervision_counter['report'] = $this->supervision->count(array("question_id" => "593a7d58ceab06c12561197f", 'site_id' => $this->site_id,  'removed' => false));
		
        return $supervision_counter;
    }


	protected function counterMonthList($limit = 10, $offset = 0) {
        $this->load->model('openness_counter_month_model', 'openness_counter_month');
        $this->load->model('site_branch_model', 'site_branch');
        $arr_sort = array('value.total' => 'DESC');

        $select = array('_id', 'value');
        $item_list_all = $this->openness_counter_month->find(array('_id.site_id' => $this->site_id,'_id.report_month'=>date('Y-m',strtotime("-1 month"))), $limit, $offset, $select, $arr_sort);
	
        foreach ($item_list_all as $key => $item) {
            $branch = $this->site_branch->find(array('_id' => $item['_id']['branch_id']));
            $item_list[$key]['branch'] = $branch['name'];
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
        }

        return $item_list;
    }
	
	//部门信件满意度排行
    protected function ratingBranch($limit, $offset, $length, $sort_by) {
        $this->load->model('supervision_rating_counter_model', 'supervision_rating_counter');
        $arr_sort = array("total" => "DESC");
        $record = $this->supervision_rating_counter->find(NULL, $limit, $offset, "*", $arr_sort);
        if (!empty($record) && $limit == 1) {
            $item_list[] = $record;
        } else {
            $item_list = $record;
        }
        foreach ($item_list as $key => $item) {
            $branch_name = $this->branch_list[$item['branch_id']];
            if (mb_strlen($branch_name) > $length) {
                $item_list[$key]['branch_name'] = mb_substr($branch_name, 0, $length) . '';
            } else {
                $item_list[$key]['branch_name'] = $branch_name;
            }
        }
		//echo'<pre>';var_dump($item_list);
        return $item_list;
    }
	
	//一天、一周、一月统计
	protected function contentHitList($limit = 10, $offset = 0, $length = 60, $date_format = 0, $date_rank = 0) {

        $arr_sort = array('views' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type', 'title_color','source_table', 'views');
		if($date_rank==0){
			$date_rank=strtotime('-1 day');
		}elseif($date_rank==1){
			$date_rank=strtotime('-1 week');
		}else{
			$date_rank=strtotime('-1 month');
		}
        $filter = array('create_date'=>array("\$gt"=>$date_rank),'release_date'=>array("\$lte"=>time()),'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $item_list = $this->content->findList($_id_list, NULL, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
			
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }
	
	
		//信息公开信息点击量一天、一周、一月统计
	protected function opennessHitList($limit = 10, $offset = 0, $length = 60, $date_format = 0, $date_rank = 0) {

        $arr_sort = array('views' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'column_code', 'openness_date', 'id','link_url', 'views');
		if($date_rank==0){
			$date_rank=strtotime('-1 day');
		}elseif($date_rank==1){
			$date_rank=strtotime('-1 week');
		}else{
			$date_rank=strtotime('-1 month');
		}
        $filter = array('create_date'=>array("\$gt"=>$date_rank),'openness_date'=>array("\$lte"=>date("Y-m-d H:i:s",time())),'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $item_list = $this->openness_content->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : '/openness/detail/content/' . $item['_id'] . '.html';
			
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }
	
	//一天、一月、一年统计
	protected function contentHitsList($limit = 10, $offset = 0, $length = 60, $date_format = 0, $date_rank = 0) {
        //$_id_list = '53d6fe9cd55d501e44d1328b';  
        $arr_sort = array('views' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type', 'title_color','source_table', 'views');
		if($date_rank==0){
			$date_rank=strtotime('-1 day');
		}elseif($date_rank==1){
			$date_rank=strtotime('-1 month');
		}else{
			$date_rank=strtotime('-1 year');
		}
        $filter = array('create_date'=>array("\$gt"=>$date_rank),'release_date'=>array("\$lte"=>time()),'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $item_list = $this->content->findList($_id_list, NULL, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);
//echo'<pre>';var_dump($item_list);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
			
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }
	
	protected function requestCounterList($limit = 10, $offset = 0) {
		//var_dump($limit);
        $this->load->model('openness_request_counter_model', 'openness_request_counter');

        $arr_sort = array('value.total' => 'DESC');
		//$filter = array('status' => true, 'removed' => false);
        $item_lists = $this->openness_request_counter->find(null, $limit, $offset, null, $arr_sort);
		//echo"<pre>";var_dump($item_lists);
		
        foreach ($item_lists as $key => $item) {
			if(empty($item['_id']['branch_id'])){
				continue;
			}
			$item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
            $item_list[$key]['branch'] = $this->branch_list[$item['_id']['branch_id']];
			$item_list[$key]['total'] = $item['value']['total'];
        }
        return $item_list;
    }

	
    public function index() {
		$type_id = (int)$this->input->get('type');
       //var_dump($type_id);
		if($type_id == 4){
			$View = new Blitz('template/wztj/zmhd.html');
		}elseif($type_id == 5){
			$View = new Blitz('template/wztj/djph.html');
		}elseif($type_id == 6){
			$View = new Blitz('template/wztj/openness_djph.html');
		}else{
			$View = new Blitz('template/wztj/xctj.html');
		}

        $struct_list = $View->getStruct();
        $data = array();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                    
                   
                }   elseif ($action == 'product') {
                    //信箱列表
                    list($product_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->itemSupervision(array("product_id" =>(int)$product_id), $limit, $offset, $length, $sort_by, $date_format);
                     
                } elseif ($action == 'question') {
                    //信箱列表
                    list($question_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->itemSupervision(array("question_id" => $question_id), $limit, $offset, $length, $sort_by, $date_format);
                    
                // }elseif ($action == 'newreply') {
                    // list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    // $item_list = $this->itemSupervision(array("process_status" => 3), $limit, $offset, $length, $sort_by, $date_format);
                   
                }elseif ($action == 'rating') {
                    //部门信件满意度排行
                    list($limit, $offset, $length, $sort_by) = explode('_', $matches[2]);
                    $item_list = $this->ratingBranch($limit, $offset, $length, $sort_by);
                }elseif ($action == 'welcomenews') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    $item_list = $this->welcomeList($_id_list, $limit, $offset, $length, $date_format, $description_length,false,$_id_list[0]);
                    //err 4s
                   
               
					}elseif ($action == 'counterm') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterMonthList($limit, $offset);
                  
                }elseif ($action == 'hit') {
                    list($limit, $offset, $length, $date_format, $date_rank) = explode('_', $matches[2]);
                    $item_list = $this->contentHitList($limit, $offset, $length, $date_format, $date_rank);
                }elseif ($action == 'opennesshit') {
                    list($limit, $offset, $length, $date_format, $date_rank) = explode('_', $matches[2]);
                    $item_list = $this->opennessHitList($limit, $offset, $length, $date_format, $date_rank);
                }elseif ($action == 'hits') {
                    list($limit, $offset, $length, $date_format, $date_rank) = explode('_', $matches[2]);
                    $item_list = $this->contentHitsList($limit, $offset, $length, $date_format, $date_rank);
                }elseif ($action == 'requestcounter') {
					list($limit, $offset) = explode('_', $matches[2]);
					$item_list = $this->requestCounterList($limit, $offset);
                }
                $data[$struct_val] = $item_list;
            }
        }

        $View->set(array('folder_prefix' => $this->folder_prefix));
       
		
        
		//今日热帖-今日回复-本年度回复
 	
		$supervision_counter = $this->counterSupervision();
        
        //var_dump($supervision_counter);

         $data['complain'] = $supervision_setting['total_complain'];
        //今日热帖-今日回复-本年度回复
        $supervision_counter = $this->counterSupervision();
        //今日
        $data['today_counter'] = $supervision_counter['today_counter'];
        //本月
        $data['this_month_counter'] = $supervision_counter['this_month_counter'];
        //今年
        $data['this_year_counter'] = $supervision_counter['this_year_counter'];
        //咨询
        $data['consult'] = $supervision_counter['consult'];
        //建议
        $data['suggest'] = $supervision_counter['suggest'];
        //投诉
        $data['complaint'] = $supervision_counter['complaint'];
        $data['total_supervision'] =(int) $this->supervision->count(array('status' => true, 'removed' => False, 'share_on' => true, 'cancelled' => false, 'site_id' => $this->site_id));
       
         $data['total_supervision_counter'] =(int) $this->supervision->count(array('status' => true, 'removed' => False, 'share_on' => true, 'cancelled' => false, 'site_id' => $this->site_id,'process_status'=>array('$gte'=>3)));
		
		
		
		
		
		//print_r($data);die;
		
        $View->display($data);
    }

}

?>
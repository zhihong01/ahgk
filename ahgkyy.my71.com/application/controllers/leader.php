<?php

class leader extends MY_Controller {

    public function __construct() {
        parent::__construct();
		$this->load->model('site_leader_type_model', 'site_leader_type');
        $this->load->model('site_leader_model', 'site_leader');
        $this->load->model('site_channel_model', 'site_channel');
        $this->load->model("site_channel_tree_model", "site_channel_tree");
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('content_model', 'content');
    }
	
	
	
	public function tag() {
	
		$tag =(string) htmlspecialchars($this->input->get('tag'));
		$tag=str_replace(array(" ","　","\t","\n","\r","（挂职）"),'',$tag);
		
		$type = (string) $this->input->get('type');
        $page = (int) $this->input->get('page');
		$data=array();
		
		
		if($type=='huodong'){
			$filter=array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
			$type_name='领导活动';
		}
		
		//$tag_array=$tag?array($tag):array("陆应平","刘中汉");
		
		$total_row = $this->content->tagCount($tag_array,$filter);
		$View = new Blitz('template/leader/list-leader.html');
		
		$struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //列表
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);

                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = $_id_list;
                    }

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->tagList($tag, $type, $limit, $offset, $length, $date_format, $description_length);
					
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count,0);
                    $item_list['page'] = $link;
                }

                $data[$struct_val] = $item_list;
            }
        }
		
		
		
		$leader = $this->site_leader->find(array('name' => $tag), 1);
		//echo'<pre>';var_dump($leader);
	    //$data['location']='<a href="/">网站首页</a> > <a href="/leader/?id='.$leader['_id'].'">'.$tag.'</a> > '.$type_name;
        $data['location']='<a href="/">网站首页</a> > <a href="/leader/?type='.$leader['type_id'].'&_id='.$leader['_id'].'">'.$tag.'</a> > '.$type_name;
		
		$data['channel_name']=$type_name;
		
        $View->display($data);
    }

	 protected function getLocation($channel_tree, $current_id, $current_name) {
        $result = array();
        $result[] = array('/', '网站首页');

        /* if (count($channel_tree['parent'])) {
            array_shift($channel_tree['parent']);
        }
        foreach ($channel_tree['parent'] as $key => $value) {
            $result[] = array($this->folder_prefix . '/channel/' . $key . '/', $value);
        } */

        $result[] = array($this->folder_prefix . '/channel/' . $current_id . '/', $current_name);

        return $result;
    }


    protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false,$keyword=NULL) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type');
        if ($is_pic) {
            $filter = array('channel'=>$_id_list,'status' => true, 'thumb_name' => array("\$ne" => ''), 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('channel'=>$_id_list,'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        }
        $item_list = $this->content->findTag($keyword, $filter, $limit, $offset, $select, $arr_sort); 

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
            $item_list[$key]['thumb'] = $item['type'] == 1 ? $item['thumb_name'] : $item['thumb_large'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }

    // 领导列表
    protected function leaderList($type_id, $limit = 10, $offset = 0, $length = 60,$current_leader=null) {

        $this->load->model('site_leader_model', 'site_leader');

        $filter = array("type_id" => $type_id, 'status' => true, 'removed' => False, 'site_id' => $this->site_id);
        $arr_sort = array('sort' => 'DESC');

        $select = array('_id', 'name', 'job_title','type_id','photo');

        $item_lists = $this->site_leader->find($filter, $limit, $offset, $select, $arr_sort);
    	if ($limit == 1&&$item_lists) {
            $item_lists = array(0 => $item_lists);
        }
		$item_list=array();
        foreach ($item_lists as $key => $item) {

			if($current_leader!= (string) ($item['_id'])){
				$item_list[$key]=$item;
				$item_list[$key]['_id'] = (string) ($item['_id']);

				if (mb_strlen($item['name']) > $length) {
					$item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...'; 
				} else {
					$item_list[$key]['name'] = $item['name'];
				}
				if (mb_strlen($item['job_title']) > 11) {
					$item_list[$key]['sjob_title'] = mb_substr($item['job_title'], 0, 12) . ''; // 领导列表 删除...
				} else {
					$item_list[$key]['sjob_title'] = $item['job_title'];
				}
				$item_list[$key]['is-first'] =$key==0?'class="first"':'';
				$item_list[$key]['url'] = "/leader/?type=".$type_id."&_id=" . $item['_id'];
			}
        }
        return $item_list;
    }

    protected function getMenu($channel_id, $limit = 50, $offset = 0, $length = 60) {
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
	
	 // 获取互动信件列表
    protected function itemSupervision($filter, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('supervision_model', 'supervision');
        $filter = array_merge($filter, array('status' => true, 'share_on' => true, 'cancelled' => false, 'removed' => False, 'site_id' => $this->site_id));
        $select = array('_id', 'process_status', 'subject', 'create_date', 'update_date', 'branch_id', 'no', 'question_id', 'hit');
        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $item_list = $this->supervision->find($filter, $limit, $offset, $select, $arr_sort);
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
	 protected function tagList($tag, $type, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {
		$this->load->model('content_model', 'content');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type', 'subhead','body','old_InfoFile','old_InfoValue2');
		if($type=='huodong'){
			$filter=array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
		}
		$tag=$tag?array($tag):array("刘凌晨","王竹梅");

        $item_list = $this->content->findTag($tag, $filter, $limit, $offset, $select, $arr_sort);

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
            } else {
                $item_list[$key]['description'] = strip_tags($item['description']);
            }
            $weibo = explode(";", $item_list[$key]['link_url']);
            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            $item_list[$key]['xinlang_url'] = $weibo[0];
            $item_list[$key]['tengxun_url'] = $weibo[1];
			$item_list[$key]['thumb']=$item['thumb_name'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
            if (($key + 1) % 5 == 0) {
                $item_list[$key]['line'] = 'line';
            }
        }

        return $item_list;
    }


    public function index() {
        $data = array();
        $_id = $this->input->get('_id');
        $View = new Blitz('template/leader/leader.html');
        
        $type_id=$this->input->get('type');
        
        if(empty($type_id)){
        	$type_id='597c387aceab068b3413e593';//xian委领导
        }
        if($_id == '597c39b7ceab06923a13e593' || empty($_id) || empty($type_id)){
			
			$data['writemail'] = true;
		} else{
			$data['writemail'] = false;
		}
 		$type = $this->site_leader_type->find(array('_id' => $type_id), 1);
    	if (empty($type)) {
            show_404();
        }
        $data['type']=$type;
        
        if (empty($_id)) {//默认按sort排序，第一个领导
            $leader = $this->site_leader->find(array('name' => array('$ne' => ''), 'removed' => false, 'status' => true, 'site_id' => $this->site_id,'type_id'=>$type_id), 1, 0, '*', array('sort' => 'DESC'));
        } else {
            $leader = $this->site_leader->find(array('_id' => $_id), 1);
        }

        $name=$leader['name'];
		 $tag=str_replace(array(" ","　","\t","\n","\r"),'',$leader['name']);

        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
					
                    $item_list = $this->contentList($channel_id, $limit, $offset, $length, $date_format, $description_length,false,$name);
                }else if ($action == 'slider') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    $item_list = $this->contentList($channel_id, $limit, $offset, $length, $date_format, $description_length,true,$name);
                }elseif ($action == 'leader') {
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                 	if ($channel_id == 'current') {
                        $channel_id = $type_id;
                    }
                    $item_list = $this->leaderList($channel_id, $limit, $offset, $length);
              
				}elseif ($action == 'huodong') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
					
                    $item_list = $this->tagList($tag,'huodong', $limit, $offset, $length, $date_format, $description_length);
                }
				
				
				
				
				elseif($action == 'supervision') {
                    list($channel_id,$limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
			
                    $filter = array("product_id" => $product_id, "branch_id" => $leader['branch_id']);
                  
                    $item_list = $this->itemSupervision($filter, $limit, $offset, $length, $sort_by, $date_format);
                } 
                $data[$struct_val] = $item_list;
            }
        }
		
		
		
		//当前位置
        if ($View->hasContext('location')) {
            $location = array();
            $result = $this->getLocation($channel_tree, $channel_tree['_id'], $channel_tree['name']);
            foreach ($result as $val) {
                $location[] = '<a href="' . $val[0] . '">' . $val[1] . '</a>';
            }

            $data['location'] = implode(' / ', $location).'县长之窗';
        }
		 $data['leader_id'] = $_id;
		// $leader['resume']=strip_tags(nl2br($leader['resume']));//工作简历
		$leader['resume']=nl2br($leader['resume']);//工作简历
		$leader['duty']=nl2br($leader['duty']);//工作分工
		$leader['tag_name']=urlencode($leader['name']);
        $data['leader'] = $leader;
        $data['current_channel'] = (string) $this_channel['_id'];
        $this_branch = $this->site_branch->find(array('name' => $leader['name']), 1);
        $data['current_branch'] = (string) $this_branch['_id'];
		$data['type_id']=$type_id;
		$data['product_id']=$product_id;
		$data['product_name']=$product_name;
		$data['is_hidden']=$is_hidden;
		$data['menu_id']=$item['_id'];
        $View->set(array('folder_prefix' => $this->folder_prefix));
        $View->display($data);
    }

    
    //根据头衔名称获取该头衔下的所有领导
    protected function getLeader($job,$leaderList){
    	$leader_arr=array();
    	foreach ($leaderList as $key=>$val){
    		if($val['job_title']==$job){
    			$leader_arr[]=$val;
    		}
    	}	
    	return $leader_arr;
    }
    
}

?>
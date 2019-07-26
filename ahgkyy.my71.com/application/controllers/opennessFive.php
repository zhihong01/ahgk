<?php

class opennessFive extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('openness_column_model', 'openness_column');
        $this->load->model('openness_topic_model', 'openness_topic');
    }

    protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null,$cljg=null) {
        $this->load->model('openness_content_model', 'openness_content');

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $where_array['status'] = True;
        $where_array['removed'] = False;

        $select = array('_id', 'title','short_title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'column_code', 'openness_date', 'id','topic_id','link_url','foreign_id','publisher','relation','jiedu_type','coll_result_on');

		if($cljg == 1){
			$codearr =array(50201,50301,50401,50501,50601,50701,50801,50901);
			$where_array = array_merge(array('column_code'=>array("\$in" => $codearr),'branch_id'=>$branch_id), $where_array);
			$item_list = $this->openness_content->find($where_array, $limit, $offset, '*', $arr_sort);
		}elseif($branch_id=="545214aaba6de118f0c2bde5" && $code==180101){
			$item_list = $this->openness_content->find($where_array, $limit, $offset, '*', $arr_sort);
		}else{
			$item_list = $this->openness_content->findList($branch_id, $where_array, $limit, $offset, '*', $arr_sort, $code);
		}

        foreach ($item_list as $key => $item) {
	//	 if($item_list[$key]['branch_id']!='523003727f8b9a1508ee347f' && $item_list[$key]['column_code']==50202){	
            if ($item['branch_id']) {
                $this_branch = $this->site_branch->find(array('_id' => $item['branch_id']));
                $item_list[$key]['branch'] = $this_branch['name'];
            }
            if ($item['column_id']) {
                $this_column = $this->openness_column->find(array('_id' =>$item['column_id']));
                $item_list[$key]['column'] = $this_column['name'];
            }
			if (!empty($item['topic_id'][0])) {
				if (is_array($item['topic_id'])) {
					$item_list[$key]['topic']='';
					foreach ($item['topic_id'] as $val) {
						$current_topic = $this->openness_topic->find(array('_id' => (string) $val), 1, 0);
						$item_list[$key]['topic'] = !empty($current_topic) ? $current_topic['name'] . "&nbsp;&nbsp;" . $item_list[$key]['topic'] : '';
					}
				}
			}
			if ($item['tag']) {
				foreach ($item['tag'] as $val) {
					$item_list[$key]['tags'] = $item_list[$key]['tags'] . $val . "&nbsp;&nbsp;";
				}
			}
			
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['id'] =$item['_id'];
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }
			if($item['relation']){
				//print_r($item);
				foreach($item['relation'] as $id=>$name){
					if($item['jiedu_benji']){//本级政策文件及解读
						$jiedu_type = '解读';
					}else if($item['jiedu_meiti']){//媒体解读
						$jiedu_type = '解读';
					}else if($item['jiedu_shangji']){//上级政策文件及解读
						$jiedu_type = '解读';
					}else if($item['jiedu_tubiao']){//图表解读
						$jiedu_type = '解读';
					}else if($item['jiedu_wenjian']){//文件解读
						$jiedu_type = '解读';
					}else if($item['coll_result_on']){//意见征求公告
						$jiedu_type = '意见征求公告';
					}else if($item['feedback_on']){//意见采纳反馈情况
						$jiedu_type = '意见采纳反馈情况';
					}else{
						$jiedu_type = '文件';
					}
					$item_list[$key]['title'] = $item_list[$key]['title'].' | <a style="color:#E20304" href="/openness/detail/content/'.$id.'.html" target="_black">'.$jiedu_type.'</a>';
				}
			}
			//$item_list[$key]['titles'] = $item['title'];
			//$item_list[$key]['serial_numbers'] = $item['serial_number'];
            $item_list[$key]['date'] = $item['openness_date'];
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : "/opennessFive/detail/content/" . $item['_id'] . '.html';
	//		}
        }

        return $item_list;
    }

    protected function topicList($branch_id, $parent_id, $limit = 10, $offset = 0, $length = 60) {
        $this->load->model('openness_topic_model', 'openness_topic');

        $arr_sort = array('sort' => 'ASC');
        $where_array['status'] = True;
        //$where_array['removed'] = False;

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
	
    protected function columnList($branch_id, $parent_id, $limit = 10, $offset = 0, $length = 60) {

        $arr_sort = array('sort' => 'ASC');
        $where_array['status'] = True;
        $where_array['removed'] = False;

        $select = array('_id', 'name');
        $item_list = $this->openness_column->findList($parent_id, null, $where_array, null, null, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            }
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $branch_id . "&topic_id=" . $item['_id'];
        }
        return $item_list;
    }
	
    protected function branchList($parent_id, $limit = 10, $offset = 0, $length = 60, $sort_by = 0) {

        $arr_sort = array('sort' => 'DESC');

        $select = array('_id', 'name', 'website');
        $item_list = $this->site_branch->find(array('parent_id' => $parent_id, 'status' => true, 'openness_on' => true, 'removed' => False), null, $offset, $select, $arr_sort);


        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            }
            if (empty($item['website'])) {
                $item_list[$key]['is_website'] = true;
                $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id'];
                $item_list[$key]['url_guide'] = "/opennessGuide/?branch_id=" . $item['_id'];
                $item_list[$key]['url_annual_report'] = "/opennessAnnualReport/?branch_id=" . $item['_id'];
            } else {
                $item_list[$key]['url'] = $item['website'];
                $item_list[$key]['target'] = "_blank";
            }
        }

        return $item_list;
    }
	
	protected function attachList($content_id) {
        $this->load->model('site_attach_model', 'site_attach');

        $item_list = $this->site_attach->find(array('module_id' => $content_id), NULL);
        return $item_list;
    }
	
    public function index() {

        $current_branch_id = $this->input->get('branch_id') ? $this->input->get('branch_id') : null;
        $typefive = $this->input->get('typefive') ? $this->input->get('typefive') : null;
        if ($this->input->get('branch_id') == 'all') {
            $current_branch_id = $this->gov_branch;
        }
        $current_column_code = $this->input->get('column_code') ? (int)$this->input->get('column_code') : null;
        $current_topic_id = $this->input->get('topic_id') ? $this->input->get('topic_id') : null;
		$data['code_type'] = $current_column_code;
		if($current_branch_id){
			$data['branch'] = $this->site_branch->find(array('_id' => $current_branch_id));
		}
		
		
		$current_cljg = $this->input->get('cljg') ? $this->input->get('cljg') : null;
		$current_tag = $this->input->get('tag') ? $this->input->get('tag') : null;
		
       $page = (int) $this->security->xss_clean(htmlentities($this->input->get('page'),ENT_COMPAT,'UTF-8'));
        if ($page == 0) {
            $page = 1;
        }

        $item_list = array();
        if ($current_column_code) {
            $data['column'] = $this->openness_column->find(array('code' => $current_column_code, 'branch_id' => $current_branch_id,'status'=>true,'removed'=>false));
        }

        if ($current_topic_id) {
            $where_array = array('topic_id' => $current_topic_id);
            $topic = $this->openness_topic->find(array('_id' => $current_topic_id));
            $data['column']['name'] = $topic['name'];
        }

        $where_array['status'] = True;
        $where_array['removed'] = False;
        if ($this->input->get('branch_id') == 'all') {
            $current_branch_id = null;
            $data['column']['name'] = '';
            $data['branch'] = '';
        }
		
		if(empty($current_column_code)){
			$this_child_column=$this->openness_column->find(array('branch_id'=>$current_branch_id,'removed'=>false,'status'=>true,'parent_id'=>'/'),null,0,array('name','link_url','code'),array("sort" => 'desc','code'=>'asc'));
			if($this_child_column[0]){
				$current_type='folder';
			}
		}elseif($current_column_code % 10==0){
			$this_child_column=$this->openness_column->findFloder(array('branch_id'=>$current_branch_id,'removed'=>false,'status'=>true), $current_column_code,null,0,array('name','link_url','code'),array("sort" => 'desc','code'=>'asc'));//print_r($this_child_column);die();
			if($this_child_column[0]&&$current_column_code){
				$current_type='folder';
			}
		}
		if($typefive == "four"){
			foreach($this_child_column as $key => $val){
				if (($current_column_code % 10000) === 0) {
					if($val['code'] % 100 == 0){
						$level_one[$key]['name']=$val['name'];
						$level_one[$key]['url']=$val['link_url']?$val['link_url']:"/opennessFive/?branch_id=$current_branch_id&column_code=".$val['code']."&typefive=last";
						$level_one[$key]['code']=$val['code'];
						$level_one[$key]['info']=$this->opennessList($current_branch_id, $where_array, 7, 0, 45, 1, (int)$val['code']);
					}
				} elseif (($current_column_code % 100) === 0) {
					if($val['code'] % 100 != 0){
						$level_one[$key]['name']=$val['name'];
						
						$level_one[$key]['url']=$val['link_url']?$val['link_url']:"/opennessFive/?branch_id=$current_branch_id&column_code=".$val['code']."&typefive=last";
						
						$level_one[$key]['code']=$val['code'];
						$level_one[$key]['info']=$this->opennessList($current_branch_id, $where_array, 7, 0, 45, 1, (int)$val['code']);
					}
				}
			}
			
			$View = new Blitz('template/openness/opennessfive/list-four-sjxx.html');
			$data['level_one'] = $level_one;
		}
		
		
		
		

       elseif($typefive == "zxgkopen"){
		  $View = new Blitz('template/openness/opennessfive/list-four-zxgk.html'); 
	   }
	   elseif($typefive == "sjxxopen"){
		  $View = new Blitz('template/openness/opennessfive/list-five-sjxx.html'); 
	   }
	   elseif($typefive == "jgqgopen"){
		  $View = new Blitz('template/openness/opennessfive/list-three-jgqg.html'); 
	   }
	   elseif($typefive == "jrscfour"){
		  $View = new Blitz('template/openness/opennessfive/list-four-jrsc.html'); 
	   }
	   elseif($typefive == "govjcsx"){
		  $View = new Blitz('template/openness/opennessfive/list-four-govjcsx.html'); 
	   }
	   elseif($typefive == "four"){
		  $View = new Blitz('template/openness/opennessfive/list-four.html'); 
	   }
	   elseif($typefive == "last"){
		  $View = new Blitz('template/openness/opennessfive/list-last.html'); 
	   }
	   else{
		  $View = new Blitz('template/openness/opennessfive/list-one.html'); 
	   }
	   
	   
	   $total_row = $this->openness_content->listCount($current_branch_id, $where_array, $current_column_code);
	   
		//收费目录清单
		if($current_branch_id=="545214aaba6de118f0c2bde5" && $current_column_code==180101){
			
			$column_id_arr = array("548fdad07f8b9a695c3784f1","5a30f4f57f8b9a8a15829c0f","560a431e7f8b9a864aec351b");
			$brancharr = array("5230012d7f8b9a1c08ee347d","523001b37f8b9a1a08ee347d",$current_branch_id);
			$where_array = array_merge(array('all_column_ids'=>array("\$in"=>$column_id_arr),'branch_id'=>array("\$in"=>$brancharr)),$where_array);
			
			$total_row = $this->openness_content->count($where_array);
		}
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');

                if ($action == 'list') {
                    list($branch_id, $column_code, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($branch_id != 'current') {
                        $branch_id = $branch_id;
                        $current_column_code = (int)$column_code;
                    } else {
                        $branch_id = $current_branch_id;
                    }

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->opennessList($branch_id, $where_array, $limit, $offset, $length, $date_format, $current_column_code,$current_cljg);
                }

                //获取信息公开专题列表
                if ($action == 'topic') {
                    list($branch_id, $parent_id, $limit, $offset, $length, $sort_by) = explode('_', $matches[2]);
                    if ($branch_id != 'current') {
                        $branch_id = explode('-', $branch_id);
                    } else {
                        $branch_id = $current_branch_id;
                    }
                    $parent_id = explode('-', $parent_id);
                    $item_list = $this->topicList($branch_id, $parent_id, $limit, $offset, $length, $sort_by);
                }

                //获取部门列表
                if ($action == 'branch') {
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($parent_id == 'current') {
                        $parent_id = $parent_branch_id;
                    }
                    $item_list = $this->branchList($parent_id, $limit, $offset, $length, $sort_by, $date_format);
                }

				//获取信息公开专题列表
                if ($action == 'column') {
                    list($branch_id, $parent_id, $limit, $offset, $length, $sort_by) = explode('_', $matches[2]);
                    if ($branch_id != 'current') {
                        $branch_id = explode('-', $branch_id);
                    } else {
                        $branch_id = $current_branch_id;
                    }
                    $parent_id = explode('-', $parent_id);
                    $item_list = $this->columnList($branch_id, $parent_id, $limit, $offset, $length, $sort_by);
                }
				
                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }

                    $link = $this->getPagination($total_row, $page, $per_count, 0);
                    $item_list[0]['page'] = $link;
                }
                $data[$struct_val] = $item_list;
            }
        }

        $data['is_column'] = $data['column']['name'] ? $data['column']['name'] : "";
		 if(!empty($current_tag)){
			 $data['is_column']=$current_tag;
		 }

        $data['no_tag'] = empty($current_tag) ?true :false;
        $data['current_branch_id'] = $current_branch_id;
        $data['openness_type'] = "信息公开目录";
        $data['openness_content'] = "hover";
        $data['gov_branch_id'] = $this->gov_branch;
		if($typefive=="zxgkopen"){
			$data['location'] = '<a href="/">首页</a> <span>></span> <a href="/opennessFive/?branch_id=545214aaba6de118f0c2bde5&column_code=50100">五公开专栏</a> > 供给侧结构性改革';
		}else{
			$data['location'] = '<a href="/">首页</a> <span>></span> <a href="/opennessFive/?branch_id=545214aaba6de118f0c2bde5&column_code=50100">五公开专栏</a> > '.$data['column']['name'];
		}
		
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
        $type = 'openness_' . $this->input->get('type');//die($type);

        $View = new Blitz('template/openness/opennessfive/openness-detail.html');
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
        if (!empty($openness['topic_id'][0])) {
            if (is_array($openness['topic_id'])) {
                $openness['topic'] = '';
                foreach ($openness['topic_id'] as $val) {
                    $current_topic = $this->openness_topic->find(array('_id' => (string) $val), 1, 0);
					$openness['topic']=!empty($current_topic) ? $current_topic['name'] . "&nbsp;&nbsp;" . $openness['topic'] : '';
                }
            }
        }

        $openness['title'] = !empty($openness['title']) ? $openness['title'] : $openness['name'];
        $openness['date'] = !empty($openness['openness_date']) ? $openness['openness_date'] : date('Y-m-d', $openness['openness_date']);

		$openness['is_effect']='有效';
		if($openness['validity']['effect_date']>time() ||$openness['validity']['abolition_date']<time()){
			$openness['is_effect']='无效';
		}

        $is_content = $type == 'openness_content' ? 1 : null;
         
		  if ($openness['invalid_status'] == '1') {
            $openness['invalid_status'] = '已废止';
        } elseif ($openness['invalid_status'] == '2') {
            $openness['invalid_status'] = '已失效';
        } else {
            $openness['invalid_status'] = '有效';
        }
		 
		 
		 

        if ($openness['tag']) {
            foreach ($openness['tag'] as $val) {
                $openness['tags'] = $openness['tags'] . $val . "&nbsp;&nbsp;";
            }
        }
		$openness['body']=str_replace('src="UploadFile/','src="/UploadFile/',$openness['body']);
		$openness['body']=str_replace('src="/Uploadfile/','src="/UploadFile/',$openness['body']);
        $data = array(
            'openness' => $openness,
            'is_content' => $is_content,
            'folder_prefix' => $this->folder_prefix,
            'location' => "<a href='/'>网站首页</a> &gt; <a href='/openness/'>信息公开</a> &gt; <a href='/opennessContent/?branch_id=" . $openness["branch_id"] . "'>" . $openness['branch'] . "信息公开</a> &gt; 信息浏览",
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
                   /*  'url' => '/download/?mod=site_attach&_id=' . $item['_id'], */
				    'url' => 'http://file.fy.gov.cn:9000/mserver/download/?_id=' . $item['_id'].'&SiteId='.$item['site_id'],
                    'file_type' => $item['file_type'],
                        )
                );
            }
        }

		$data['_id']=$_id;
        $View->display($data);
    }
	
}

?>
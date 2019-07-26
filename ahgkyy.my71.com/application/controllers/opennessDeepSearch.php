<?php

class opennessDeepSearch extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('openness_content_model', 'openness_content');
    }
	
		protected function topicList($branch_id, $parent_id, $limit = 10, $offset = 0, $length = 60, $sort_by = 0) {
        $this->load->model('openness_topic_model', 'openness_topic');

        $arr_sort = array($this->sort_by[$sort_by] => 'ASC');

        $select = array('_id', 'name');
        $item_list = $this->openness_topic->findList($parent_id, null, null, null, null, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            }
            $item_list[$key]['url'] = "/openness/channel/?type=news&branch_id=" . $branch_id[0] . "&topic_id=" . $item['_id'];
        }

        return $item_list;
    }
	
    public function search() {
		
		 $page = (int) $this->security->xss_clean(htmlentities($this->input->get('page'),ENT_COMPAT,'UTF-8'));
        if ($page == 0) {
            $page = 1;
        }
		
        $this->vals['serial_number'] = $serial_number = $this->security->xss_clean(htmlentities($this->input->get('serial_number'),ENT_COMPAT,'UTF-8'));
        $this->vals['document_number'] = $document_number = $this->security->xss_clean(htmlentities($this->input->get('document_number'),ENT_COMPAT,'UTF-8'));
        $this->vals['keywords'] = $keywords = $this->security->xss_clean(htmlentities($this->input->get('keywords'),ENT_COMPAT,'UTF-8'));
        $this->vals['content'] = $content = $this->security->xss_clean(htmlentities($this->input->get('content'),ENT_COMPAT,'UTF-8'));
		
        $this->vals['from_date']=$from_date = $this->security->xss_clean(htmlentities($this->input->get('from_date'),ENT_COMPAT,'UTF-8'));
        $this->vals['to_date']=$to_date = $this->security->xss_clean(htmlentities($this->input->get('to_date'),ENT_COMPAT,'UTF-8'));
		
		$filter_list = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
		$like = array();
		if($serial_number){
			$filter_list['serial_number'] = $serial_number;
		}
		if($document_number){
			$filter_list['document_number'] = $document_number;
		}
		if($keywords){
			$like['title'] = $keywords;
		}
		if($content){
			$like['body'] = $content;
		}
		$branch_id = null;
		
        $total_row = $this->openness_content->searchCount($branch_id, $filter_list, $like, $from_date, $to_date);//print_r($filter_list);die();
		
		if($total_row == 0){
			$this->vals['empty_result'] = '没有查询到数据';
		}
		$View = new Blitz('template/openness/openness-search-advanced.html');
        $struct_list = $View->getStruct();
		
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
				$item_list = '';
				
                //列表
                if ($action == 'search') {
                    list($limit, $offset, $length, $sort_by) = explode('_', $matches[2]);

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
					
                    $item_list = $this->opennessList($branch_id, $filter_list, $limit, $offset, $length, 1, $like, $from_date, $to_date);
                }elseif ($action == 'topic') {
					list($branch_ids, $parent_id, $limit, $offset, $length, $sort_by) = explode('_', $matches[2]);
					if ($branch_ids != 'current') {
						$branch_ids = explode('-', $channel_id);
					} else {
						$branch_ids = (array) $current_branch_id;
					}
					$parent_id = explode('-', $parent_id);
					$item_list = $this->topicList($branch_ids, $parent_id, $limit, $offset, $length, $sort_by);
				}elseif ($action == 'counter') {
					list($limit, $offset) = explode('_', $matches[2]);

					$item_list = $this->counterList($limit, $offset);
				} elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list['page'] = $this->getPagination($total_row, $page, $per_count, false);
                }
				
                $this->vals[$struct_val] = $item_list;
            }
        }

        $this->vals['keywords'] = $keywords;
        $this->vals['total_row'] = $total_row;

        $View->display($this->vals);;
    }
    

    protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $like = array(),$from_date, $to_date) {
	
        $this->load->model('openness_content_model', 'openness_content');

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
       

        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'openness_date');

        $item_list = $this->openness_content->searchList($branch_id, $where_array, $limit, $offset, $select, $arr_sort, $like,$from_date, $to_date);

        foreach ($item_list as $key => $item) {

            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            }else{
				$item_list[$key]['short_title'] = $item['title'];
			}

            $item_list[$key]['date'] = $item['openness_date'];
            $item_list[$key]['url'] = "/openness/detail/content/" . $item['_id'] . '.html';
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
	protected function counterMonthList($limit = 10, $offset = 0,$length = 8) {
        $this->load->model('openness_counter_month_model', 'openness_counter_month');
        $this->load->model('site_branch_model', 'site_branch');
        $arr_sort = array('value.total' => 'DESC');

        $select = array('_id', 'value');
        $item_list_all = $this->openness_counter_month->find(array('_id.site_id' => $this->site_id,'_id.report_month'=>date('Y-m',strtotime("-1 month"))), $limit, $offset, $select, $arr_sort);
	
        foreach ($item_list_all as $key => $item) {
             $branch = $this->site_branch->find(array('_id' => $item['_id']['branch_id']));
             $item_list[$key]['branch'] = $branch['name'];
			if (mb_strlen($branch['name']) > $length) {
				$item_list[$key]['short_branch'] = mb_substr($branch['name'],0,$length). '...';
			}else{
				 $item_list[$key]['short_branch'] = $branch['name'];
			}
			$item_list[$key]['i'] = $key+1;
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
        }

        return $item_list;
    }
	
	protected function counterYearList($limit = 10, $offset = 0,$length = 8) {
        $this->load->model('openness_counter_year_model', 'openness_year_month');
        $this->load->model('site_branch_model', 'site_branch');
        $arr_sort = array('value.total' => 'DESC');

        $select = array('_id', 'value');
        $item_list_all = $this->openness_year_month->find(array('_id.site_id' => $this->site_id), $limit, $offset, $select, $arr_sort);
        foreach ($item_list_all as $key => $item) {
            $branch = $this->site_branch->find(array('_id' => $item['_id']['branch_id']));
			$item_list[$key]['branch'] = $branch['name'];
			if (mb_strlen($branch['name']) > $length) {
				$item_list[$key]['short_branch'] = mb_substr($branch['name'],0,$length). '...';
			}else{
				 $item_list[$key]['short_branch'] = $branch['name'];
			}
            $item_list[$key]['total'] = $item['value']['total'];
            $item_list[$key]['url'] = "/opennessContent/?branch_id=" . $item['_id']['branch_id'];
        }

        return $item_list;
    }


	
    public function index() {
        $current_branch_id = $this->input->get('branch_id') ? $this->input->get('branch_id') : null;

        $keywords = $this->input->get('keywords')?$this->input->get('keywords'):null;
        $way = $this->input->get('way')?$this->input->get('way'):null;
        $from_date = $this->input->get('from_date')?$this->input->get('from_date'):null;
        $to_date = $this->input->get('to_date')?$this->input->get('to_date'):null;
		
        $page = (int) $this->security->xss_clean(htmlentities($this->input->get('page'),ENT_COMPAT,'UTF-8'));
        if ($page == 0) {
            $page = 1;
        }
		
		$where_array=array('status'=>True,'removed'=>False,'site_id'=>$this->site_id);
		
		$like=array();
		if($keywords){
			if($way){
				$like=array($way=>$keywords);
			}else{
				$like=array('title'=>$keywords);
			}
		}
		
		
		
        $total_row = $this->openness_content->searchCount($current_branch_id, $where_array,$like,$from_date, $to_date);

        $View = new Blitz('template/openness/openness-search-advanced.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];

                $struct_val = trim($matches[0], '/');
                //列表
                if ($action == 'search') {
                    list($branch_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($branch_id != 'current') {
                        $branch_id = explode('-', $branch_id);
                    } else {
                        $branch_id = $current_branch_id;
                    }

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->opennessList($branch_id, $where_array, $limit, $offset, $length, $date_format,$like,$from_date, $to_date);
					
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
				
				if ($action == 'branch') {
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($parent_id == 'current') {
                        $parent_id = $parent_branch_id;
                    }
                    $item_list = $this->branchList($parent_id, $limit, $offset, $length, $sort_by, $date_format);
                }
				
				if ($action == 'counterm') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterMonthList($limit, $offset);
                }
				
				if ($action == 'countery') {
                    list($limit, $offset) = explode('_', $matches[2]);

                    $item_list = $this->counterYearList($limit, $offset);
                }
				
                $data[$struct_val] = $item_list;
            }
        }

        $data['keywords'] = $keywords;
        $data['total_row'] = $total_row;
        $data['location']='<a href="/">网站首页</a> &gt; <a href="/openness/">信息公开</a> &gt; 搜索列表';

        $View->display($data);
    }

    

}

?>
<?php

class wbdt extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('special_model', 'special');
        $this->load->model('special_content_model', 'special_content');
        $this->load->model('special_column_model', 'special_column');
       
    }

  
    protected function contentList($special_id,$column_id, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0, $description_length = 0, $is_pic = false) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'thumb', 'link_url', 'body');
		if(empty($column_id)){
			// 如果是图片，过滤没有图片的
			if ($is_pic) {
				$filter = array('special_id'=>$special_id,'thumb' => array("\$ne" => ""), 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
			} else {
				$filter = array('special_id'=>$special_id,'status' => true, 'removed' => false, 'site_id' => $this->site_id);
			}
		}else{
			// 如果是图片，过滤没有图片的
			if ($is_pic) {
				$filter = array('special_id'=>$special_id,'column_id' => $column_id, 'thumb' => array("\$ne" => ""), 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
			} else {
				$filter = array('special_id'=>$special_id,'column_id' => $column_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
			}
		}

        $item_list = $this->special_content->find($filter, $limit, $offset, $select, $arr_sort);
		//echo'<pre>';var_dump($item_list);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            
            $item_list[$key]['url'] = empty($item['link_url']) ? '/wbdt/detail/?_id=' . $item['_id'] : $item['link_url'];
           
        }
        return $item_list;
    }

	
    public function index() {
    	$data=array();
		$special_id = "59657da87f8b9a887a0ebfee";
        $View = new Blitz('template/wbdt.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($column_id, $limit, $offset) = explode('_', $matches[2]);
                    $item_list = $this->contentList($special_id,$column_id, $limit, $offset);
                }
                $data[$struct_val] = $item_list;
            }
        }

        $View->set(array('folder_prefix' => $this->folder_prefix));
        $View->display($data);
    }
	
	protected function attachList($content_id) {
        $this->load->model('site_attach_model', 'site_attach');
        $item_list = $this->site_attach->find(array('module_id' => $content_id), NULL);
        return $item_list;
    }
    
	public function detail() {
        $_id = (string) $this->input->get('_id');
		
		$old_id = (int)$this->input->get('id');
        
		if(!empty($old_id)){
			$content = $this->special_content->find(array('old_id' => $old_id, 'status' => true, 'removed' => false), 1);//print_r($content);die();
		}else{
			if (empty($_id)) {
				show_404('该条信息不存在');
			}
			$content = $this->special_content->find(array('_id' => $_id, 'status' => true, 'removed' => false), 1);
		}
		
        if (empty($content)) {
            show_404('该条信息不存在');
        }
        if ($content['link_url']) {
            header("Location:" . $content['link_url']);
        }
        $special = $this->special->find(array('_id' => $content['special_id'], 'status' => true, 'removed' => false), 1);
		
        if (empty($special)) {
            show_404('专题不存在');
        }
      
	    $View = new Blitz('template/detail-wbdt.html');
		 
        $content['views']++;
		if(!empty($old_id)){
			$this->special_content->update(array('old_id' => $old_id), array("views" => (int) $content['views']));
		}else{
			$this->special_content->update(array('_id' => $_id), array("views" => (int) $content['views']));
		}
        $content['body']=str_replace('src="UploadFile/','src="/UploadFile/',$content['body']);
		$content['body']=str_replace('src="/Uploadfile/','src="/UploadFile/',$content['body']);
        $column = $this->special_column->find(array('_id' => $content['column_id']), 1);
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                // 取导航栏目
                if ($action == 'specialmenu') {
                    list($limit) = explode('_', $matches[2]);
                    $item_list = $this->menuList(array('special_id' => (string) $special['_id'], 'navigation_on' => true, 'removed' => false), $limit);
                }
                $this->vals[$struct_val] = $item_list;
            }
        }
        $content['release_date'] = ($content['release_date']) ? date('Y-m-d H:i', $content['release_date']) : '';
		
        if (!empty($content['author'])) {
            $content['author'] = '作者： ' . $content['author'];
        }
        if (!empty($content['copy_from'])) {
            $content['copy_from'] = '信息来源： ' . $content['copy_from'];
        }
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
		
		$content['table_name']='special_content';
        $this->vals['content'] = $content;
        $this->vals['column'] = $column;
        $this->vals['special'] = $special;
        $this->vals['location'] = implode(' / ', array('<a href="/">网站首页</a>', '<a href="/special/">' . $this->special_name . '</a>', "<a href='/special/column/?_id=" . $special['_id'] . "'>" . $special['title'] . "</a>", "<a href='/special/content/?_id=" . $column['_id'] . "'>" . $column['name'] . "</a>"));
        $View->display($this->vals);
    }
	

}

?>
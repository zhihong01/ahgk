<?php

class vod extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('vod_model', 'vod');
        $this->load->model("vod_channel_model", "vod_channel");
    }

    protected function itemList($channel_list = array(), $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
		
        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'description', 'thumb_name', 'create_date','release_date', 'duration', 'visits');

        $item_list = $this->vod->findList($channel_list, NULL, array('status' => true, 'removed' => false), NULL, NULL, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }

            $item_list[$key]['duration'] = @gmdate('i:s', $item['duration']);
			$item_list[$key]['url'] = $this->folder_video . '/detail/' . $item['_id'] . '.html';
            $item_list[$key]['create_date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }

    protected function itemCount($channel_id) {

        $count = $this->vod->listCount((array) $channel_id, NULL, array('status' => True, 'removed' => false));
        return $count;
    }

    protected function getMenu($parent_id, $limit,$offset) {
        $channel_list = $this->vod_channel->find(array('parent_id' => $parent_id), $limit,$offset);

        return $channel_list;
    }

    public function index() {
        $channel_id = (string) $this->input->get('channel');
        $page = (int) $this->input->get('page');

        if ($page == 0) {
            $page = 1;
        }

        $channel = $this->vod_channel->find(array('_id' => $channel_id), 1);
        if (empty($channel)) {
            show_error('目录不存在！');
        }

        $_id_list = (array) $channel_id;

        $total_row = $this->vod->listCount($_id_list, NULL, array('status' => True));
		$View = new Blitz('template/vod.html');

        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
				
				//HOT列表
                if ($action == 'hot') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($channel_id == 'all') {
                        $channel_id = NULL;
                    }

                    $item_list = $this->itemList($channel_id, $limit, $offset, $length, 3, $date_format);
					$i=1;
					
                    foreach ($item_list as $item) {
                        $thumb = substr($item['thumb_name'], 0, 4)=='http'?$item['thumb_name']:$this->upload_url . substr($item['thumb_name'], 0, 8) . '/' . $item['thumb_name'];
                        $View->block($struct, array('_id' => $item['_id'], 'url' => $this->folder . '/detail/' . $item['_id'] . '.html', 'title' => $item['title'], 'thumb' => $thumb, 'description' => $item['description'], 'date' => $item['create_date'], 'duration' => $item['duration'],'i'=>$i));
						$i++;
                    }
                }
				
                //列表
                if ($action == 'list') {
                    list($_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($_id != 'current') {
                        $_id_array = explode('-', $_id);
                    } else {
                        $_id_array = $_id_list;
                    }

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemList($_id_array, $limit, $offset, $length, $sort_by, $date_format);
                    foreach ($item_list as $item) {
                        $thumb = substr($item['thumb_name'], 0, 4)=='http'?$item['thumb_name']:$this->upload_url . substr($item['thumb_name'], 0, 8) . '/' . $item['thumb_name'];
                        $View->block($struct, array('_id' => $item['_id'], 'url' => $this->folder_video . '/detail/' . $item['_id'] . '.html', 'short_title' => $item['title'], 'thumb' => $thumb, 'description' => $item['description'], 'date' => $item['create_date'], 'duration' => $item['duration'], 'visits' => $item['visits']));
                    }
                }

                //某个内容中所有图片
                if ($action == 'slider') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $_id_list = explode('-', $channel_id);
                    $item_list = $this->itemList($_id_list, $limit, $offset, $length, $sort_by, $date_format);
                    foreach ($item_list as $item) {
                        $thumb = $this->upload_url . substr($item['thumb_name'], 0, 8) . '/' . $item['thumb_name'];
                        $View->block($struct, array('_id' => $item['_id'], 'url' => $this->folder_video . '/detail/' . $item['_id'] . '.html', 'title' => $item['title'], 'thumb' => $thumb, 'description' => $item['description'], 'duration' => $item['duration']));
                        $View->block('/preview-' . $matches[2], array('_id' => $item['_id'], 'url' => $this->folder_video . '/detail/' . $item['_id'] . '.html', 'title' => $item['title'], 'thumb' => $thumb, 'description' => $item['description'], 'duration' => $item['duration']));
                    }
                }
				
				
				//菜单
                if ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $current_id = $parent_id;
                    } else {
                        $current_id = $channel_id;
                    }
					
					if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
					
                    $menu_list = $this->getMenu($current_id, $limit, $offset, $length);
                    foreach ($menu_list as $key => $menu) {
						
						$items=$this->itemList((array)$menu['_id'],5,0,15);
                        $View->block($struct, array('_id' => $menu['_id'], 'url' => $this->folder_video . '/channel/' . $menu['_id'] . '/', 'name' => $menu['name'],'items'=>$items));
                    }
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $View->block($struct, array('page' => $link));
                }

                
				
            }
        }

        $data = array(
            'channel_id' => $channel['_id'],
            'channel_name' => $channel['name'],
            'channel_description' => nl2br($channel['description']),
            'menu_id' => $channel['_id'],
            'menu_name' => $channel['name'],
            'total_row' => $total_row,
            'folder_video' => $this->folder_video,
        );

        //当前位置
        if ($View->hasContext('location')) {
            $location = array();
            $location[] = '<a href="/">首 页</a>';
            $location[] = '<a href="' . $this->folder_video . '/channel/' . $channel['_id'] . '/' . '">' . $channel['name'] . '</a>';

            $data['location'] = implode(' / ', $location);
        }

        $View->display($data);
    }

    public function detail() {
		
        $_id = $this->input->get('_id');
        $mobile = $this->input->get('mobile');
		if($_id){
			$vod = $this->vod->find(array('_id' => $_id), 1);
		}
		
        if (empty($vod)) {
            show_404();
        }
        if (!isset($vod['channel'][0]) || empty($vod['channel'][0])) {
            show_error('栏目不存在');
        }
        
        $channel_id = $vod['channel'][0];
        $channel = $this->vod_channel->find(array('_id' => $channel_id), 1);
        if (empty($channel)) {
            show_error('栏目不存在');
        }
		
		//老站程序判断
		$file_ext=explode(".",$vod['saved_name']);
		$vod['file_ext']=$file_ext[1];
		
		if($mobile && $file_ext[1]=='flv'){
			$filepath=$file_ext[0].'.mp4';
			$vod['file_ext']='mp4';
		}else{
			$filepath=$vod['saved_name'];
		}
		
		if($vod['file_ext']=='flv' || $vod['file_ext']=='swf'){
			$vod['is_flv']=true;
		}
		
		if($vod['file_ext']=='rm' || $vod['file_ext']=='rmb'){
			$vod['is_rm']=true;
		}
		
		
		$file_url='';
		if(!empty($vod['old_id'])){
			if($vod['old_ty']==7 && $vod['old_id']>1586){
				$newurl='http://www.ahtctv.net/voddata/';
			}else{
				if($vod['old_id']>340){
					$newurl='/voddata/';
				}else{
					$newurl='http://bbs.tianchang.gov.cn/vod/';
				}
			}
		}else{
			$newurl='http://www.ahtctv.net/voddata/';
		}
		
		$vod['file_url']=$newurl.$filepath;
		


        $View = new Blitz('template/detail-vod.html');
        $struct_list = $View->getStruct();

        $data['vod']=$vod;
        $View->display($data);
    }

}

?>
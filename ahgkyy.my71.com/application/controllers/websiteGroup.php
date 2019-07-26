<?php

class WebsiteGroup extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }
    
    // 友情链接
    protected function friendLinkList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0,$sort=0) {

        $this->load->model('friend_link_model', 'friend_link');

        $filter = array("type_id" => $_id_list, 'status' => true, 'removed' => False, 'site_id' => $this->site_id);
        if($sort==1){
            $arr_sort = array('sort' => 'DESC');
        }else{
            $arr_sort = array('sort' => 'ASC');
        }
        

        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'link_url', 'file_path', 'width', 'height', 'target', 'confirm_date');

        $item_list = $this->friend_link->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = (string) ($item['link_url']);
            $item_list[$key]['thumb'] = $item['file_path'];
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }
    
  

    protected function branchList($parent_id, $limit, $offset, $length){
        $this->load->model('site_branch_model', 'site_branch');

        $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id, 'parent_id'=>$parent_id);
        $select = array('_id', 'name', 'website');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->site_branch->find($filter, $limit, $offset, $select, $arr_sort);
      ($item_list);
        if ($limit == 1 && !empty($item_list)) {
            $item_list = array(0 => $item_list);
        }
        foreach ($item_list as $key => $item) {
            $item_list[$key]['url'] ="/govCard/?branch_id=".$item['_id'];
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['name']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
        }
           //var_dump($item_list[$key]['short_title']);
        return $item_list;
    }
    public function index() {
       
        $View = new Blitz('template/website-group.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

            if ($action == 'branch') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->branchList($parent_id, $limit, $offset, $length);
                }
				
				
				 if ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }
				
				$data['location'] = '<a href="/">网站首页</a> &gt; <a >站群导航</a>';
                $data[$struct_val] = $item_list;
            }
        }
        
        $View->display($data);
    }

}


?>

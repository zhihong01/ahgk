<?php

class jsiframe extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('content_model', 'content');
        $this->load->model('content_hot_model', 'content_hot');
        $this->load->model('content_video_model', 'content_video');
        $this->load->model("site_channel_tree_model", "site_channel_tree");
    }

    protected function itemList($channel_list = array(), $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
		 $this->load->model('content_model', 'content');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'linkurl', 'link_url');
	    $findarr = array(
		 'status' => true, 
		 'removed' => false,
		  'channel'=>$channel_list
		);
        $item_list = $this->content->find($findarr,$limit, $offset, $select, $arr_sort);
		
        foreach ($item_list as $key => $item) {
			$item_list[$key]['short_title'] = strip_tags(html_entity_decode($item_list[$key]['title']));
            $item_list[$key]['_id'] = (string) ($item['_id']);
           /*  $item_list[$key]['url'] = $this->folder_prefix . '/detail/' . $item['_id'] . '.html'; */
		   $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            }
			
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
		//print_r($item_list);die;

        return $item_list;
    }
	

    public function index() {
	
		$channel_id = (string) $this->input->get('channel');
		$limit=$this->input->get('count') ? $this->input->get('count') : 10;
		$length=$this->input->get('length') ? $this->input->get('length') : 20;
		$date_format=$this->input->get('date') ? $this->input->get('date') : 0;
		
		
		$View = new Blitz('template/jsiframe.html');
		
		
		$item_list = $this->itemList($channel_id, $limit, $offset, $length, $sort_by, $date_format);
		
		$data['list'] = $item_list;
        $View->display($data);
    }
	

}

?>
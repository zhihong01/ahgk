<?php

class provinceIframe extends MY_Controller {

    public function __construct() {
        parent::__construct();
      $this->load->model('content_model', 'content');
    }
	
	   // 普通新闻列表
    protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $this->load->model('content_model', 'content');

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'link_url');
        $filter = array('status' => True, 'removed' => false);//var_dump($_id_list);die();
        $item_list = $this->content->findList($_id_list, NULL, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['short_title'] = strip_tags(html_entity_decode($item_list[$key]['short_title']));

            $item_list[$key]['title'] = strip_tags(html_entity_decode($item_list[$key]['title']));
            if (mb_strlen($item['description']) > 52) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, 52) . '...';
            }
            if (mb_strlen($item['thumb_name']) == 20) {
                $item_list[$key]['thumb'] = "/data/upfile/" . substr($item['thumb_name'], 0, 1) . "/images/" . substr($item['thumb_name'], 2, 4) . "/" . $item['thumb_name'];
            } else {
                $item_list[$key]['thumb'] = $item['thumb_name'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }

  

    public function index() {
		$_id_list=array($this->input->get('_id') ? $this->input->get('_id') : 10);
		$limit=$this->input->get('count') ? $this->input->get('count') : 10;
		$length=$this->input->get('length') ? $this->input->get('length') : 20;
		$date_format=$this->input->get('date') ? $this->input->get('date') : 0;

        $item_list = array();

		$View = new Blitz('template/province-iframe.html');

        //$_id_list=array('557695ba7f8b9a7d507cf5af');
		$offset=6;
		$sort_by=0;

		$item_list = $this->contentList($_id_list, $limit, $offset, $length, $sort_by, $date_format);
		$data['list'] = $item_list;

        $View->display($data);
    }

}

?>
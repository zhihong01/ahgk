<?php

class tag extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('content_model', 'content');
    }

    protected function tagList($tag, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $arr_sort = array($this->sort_by[$sort_by] => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date');

        $item_list = $this->content->findTag($tag, array('status' => True, 'removed' => false), $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }
            $item_list[$key]['release_date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }

    protected function tagCount($tag) {

        $count = $this->content->tagCount($tag, array('status' => True, 'removed' => false));
        return $count;
    }

    protected function itemList($channel_list = array(), $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $arr_sort = array($this->sort_by[$sort_by] => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date');

        $item_list = $this->content->findList($channel_list, NULL, array('status' => True, 'removed' => false), NULL, NULL, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }
            $item_list[$key]['release_date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }

    public function index() {
        $tag = $this->input->get('tag');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }

        $total_row = $this->content->tagCount($tag);

        $View = new Blitz('template/tag.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];

                //列表
                if ($action == 'tag') {
                    list($limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->tagList($tag, $limit, $offset, $length, $sort_by, $date_format);
                    foreach ($item_list as $item) {
                        $View->block($struct, array('_id' => $item['_id'], 'url' => $this->folder_prefix . '/detail/' . $item['_id'] . '.html', 'title' => $item['title'], 'thumb' => $item['thumb_name'], 'description' => $item['description'], 'date' => $item['release_date']));
                    }
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count, false);
                    $View->block($struct, array('page' => $link));
                }

                //栏目列表
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $_id_list = explode('-', $channel_id);
                    $item_list = $this->itemList($_id_list, $limit, $offset, $length, $sort_by, $date_format);
                    foreach ($item_list as $item) {
                        $View->block($struct, array('_id' => $item['_id'], 'url' => $this->folder_prefix . '/detail/' . $item['_id'] . '.html', 'title' => $item['title'], 'thumb' => $item['thumb_name'], 'description' => $item['description'], 'date' => $item['release_date']));
                    }
                }
            }
        }

        $data = array(
            'tag' => $tag,
            'total_row' => $total_row,
        );

        $View->display($data);
    }

}

?>
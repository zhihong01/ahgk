<?php

class serviceSearch extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('service_content_model', 'service_content');
        $this->load->model('site_attach_model', 'service_download');
        $this->load->model('service_policy_model', 'service_policy');
    }

    protected function itemServiceContent($keywords = NULL, $filter = array(), $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0, $select = array()) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'confirm_date');

        $item_list = $this->service_content->findList($keywords, $filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['title'] = strip_tags(html_entity_decode($item_list[$key]['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['url'] = '/service/contentDetail/' . $item['_id'] . '.html';
            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }

        return $item_list;
    }

    protected function itemServicePolicy($keywords = NULL, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0, $select = array()) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'confirm_date');

        $item_list = $this->service_content->findList(NULL, $keywords, array('status' => true, 'removed' => false, 'site_id' => $this->site_id), $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['title'] = strip_tags(html_entity_decode($item_list[$key]['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['url'] = '/service/contentDetail/' . $item['_id'] . '.html';
            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }

        return $item_list;
    }

    protected function itemServiceDownload($keywords = NULL, $filter = array(), $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0, $select = array()) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'confirm_date','module_id','create_date');
        $item_list = $this->service_download->findList($keywords, $filter,NULL,NULL,$select, $limit, $offset,  $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['title'] = strip_tags(html_entity_decode($item_list[$key]['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            // $item_list[$key]['url'] = '/service/downloadDetail/' . $item['_id'] . '.html';
			 $item_list[$key]['url'] = '/download/?mod=site_attach&_id=' . $item['_id'];
			 // $item_list[$key]['url'] = 'http://hexian.u.my71.com/download/?_id=' . $item['_id'].'&SiteId=59313631ceab063f2f611981';
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
        }

        return $item_list;
    }

    public function index() {
        $keywords = $this->input->get('keywords');
        $field = (string) $this->input->get('field');
        $branch_id = (string) $this->input->get('branch');
        $type_id = (string) $this->input->get('type');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $filter['status'] = true;
        $filter['removed'] = false;
        $filter['site_id'] = $this->site_id;
        if (!empty($branch_id)) {
            $filter['branch_id'] = $branch_id;
        }
        if (!empty($type_id)) {
            if ($field == 'guide') {
                $filter['service_type'] = $type_id;
            } else {
                $filter['type'] = $type_id;
            }
        }

        if ($field == 'guide') {
            $total_row = $this->service_content->listCount($keywords, $filter);
        } elseif ($field == 'download') {
            $filter['module'] = "serviceDownload";
            $total_row = $this->service_download->listCount($keywords, $filter);
        }
        $View = new Blitz('template/service/service-search.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {

                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    // 办事指南
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    if ($field == 'guide') {
                        $item_list = $this->itemServiceContent($keywords, $filter, $limit, $offset, $length, $sort_by, $date_format);
                    } elseif ($field == 'download') {
                        $item_list = $this->itemServiceDownload($keywords, $filter, $limit, $offset, $length, $sort_by, $date_format);
                    }
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
                }
                $data[$struct_val] = $item_list;
            }
        }

        $data['keywords'] = $keywords;
        $data['total_row'] = $total_row;

        $View->display($data);
    }

}

?>
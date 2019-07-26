<?php

class content_error_report_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'content_error_report';
        $this->class_name = 'content_error_report_model';
        $this->table_field = array(
            'error_type' => '',
            'error_url' => '',
            'title' => '',
            'error_text' => '',
            'creator' => array('id' => '', 'name' => ''),
            'confirmer' => array('id' => '', 'name' => ''),
            'create_date' => 0,
            'confirm_date' => 0,
            'site_id' => '',
            'status' => false,
            'removed' => false,
            'client_ip' => '',
            'contact_info' => '',
            'view_list' => array()
        );
    }

    public function findList($keyword, $filter_list = null, $from_date = null, $to_date = null, $select = array(), $limit = 20, $offset = 0, $arr_sort = null) {

        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== "" && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('create_date', strtotime($from_date . " 00:00:00"));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime("+1 day", strtotime($to_date)));
        }

        if ($arr_sort != null && is_array($arr_sort)) {
            $this->mongo_db->order_by($arr_sort);
        } else {
            $this->mongo_db->order_by(array('_id' => 'DESC'));
        }

        $this->mongo_db->select($select);
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);

        $query = $this->mongo_db->get($this->table_name);

        return $query;
    }

    public function listCount($keyword, $filter_list = null, $from_date = null, $to_date = null) {
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== "" && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('create_date', strtotime($from_date . " 00:00:00"));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime("+1 day", strtotime($to_date)));
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }

    public function appendViewList($report_id, $name) {
        $this->mongo_db->where('_id', $this->SafeMongoId($report_id) );
        $this->mongo_db->addtoset('view_list', $name);
        return $this->mongo_db->update($this->table_name);
    }

}

?>

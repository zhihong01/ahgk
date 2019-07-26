<?php

class openness_request_desc_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'openness_request_desc';
        $this->class_name = 'openness_request_desc_model';
        $this->table_field = array(
            'title' => '',
            'body' => '',
            'description' => '',
            'author' => '',
            'copy_from' => '',
            
            'openness_date' => '',
            'branch_code' => '',
            'branch_id' => 0,
            'office_code' => 0,
            "request_type" =>array(),   // 1=>'申请条件说明' , 2=>'申请范围说明', 3=>'申请方式说明', 4=>'信函申请渠道', 5=>'受理部门信息', 6=>'政务服务中心受理点'  

            
            'confirm_date' => 0,
            'confirmer' => array('id' => '', 'name' => ''),
            'create_date' => 0,
            'creator' => array('id' => '', 'name' => ''),
            'site_id' => '',
            'sort' => 0,
            'status' => false,
            'removed' => false,
        );
    }

    public function findList($keyword, $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {

        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('create_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime('+1 day', strtotime($to_date)));
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

    public function listCount($keyword, $filter_list = array(), $from_date = null, $to_date = null) {
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('create_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime('+1 day', strtotime($to_date)));
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }

}

?>

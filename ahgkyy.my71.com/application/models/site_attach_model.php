<?php

class site_attach_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'site_attach';
        $this->class_name = 'site_attach_model';
        $this->table_field = array(
            'module' => '',
            'site_id' => '',
            'branch_id' => '',
            'module_id' => '',
            
            'create_date' => 0,
            'creator' => array('id' => '', 'name' => ''),
            'confirm_date' => 0,
            'confirmer' => array('id' => '','name' => ''),
            'removed' => false,
            "status" =>false,
            "sort" => 0,

            'title' => '',
            "release_date" => 0,
            "attach_type" => array(),
            'is_image' => false,
            'media_path' => "",
            
            'downloads' => 0,
            'file_size' => 0,
            'file_type' => '',
            'picture_l' => '',
            'picture_m' => '',
            'picture_s' => '',
            'picture_xl' => '',
            'picture_xxl' => '',
            'real_name' => '',
            'saved_name' => '',

        );
    }

    public function findList($keyword, $filter_list = null, $from_date = null, $to_date = null, $select = array(), $limit = 20, $offset = 0, $arr_sort = null) {

        if (strlen($keyword) > 1) {
            $this->mongo_db->or_like('real_name', array($keyword));
            $this->mongo_db->or_like('title', array($keyword));
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
//print_r($this->mongo_db->last_query());

        return $query;
    }

    public function listCount($keyword, $filter_list = null, $from_date = null, $to_date = null) {
        if (strlen($keyword) > 1) {
            $this->mongo_db->or_like('real_name', array($keyword));
            $this->mongo_db->or_like('title', array($keyword));
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

    public function findListType($keyword, $filter_list = array(), $tids=array(), $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {

        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($tids&&(count($tids)>0)) {
            $this->mongo_db->where_in('attach_type', $tids);
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
//echo $this->mongo_db->last_query();
        return $query;
    }

    public function listCountType($keyword, $filter_list = array(), $tids=array()) {
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($tids&&(count($tids)>0)) {
            $this->mongo_db->where_in('attach_type', $tids);
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }


    public function getListInfo($_id_list = array(), $select = array()) {
        $_id_list = array_unique($_id_list);
        foreach ($_id_list as $key => $value) {
            $_id_list[$key] = $this->SafeMongoId($value);
        }

        $this->mongo_db->select($select);
        $this->mongo_db->where_in('_id', $_id_list);

        $result = $this->mongo_db->get($this->table_name);
        $nickname_list = array();
        $count = count($result);

        for ($i = 0; $i < $count; $i++) {
            $nickname_list[(string) $result[$i]['_id']] = $result[$i];
        }
        return $nickname_list;
    }


}

?>

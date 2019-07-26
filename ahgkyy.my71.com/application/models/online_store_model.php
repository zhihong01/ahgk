<?php

class online_store_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'online_store';
        $this->class_name = 'online_store_model';
        $this->table_field = array(
              "site_id"  =>  "",
              "branch_id"  =>  "",
              "folder_id"  =>  "",
              "real_name"  =>  "",
              "file_type"  =>  "",
              "file_size"  =>  "",
              "name"  =>  "",
              "large_name"  =>  "",
              "medium_name"  =>  "",
              "mini_name"  =>  "",
              "small_name"  =>  "",
              "xlarge_name"  =>  "",
              "create_date"  =>  0,
              'creator' => array('id' => '', 'name' => '')
            );
    }

    public function findList($keyword, $branch_id, $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select =array(), $arr_sort = null) {


        if (strlen($keyword) > 1) {
            $this->mongo_db->like('real_name', $keyword);
        }
        $this->mongo_db->where("branch_id", $branch_id);

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

    public function listCount($keyword, $branch_id, $filter_list = array(), $from_date = null, $to_date = null) {

        if (strlen($keyword) > 1) {
            $this->mongo_db->like('real_name', $keyword);
        }
        $this->mongo_db->where("branch_id", $branch_id);

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

    public function findExists($oldId=null, $filter_list = array()) {

        foreach ($filter_list as $key => $filter) {
            if ($filter !== "" && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if (!empty($oldId) && !($oldId instanceof MongoId)) {
            $oldId = new MongoId($oldId);
        }

        $this->mongo_db->where_ne('_id', $oldId);

        $this->mongo_db->limit(1);
        $query = $this->mongo_db->get($this->table_name);

        return $query;
    }
}

?>

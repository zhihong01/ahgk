<?php

class content_author_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'content_author';
        $this->class_name = 'content_author_model';
    }

    public function findList($keyword, $filter_list = array(), $limit = 20, $offset = 0, $select = array(), $arr_sort = array()) {
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== "" && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
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

    public function listCount($keyword, $branchid = array(), $filter_list = array()) {
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== "" && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }

}

?>

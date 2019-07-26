<?php

class openness_annual_report_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'openness_annual_report';
        $this->class_name = 'openness_annual_report_model';
    }

    public function findList($branch_id = null, $filter_list = array(), $keyword = '', $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {

        if ($branch_id !== null) {
            if (is_array($branch_id)) {
                $this->mongo_db->where_in('branch_id', $branch_id);
            } else {
                $this->mongo_db->where('branch_id', $branch_id);
            }
        }

        if (count($filter_list) > 0) {
            foreach ($filter_list as $key => $filter) {
                if (is_array($filter)) {
                    $this->mongo_db->where_in('key', $filter);
                } elseif ($filter !== '' && $filter !== NULL) {
                    $this->mongo_db->where($key, $filter);
                }
            }
        }

        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
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

    public function listCount($branch_id = null, $filter_list = array(), $keyword = '', $from_date = null, $to_date = null) {

        if ($branch_id !== null) {
            if (is_array($branch_id)) {
                $this->mongo_db->where_in('branch_id', $branch_id);
            } else {
                $this->mongo_db->where('branch_id', $branch_id);
            }
        }

        if (count($filter_list) > 0) {
            foreach ($filter_list as $key => $filter) {
                if (is_array($filter)) {
                    $this->mongo_db->where_in('key', $filter);
                } elseif ($filter !== '' && $filter !== NULL) {
                    $this->mongo_db->where($key, $filter);
                }
            }
        }

        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
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

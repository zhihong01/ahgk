<?php

class site_office_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'site_office';
        $this->class_name = 'site_office_model';
        $this->table_field = array(
            'branch_id' => '',
            'confirmer' => array('id' => '', 'name' => ''),
            'create_date' => 0,
            'creator' => array('id' => '', 'name' => ''),
            'name' => '',
            'office_code' => 0,
            'phone' => '',
            'remark' => '',
            'site_id' => '',
            'sort' => 2000,
            'removed' => false,
        );
    }

    public function findList($keyword, $filter_list = null, $select = null, $limit = 20, $offset = 0, $arr_sort = null) {
        $table_name = $this->db->dbprefix . $this->table_name;
        if (strlen($keyword) > 0) {
            $keyword = trim($this->db->escape_like_str($keyword));
            $this->db->where("(" . $table_name . ".name LIKE '%" . $keyword . "%')");
        }

        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (is_array($filter)) {
                    $this->db->where_in($table_name . "." . $key, $filter);
                } else if (trim($filter) != "") {
                    $this->db->where($table_name . "." . $key, $filter);
                }
            }
        }
        if ($select) {
            $this->db->select($select);
        } else {
            $this->db->select("* ");
        }

        if ($arr_sort != null && is_array($arr_sort)) {
            foreach ($arr_sort as $field => $order) {
                $this->db->order_by($field, $order);
            }
        } else {
            $this->db->order_by($table_name . ".id", "DESC");
        }

        $query = $this->db->get($this->table_name, $limit, $offset);
//echo $this->db->last_query();

        $result = $query->result_array();

        return $result;
    }

    public function listCount($keyword, $filter_list = null) {
        $table_name = $this->db->dbprefix . $this->table_name;
        if (strlen($keyword) > 0) {
            $keyword = trim($this->db->escape_like_str($keyword));
            $this->db->where("(" . $table_name . ".name LIKE '%" . $keyword . "%')");
        }

        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (is_array($filter)) {
                    $this->db->where_in($table_name . "." . $key, $filter);
                } else if (trim($filter) != "") {
                    $this->db->where($table_name . "." . $key, $filter);
                }
            }
        }

        $this->db->select("COUNT(*) AS counts");
        $query = $this->db->get($this->table_name);
        $result = $query->row_array();

        return (int) $result["counts"];
    }

}

?>

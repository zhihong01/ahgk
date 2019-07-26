<?php

class content_custom_model extends MY_Model {

    function __construct() {
        parent::__construct();        

        $this->table_name = 'content_custom';
        $this->class_name = 'content_custom_model';
    }

    public function findList($keyword,$filter_list = null, $select=null, $limit = 20, $offset = 0, $arr_sort = null) {

        $table_name = $this->db->dbprefix . $this->table_name;
        if (strlen($keyword) > 0) {
            $keyword = trim($this->db->escape_like_str($keyword));
            $this->db->where("(" . $table_name . ".name LIKE '%" . $keyword  . "%')");
        }

        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (is_array($filter)) {
                    $this->db->where_in($table_name . "." . $key, $filter);
                }else if (trim($filter) != "") {
                    $this->db->where($table_name . "." . $key, $filter);
                }
            }
        }

        if($select){
            $this->db->select($select);
        } else {
            $this->db->select($this->table_name.".*, content_channel.name as content_channel_name");
        }

        if ($arr_sort != null && is_array($arr_sort)) {
            foreach ($arr_sort as $field => $order) {
                $this->db->order_by($field, $order);
            }
        } else {
            $this->db->order_by($table_name . ".id", "DESC");
        }

        $this->db->join("content_channel AS content_channel", "content_channel.id=" . $this->table_name . ".channel_id", "left");

        $query = $this->db->get($this->table_name, $limit, $offset);
//echo $this->db->last_query();

        $result = $query->result_array();

        return $result;
    }

    public function listCount($keyword,$filter_list = null ) {
        $table_name = $this->db->dbprefix . $this->table_name;
        if (strlen($keyword) > 0) {
            $keyword = trim($this->db->escape_like_str($keyword));
            $this->db->where("(" . $table_name . ".name LIKE '%" . $keyword  . "%')");
        }


        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (is_array($filter)) {
                    $this->db->where_in($table_name . "." . $key, $filter);
                }else if (trim($filter) != "") {
                    $this->db->where($table_name . "." . $key, $filter);
                }
            }
        }

        $this->db->select("COUNT(*) AS counts");
        $this->db->join("content_channel AS content_channel", "content_channel.id=" . $this->table_name . ".channel_id", "left");
        $query = $this->db->get($this->table_name);
        $result = $query->row_array();

        return (int) $result["counts"];
    }

    function getList($channelId,$filter_list = null, $select=null, $limit = 20, $offset = 0, $arr_sort = null){

        $this->db->where("channel_id", $channelId);
        $this->db->where("issys!=",2, false);

        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (trim($filter) != "") {
                    $this->db->where($key, $filter);
                }
            }
        }

        if($select){
            $this->db->select($select);
        } else {
            $this->db->select("* ");
        }

        if ($arr_sort != null && is_array($arr_sort)) {
            foreach ($arr_sort as $field => $order) {
                $this->db->order_by($field, $order);
            }
        } else {
            $this->db->order_by( "id", "DESC");
        }

        $query = $this->db->get($this->table_name, $limit, $offset);
//echo $this->db->last_query();

        $result = $query->result_array();

        return $result;
    }

    public function plusCounter($id, $number = 1) {
        $this->db->where("id", $id);
        $this->db->set('sort', "sort+" . $number, FALSE);

        if ($this->db->update($this->table_name)) {
            return true;
        } else {
            return false;
        }
    }


    public function minusCounter($id, $number = 1) {
        $this->db->where("id", $id);
        $this->db->set('sort', "sort-" . $number, FALSE);

        if ($this->db->update($this->table_name)) {
            return true;
        } else {
            return false;
        }
    }

}

?>

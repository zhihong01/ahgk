<?php

class sms_log_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'sms_log';
        $this->class_name = 'sms_log_model';
    }

    public function findList($user_id, $keyword, $from_date = null, $to_date = null, $limit = 20, $offset = 0, $ar_sort = null) {

        $this->db->select($this->table_name . ".*, sms.phone AS phone, sms.id AS sms_id ");

        $keyword = trim($keyword);
        if (strlen($keyword) > 2) {
            $this->db->like($this->table_name . ".result_message", $keyword);
        }

        if (is_array($user_id)) {
            $this->db->where_in($this->table_name . ".user_id", $user_id);
        } else {
            $this->db->where($this->table_name . ".user_id", $user_id);
        }

        if (($from_date) && ($to_date)) {
            if (strtotime($from_date) > strtotime($to_date)) {
                list($from_date, $to_date) = array($to_date, $from_date);
            }
        }
        if ($from_date) {
            $this->db->where('sms.create_date > ', date('Y-m-d', strtotime($from_date)));
        }

        if ($to_date) {
            $this->db->where('sms.create_date < ', date('Y-m-d', strtotime('+1 day', strtotime($to_date))));
        }

        if ($ar_sort != null) {
            if (is_array($ar_sort)) {
                foreach ($ar_sort as $field => $order) {
                    $this->db->order_by($field, $order);
                }
            }
        } else {
            $this->db->order_by($this->table_name . ".id", "DESC");
        }

        $this->db->join("sms", "sms.id=" . $this->table_name . ".sms_id", "left");

        $query = $this->db->get($this->table_name, $limit, $offset);
        $result = $query->result_array();

        return $result;
    }

    public function listCount($user_id, $keyword, $from_date = null, $to_date = null) {
        $keyword = trim($keyword);
        if (strlen($keyword) > 2) {
            $this->db->like("result_message", $keyword);
        }

        if (($from_date) && ($to_date)) {
            if (strtotime($from_date) > strtotime($to_date)) {
                list($from_date, $to_date) = array($to_date, $from_date);
            }
        }

        if ($from_date) {
            $this->db->where('sms.create_date > ', date('Y-m-d', strtotime($from_date)));
        }

        if ($to_date) {
            $this->db->where('sms.create_date < ', date('Y-m-d', strtotime('+1 day', strtotime($to_date))));
        }

        if (is_array($user_id)) {
            $this->db->where_in($this->table_name . ".user_id", $user_id);
        } else {
            $this->db->where($this->table_name . ".user_id", $user_id);
        }

        $this->db->select("COUNT(*) AS counts");
        $this->db->join("sms", "sms.id=" . $this->table_name . ".sms_id", "left");
        $query = $this->db->get($this->table_name);
        $result = $query->row_array();

        return (int) $result["counts"];
    }

}

?>
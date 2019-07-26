<?php

class sms_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'sms';
        $this->class_name = 'sms_model';

        $this->table_field = array(
            "user_id" => "",
            "task_id" => '',
            "task_name" => "",
            "phone" => "",
            "message" => "",
            "create_date" => "",
            'creator' => array('id' => '', 'name' => '系统管理员'),
            "scheduled_date" => "",
            "status" => false,
            "sent_date" => ""
        );
    }

    public function findList($keyword, $access_users = null, $status = null, $from_date = null, $to_date = null, $limit = 20, $offset = 0, $arr_sort = null) {
        if (strlen($keyword) > 1) {
            $this->mongo_db->like("message" , $keyword);
        }

        if ($access_users != null ) {
            $this->mongo_db->where_in('user_id', (array)$access_users);
        } else {
            return array();
        }

        if ($status && is_array($status)) {
           $this->mongo_db->where_in("status", $status);
        } else if ($status && !is_array($status)) {
           $this->mongo_db->where("status", (int)$status);
        }

        if ($from_date) {
            $this->mongo_db->where_gt('scheduled_date', strtotime($from_date) );
        }

        if ($to_date) {
            $this->mongo_db->where_lt('scheduled_date', strtotime('+1 day', strtotime($to_date)));
        }


        if ($arr_sort !== null && is_array($arr_sort)) {
            $this->mongo_db->order_by($arr_sort);
        } else {
            $this->mongo_db->order_by(array('_id' => 'DESC'));
        }

        $this->mongo_db->select("*");
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);

        $result = $this->mongo_db->get($this->table_name);
//print_r($this->mongo_db->last_query());
        return $result;
    }

    public function listCount($keyword, $access_users = null, $status = null, $from_date = null, $to_date = null) {
        if (strlen($keyword) > 1) {
            $this->mongo_db->like("message" , $keyword);
        }

        if ($access_users != null ) {
            $this->mongo_db->where_in('user_id', (array)$access_users);
        } else {
            return array();
        }

        if ($status && is_array($status)) {
           $this->mongo_db->where_in("status", $status);
        } else if ($status && !is_array($status)) {
           $this->mongo_db->where("status", (int)$status);
        }

        if ($from_date) {
            $this->mongo_db->where_gt('scheduled_date', strtotime($from_date) );
        }

        if ($to_date) {
            $this->mongo_db->where_lt('scheduled_date', strtotime('+1 day', strtotime($to_date)));
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }

    /************************ 新加的 high chart 报表所需的 MYSQL函数 ****************************/
    public function get_min_sent() {
        $this->db->select_min('sent_date', 'min');

        $query = $this->db->get($this->table_name, 1);
        $min = $query->row_array();

        return $min['min'];
    }

    public function getSms($keyword, $access_users, $filter_list = null, $from_date = null, $to_date = null, $limit = 20, $offset = 0, $arr_sort = null) {

        $table_name = $this->db->dbprefix . $this->table_name;
        if ($keyword) {
            $keyword = trim($this->db->escape_like_str($keyword));
            $this->db->where("(" . $table_name . ".id LIKE '%" . $keyword . "%' OR " . $table_name . ".phone LIKE '%" . $keyword . "%' OR " . $table_name . ".message LIKE '%" . $keyword . "%' OR sms_task.name LIKE '%" . $keyword  . "%')");
        }

        if ($access_users != null && is_array($access_users)) {
            $this->db->where_in($table_name . ".user_id", $access_users);
        } else {
            return null;
        }

        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (trim($filter) != "") {
                    $this->db->where($table_name . "." . $key, $filter);
                }
            }
        }
        
        if (($from_date) && ($to_date)) {
            if (strtotime($from_date) > strtotime($to_date)) {
                list($from_date, $to_date) = array($to_date, $from_date);
            }
        }

        if ($from_date) {
            $this->db->where($table_name . '.sent_date > ', date('Y-m-d', strtotime($from_date)));
        }

        if ($to_date) {
            $this->db->where($table_name . '.sent_date < ', date('Y-m-d', strtotime('+1 day', strtotime($to_date))));
        }

        if ($arr_sort != null && is_array($arr_sort)) {
            foreach ($arr_sort as $field => $order) {
                $this->db->order_by($field, $order);
            }
        } else {
            $this->db->order_by($table_name . ".sent_date", "DESC");
        }

        $this->db->select($table_name . ".*, sms_task.name AS task_name, user.name AS user_name");

        $this->db->join("sms_task AS sms_task", "sms_task.id=" . $table_name . ".task_id", "left");
        $this->db->join("user AS user", "user.id=" . $table_name . ".user_id", "left");

        $query = $this->db->get($this->table_name, $limit, $offset);
//echo $this->db->last_query();

        $result = $query->result_array();
        return $result;
    }

    public function hourReportInterval($key_word, $access_users = null, $filter_list = null, $from_date = null, $to_date = null) {
        $table_name = $this->db->dbprefix . $this->table_name;

        $this->db->select("DATE(sent_date) AS report_day, HOUR(sent_date) AS report_hour, count(*) AS total");
        if ($key_word) {
            $keyword = trim($this->db->escape_like_str($keyword));
            $this->db->where("(" . $table_name . ".id LIKE '%" . $keyword . "%' OR " . $table_name . ".phone LIKE '%" . $keyword . "%' OR " . $table_name . ".message LIKE '%" . $keyword . "%' OR sms_task.name LIKE '%" . $keyword  . "%')");
        }

        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (trim($filter) != "") {
                    $this->db->where($table_name . "." . $key, $filter);
                }
            }
        }

        if (($from_date) && ($to_date)) {
            if ($from_date > $to_date) {
                list($from_date, $to_date) = array($to_date, $from_date);
            }
        }


        if (!empty($from_date)) {
            $this->db->where('sent_date > ', date('Y-m-d', strtotime($from_date)));
        }
        if (!empty($to_date)) {
            $this->db->where('sent_date < ', date('Y-m-d', strtotime('+1 day', strtotime($to_date))));
        }

        if ($access_users != null && is_array($access_users)) {
            $this->db->where_in("user_id", $access_users);
        } else {
            return null;
        }

        $this->db->group_by(array("report_day", "report_hour"));
        $this->db->order_by("report_day", "DESC");
        $this->db->order_by("report_hour", "DESC");

        $query = $this->db->get($this->table_name);

        return $query->result_array();
    }

    public function dailyReportInterval($key_word, $access_users = null, $filter_list = null, $from_date = null, $to_date = null) {
        $table_name = $this->db->dbprefix . $this->table_name;

        $this->db->select("DATE(sent_date) AS report_day, count(*) AS total");
        if ($key_word) {
            $keyword = trim($this->db->escape_like_str($keyword));
            $this->db->where("(" . $table_name . ".id LIKE '%" . $keyword . "%' OR " . $table_name . ".phone LIKE '%" . $keyword . "%' OR " . $table_name . ".message LIKE '%" . $keyword . "%' OR sms_task.name LIKE '%" . $keyword  . "%')");
        }

        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (trim($filter) != "") {
                    $this->db->where($table_name . "." . $key, $filter);
                }
            }
        }

        if (($from_date) && ($to_date)) {
            if ($from_date > $to_date) {
                list($from_date, $to_date) = array($to_date, $from_date);
            }
        }


        if (!empty($from_date)) {
            $this->db->where('sent_date > ', date('Y-m-d', strtotime($from_date)));
        }
        if (!empty($to_date)) {
            $this->db->where('sent_date < ', date('Y-m-d', strtotime('+1 day', strtotime($to_date))));
        }

        if ($access_users != null && is_array($access_users)) {
            $this->db->where_in("user_id", $access_users);
        } else {
            return null;
        }

        $this->db->group_by("report_day");
        $this->db->order_by("report_day", "DESC");

        $query = $this->db->get($this->table_name);

        return $query->result_array();
    }

    public function monthReportInterval($key_word, $access_users = null, $filter_list = null, $from_date = null, $to_date = null) {
        $table_name = $this->db->dbprefix . $this->table_name;

        $this->db->select(" YEAR(sent_date) AS report_year, MONTH(sent_date) AS report_month, count(*) AS total");
        if ($key_word) {
            $keyword = trim($this->db->escape_like_str($keyword));
            $this->db->where("(" . $table_name . ".id LIKE '%" . $keyword . "%' OR " . $table_name . ".phone LIKE '%" . $keyword . "%' OR " . $table_name . ".message LIKE '%" . $keyword . "%' OR sms_task.name LIKE '%" . $keyword  . "%')");
        }

        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (trim($filter) != "") {
                    $this->db->where($table_name . "." . $key, $filter);
                }
            }
        }

        if (($from_date) && ($to_date)) {
            if ($from_date > $to_date) {
                list($from_date, $to_date) = array($to_date, $from_date);
            }
        }


        if (!empty($from_date)) {
            $this->db->where('sent_date > ', date('Y-m-d', strtotime($from_date)));
        }
        if (!empty($to_date)) {
            $this->db->where('sent_date < ', date('Y-m-d', strtotime('+1 day', strtotime($to_date))));
        }

        if ($access_users != null && is_array($access_users)) {
            $this->db->where_in("user_id", $access_users);
        } else {
            return null;
        }

        $this->db->group_by(array("report_year", "report_month"));
        $this->db->order_by("report_year", "DESC");
        $this->db->order_by("report_month", "DESC");

        $query = $this->db->get($this->table_name);

        return $query->result_array();
    }

    public function yearReportInterval($key_word, $access_users = null, $filter_list = null, $from_date = null, $to_date = null) {
        $table_name = $this->db->dbprefix . $this->table_name;

        $this->db->select(" YEAR(sent_date) AS report_year, count(*) AS total");
        if ($key_word) {
            $keyword = trim($this->db->escape_like_str($keyword));
            $this->db->where("(" . $table_name . ".id LIKE '%" . $keyword . "%' OR " . $table_name . ".phone LIKE '%" . $keyword . "%' OR " . $table_name . ".message LIKE '%" . $keyword . "%' OR sms_task.name LIKE '%" . $keyword  . "%')");
        }

        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (trim($filter) != "") {
                    $this->db->where($table_name . "." . $key, $filter);
                }
            }
        }

        if (($from_date) && ($to_date)) {
            if ($from_date > $to_date) {
                list($from_date, $to_date) = array($to_date, $from_date);
            }
        }


        if (!empty($from_date)) {
            $this->db->where('sent_date > ', date('Y-m-d', strtotime($from_date)));
        }
        if (!empty($to_date)) {
            $this->db->where('sent_date < ', date('Y-m-d', strtotime('+1 day', strtotime($to_date))));
        }

        if ($access_users != null && is_array($access_users)) {
            $this->db->where_in("user_id", $access_users);
        } else {
            return null;
        }

        $this->db->group_by("report_year");
        $this->db->order_by("report_year", "DESC");

        $query = $this->db->get($this->table_name);

        return $query->result_array();
    }


}

?>
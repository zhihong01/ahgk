<?php

class forum_riches_model extends MY_Model {

    function __construct() {
        parent::__construct();        

        $this->table_name = 'forum_riches';
        $this->class_name = 'forum_riches_model';
    }

    
    public function findList($keyword, $filter_list = null, $from_date = null, $to_date = null, $limit = 20, $offset = 0, $arr_sort = null) {
        $table_name = $this->db->dbprefix . $this->table_name;
        $this->db->select($table_name . '.*, ch.name AS channel_name');

        if (strlen($keyword) > 1) {
            $keyword = trim($this->db->escape_like_str($keyword));
            $this->db->where("(" . $table_name . ".title LIKE '%" . $keyword . "%' OR " . $table_name . ".description LIKE '%" . $keyword . "%')");
        }

        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (is_array($filter)) {
                    $this->db->where_in($table_name . "." . $key, $filter);
                } else if ($filter !== "" && $filter !== NULL) {
                    $this->db->where($table_name . "." . $key, $filter);
                }
            }
        }

        if ($from_date) {
            $this->db->where("(" . $table_name . ".create_date > " . strtotime($from_date . " 00:00:00") . ")", NULL, FALSE);
        }

        if ($to_date) {
            $this->db->where("(" . $table_name . ".create_date < " . strtotime("+1 day", strtotime($to_date)) . ")", NULL, FALSE);
        }

        if ($arr_sort != null && is_array($arr_sort)) {
            foreach ($arr_sort as $field => $order) {
                $this->db->order_by($field, $order);
            }
        } else {
            $this->db->order_by($table_name . ".id", "DESC");
        }

        $this->db->join("site_channel AS ch", "ch.id=" . $this->table_name . ".channel_id", "left");
        $query = $this->db->get($this->table_name, $limit, $offset);
        $result = $query->result_array();

        return $result;
    }

    public function listCount($keyword, $filter_list = null, $from_date = null, $to_date = null) {
        $table_name = $this->db->dbprefix . $this->table_name;
        if (strlen($keyword) > 1) {
            $keyword = trim($this->db->escape_like_str($keyword));
            $this->db->where("(" . $table_name . ".title LIKE '%" . $keyword . "%' OR " . $table_name . ".description LIKE '%" . $keyword . "%')");
        }

        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (is_array($filter)) {
                    $this->db->where_in($table_name . "." . $key, $filter);
                } else if ($filter !== "" && $filter !== NULL) {
                    $this->db->where($table_name . "." . $key, $filter);
                }
            }
        }

        if ($from_date) {
            $this->db->where("(" . $table_name . ".create_date > " . strtotime($from_date . " 00:00:00") . ")", NULL, FALSE);
        }

        if ($to_date) {
            $this->db->where("(" . $table_name . ".create_date < " . strtotime("+1 day", strtotime($to_date)) . ")", NULL, FALSE);
        }

        $this->db->select("COUNT(*) AS counts");

        $query = $this->db->get($this->table_name);
        $result = $query->row_array();

        return (int) $result["counts"];
    }

    public function undoHome($id) {
        if (is_array($id))
            $this->db->where_in("id", $id);
        else
            $this->db->where("id", $id);
        $this->db->set('ishome', "1-ishome", FALSE);

        if ($this->db->update($this->table_name)) {
            return true;
        } else {
            return false;
        }
    }
    
    
}

?>

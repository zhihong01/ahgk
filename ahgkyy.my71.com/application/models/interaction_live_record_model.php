<?php

class interaction_live_record_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'interaction_live_record';
        $this->class_name = 'interaction_live_record_model';
        $this->table_field = array(
            'client_ip' => '',
            'confirmer' => array('id' => '', 'name' => '主持人'),
            'content' => '',
            'create_date' => 0,
            'creator' => array('id' => '', 'name' => '主持人'),
            'email' => '',
            'live_id' => '',
            'phone' => '',
            'release_date' => 0,
            'reply' => '',
            'reply_name' => '',
            'reply_open' => False,
            'reply_date' => 0,
            'site_id' => '',
            'status' => False,
            'removed' => false,
            'sort' => 0,
            'isweb' => 0,
            'no' => 0,
            'reply_thumb_name' => '',
        );
    }

    public function findList($live_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {

        if (count($live_id) > 0) {
            $this->mongo_db->where_in_all('live_id', $live_id);
        }
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('release_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('release_date', strtotime('+1 day', strtotime($to_date)));
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

    public function listCount($live_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null) {
        if (count($live_id) > 0) {
            $this->mongo_db->where_in_all('live_id', $live_id);
        }
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('release_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('release_date', strtotime('+1 day', strtotime($to_date)));
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }

}

?>

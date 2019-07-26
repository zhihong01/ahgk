<?php

class forum_thread_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'forum_thread';
        $this->class_name = 'forum_thread_model';
        $this->table_field = array(
            'branchid' => '',
            'body' => '',
            'client_ip' => '',
            'close_remark' => '',
            'closed' => false,
            'confirm_date' => 0,
            'confirmer' => array(
                'id' => '',
                'name' => ''
            ),
            'create_date' => 0,
            'creator' => array(
                'id' => '',
                'name' => ''
            ),
            'edit_remark' => '',
            'group_id' => 0,
            'forum_group_id' =>0,
            'has_attach' => false,
            'icon_id' => 1,
            'last_date' => 0,
            'last_info' => array(
                'id' => '',
                'name' => '',
                'create_date' => 0
            ),
            'rating' => 0,
            'removed' => false,
            'replies' => 0,
            'set_channel_top' => false,
            'set_good' => false,
            'set_lock' => false,
            'set_top' => false,
            'site_id' => '',
            'status' => false,
            'title' => '',
            'update_date' => 0,
            'view_list' => array(),
            'views' => 0,
            'votes' => 0
        );
    }

    public function findList($keyword, $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {

        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
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

    public function listCount($keyword, $filter_list = array(), $from_date = null, $to_date = null) {
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
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

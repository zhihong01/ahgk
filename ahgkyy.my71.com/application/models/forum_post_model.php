<?php

class forum_post_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'forum_post';
        $this->class_name = 'forum_post_model';
        $this->table_field = array(
            'body' => '',
            'client_ip' => '',
            'thread_id'=>'',
            'thread_old_id'=>0,
            'close_remark' => '',
            'confirm_date' => 0,
            'confirmer' => array(
                'id' => '5174f50016661e0f481040e6',
                'name' => '管理员'
            ),
            'create_date' => 0,
            'creator' => array(
                'id' => '5174f50016661e0f481040e6',
                'name' => '管理员'
            ),
            'edit_remark' => '',
            'group_id' => 60,
            'has_attach' => 0,
            'rating' => 0,
            'set_lock' => 1,
            'site_id' => '517509ed16661e0f481f8144',
            'status' => false,
            'title' => '',
            'update_date' => 0,
            'view_list' => array(),
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

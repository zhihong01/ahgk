<?php

class vod_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'vod';
        $this->class_name = 'vod_model';

        $this->table_field = array(
            'bit_rate' => '',
            'channel' => array(''),
            'create_date' => 1225337696,
            'creator' => array('id' => '', 'name' => ''),
            'description' => '',
            'duration' => 0,
            'media_path' => '',
            'name' => '',
            'release_date' => 0,
            'removed' => false,
            'saved_name' => '',
            'server_id' => 0,
            'site_id' => '',
            'sort' => 20000,
            'status' => 0,
            'thumb_name' => '',
            'thumb_large' => '',
            'title' => '',
            'type_name' => 'FLV',
            'update_date' => 0,
            'visits' => ''			
        );
    }

    public function findList($channel_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {
        if (count($channel_id) > 0) {
            $this->mongo_db->where_in('channel', $channel_id);
        }

        if (strlen(trim($keyword)) > 1) {
            $this->mongo_db->like('title', trim($keyword));
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

        return $query;
    }

    public function listCount($channel_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null) {

        if (count($channel_id) > 0) {
            $this->mongo_db->where_in('channel', $channel_id);
        }

        if (strlen(trim($keyword)) > 1) {
            $this->mongo_db->like('title', trim($keyword));
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
	
	 public function next($channel_id = array(), $release_date = 0) {

        if (count($channel_id) > 0) {
            $this->mongo_db->where_in('channel', $channel_id);
        }
		$this->mongo_db->where(array('status' => true, 'removed' => False));
        if ($release_date) {
            $this->mongo_db->where_gt('release_date', $release_date);
        }
        //$this->mongo_db->order_by(array('release_date' => 'DESC'));
        $this->mongo_db->order_by(array('release_date' => 'ASC'));

        $this->mongo_db->select(array('_id', 'title', 'thumb_name'));
        $this->mongo_db->limit(1);
        $this->mongo_db->offset(0);

        $query = $this->mongo_db->get($this->table_name);

        if ($query) {
            return $query[0];
        } else {
            return NULL;
        }
    }

    public function prev($channel_id = array(), $release_date = 0) {

        if (count($channel_id) > 0) {
            $this->mongo_db->where_in('channel', $channel_id);
        }
        $this->mongo_db->where(array('status' => true, 'removed' => False));
        if ($release_date) {
            $this->mongo_db->where_lt('release_date', $release_date);
        }
		
        $this->mongo_db->order_by(array('release_date' => 'DESC'));
        //$this->mongo_db->order_by(array('release_date' => 'ASC'));
        $this->mongo_db->select(array('_id', 'title', 'thumb_name'));
        $this->mongo_db->limit(1);
        $this->mongo_db->offset(0);

        $query = $this->mongo_db->get($this->table_name);

        if ($query) {
            return $query[0];
        } else {
            return NULL;
        }
    }

}

?>
<?php

class content_hot_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'content_hot';
        $this->class_name = 'content_hot_model';
    }

    public function findList($channel_id = array(), $filter_list = array(), $limit = 20, $offset = 0, $select = array()) {
        if (count($channel_id) > 0) {
            $this->mongo_db->where_in_all('channel', $channel_id);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        $this->mongo_db->order_by(array('sort' => 'DESC'));

        $this->mongo_db->select($select);
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);

        $query = $this->mongo_db->get($this->table_name);

        return $query;
    }

    public function listCount($channel_id = array(), $filter_list = array()) {
        if (count($channel_id) > 0) {
            $this->mongo_db->where_in_all('channel', $channel_id);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }


        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }

}

?>

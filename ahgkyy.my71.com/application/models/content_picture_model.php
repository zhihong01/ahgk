<?php

class content_picture_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'content_picture';
        $this->class_name = 'content_picture_model';
        $this->table_field = array(
            'content_id' => '',
            'description' => '',
            'file_size' => 0,
            'small_thumb' => '',
            'medium_thumb' => '',
            'large_thumb' => '',
            'xlarge_thumb' => '',
            'xxlarge_thumb' => '',
            'real_name' => '',
            'saved_name' => '',
            'status' => 1,
            'sort' => 2000
        );
    }

    public function findList($content_id = array(), $filter_list = array(), $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {

        if (count($content_id) > 0) {
            $this->mongo_db->where_in_all('content_id', $content_id);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
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

    public function listCount($content_id = array(), $filter_list = array()) {
        if (count($content_id) > 0) {
            $this->mongo_db->where_in_all('content_id', $content_id);
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

<?php

class vod_channel_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'vod_channel';
        $this->class_name = 'vod_channel_model';

        $this->table_field = array(
            'folder_name' => '',
            'name' => '',
            'parent_id' => '',
            'removed' => false,
            'site_id' => '',
            'sort' => 2000,
            'status' => 0,
        );
    }

    public function getVodCategoryInfo($_id_list = array(), $select = array()) {
        $_id_list = array_unique(array_filter($_id_list));
        foreach ($_id_list as $key => $value) {
            $_id_list[$key] = new MongoId($value);
        }

        $this->mongo_db->select($select);
        $this->mongo_db->where_in('_id', $_id_list);

        $result = $this->mongo_db->get($this->table_name);
        $nickname_list = array();
        $count = count($result);

        for ($i = 0; $i < $count; $i++) {
            $nickname_list[(string) $result[$i]['_id']] = $result[$i];
        }
        return $nickname_list;
    }

}

?>
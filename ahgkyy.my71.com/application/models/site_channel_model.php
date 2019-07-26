<?php

class site_channel_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'site_channel';
        $this->class_name = 'site_channel_model';
    }

    public function getChannelInfo($_id_list = array()) {
        $_id_list = array_unique(array_filter($_id_list));
        foreach ($_id_list as $key => $value) {
            $_id_list[$key] = new MongoId($value);
        }

        $this->mongo_db->where_in('_id', $_id_list);

        return $this->mongo_db->get($this->table_name);
    }

}

?>

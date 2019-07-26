<?php

class site_cache_model extends MY_Model {

    function __construct() {
        parent::__construct();
        $this->table_name = 'site_cache';
        $this->class_name = 'site_cache_model';
        $this->table_field = array(
            'create_date' => 0,
            'key' => '',
            'site_id' => '',
            'value' => '',
        );
    }

}

?>
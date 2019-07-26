<?php

class vod_type_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'vod_type';
        $this->class_name = 'vod_type_model';

        $this->table_field = array(
            'extension' => 'flv',
            'name' => 'FLA',
            'removed' => false,
            'site_id' => '',
            'status' => 0,
        );
    }

}

?>
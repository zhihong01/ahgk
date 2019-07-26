<?php

class vod_notice_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'vod_notice';
        $this->class_name = 'vod_notice_model';

        $this->table_field = array(
            'body' => '',
            'confirm_date' => 0,
            'confirmer' => array('id' => '', 'name' => ''),
            'create_date' => 0,
            'creator' => array('id' => '', 'name' => ''),
            'title' => '',
            'site_id' => '',
            'status' => 0,
        );
    }

}

?>
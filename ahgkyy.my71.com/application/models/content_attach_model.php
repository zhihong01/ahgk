<?php

class content_attach_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'content_attach';
        $this->class_name = 'content_attach_model';
        $this->table_field = array(
            'branch_id' => '',
            'content_id' => '',
            'create_date' => 0,
            'creator' => array('id' => '', 'name' => ''),
            'downloads' => 0,
            'file_size' => 0,
            'file_type' => '',
            'picture_l' => '',
            'picture_m' => '',
            'picture_s' => '',
            'picture_xl' => '',
            'picture_xxl' => '',
            'real_name' => '',
            'saved_name' => '',
            'site_id' => '',
            'removed' => false
        );
    }

}

?>

<?php

class site_channel_tree_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'site_channel_tree';
        $this->class_name = 'site_channel_tree_model';
        $this->table_field = array(
            'site_id' => '',
            'type' => 2,
            'name' => '',
			'en_name' => '',
            'parent' => array(),
            'child' => array(),
            'folder'=>'',
            'list_template'=>'',
            'detail_template'=>'',
        );
    }

}

?>

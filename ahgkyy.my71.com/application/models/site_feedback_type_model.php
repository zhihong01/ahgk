<?php

class site_feedback_type_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'site_feedback_type';
        $this->class_name = 'site_feedback_type_model';
        $this->table_field = array(
            'name' => '',
            'site_id' => '',
        );
    }

}

?>

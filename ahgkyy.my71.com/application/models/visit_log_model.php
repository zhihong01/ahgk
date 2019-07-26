<?php

class visit_log_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'visit_log';
        $this->class_name = 'visit_log_model';

        $this->table_field = array(
            'client_ip' => '',
            'table_name' => '',
            'record_id' => '',
            'title' => '',
            'create_date' => 1378542250,
            'site_id' => '',
        );
    }

}

?>
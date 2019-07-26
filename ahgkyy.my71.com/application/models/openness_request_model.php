<?php

class openness_request_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'openness_request';
        $this->class_name = 'openness_request_model';
    }
}

?>

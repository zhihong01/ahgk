<?php

class spider_iframe_data_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'spider_iframe_data';
        $this->class_name = 'spider_iframe_data_model';
    }
}

?>
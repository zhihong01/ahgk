<?php

class site_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'site';
        $this->class_name = 'site_model';
    }

}

?>

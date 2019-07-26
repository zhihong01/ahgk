<?php

class site_menu_operation_model extends MY_Model {

    function __construct() {
        parent::__construct();        

        $this->table_name = 'site_menu_operation';
        $this->class_name = 'site_menu_operation_model';
    }

}

?>

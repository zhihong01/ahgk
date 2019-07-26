<?php

class site_setting_model extends MY_Model {

    function __construct() {
        parent::__construct();        

        $this->table_name = 'site_setting';
        $this->class_name = 'site_setting_model';
    }

}

?>

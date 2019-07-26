<?php

class content_setting_model extends MY_Model {

    function __construct() {
        parent::__construct();        

        $this->table_name = 'content_setting';
        $this->class_name = 'content_setting_model';
    }

}

?>

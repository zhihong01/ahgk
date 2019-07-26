<?php

class content_hotkey_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'content_hotkey';
        $this->class_name = 'content_hotkey_model';
    }

}

?>
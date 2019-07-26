<?php

class forum_setting_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'forum_setting';
        $this->class_name = 'forum_setting_model';
    }

}

?>

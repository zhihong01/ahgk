<?php

class friend_link_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'friend_link';
        $this->class_name = 'friend_link_model';
    }

}

?>
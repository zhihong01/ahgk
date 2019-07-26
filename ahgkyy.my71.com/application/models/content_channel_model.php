<?php

class content_channel_model extends MY_Model {

    function __construct() {
        parent::__construct();        

        $this->table_name = 'content_channel';
        $this->class_name = 'content_channel_model';
    }

}

?>
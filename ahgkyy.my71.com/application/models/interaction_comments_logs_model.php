<?php

class interaction_comments_logs_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'interaction_comments_logs';
        $this->class_name = 'interaction_comments_logs_model';
    }

}

?>

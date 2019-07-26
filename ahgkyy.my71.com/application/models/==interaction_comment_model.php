<?php

class interaction_comment_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'interaction_comment';
        $this->class_name = 'interaction_comment_model';
    }

}

?>

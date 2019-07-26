<?php

class supervision_rating_counter_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'supervision_rating_counter';
        $this->class_name = 'supervision_rating_counter_model';
    }

}

?>
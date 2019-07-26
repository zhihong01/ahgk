<?php

class advert_size_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'advert_size';
        $this->class_name = 'advert_size_model';
    }

}

?>

<?php

class advert_resource_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'advert_resource';
        $this->class_name = 'advert_resource_model';
    }

}

?>

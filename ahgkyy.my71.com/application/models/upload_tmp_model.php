<?php

class upload_tmp_model extends MY_Model {

    function __construct() {
        parent::__construct();        

        $this->table_name = 'upload_tmp';
        $this->class_name = 'upload_tmp_model';
    }

}

?>

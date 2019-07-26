<?php

class site_notification_model extends MY_Model {

    function __construct() {
        parent::__construct();        

        $this->table_name = 'site_notification';
        $this->class_name = 'site_notification_model';
    }

}

?>

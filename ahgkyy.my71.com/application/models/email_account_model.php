<?php

class email_account_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'email_account';
        $this->class_name = 'email_account_model';
    }

}

?>

<?php

/**
 * Description of Power_type_model
 *
 * @author Administrator
 */
class Power_type_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'power_type';
        $this->class_name = 'power_type_model';
        $this->table_field = array(
            "create_date" => 0,
            'name' => '',
            'code'=>'',
            'parent_id' => '/',
            'site_id' => '',
            'sort' => 200,
            'removed' => false,
            'status' => false,
        );
    }

}

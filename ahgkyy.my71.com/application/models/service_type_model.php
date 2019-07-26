<?php

class service_type_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'service_type';
        $this->class_name = 'service_type_model';
        $this->table_field = array(
            'creator' => array(
                'id' => '',
                'name' => ''
            ),
            'name' => '',
            'parent_id' => '',
            'site_id' => '',
            'sort' => 200
        );
    }

    public function getTypeInfo($_id_list = array(), $select = array()) {
        $_id_list = array_unique(array_filter($_id_list));
        foreach ($_id_list as $key => $value) {
            $_id_list[$key] = new MongoId($value);
        }

        $this->mongo_db->select($select);
        $this->mongo_db->where_in('_id', $_id_list);

        $result = $this->mongo_db->get($this->table_name);
        $nickname_list = array();
        $count = count($result);

        for ($i = 0; $i < $count; $i++) {
            $nickname_list[(string) $result[$i]['_id']] = $result[$i];
        }
        return $nickname_list;
    }

}

?>

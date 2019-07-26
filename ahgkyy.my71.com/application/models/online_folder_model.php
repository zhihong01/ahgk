<?php

class online_folder_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'online_folder';
        $this->class_name = 'online_folder_model';
        $this->table_field = array(
              "site_id"  =>  "",
              "branch_id"  =>  "",
              "parent_id" => "",
              "name" => "",
              "real_name" => "",
              "description" => "",
              "create_date"  =>  0,
              'creator' => array('id' => '', 'name' => '')
            );
    }

    public function findExists($oldId=null, $filter_list = array()) {

        foreach ($filter_list as $key => $filter) {
            if ($filter !== "" && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if (!empty($oldId) && !($oldId instanceof MongoId)) {
            $oldId = new MongoId($oldId);
        }

        $this->mongo_db->where_ne('_id', $oldId);

        $this->mongo_db->limit(1);
        $query = $this->mongo_db->get($this->table_name);

        return $query;
    }
}

?>

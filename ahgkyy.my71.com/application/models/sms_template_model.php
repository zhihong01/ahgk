<?php

  class sms_template_model extends MY_Model {

      function __construct() {
          parent::__construct();         

          $this->table_name = 'sms_template';
          $this->class_name = 'sms_template_model';
      }

    public function getOneNotId($where_array, $id) {
        // Apply filters
        if (is_array($where_array)) {
            foreach ($where_array as $key => $val) {
                $this->mongo_db->where($key, $val);
            }
        }

        $this->mongo_db->where_ne("_id", new MongoId($id));
        $this->mongo_db->select("*");
        $this->mongo_db->limit(1);
        $this->mongo_db->offset(0);

        $query = $this->mongo_db->get($this->table_name);
        if ($query ) {
            return $query[0];
        }

        return $query;
    }
  }

?>
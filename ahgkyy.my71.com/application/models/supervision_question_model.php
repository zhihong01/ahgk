<?php

class supervision_question_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'supervision_question';
        $this->class_name = 'supervision_question_model';

        $this->table_field = array(
            "site_id" => "",
            "name" => ""
        );
    }

    public function getQuestionInfo($_id_list = array(), $select = array()) {
        $_id_list = array_unique($_id_list);
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

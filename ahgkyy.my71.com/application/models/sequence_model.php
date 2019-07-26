<?php

class sequence_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'sequence';
        $this->class_name = 'sequence_model';
    }

    public function getSeq($name="supervision"){
        $update = array('$inc'=>array("id"=>1));
        $query = array('name'=>$name);
        $command = array(
                'findandmodify'=> $this->table_name, 'update'=>$update,
                'query'=>$query, 'new'=>true, 'upsert'=>true
        );
        $id = $this->mongo_db->command($command);
        return $id['value']['id'];
    }
    public function getNoSeq($date_str, $name = "supervision") {
        $update = array('$inc' => array("id" => 1));
        $query = array("date_str" => $date_str, 'name' => $name);
        $command = array(
            'findandmodify' => $this->table_name, 'update' => $update,
            'query' => $query, 'new' => true, 'upsert' => true
        );
        $id = $this->mongo_db->command($command);
        return $id['value']['id'];
    }
}

?>
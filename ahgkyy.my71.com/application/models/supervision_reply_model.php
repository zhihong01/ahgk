<?php

  class supervision_reply_model extends MY_Model {

      function __construct() {
          parent::__construct();         

          $this->table_name = 'supervision_reply';
          $this->class_name = 'supervision_reply_model';

          $this->table_field = array(
            "supervision_id" => "",
            "type" => 1,
            "user_id" => "",
            "message" => "",
            "rand_key" => "",
            "create_date" => 0,
            "update_date" => 0
            );
      }

      function getCount($supervision_id){
          $this->mongo_db->select("user_id, _id");
          $this->mongo_db->where("type", 1);
          $this->mongo_db->where("supervision_id", $supervision_id );

//          $this->mongo_db->select("user_id, COUNT(*) AS total");
//          $this->db->group_by("user_id");

          $result = $this->mongo_db->get($this->table_name);
          $ret_map = array();
          foreach($result as $k=>$v){
              $user_id = $v['user_id'];
              if(!isset($ret_map[ $user_id ]))
                  $ret_map[ $user_id ] = array("total"=>0);
              $ret_map[ $user_id ]['total'] = $ret_map[$user_id]['total'] +1;
              $ret_map[ $user_id ]['user_id'] =$user_id;
          }
          return $ret_map;
      }


    public function getReplyList($supervision_id) {
        $this->mongo_db->select("*");
        $this->mongo_db->where("supervision_id", $supervision_id);
        $this->mongo_db->order_by(array('_id' => 'DESC'));
        $result = $this->mongo_db->get($this->table_name);

        $account_id_list = array();
        foreach($result as $k=>$v) {
            array_push($account_id_list, new MongoId($v['user_id']) );
        }

        $this->mongo_db->select("_id,name,email,avatar");
        $this->mongo_db->where_in("_id", $account_id_list);
        $user_account_list = $this->mongo_db->get("site_account");
        $account_map = array();
        foreach($user_account_list as $k=>$v) {
            $account_map[ (string)$v['_id'] ] = $v;
        }

        foreach($result as $k=>$v) {
            if(isset($account_map[ $v['user_id'] ])) {
                $result[$k]["user_info"] = $account_map[ $v['user_id'] ];
            }
        }

        return $result;
    }

  }

?>
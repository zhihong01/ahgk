<?php

class site_user_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'site_user';
        $this->class_name = 'site_user_model';
        $this->table_field = array(
            'account_id' => '',
            'branch_id' => '',
            'backup_user' => '',
            'group_id' => 1,
            'office_id' => '',
            'row_per_page' => 20,
            'site_id' => '',
            'time_zone' => '+0800'
        );
    }

    public function findAccountList($where_array = null) {
        $this->mongo_db->select(array('_id', 'account_id'));

        if (is_array($where_array)) {
            $this->mongo_db->where($where_array);
        }

        $result = $this->mongo_db->get($this->table_name);

        $account_id_arr = array();
        foreach ($result as $k => $v) {
            array_push($account_id_arr, new MongoId($v['account_id']));
        }

        //get account id name pair
        $this->mongo_db->select(array('_id', 'name', 'last_time'));
        $this->mongo_db->where_in("_id", $account_id_arr);
        $account_list = $this->mongo_db->get("site_account");
        return $account_list;
    }

}

?>

<?php

class site_account_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'site_account';
        $this->class_name = 'site_account_model';
        $this->table_field = array(
            'address' => array('province' => '', 'city' => '', 'area' => '', 'street' => ''),
            'avatar' => '/media/avatar/none.jpg',
            'confirmer' => array('id' => '', 'name' => ''),
            'create_date' => 0,
            'confirm_date' => 0,
            'activated' => false,
            'email' => '',
            'gender' => 0,
            'last_ip' => '',
            'last_time' => 0,
            'login_count' => 0,
            'login_method' => 1, //1,direct,2 weibo, 3:qq,
            'name' => '',
            'nickname' => '',
            'oauth_token' => '',
            'open_id' => '',
            'password' => '',
            'rand_key' => '',
            'phone' => '',
			"IDno" => "",   //身份证号码
            'site_id' => '',
            'status' => false,
            'removed' => false,
            'type' => 2
        );
    }

    public function findList($keyword, $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {

        if ($keyword && is_array($keyword) && (count($keyword) > 0)) {
//echo($keyword['name'] .":". $keyword['content']);
            if (!empty($keyword['name']))
                $this->mongo_db->like($keyword['name'], $keyword['content']);
        }
        else if (is_string($keyword) && (strlen($keyword) > 1)) {
            $this->mongo_db->like('name', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== "" && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('create_date', strtotime($from_date . " 00:00:00"));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime("+1 day", strtotime($to_date)));
        }

        if ($arr_sort != null && is_array($arr_sort)) {
            $this->mongo_db->order_by($arr_sort);
        } else {
            $this->mongo_db->order_by(array('_id' => 'DESC'));
        }

        $this->mongo_db->select($select);
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);

        $query = $this->mongo_db->get($this->table_name);

        return $query;
    }

    public function listCount($keyword, $filter_list = array(), $from_date = null, $to_date = null) {

        if ($keyword && is_array($keyword) && (count($keyword) > 0)) {
            if (!empty($keyword['name']))
                $this->mongo_db->like($keyword['name'], $keyword['content']);
        }
        else if (is_string($keyword) && (strlen($keyword) > 1)) {
            $this->mongo_db->like('name', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== "" && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('create_date', strtotime($from_date . " 00:00:00"));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime("+1 day", strtotime($to_date)));
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }

    public function getAccountInfo($_id_list = array(), $select = array()) {
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

    public function getBranchAccount($branch_id) {
        $this->mongo_db->select(array("account_id"));
        $this->mongo_db->where('branch_id', $branch_id);
        $result = $this->mongo_db->get("site_user");
        if ($result) {
            $_id_list = array();
            foreach ($result as $key => $value) {
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
        return array();
    }

    public function getPhoneList($site_id = null, $access_user = null) {
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->select(array("name", "phone"));
        $this->mongo_db->where_ne("phone", "");
        $result = $this->mongo_db->get($this->table_name);
        return $result;
    }

}

?>

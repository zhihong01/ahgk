<?php

class supervision_model extends MY_Model {

    function __construct() {
        parent::__construct();


        $this->table_name = 'supervision';
        $this->class_name = 'supervision_model';

        $this->table_field = array(
            "site_id" => "",
            "member_id" => "",
            "branch_id" => "",
            "priority" => 3,//一般
            "subject" => "",
            "message" => "",
            "create_date" => 0,
            "update_date" => 0,
            "client_ip" => "",
            "display_id" => "",
            "rand_key" => "",
            "status" => true,
            "locked" => FALSE,
            "rating" => 0,
            "removed" => FALSE,
            "cancelled" => FALSE,
            'creator' => array('id' => '', 'name' => ''),
            "assigned_user" => array(),
            "account_score" => array(),
            "replies" => 0,
            "open_date" => 0,
            "reply_date" => 0,
            "close_date" => 0,
            'question_id' => "",
            "no" => 10000,
			"no_password" => '',
            "old_no" => "",
            "hit" => 1,
            'product_id' => 0,
            'process_status' => false,
            "confirm_remark" => "",
            "share_on" => false,
            "submitter_share_on" => false,
            'reply_confirmed' => false,
            'extension' => array("start_oa" => false, "pushoa_time" => 0, 'pushoa_bid' => "", "oa_reply" => false),
        	'allow_rate' => true, //允许 评价 
			'no_password' => "" ,//查询码
        
        );
    }

    public function findList($keyword, $access_branchs, $access_users, $status, $filter_list = null, $data_range = null, $limit = 20, $offset = 0, $arr_sort = null, $select = '*', $include_detail = false) {

        if (strlen($keyword) > 1) {
            $this->mongo_db->like("subject", $keyword);
        }

        if (($access_branchs != null) && is_array($access_branchs) && (count($access_branchs) > 0)) {
            $this->mongo_db->where_in("branch_id", $access_branchs);
        } else if (($access_branchs != null) && !(is_array($access_branchs))) {
            $this->mongo_db->where("branch_id", $access_branchs);
        } else {
            return array();
        }

        if ($access_users != null && is_array($access_users) && (count($access_users) > 0)) {
            $this->mongo_db->where_in('assigned_user_account', $access_users);
//            $this->mongo_db->or_like("assigned_user.account_id", $access_users);
        } else if (($access_users !== null) && !(is_array($access_users))) {
            $this->mongo_db->where_in('assigned_user_account', (array) $access_users);
//            $this->mongo_db->like("assigned_user.account_id", $access_users); // ",".$access_users.","
        } else {
//            return array();
        }

//print_r($status);
        if ($status && is_array($status)) {
            $this->mongo_db->where_in("status", $status);
        } else if ($status && !is_array($status)) {
            $this->mongo_db->where("status", (int) $status);
        }

        if ($filter_list && is_array($filter_list)) {
            if (isset($filter_list['_id']) && !($filter_list['_id'] instanceof MongoId)) {
                $filter_list['_id'] = new MongoId($filter_list['_id']);
            }
            foreach ($filter_list as $key => $val) {
                $this->mongo_db->where($key, $val);
            }
        }

        if ($data_range) {
            $this->mongo_db->where_gt('create_date', strtotime("-" . $data_range . " days"));
        }

        if ($arr_sort !== null && is_array($arr_sort)) {
            $this->mongo_db->order_by($arr_sort);
        } else {
            $this->mongo_db->order_by(array('_id' => 'DESC'));
        }

        $this->mongo_db->select("*");
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);

        $result = $this->mongo_db->get($this->table_name);

        if ($include_detail) {
            foreach ($result as $key => $val) {
                if (isset($val['assigned_user_account']) && !empty($val['assigned_user_account'])) {
                    $account_ids = $val['assigned_user_account'];
                    $accounts = array();
                    foreach ($account_ids as $myid) {
                        array_push($accounts, new MongoId($myid));
                    }
                    $this->mongo_db->select("_id,name,email,nickname");
                    $this->mongo_db->where_in("_id", $accounts);
                    $name_list = $this->mongo_db->get("site_account");
                    $names = array();
                    foreach ($name_list as $myname) {
                        array_push($names, $myname['name']);
                    }
                    $result[$key]['assigned_user_names'] = implode(",", $names);
                }
                else
                    $result[$key]['assigned_user_names'] = "";
            }
        }
        return $result;
    }

   /*  public function findLists($keyword = null, $filter_list = null, $limit = 20, $offset = 0, $select = '*', $arr_sort = array()) {
        if (!empty($keyword)) {
            $this->mongo_db->like("subject", $keyword);
        }
        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (!empty($filter)) {
                    $this->mongo_db->where($key, $filter);
                }
            }
        }

        $this->mongo_db->select($select);
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);
        if ($arr_sort !== null && is_array($arr_sort)) {
            $this->mongo_db->order_by($arr_sort);
        } else {
            $this->mongo_db->order_by(array('_id' => 'DESC'));
        }
        $result = $this->mongo_db->get($this->table_name);

        return $result;
    } */
	
	public function findLists($keyword = null, $filter_list = null, $limit = 20, $offset = 0, $select = '*', $arr_sort = array()) {
        if (!empty($keyword)) {
            $this->mongo_db->like("subject", $keyword);
        }
        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (!empty($filter)) {
                    $this->mongo_db->where($key, $filter);
                }
            }
        }

        $this->mongo_db->select($select);
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);
        if ($arr_sort !== null && is_array($arr_sort)) {
            $this->mongo_db->order_by($arr_sort);
        } else {
            $this->mongo_db->order_by(array('_id' => 'DESC'));
        }
        $result = $this->mongo_db->get($this->table_name);

        return $result;
    }

    public function listCount($keyword = null, $filter_list = null) {
        if (!empty($keyword)) {
            $this->mongo_db->like("subject", $keyword);
        }
        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (!empty($filter)) {
                    $this->mongo_db->where($key, $filter);
                }
            }
        }
        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }
	
	
		public function searchList($keyword = null, $filter_list = null, $from_date = null, $to_date = null,$limit = 20, $offset = 0, $select = '*', $arr_sort = array()) {
        if (!empty($keyword)) {
            $this->mongo_db->like("subject", $keyword);
        }
        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (!empty($filter)) {
                    $this->mongo_db->where($key, $filter);
                }
            }
        }

        $this->mongo_db->select($select);
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);
        if ($arr_sort !== null && is_array($arr_sort)) {
            $this->mongo_db->order_by($arr_sort);
        } else {
            $this->mongo_db->order_by(array('_id' => 'DESC'));
        }
		
		if ($from_date) {
            $this->mongo_db->where_gt('create_date', strtotime($from_date));
        }
		if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime('+1 day', strtotime($to_date)));
        }
		
        $result = $this->mongo_db->get($this->table_name);

        return $result;
    }
	
	
	public function searchCount($keyword = null, $filter_list = null, $from_date = null, $to_date = null) {
        if (!empty($keyword)) {
            $this->mongo_db->like("subject", $keyword);
        }
        if ($filter_list && is_array($filter_list)) {
            foreach ($filter_list as $key => $filter) {
                if (!empty($filter)) {
                    $this->mongo_db->where($key, $filter);
                }
            }
        }
		
		if ($from_date) {
            $this->mongo_db->where_gt('create_date', strtotime($from_date));
        }
		if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime('+1 day', strtotime($to_date)));
        }
		
        $result = $this->mongo_db->count($this->table_name);

        return $result;
    }
	
	

    public function getOne($access_branchs, $assigned_users, $id, $include_user_name = false) {

        if (($access_branchs != null) && is_array($access_branchs) && (count($access_branchs) > 0)) {
            $this->mongo_db->where_in("branch_id", $access_branchs);
        } else if (($access_branchs != null) && !(is_array($access_branchs))) {
            $this->mongo_db->where("branch_id", $access_branchs);
        } else {
            return null;
        }

        if ($assigned_users != null && is_array($assigned_users) && (count($assigned_users) > 0)) {
            $this->mongo_db->where_in('assigned_user_account', $assigned_users);
//            $this->mongo_db->or_like("assigned_user.account_id", $access_users);
        } else if (($assigned_users !== null) && !(is_array($assigned_users))) {
            $this->mongo_db->where_in('assigned_user_account', (array) $assigned_users);
//            $this->mongo_db->like("assigned_user.account_id", $access_users); // ",".$access_users.","
        } else {
//            return 0;
        }

        if (!empty($id) && !($id instanceof MongoId)) {
            $id = new MongoId($id);
        }
        $this->mongo_db->where("_id", $id);

        $this->mongo_db->select("*");
        $this->mongo_db->limit(1);
        $this->mongo_db->offset(0);

        $result = $this->mongo_db->get($this->table_name);
        if (isset($result[0]))
            $result = $result[0];

        if ($include_user_name) {
            $result['assigned_user_names'] = "";
            if (isset($result['assigned_user_account']) && !empty($result['assigned_user_account'])) {
                $account_ids = $result['assigned_user_account']; //explode(",", $result['assigned_user']['account_id']);
                $accounts = array();
                foreach ($account_ids as $myid) {
                    array_push($accounts, new MongoId($myid));
                }
                $this->mongo_db->select("_id,name,email,nickname");
                $this->mongo_db->where_in("_id", $accounts);
                $name_list = $this->mongo_db->get("site_account");
                $names = array();
                foreach ($name_list as $myname) {
                    array_push($names, $myname['name']);
                }
                $result['assigned_user_names'] = implode(",", $names);
            }

            $result['member_info'] = array("name" => "", "email" => "");
            if (!empty($result['member_id'])) {
                $this->mongo_db->select("_id,name,email,nickname,avatar");
                $this->mongo_db->where("_id", new MongoId($result['member_id']));
                $this->mongo_db->limit(1);
                $this->mongo_db->offset(0);
                $memeber_list = $this->mongo_db->get("site_account");
                if (isset($memeber_list[0]))
                    $result['member_info'] = $memeber_list[0];
            }

            $result['question_name'] = "";
            if (!empty($result['question_id'])) {
                $this->mongo_db->select("name");
                $this->mongo_db->where("_id", new MongoId($result['question_id']));
                $this->mongo_db->limit(1);
                $this->mongo_db->offset(0);
                $question_list = $this->mongo_db->get("supervision_question");
                if (isset($question_list[0]['name']))
                    $result['question_name'] = $question_list[0]['name'];
            }
        }

        return $result;
    }

}

?>
<?php

class special_content_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'special_content';
        $this->class_name = 'special_content_model';
        $this->table_field = array(
            'author' => '',
            'body' => '',
            'branch_id' => '',
            'channel' => array(''),
            'close_date' => 1893369600,
            'comment_on' => false,
            'confirm_date' => 0,
            'confirmer' => array('id' => '', 'name' => ''),
            'copy_form' => '',
            'create_date' => 0,
            'creator' => array('id' => '', 'name' => ''),
            'description' => '',
            'release_date' => 0,
            'removed' => false,
            'replies' => 0,
            'share_on' => false,
            'site_id' => '',
            'status' => 0,
            'subhead' => '',
            'tag' => array(),
            'thumb_name' => '',
            'title' => '',
            'title_color' => '',
            'type' => 2,
            'update_date' => 0,
            'views' => 0);
    }

    public function findList($column_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {

        if (count($column_id) > 0) {
            //$this->mongo_db->where_in_all('channel', $channel_id);
			$this->mongo_db->where_in('column_id', $column_id);
        }
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('release_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('release_date', strtotime('+1 day', strtotime($to_date)));
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
		//echo $this->mongo_db->last_query();
        return $query;
    }

    public function listCount($column_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null) {
        if (count($column_id) > 0) {
            $this->mongo_db->where_in('column_id', $column_id);
        }
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('release_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('release_date', strtotime('+1 day', strtotime($to_date)));
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }


	public function searchList($keyword = '', $somewhere = '', $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {
		if(strlen($somewhere) > 1){
			if($somewhere=="title"){
				if (strlen($keyword) > 1) {
					$this->mongo_db->like('title', $keyword);
				}
			}
			if($somewhere=="body"){
				 if (strlen($keyword) > 1) {
					$this->mongo_db->like('body', $keyword);
				}
			}
			if($somewhere=="author"){
				if (strlen($keyword) > 1) {
					$this->mongo_db->like('author', $keyword);
				}
			}
			if($somewhere=="copy_form"){
				if (strlen($keyword) > 1) {
					$this->mongo_db->like('copy_form', $keyword);
				}
			}
		}

        if ($from_date) {
            $this->mongo_db->where_gt('create_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime('+1 day', strtotime($to_date)));
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
		//echo $this->mongo_db->last_query();
        return $query;
    }

    public function searchCount($keyword = '', $somewhere = '', $from_date = null, $to_date = null) {
        
		if(strlen($somewhere) > 1){
			if($somewhere=="title"){
				if (strlen($keyword) > 1) {
					$this->mongo_db->like('title', $keyword);
				}
			}
			if($somewhere=="body"){
				 if (strlen($keyword) > 1) {
					$this->mongo_db->like('body', $keyword);
				}
			}
			if($somewhere=="author"){
				if (strlen($keyword) > 1) {
					$this->mongo_db->like('author', $keyword);
				}
			}
			if($somewhere=="copy_form"){
				if (strlen($keyword) > 1) {
					$this->mongo_db->like('copy_form', $keyword);
				}
			}
		}

        if ($from_date) {
            $this->mongo_db->where_gt('create_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime('+1 day', strtotime($to_date)));
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }

    public function get_min_created() {
        $this->mongo_db->select(array("create_date") );
        $this->mongo_db->where_gt("create_date",0);

        $this->mongo_db->limit(1);
        $this->mongo_db->offset(0);
        $this->mongo_db->order_by(array('create_date' => 'ASC'));

        $result = $this->mongo_db->get($this->table_name);

        if(isset($result[0]['create_date']) )
            return  $result[0]['create_date'];
        else
            return null;
    }


    public function get_max_created() {
        $this->mongo_db->select(array("create_date") );
        $this->mongo_db->where_gt("create_date",0);
        $this->mongo_db->limit(1);
        $this->mongo_db->offset(0);
        $this->mongo_db->order_by(array('create_date' => 'DESC'));

        $result = $this->mongo_db->get($this->table_name);

        if(isset($result[0]['create_date']) )
            return  $result[0]['create_date'];
        else
            return null;
    }

    public function getcontents ($keyword, $access_branchs, $access_users, $filter_list = null, $from_date = null, $to_date = null, $limit = 20, $offset = 0, $arr_sort = null) {


        if (strlen($keyword) > 1) {
            $this->mongo_db->like("title" , $keyword);
        }

        if ( ($access_branchs != null) && is_array($access_branchs) &&(count($access_branchs)>0)) {
            $this->mongo_db->where_in("branch_id", $access_branchs);
        } else if ( ($access_branchs != null) && !(is_array($access_branchs))) {
            $this->mongo_db->where("branch_id", $access_branchs);
        } else {
//            return array();           //2013-05-31 支持 管理员可以 查看所有部门 ，性能优化版本
        }

        if ($access_users != null && is_array($access_users)&&(count($access_users)>0)) {
            $this->mongo_db->where_in('creator.id', $access_users);
        } else if ( ($access_users !== null) && !(is_array($access_users))) {
            $this->mongo_db->where_in('creator.id', (array)$access_users);
        } else {
//            return array();
        }

        if ($filter_list && is_array($filter_list)) {
            if (isset($filter_list['_id']) && !($filter_list['_id'] instanceof MongoId)) {
                $filter_list['_id'] = new MongoId($filter_list['_id']);
            }
            foreach ($filter_list as $key => $val) {
                $this->mongo_db->where($key, $val);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gte('create_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime('+1 day', strtotime($to_date)));
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

        return $result;
    }

    public function countcontents ($keyword, $access_branchs, $access_users, $filter_list = null, $from_date = null, $to_date = null) {


        if (strlen($keyword) > 1) {
            $this->mongo_db->like("title" , $keyword);
        }

        if ( ($access_branchs != null) && is_array($access_branchs) &&(count($access_branchs)>0)) {
            $this->mongo_db->where_in("branch_id", $access_branchs);
        } else if ( ($access_branchs != null) && !(is_array($access_branchs))) {
            $this->mongo_db->where("branch_id", $access_branchs);
        } else {
//            return array();           //2013-05-31 支持 管理员可以 查看所有部门 ，性能优化版本
        }

        if ($access_users != null && is_array($access_users)&&(count($access_users)>0)) {
            $this->mongo_db->where_in('creator.id', $access_users);
        } else if ( ($access_users !== null) && !(is_array($access_users))) {
            $this->mongo_db->where_in('creator.id', (array)$access_users);
        } else {
//            return array();
        }

        if ($filter_list && is_array($filter_list)) {
            if (isset($filter_list['_id']) && !($filter_list['_id'] instanceof MongoId)) {
                $filter_list['_id'] = new MongoId($filter_list['_id']);
            }
            foreach ($filter_list as $key => $val) {
                $this->mongo_db->where($key, $val);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gte('create_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime('+1 day', strtotime($to_date)));
        }

        $query = $this->mongo_db->count($this->table_name);
//print_r($this->mongo_db->last_query());
        return (int) $query;
    }
	
	public function findList1($column_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {

        if (count($column_id) > 0) {
            //$this->mongo_db->where_in_all('channel', $channel_id);
			$this->mongo_db->where_in('column_id', $column_id);
        }
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            //if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            //}
        }

        if ($from_date) {
            $this->mongo_db->where_gt('release_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('release_date', strtotime('+1 day', strtotime($to_date)));
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
		//echo $this->mongo_db->last_query();
        return $query;
    }
    
}

?>

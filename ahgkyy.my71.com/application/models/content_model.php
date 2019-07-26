<?php

class content_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'content';
        $this->class_name = 'content_model';
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
            'title_bold' => true,
            'type' => 2,
            'update_date' => 0,
            'views' => 0);
    }

    public function findList($channel_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null, $field =null) {

        if (count($channel_id) > 0) {
            $this->mongo_db->where_in('channel', $channel_id);
        }
        if (strlen(trim($keyword)) > 1) {
			if(!empty($field)){
				$this->mongo_db->like("$field", trim($keyword));
			}else{
				$this->mongo_db->like('title', trim($keyword));
			}
            
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

        return $query;
    }
	
	

    public function listCount($channel_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null,$field=null) {
        if (count($channel_id) > 0) {
            $this->mongo_db->where_in('channel', $channel_id);
        }
        if (strlen(trim($keyword)) > 1) {
			if(!empty($field)){
				$this->mongo_db->like("$field", trim($keyword));
			}else{
				$this->mongo_db->like('title', trim($keyword));
			}
            
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

    public function findTag($tag = '商网', $filter_list = array(), $limit = 20, $offset = 0, $select = array(), $arr_sort = array()) {
        $this->mongo_db->where_in('tag', (array) $tag);
		if (count($channel_id) > 0) {
            $this->mongo_db->where_in('channel', $channel_id);
        }
        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }
		if ($arr_sort != null && is_array($arr_sort)) {
            $this->mongo_db->order_by($arr_sort);
        } else {
			$this->mongo_db->order_by(array('release_date' => 'DESC'));
		}
        $this->mongo_db->select($select);
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);

        $query = $this->mongo_db->get($this->table_name);

        return $query;
    }

    public function tagCount($tag = '商网', $filter_list = array()) {
        $this->mongo_db->where_in('tag', (array) $tag);

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }

    public function next($channel_id = array(), $release_date = 0) {

        if (count($channel_id) > 0) {
            $this->mongo_db->where_in('channel', $channel_id);
        }
		$this->mongo_db->where(array('status' => true, 'removed' => False));
        if ($release_date) {
            $this->mongo_db->where_gt('sort', $release_date);
        }
        //$this->mongo_db->order_by(array('release_date' => 'DESC'));
        $this->mongo_db->order_by(array('sort' => 'ASC'));

        $this->mongo_db->select(array('_id', 'title', 'thumb_name'));
        $this->mongo_db->limit(1);
        $this->mongo_db->offset(0);

        $query = $this->mongo_db->get($this->table_name);

        if ($query) {
            return $query[0];
        } else {
            return NULL;
        }
    }

    public function prev($channel_id = array(), $release_date = 0) {

        if (count($channel_id) > 0) {
            $this->mongo_db->where_in('channel', $channel_id);
        }
        $this->mongo_db->where(array('status' => true, 'removed' => False));
        if ($release_date) {
            $this->mongo_db->where_lt('sort', $release_date);
        }
		
        $this->mongo_db->order_by(array('sort' => 'DESC'));
        //$this->mongo_db->order_by(array('release_date' => 'ASC'));
        $this->mongo_db->select(array('_id', 'title', 'thumb_name'));
        $this->mongo_db->limit(1);
        $this->mongo_db->offset(0);

        $query = $this->mongo_db->get($this->table_name);

        if ($query) {
            return $query[0];
        } else {
            return NULL;
        }
    }
	
	
	public function findLists($channel_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null, $field =null) {

        if (count($channel_id) > 0) {
            $this->mongo_db->where_in('channel', $channel_id);
        }
       /*  if (strlen(($keyword)) > 1) { */
			if(!empty($field)){
				$this->mongo_db->like("body", $keyword);
			}else{
				$this->mongo_db->like('title', $keyword);
			}
            
    /*     } */

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

        return $query;
    }	

}

?>

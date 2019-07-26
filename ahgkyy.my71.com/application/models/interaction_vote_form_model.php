<?php

  class interaction_vote_form_model extends MY_Model {

      function __construct() {
          parent::__construct();
          
          $this->table_name = 'interaction_vote_form';
          $this->class_name = 'interaction_vote_form_model';

          $this->table_field = array(
            "site_id" => '',
			"status" => false,
			"removed" => false,
			"create_date" => 0,
            "creator" => array(
                "id" => "",
                "name" => ""
            ),
			'confirm_date' => 0,
            'confirmer' => array('id' => '', 'name' => ''),
			"sort" => 0,

			"vote_id" => '',
			"type" => '',	// 表单类型 radio,checkbox,select,input,textarea
			"attr" => '',	// 属性，inline,multiple
            "name" => '',	
            "description" => '',
            "requried" => false,	// 必填项
            "data" => array(
            	'option' => array(),	// 选项
            	'placeholder' => '',	// 占位符
            ),
          );
      }

      public function findList($keyword, $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select =array(), $arr_sort = null) {


        if (strlen($keyword) > 1) {
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

        if (strlen($keyword) > 1) {
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

  }

?>
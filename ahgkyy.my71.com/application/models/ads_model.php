<?php
  class ads_model extends MY_Model {

      function __construct() {
          parent::__construct();
        $this->table_name = 'web_ads';
        $this->class_name = 'ads_model';
          
      }
	  
		public function findList($ty,$offset = 0,$limit = 0){
			$table_name = $this->db->dbprefix . $this->table_name;
			
			$this->db->where('ty',$ty);
			$this->db->where('isshow','1');
			
			if($limit!=0){
				$this->db->limit($limit,$offset);
			}
			
			$this->db->order_by("id asc");
			
			$query = $this->db->get($this->table_name);
			$result = $query->result_array();

			return $result;
		}
		
		public function getOne($ty){
			$table_name = $this->db->dbprefix . $this->table_name;
			
			$this->db->where('ty',$ty);
			$this->db->where('isshow','1');
			
			$query = $this->db->get($this->table_name);

			$result = $query->row_array();

			return $result;
		
		}
		


  }

?>
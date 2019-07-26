<?php

  class supervision_attachment_model extends MY_Model {

      function __construct() {
          parent::__construct();
          

          $this->table_name = 'supervision_attachment';
          $this->class_name = 'supervision_attachment_model';

          $this->table_field = array(
            "supervision_id" => "",
            "real_name" => "",
            "saved_name" => "",
            "file_type" => "",
            "file_size" => 0,
            "rand_key" => "",
            "thumb_name" => ""
            );
      }

  }

?>
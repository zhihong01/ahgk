<?php

  class supervision_setting_model extends MY_Model {

      function __construct() {
          parent::__construct();
          

          $this->table_name = 'supervision_setting';
          $this->class_name = 'supervision_setting_model';

        $this->table_field = array(
            "supervision_attach_number" => "",
            "supervision_attach_on" => 1,
            "supervision_attach_size" => "",
            "supervision_auto_assign" => "",
            "supervision_auto_response" => 0,
            "supervision_auto_retrieve_pop3" => "",
            "supervision_autoclose_day" => "",
            "supervision_bcc_address" => "",
            "supervision_form_id" => "",
            "supervision_id_prefix" => "",
            "supervision_idle_nonresponse" => "",
            "supervision_include_request" => 0,
            "supervision_response_interval" => ""
        );

      }

  }

?>
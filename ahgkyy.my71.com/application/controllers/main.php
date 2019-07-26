<?php

class main extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('content_model', 'content');
       
        
    }



    

    public function index() {
		 header("Location: " . '/');
		 //$this->load->view("main", $this->vals);
    }

    

}

?>
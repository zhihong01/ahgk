<?php

class getFile extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('online_store_new_model', 'online_store_new');
		
    }


    public function index() {
		
		$id = (int) $this->input->get('id');
		$type = (string) $this->input->get('type');
		$store = $this->online_store_new->find(array('id' => $id), 1);
		$data=$this->upload_url.$this->site_id.'/'.substr($store['saved_name'],0,6).'/'.$store['saved_name'];//print_r($data);die();
		
		$iconcontent = file_get_contents($data);
		header('Content-type:image/'.$type);
		header('Content-length: ' . strlen($iconcontent));
		echo $iconcontent;
		exit;
}

}

?>
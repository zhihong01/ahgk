<?php

class u extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('site_account_model', 'site_account');
		
    }


    public function index() {
	
       $account = $this->site_account->find(array("branchid"=> array('$nin' => array(0,null)),'removed'=>false),500);
	   foreach($account as $k=> $val){
			echo $val['name']."/".$val['nickname']."<br/>";
	   }

	}

    public function phpinfo() {
        echo(phpinfo());
    }
}

?>
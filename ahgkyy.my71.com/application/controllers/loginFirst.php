<?php
class loginFirst extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }
    public function index() {
        $View = new Blitz('template/login-first.html');
		$View->display();
    }
}

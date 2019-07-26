<?php 

class wechat extends MY_Controller{

	
	public function index(){
		$View = new Blitz('template/wechat.html'); 
		$View->display(); 
		

	}	 
}

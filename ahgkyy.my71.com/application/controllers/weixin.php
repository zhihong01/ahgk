<?php
class weixin extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }
    public function index() {
        $View = new Blitz('template/weixin.html');
		$View->display();
    }
	
	public function senddx(){
		$this->load->model('sms_template_model', 'sms_template');
		$template = $this->sms_template->find(array('site_id' => $this->site_id, 'key_word' => "activate_account"), 1);

		if ($template) {
			$template["content"] = str_replace(array("%MEMBERNAME%", '%RANDKEY%'), array($nickname, $sms_code), $template["content"]);
			$this->sendSms('15209898978', $template['content'], $this->site_id);
		}
	}
}

<?php

class interactRss extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
       
        $View = new Blitz('template/rss.html');
		$View->display();
        
    }

	public function rss() {
		
		$this->load->helper('rss');
        $rss = new UniversalFeedCreator();
        $rss->title = $this->vals['setting']['site_name'];
        $rss->link = $this->vals['setting']['software_url'];
        $rss->description = '';
		
		$group_id = '5720870ad09491e8c76d48de';
		$group_name = '';
		
		$this->load->model('content_model', 'content');
		$channel=$this->input->get('channel');
		$data['channel_id'] = array($channel);
		$select=array("_id","title","body","author","copy_form","create_date");
	    $rssinfo= $this->content->findList($data['channel_id'], NULL, array("status"=>true,"removed"=>FALSE), null, null, 30,  0, $select ,array("sort"=>"desc"));
		
        foreach ($rssinfo as $rs) {
            $item = new FeedItem();
			$item->descriptionHtmlSyndicated = TRUE;
            $item->title = mb_substr(htmlspecialchars($rs['title']),0,13) . '...';
            $item->link = "/index.php?c=content&m=detail&_id=".$rs['_id'];
            $item->description = mb_substr($rs['body'], 0, 255) . '...';
            $item->category = $group_name;
            $rss->addItem($item);
        }
/* 		echo "<pre/>";
		print_r($rssinfo);
		die(); */
        $rss->saveFeed("RSS2.0", 'cache/' . $channel . "_rss.xml");

	}
        

}


?>

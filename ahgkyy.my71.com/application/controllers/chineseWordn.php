<?php

class chineseWordn extends MY_Controller {

    public function __construct() {
        parent::__construct();
		$this->load->model('content_model', 'content');
    }

    public function getTag($text) {
        $text = strip_tags($text);
		$text = str_replace('&nbsp;','', $text);
		
		//查找 自定义的 词库
        $result = array();
        $tag_defined = array('官山月','董众兵','张祖保','王宏','洪渊','成祖德','陈儒江','乔兴力','阮怀楼','王崧','许承通','沈强','刘涛','张健','蔡宜骅','徐礼国','张东明');
        foreach($tag_defined as $val) {
            $idx = strstr($text, $val);
            if ( $idx > -1 ) {
                $result[] = $val ;
            }
        }
        if ( count($result) == 0 ) {
		
			$result=array("淮南市");  
        }
		
		return $result;  

    }
	
	public function index() {
		
		$content = $this->content->findList(null, '', array("body" => array("\$nin" => array('',null)),'tag'=>array()), NULL, NULL, 500, 0,null,array('sort' => 'DESC'));
	   //print_r($content);die();
	   foreach($content as $k=> $val){
			if($val['body']!=''){
				$tag=$this->getTag($val['body']);
				$this->content->update(array("_id" => $val['_id']), array("tag" => $tag));
			}
			echo($val['_id'].'<br/>');
	   }
	   
	   echo '<meta http-equiv="Refresh" content="2;URL='.$PHP_SELF.'" />';
    }

}

?>

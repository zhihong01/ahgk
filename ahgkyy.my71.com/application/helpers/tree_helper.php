<?php
//********************************************
//	作者：Kevin
//	时间：2010-12-14
//	作用：树形菜单函数
//********************************************

if(!defined('IN_COPY')) { exit('Access Denied');}


/**
* 得到需要的数组
*/
function tree_getchild($StartID,$array){
	$a = $newarr = array();
	if(is_array($array)){
		$i=0;
		foreach($array as $id => $a){
			if($a['onid'] == $StartID){
				$newarr[$i] = $a;
				$i++;
			}
		}
	}
	return $newarr ? $newarr : false;
}

/**
* 得到树型结构
* StartID 开始ID
* array 目录数组
* stype 是否开启点击主菜单时同时开启子菜单
* url_level 限制链接的级别数
* level 当前菜单级别
*/
function tree_gethtml($StartID,$array,$stype='0',$url_level='1000',$level='1'){
	$ret='';
	$child = tree_getchild($StartID,$array);
	//print_r($child);
	if(is_array($child)){
		foreach($child as $id=>$a){
			$imup=0;
			if (is_array(tree_getchild($a['id'],$array))){
				$imup=1;
			}
			$ret.="<dd id=\"".$a['id']."\"";
			if ($stype==1){
				if ($imup==1){
					$ret.=" class=\"idd\"><img src=\"./images/im_off.gif\" id=\"img_".$a['id']."\" onclick=\"return openim(".$a['id'].");\">";
					if ($url_level && $url_level>=$level){
						$ret.="<a href=\"javascript:;\" onclick=\"return openim('".$a['id']."');\">";
					}
				}else{
					$ret.=">";
					if ($url_level && $url_level>=$level){
						$ret.="<a href=\"javascript:;\" onclick=\"return imguide('".$a['url']."','".$a['id']."');\">";
					}
				}
				$ret.=$a['name'];
				if ($url_level && $url_level>=$level){
					$ret.="</a>";
				}
				$ret.="</dd>\n";
			}else{
				if ($imup==1){
					$ret.=" class=\"idd\"><img src=\"./images/im_off.gif\" id=\"img_".$a['id']."\" onclick=\"return openim(".$a['id'].");\"";
				}
				$ret.=">";
				if ($url_level && $url_level<$level){
					$ret.=$a['name']."</dd>\n";
				}else{
					$ret.="<a href=\"javascript:;\" onclick=\"return imguide('".$a['url']."','".$a['id']."');\">".$a['name']."</a></dd>\n";
				}
			}
			if ($imup==1){
				$ret.="<dl class=\"imlist\" id=\"dl_".$a['id']."\" style=\"display:none;\">\n";
				$ret.=tree_gethtml($a['id'],$array,$stype,$url_level,$level+1);
				$ret.="</dl>\n";
			}
		}
	}
	if ($level<=1){
		$ret="<dl class=\"imlist\">\n".$ret."</dl>\n";
	}
	//$ret=substr($ret,1);
	return $ret;
}
?>
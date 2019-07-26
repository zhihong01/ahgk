<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/temp.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
        <title><?php echo($this_form['name']); ?></title>
        <script type="text/javascript" src="/media/js/Columns_index.js"></script>
        <script type="text/javascript" src="/media/js/MSClass.js"></script>
        <link href="/media/css/is-about.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="/media/js/jquery-1.8.3.min.js"></script>
	
		<script type="text/javascript" src="/media/js/utility.js"></script>
        <script type="text/javascript" src="/media/js/interactionVoteUtilDetail.js"></script>
        <script type="text/javascript" src="/media/js/interactionVoteUtilResult.js"></script>

         <style type="text/css">
            .input_date {
                background-image: url("./media/images/ico_calendar.png");
                background-position: right center;
                background-repeat: no-repeat;
                border: 1px solid #CCCCCC;
                height: 20px;
                line-height: 20px;
                padding: 3px;
            }

            .form-horizontal .control-label {
                float: none;
                min-width: 140px;
                width:auto;
                padding-top: 5px;
                text-align: left;
                background-color: #FFF9E3;
            }
            .form-horizontal .controls {
                margin-left: 20px;
            }
            .form-horizontal .control-group {
                margin-left: 20px;
            }
            .stat_data{
                float:right;
                margin-left: 20px;
                width: 180px;
                font-size: 12px;
            }
            .stat_slider{
                float:right;
                width:100px;
                margin-left: 100px;
            }
            .stat_data_1{
                float:left;
                margin-left: 40px;
                width: 220px;
            }
            .stat_slider_1{
                /*float:left;*/
                width:200px;
                margin-left: 180px;
            }
            .clear{
                clear:both;
            }
        </style>
       <script type="text/javascript">
            $(document).ready(function() {

                addSlider();
                var my_progress_bar_string;
                var pObj = null;
<?php
foreach ($stat_data as $field_id => $value_arr) {
    $field_total = $stat_data[$field_id]['field_total'];
    $field_type = $stat_data[$field_id]['field_type'];
    $field_data = $stat_data[$field_id]['field_data'];

    if (($field_type == "text") || ($field_type == "textarea")) {
        $status_data['total'] = $stat_data[$field_id]['field_data'][0]['total'];
        if ($voter_count > 0)
            $status_data['rate'] = round($status_data['total'] / $voter_count * 100, 2);
        else
            $status_data['rate'] = 0;
        ?>
                        $("#div_field_<?php echo($field_id); ?>").html("<span><?php echo($status_data['total']); ?>"
                                + "人填写，所占比例：<?php echo($status_data['rate']); ?>%</span>");
                        my_progress_bar_string = "<div class='progress progress-success'><div class='bar' style='width: <?php echo($status_data['rate']); ?>%'></div></div>";
                        $("#div_slider_field_<?php echo($field_id); ?>").html(my_progress_bar_string);

        <?php
    } else if (($field_type == "radio") || ($field_type == "select")) {
        foreach ($field_data as $key => $status_data) {
            ?>
                            var data_obj_str = "#div_field_<?php echo($field_id); ?>_<?php echo($key); ?>";
                            $(data_obj_str).html("<span><?php echo($status_data['total']); ?>"
                                    + "票，所占比例：<?php echo($status_data['rate']); ?>%</span>");
                            my_progress_bar_string = "<div class='progress progress-success'><div class='bar' style='width: <?php echo($status_data['rate']); ?>%'></div></div>";
                            $("#div_slider_field_<?php echo($field_id); ?>_<?php echo($key); ?>").html(my_progress_bar_string);

            <?php
        }
    } else if ($field_type == "checkbox") {
        foreach ($field_data as $key => $status_data) {
            ?>
                            var data_obj_str = "#div_field_<?php echo($field_id); ?>_<?php echo($key); ?>";
                            $(data_obj_str).html("<span><?php echo($status_data['total']); ?>"
                                    + "票，所占比例：<?php echo($status_data['rate']); ?>%</span>");
                            my_progress_bar_string = "<div class='progress progress-success'><div class='bar' style='width: <?php echo($status_data['rate']); ?>%'></div></div>";
                            $("#div_slider_field_<?php echo($field_id); ?>_<?php echo($key); ?>").html(my_progress_bar_string);

            <?php
        }
    }
}
?>
            })
        </script>
        <SCRIPT language=JavaScript>
            function doZoom(size) {
                var zoom = document.all ? document.all['zoom'] : document.getElementById('zoom');
                zoom.style.fontSize = size + 'px';

            }
        </SCRIPT>

        <!-- InstanceEndEditable -->
<link rel="stylesheet" type="text/css" href="/media/css/is-custom.css">
<script type="text/javascript" src="/media/js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="/media/js/MSClass.js"></script>
<script type="text/javascript" src="/media/js/Columns_index.js"></script>
<script type="text/javascript" src="/media/js/slider.js"></script>
<!-- InstanceBeginEditable name="head" -->
        <script type="text/javascript">
            $(document).ready(function() {
                $(".is-navtitle a").addClass('hover');
            });
        </script>
        <!-- InstanceEndEditable -->
</head>

<body>
	<div class="is-head">
		<div class="is-topbg">
			<div class="is-top">
				<span class="fl">
					<a href="http://www.hlbe.gov.cn/" target="_blank">广德县人民政府</a>
					|
					<a href="/" target="_blank">返回首页</a>
				</span>
				<span class="fr">
	      			<a onclick="SetHome(window.location)" href="javascript:void(0)">设为首页</a>
	      			|
	            	<a onclick="AddFavorite(window.location,document.title)" href="javascript:void(0)">加入收藏</a>
	            	|
	            	<a href="/content/channel/55fa8ce17f8b9af4092305d5/" target="_blank">联系我们</a>
	      		</span>
	      		<script type="text/javascript" language="javascript">
				    //加入收藏
				 
				        function AddFavorite(sURL, sTitle) {
				 
				            sURL = encodeURI(sURL); 
				        try{   
				 
				            window.external.addFavorite(sURL, sTitle);   
				 
				        }catch(e) {   
				 
				            try{   
				 
				                window.sidebar.addPanel(sTitle, sURL, "");   
				 
				            }catch (e) {   
				 
				                alert("加入收藏失败，请使用Ctrl+D进行添加,或手动在浏览器里进行设置.");
				 
				            }   
				 
				        }
				  
				    }
				 
				    //设为首页
				 
				    function SetHome(url){
				 
				        if (document.all) {
				 
				            document.body.style.behavior='url(#default#homepage)';
				 
				               document.body.setHomePage(url);
				 
				        }else{
				 
				            alert("您好,您的浏览器不支持自动设置页面为首页功能,请您手动在浏览器里设置该页面为首页!");
				 
				        }
				 
				    }
				</script>
			</div>
		</div>
		
		<div class="is-banner">
			<img src="/media/images/banner.png" width="1043" height="164">
	        <!-- <object data="/media/images/index.swf" type="application/x-shockwave-flash" height="164" width="1043">
	            <param name="movie" value="/media/images/index.swf">
	            <param name="wmode" value="transparent">
	        </object>  -->
		</div>

		<div class="is-nav">
			<ul>
				<li><a href="/" id="nav_home">首页</a></li>
				<li>
                	<a href="/content/channel/55ffcb646eed730979dac85b/" id="nav_55ffcb646eed730979dac85b">系统简介</a>
                    <div class="is-subnav" style="display:none;">
                         <div style="margin-left:40px;">{{BEGIN menu-55ffcb646eed730979dac85b_10_0_5 }}<a href="{{ url }}">{{ name }}</a>{{ END }}</div>
                    </div>
                </li>
				<li>
                	<a href="/content/channel/55ffcb646eed730979dac85c/" id="nav_55ffcb646eed730979dac85c">新闻中心</a>
                    <div class="is-subnav" style="display:none;">
                        <div style="margin-left:130px;">{{BEGIN menu-55ffcb646eed730979dac85c_10_0_5 }}<a href="{{ url }}">{{ name }}</a>{{ END }}</div>
                    </div>
              </li>
				<li>
                	<a href="/content/channel/55ffcb646eed730979dac85d/" id="nav_55ffcb646eed730979dac85d">政务公开</a>
                    <div class="is-subnav" style="display:none;">
                        <div style="margin-left:240px;">{{BEGIN menu-55ffcb646eed730979dac85d_10_0_5 }}<a href="{{ url }}">{{ name }}</a>{{ END }}</div>
                    </div>
                </li>
				<li>
                	<a href="/content/channel/55ffcb646eed730979dac85e/" id="nav_55ffcb646eed730979dac85e">信息栏目</a>
                    <div class="is-subnav" style="display:none;">
                        <div style="margin-left:410px;">{{BEGIN menu-55ffcb646eed730979dac85e_10_0_5 }}<a href="{{ url }}">{{ name }}</a>{{ END }}</div>
                    </div>
                </li>
                <li>
                	<a href="/content/channel/55ffcb646eed730979dac85f/" id="nav_55ffcb646eed730979dac85f">体育风采</a>
                    <div class="is-subnav" style="display:none;">
                        <div style="margin-left:540px;">{{BEGIN menu-55ffcb646eed730979dac85f_10_0_5 }}<a href="{{ url }}">{{ name }}</a>{{ END }}</div>
                    </div>
                </li>
				<li>
                	<a href="/content/channel/55ffcb646eed730979dac860/" id="nav_55ffcb646eed730979dac860">互动交流</a>
                    <div class="is-subnav" style="display:none;">
                        <div style="margin-left:560px;">{{BEGIN menu-55ffcb646eed730979dac860_10_0_5 }}<a href="{{ url }}">{{ name }}</a>{{ END }}</div>
                    </div>
                </li>
				<li>
                	<a href="/content/channel/55ffcb646eed730979dac861/" id="nav_55ffcb646eed730979dac861">网上办事</a>
                    <div class="is-subnav" style="display:none;">
                        <div style="margin-left:730px;">{{BEGIN menu-55ffcb646eed730979dac861_10_0_5 }}<a href="{{ url }}">{{ name }}</a>{{ END }}</div>
                    </div>
                </li>
                <li>
                	<a href="/content/channel/55ffcb646eed730979dac862/" id="nav_55ffcb646eed730979dac862">专题专栏</a>
                    <div class="is-subnav" style="display:none;">
                        <div style="margin-left:680px;">{{BEGIN menu-55ffcb646eed730979dac862_10_0_5 }}<a href="{{ url }}">{{ name }}</a>{{ END }}</div>
                    </div>
                </li>
			</ul>
		</div>
        <script type="text/javascript">
            $(document).ready(function() {
                $('.is-nav li ').mouseenter(function() {
                    jQuery(this).attr("class", "hover");
                    $(this).find('.is-subnav').stop(true, true).slideDown(0);//you can give it a speed
                });
                $('.is-nav li ').mouseleave(function() {
                    jQuery(this).attr("class", "");
                    $(this).find('.is-subnav').stop(true, true).slideUp(0);
                });
            });
		</script>

		<div class="is-notice">
	 		<div class="is-date"><script type="text/javascript" src="/media/js/time.js"></script></div>
			<div class="is-weather">
        		<iframe src="http://m.weather.com.cn/m/pn3/weather.htm?id=101081010T" width="156" height="20" marginwidth="0" marginheight="0" hspace="0" vspace="0" frameborder="0" scrolling="no" allowtransparency="no"></iframe>
            </div>
			<div class="is-search">
				<form method="GET" action="/index.php?c=search">
					<input type="text" id="search" name="keywords" placeholder="--站内搜索--" value="" class="is-searchtext fl" x-webkit-speech/>
					<input name="" type="submit" value="" class="searchbtn"  id="submit"/>
					<input name="c" type="hidden" value="search" />
			    </form>
			</div>
  		</div>
	</div>
	<!-- InstanceBeginEditable name="EditRegion3" -->
            <div class="is-mainbox">
              <div class="is-mainnr">
              <div class="is-line">
                <div class="is-subcbox">
                    <div class="is-newsbox">
                        <div class="is-postion"><div class="is-posbg">当前位置：<a href="/">网站首页</a> &gt; <a href="/content/channel/53edaa429a05c2883e2b2f6b/">公众互动</a> &gt; <a href="/interactVote/">网上调查</a> &gt; <span><?php echo($this_form['name']); ?></span></div></div>
                        <div class="is-contentbox" id="color_printsssss">

                                <div id="zoom" class="is-newscontnet">
                                 <?php if (!empty($this_form)) { ?>
                                 
                                 <div class="is-votetitle"><?php echo($this_form['name']); ?></div>
                            <div class="is-viewdate"> <span style="float:right;"> 截至日期：<?php echo($this_form['overdate']); ?></span><span >调查日期：<?php echo($this_form['startdate']); ?></span></div>
                            <div class="is-viewcontent" ><?php echo($this_form['description']); ?></div>
                            
                            <div class="is-viewdate"> <span style="float:right;">当前时间：<?php echo(date("Y-m-d H:i:s")); ?></span><span>投票人数：<?php echo($voter_count); ?></span></div>
								
                              <div class="is-votenr">
								<form class="form-horizontal">
								<?php  echo($this_form['content']); ?>
								</form>
							<?php } ?></div>
                              </div>

                    </div>
               </div>

           </div>
       <div class="clear-1"></div>
       </div>
       </div>
      </div>
     <!-- InstanceEndEditable -->

	

	<div class="is-foot">
	  <p>
			Copyright(C) 2017 广德县人民政府 All Rights Reserved.</br>
			Email:admin@hlbesports.gov.cn  技术支持：<a href="http://www.ishang.net/" target="_blank">商网信息</a>
		</p>
		<div class="img"><img src="/media/images/foot_icon.png" /></div>
	</div>
</body>
<!-- InstanceEnd --></html>
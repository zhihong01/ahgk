/*
* @description: 网站公共脚本
* @author: ishang_pan
* @update: ishang_pan (2018-10-16 17:53)
*/

//tab选项卡1
function isShowIndex(tab_id,div_id,who,num){
  for(var i =0;i < num;i++){
    $('#'+'dt'+who+i).css("display","none");
    $('#'+'sp'+who+i).removeClass();
  }
  $('#' + div_id).css("display","block");
  $('#' + tab_id).addClass("u-active");
}

//tab选项卡2
$(function(){
  $('.tit-switch').children().children("li").mouseover(function(){
    $(this).addClass('u-active').siblings().removeClass('u-active');
    $(this).parent().parent().siblings('.list-switch').hide().eq($(this).index()).show();
  });
});

//友情链接
$(function(){
  $(".m-friendtab").hover(function(){
    $(this).children("span").addClass("u-active").end().children("div.m-friendbd").show();
  },function(){
    $(this).children("span").removeClass("u-active").end().children("div.m-friendbd").hide();
  });
  $(".select").each(function(){
	  var s=$(this);
	  var z=parseInt(s.css("z-index"));
	  var dt=$(this).children("dt");
	  var dd=$(this).children("dd");
	  var _show=function(){dd.slideDown(200);dt.addClass("cur");s.css("z-index",z+1);};   //展开效果
	  var _hide=function(){dd.slideUp(200);dt.removeClass("cur");s.css("z-index",z);};    //关闭效果
	  dt.click(function(){dd.is(":hidden")?_show():_hide();});
	  dd.find("a").click(function(){dt.html($(this).html());_hide();});     //选择效果（如需要传值，可自定义参数，在此处返回对应的“value”值 ）
	  $("body").click(function(i){ !$(i.target).parents(".select").first().is(s) ? _hide():"";});
	})
});


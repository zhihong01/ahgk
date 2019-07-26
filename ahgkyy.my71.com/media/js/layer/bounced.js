//截取URL域名部份
var getHost = function(url) {
	var host = "null";
	if (typeof url == "undefined" || null == url) url = window.location.host;
	var regex = /^\/[\w\/]*/;
	var match = url.match(regex);
	if (typeof match != "undefined" && null != match) host = window.location.host;
	return host;
};
$(document).ready(function () {
	$("a").click(function(){
		var o = $(this);
		var url = o.attr('href');
		var host = getHost(url);
		if (host.indexOf(".gov.cn") == -1 && url.indexOf(".gov.cn") == -1 && url.indexOf("javascript") && url != "#"){
			$(this).attr('target','_self');
			$(this).attr('href','javascript:void(0)');
			var w = '480px';
			var h = '170px';
			if(window.screen.width < 768) { w = '90%'; h = '170px';}
			var cf = layer.confirm('<div style="margin-top:30px; font:16px;">您访问的链接即将离开“定远县人民政府网主站” 是否继续？</div>', {
				btn:[ '继续访问', '放弃' ],
				title: false,
				shade: 0.7,
				area: [w, h],
				cancel: function(index){ o.attr('href',url); }
			}, function() {
				window.open(url); 
				o.attr('href',url);
				layer.close(cf);
			}, function() {
				o.attr('href',url);
			});
		}
	});
	
	$('.m-select select').change(function(){ 
		var ob = $(this).children('option:selected');
		var url1 = ob.val();
		var host1 = getHost(url1);
		if (host1.indexOf(".gov.cn") == -1 && url1.indexOf(".gov.cn") == -1 && url1.indexOf("javascript") && url1 != "#"){
			ob.attr('target','_self');
			ob.attr('value','#');
			var w1 = '480px';
			var h1 = '170px';
			if(window.screen.width < 768) { w1 = '90%'; h1 = '170px';}
			var cf1 = layer.confirm('<div style="margin-top:30px; font:16px;">您访问的链接即将离开“定远县人民政府网主站” 是否继续？</div>', {
				btn:[ '继续访问', '放弃' ],
				title: false,
				shade: 0.7,
				area: [w1, h1],
				cancel: function(index){ ob.attr('value',url1); }
			}, function() {
				window.open(url1); 
				ob.attr('value',url1);
				layer.close(cf1);
			}, function() {
				ob.attr('value',url1);
			});
		}else{
			window.open(url1); 
		}
	}) 
	
});
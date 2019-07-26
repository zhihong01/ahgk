/*
漂浮广告1
*/
var it2;
var delay = 10;
var x = 50,y = 400; //初始坐标
var xin = true,yin = true;
var step = 1;

function ShowAd(){
	document.write("<div id='floatAd' style='position:absolute;z-index:999;'  align='right'>");
	document.write("<a href=''>"+floatCode+"</a>");
	document.write("<br /><a style='cursor:pointer; font-size:13px' onclick='hideAd();'><b>关闭×</b></a></div>");
	obj = document.getElementById("floatAd");
	it2= setInterval("floatAd()", delay);
	obj.onmouseover=function(){clearInterval(it2)};
	obj.onmouseout=function(){it2=setInterval("floatAd()", delay)};
}
function floatAd(){
	var L=T=0;
	var R = document.body.clientWidth-obj.offsetWidth;
	var B = document.body.clientHeight-obj.offsetHeight;
	obj = document.getElementById("floatAd");
	obj.style.left = x + document.body.scrollLeft + "px";
	obj.style.top = y + document.body.scrollTop + "px";
	x = x + step*(xin?1:-1);
	if (x < L) { xin = true; x = L};
	if (x > R) { xin = false; x = R};
	y = y + step*(yin?1:-1);
	if (y < T) { yin = true; y = T };
	if (y > B) { yin = false; y = B };
}
function hideAd(){
	document.getElementById("floatAd").style.display="none";
	clearInterval(it2);
}
/*
漂浮广告2
*/
var it21;
var delay2 = 60;
var x2 = 450,y2 = 160; //初始坐标
var w_l = true,w_r = true;
var step2 = 2;

// 判断浏览器
var Sys = {};
var ua = navigator.userAgent.toLowerCase();
var s;
(s = ua.match(/msie ([\d.]+)/)) ? Sys.ie = s[1] :
(s = ua.match(/firefox\/([\d.]+)/)) ? Sys.firefox = s[1] :
(s = ua.match(/chrome\/([\d.]+)/)) ? Sys.chrome = s[1] :
(s = ua.match(/opera.([\d.]+)/)) ? Sys.opera = s[1] :
(s = ua.match(/version\/([\d.]+).*safari/)) ? Sys.safari = s[1] : 0;
//以下进行测试
/*if (Sys.ie) document.write('IE: ' + Sys.ie);
if (Sys.firefox) document.write('Firefox: ' + Sys.firefox);
if (Sys.chrome) document.write('Chrome: ' + Sys.chrome);
if (Sys.opera) document.write('Opera: ' + Sys.opera);
if (Sys.safari) document.write('Safari: ' + Sys.safari);*/

function ShowAd2(){
	document.write("<div id='floatAd2' style='position:absolute;z-index:999;' align='right'>");
	document.write(floatCode2);
	document.write("<br /><a style='cursor:pointer;font-size:13px' onclick='hideAd2();'><b>关闭×</b></a></div>");
	obj = document.getElementById("floatAd2");
	it21= setInterval("floatAd2()", delay2);
	obj.onmouseover=function(){clearInterval(it21)};
	obj.onmouseout=function(){it21=setInterval("floatAd2()", delay2)};
}
function floatAd2(){
	var L=T=0;
	var R = document.body.clientWidth-obj.offsetWidth;
	var B =document.documentElement.clientHeight-obj.offsetHeight;
	obj = document.getElementById("floatAd2");
	obj.style.left = x2 + document.body.scrollLeft + "px";
	
	// 获取浏览器高度兼容
	if (Sys.chrome){
		obj.style.top = y2 + document.body.scrollTop + "px";
	}else{
		obj.style.top = y2 + document.documentElement.scrollTop + "px";
	}
	//alert(document.documentElement.offsetHeight);
	x2 = x2 + step2*(w_l?1:-1);
	if (x2 < L) { w_l = true; x2 = L};
	if (x2 > R) { w_l = false; x2 = R};
	y2 = y2 + step2*(w_r?1:-1);
	if (y2 < T) { w_r = true; y2 = T };
	if (y2 > B) { w_r = false; y2 = B };
}
function hideAd2(){
	document.getElementById("floatAd2").style.display="none";
	clearInterval(it21);
}
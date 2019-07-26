// fengjunfeng 搜索使用
function changefoucs(newnav) { document.getElementById(newnav).style.display = "block"; }
function changeblur(newnav) { document.getElementById(newnav).style.display = "none"; }
function changeinput(newid) { if (newid.value != "") { newid.value = ""; } }
function blurinput(newid) { if (newid.value == "") { newid.value = "请输入关键词" } }

//功能1  无障碍工具条生成代码
function zoombig() {
    document.getElementById("zoom").style.border = "1px solid red";
    document.getElementById("zoom").style.fontSize = "24px";
    document.getElementById("zoom").style.lineHeight = "26px";
    document.getElementById("zoom").style.position = "absolute";
    document.getElementById("zoom").style.width = "120px";
    document.getElementById("zoom").style.height = "26px";
    document.getElementById("zoom").style.zIndex = "10";
    document.getElementById("zoom").style.display = "block";
    document.getElementById("zoom").style.marginTop = "-5px";
    document.getElementById("zoom").style.marginleft = "-5px";
    document.getElementById("zoom").style.backgroundColor = "#FFF";
}
function zoomsmall() {
    document.getElementById("zoom").style.border = "";
    document.getElementById("zoom").style.fontSize = "";
    document.getElementById("zoom").style.lineHeight = "";
    document.getElementById("zoom").style.position = "";
    document.getElementById("zoom").style.width = "";
    document.getElementById("zoom").style.height = "";
    document.getElementById("zoom").style.zIndex = "";
    document.getElementById("zoom").style.display = "";
    document.getElementById("zoom").style.backgroundColor = "";
    document.getElementById("zoom").style.marginTop = "";
    document.getElementById("zoom").style.marginleft = "";
}
function zoomthis() {

    document.getElementById('zoomthis').style.width = "140px"
    document.getElementById('zoomthis').style.height = "48px"

    document.getElementById('zoomthis').style.position = "absolute";
    document.getElementById('zoomthis').style.zIndex = 99;
}
function zoomthat() {
    document.getElementById('zoomthis').style.width = "70px"
    document.getElementById('zoomthis').style.height = "24px"
    document.getElementById('zoomthis').style.position = "static";
}
var newLineText = "";
newLineText = newLineText + "<a id=\"fzgjt1\" href='#' style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF'>无障碍浏览工具已开启</a>";
if (getCookie('skyest') == 3) {
    newLineText = newLineText + "<button style='border:1px solid #be0707; border-radius: 5px; margin:15px 15px; background-color:#FFF; color:#be0707; padding:10px 0 10px 45px; font-size:20px; font-weight:bold; background: url(/media/images/ico-wza.png) 14px 8px no-repeat;'  onclick=\"window.location.reload();SetCookie('skyest', '2')\">切换可视模式</button>";
} else {
    newLineText = newLineText + "<span id=\"cwbtd\"><button style='border:1px solid #be0707; border-radius: 5px; margin:15px 15px; background-color:#FFF; color:#be0707; padding:10px 10px 10px 45px; font-size:20px; font-weight:bold; background: url(/media/images/ico-wza.png) 14px 8px no-repeat;' onclick=\"changeStyle();SetCookie('skyest', '3')\">纯文本通道</button></span>";
}
newLineText = newLineText + "<button onclick=\"changeFontSize(this)\" style='border:1px solid #be0707; border-radius: 5px; margin:15px 15px; background-color:#FFF; color:#be0707; padding:10px 10px 10px 45px; font-size:20px; font-weight:bold; background: url(/media/images/ico-wza.png) -170px 8px no-repeat;'>文字放大</button>";
newLineText = newLineText + "<button onclick=\"changeFontSmall(this)\" style='border:1px solid #be0707; border-radius: 5px; margin:15px 15px; background-color:#FFF; color:#be0707; padding:10px 10px 10px 45px; font-size:20px; font-weight:bold; background: url(/media/images/ico-wza.png) -348px 8px no-repeat;'>文字缩小</button>";
newLineText = newLineText + "<button onclick=\"changeBack(this)\" style='border:1px solid #be0707; border-radius: 5px; margin:15px 15px; background-color:#FFF; color:#be0707; padding:10px 10px 10px 45px; font-size:20px; font-weight:bold; background: url(/media/images/ico-wza.png) -520px 8px no-repeat;'>高对比度</button>";
newLineText = newLineText + "<button onclick=\"oDownLine(arguments[0])\" id=\"guidesbutton\" type=\"button\" value=\"开启辅助线\" title=\"开启辅助线\" style='border:1px solid #be0707; border-radius: 5px; margin:15px 15px; background-color:#FFF; color:#be0707; padding:10px 10px 10px 45px; font-size:20px; font-weight:bold; background: url(/media/images/ico-wza.png) -693px 8px no-repeat;'>辅助线</button>";
if (navigator.userAgent.indexOf("MSIE") > 0) {
    newLineText = newLineText + "<button onclick=\"changeZoom('big')\" style='border:1px solid #C80000;margin:5px 1px;background-color:#FFF;	color:#C80000;padding:0px 1px;font-size:16px;font-weight:bold;'>界面放大</button>";
    newLineText = newLineText + "<button onclick=\"changeZoom('small')\" style='border:1px solid #C80000;margin:5px 1px;background-color:#FFF;	color:#C80000;padding:0px 1px;font-size:16px;font-weight:bold;'>界面缩小</button>";
}
//newLineText = newLineText + "<button onclick=\"gowza()\" style='border:1px solid #C80000;margin:5px 1px;background-color:#FFF;	color:#C80000;padding:0px 1px;font-size:16px;font-weight:bold;'>无障碍操作说明</button>";
newLineText = newLineText + "<button onclick=\"resetToolbar()\" style='border:1px solid #be0707; border-radius: 5px; margin:15px 15px; background-color:#FFF; color:#be0707; padding:10px 10px 10px 45px; font-size:20px; font-weight:bold; background: url(/media/images/ico-wza.png) -836px 8px no-repeat;'>重置</button>";
newLineText = newLineText + "<button onclick=\"colsenav1()\" style='border:1px solid #be0707; border-radius: 5px; margin:15px 15px; background-color:#FFF; color:#be0707; padding:10px 10px 10px 45px; font-size:20px; font-weight:bold;background: url(/media/images/ico-wza.png) -982px 8px no-repeat;'>关闭</button>";

newLineText = newLineText + "<div id=\"lineX\" style='border-top:5px red solid;width:100%;position:absolute;z-index:200;left:0px;display:none;line-height:0px;font-size:0px;height:1px;'></div>";
newLineText = newLineText + "<div id=\"lineY\" style='border-left:5px red solid;height:100%;position:absolute;z-index:220;top:0px;left:0px;display:none;'></div>";
//操作说明链接地址
function gowza() {
    window.open("http://www.shanghai.gov.cn/shanghai/node2314/node27091/userobject21ai550385.html");
}
//***创建工具条的过程
function creatWcagNav() {
    if (!document.getElementById("wcagnav")) {

        NewDiv1 = document.createElement("div");
        NewDiv1.setAttribute("id", "wcagnav");
        NewDiv1.innerHTML = newLineText;

        document.body.insertBefore(NewDiv1, document.getElementById('skip'))
        NewDiv1.style.display = 'none';
        var Owcreatdiv = document.createElement("div");
        Owcreatdiv.innerHTML = "<a href=\"javascript:;\" id=\"gjtygb\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' tabindex=\"-1\" title=\"工具条已关闭\" >工具条已关闭</a>"
        document.getElementById('wcagnav').parentNode.insertBefore(Owcreatdiv, document.getElementById('wcagnav'));
    }
}
//描述：生成一段html文本。展现工具条辅助按钮。调用变量 newLineText;

//工具条辅助功能 
//字体大小创建
//此函数用来修改页面的字体大小
var iFontSize = 16;
var zoomValue = 1;
var zoomKey = true;
function changeFontSize(newSize) {
    //搜集所有元素名称 aAllElement
    var aAllElement = document.getElementById("container").getElementsByTagName("*");
  
	if (zoomValue >= 1 && zoomValue <= 1.2 && zoomKey) {
		zoomValue = zoomValue + 0.1;
		
		if (navigator.userAgent.indexOf('Firefox') >= 0) {  
			document.getElementsByTagName('html')[0].setAttribute("style","-moz-transform:scale(" + zoomValue +");-moz-transform-origin:top;");
		} else {  
			document.getElementsByTagName('html')[0].style.zoom = zoomValue;
		} 
		
		
} 
	else if (!zoomKey) {
		if (iFontSize <= 18) {
			
			iFontSize = iFontSize + 2;
			for (i = 0; i < aAllElement.length; i++) {
				aAllElement[i].style.fontSize = iFontSize + "px";
			}
		}
		
		if (iFontSize == 16) {
			zoomKey = true;
			return false;
		}
	
	
	}

	
	
	
	
}
function changeFontSmall(newSize) {
    //搜集所有元素名称 aAllElement
    var aAllElement = document.getElementById("container").getElementsByTagName("*");
	
	
	if (zoomValue > 1) {
		zoomKey = true;
		zoomValue = zoomValue - 0.1;
		if (navigator.userAgent.indexOf('Firefox') >= 0) {  
			document.getElementsByTagName('html')[0].setAttribute("style","-moz-transform:scale(" + zoomValue +");-moz-transform-origin:top;");
		} else {  
			document.getElementsByTagName('html')[0].style.zoom = zoomValue;
		} 
	}
	else {
		
		 if (iFontSize >= 14) {
		   iFontSize = iFontSize - 2; 
		   zoomKey = false;
		   for (i = 0; i < aAllElement.length; i++) {
			   aAllElement[i].style.fontSize = iFontSize + "px";
			}
		}   
	}

   
}
//此函数用来执行页面的背景和文字颜色的变换。
var iChangeBack = 1;
function changeBack(newBack) {
    //搜集所有元素名称 aAllElement

    var aAllElement = document.getElementById("container").getElementsByTagName("*")
    //循环更改所有的背景和字体
    if (iChangeBack == 1) {
        document.getElementById("container").style.backgroundColor = "#000";
        document.getElementById("container").style.color = "#FFF";
        for (i = 0; i < aAllElement.length; i++) {
            //黑白对比度
            aAllElement[i].style.backgroundColor = "#000";
            aAllElement[i].style.color = "#FFF"
            newBack.innerHTML = "还原对比度";
            iChangeBack = 2;
        }
    } else if (iChangeBack == 2) {
        document.getElementById("container").style.backgroundColor = "";
        document.getElementById("container").style.color = "";
        for (i = 0; i < aAllElement.length; i++) {
            //黑白对比度

            aAllElement[i].style.backgroundColor = "";
            aAllElement[i].style.color = ""
            newBack.innerHTML = "高对比度";
            iChangeBack = 1;
        }
    }
}
//辅助线 移动工具.   功能描述：此函数执行后，页面出现2条辅助功能线，用来校对页面文本。
var bDecision = true;
function oDownLine(e) {

    var e = window.event ? window.event : e;
    if (bDecision) {
        document.getElementById('lineX').style.display = 'block'
        document.getElementById('lineY').style.display = 'block'
        document.onmousemove = oMoveLine;
        bDecision = false;
    }
    else {
        document.getElementById('lineX').style.display = 'none'
        document.getElementById('lineY').style.display = 'none'
        document.onmousemove = "";
        bDecision = true;
    }
}
//此函数用来判断当前鼠标位置。
function oMoveLine(e) {
    var e = window.event ? window.event : e;
	var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
	var scrollLeft = document.documentElement.srollLeft || document.body.scrollLeft;
	 
    document.getElementById('lineX').style.top = e.clientY + 10 + scrollTop + "px";
    document.getElementById('lineY').style.height = scrollTop + document.documentElement.clientHeight + 'px';
    document.getElementById('lineY').style.left = e.clientX + 10 + scrollLeft + "px";
}
//
//页面放大缩小函数 此函数只支持ie.
var ZoomCountTeam = 1;
function changeZoom(ZoomText) {
    if (ZoomText == "small") {
        ZoomCountTeam = ZoomCountTeam - 0.5;
        if (ZoomCountTeam <= 1) { ZoomCountTeam = 1 }
        document.getElementById("container").style.zoom = ZoomCountTeam;

    } else {
        ZoomCountTeam = ZoomCountTeam + 0.5;
        if (ZoomCountTeam <= 1) { ZoomCountTeam = 1 }
        document.getElementById("container").style.zoom = ZoomCountTeam;
    }
}
//图片及框架替换函数 将图片替换为文字。将框架内容抓取为文本。
var aAllAlt = new Array;
var aAlliframeText = new Array;
function changeImage() {
    var newtag1 = document.getElementById("container").getElementsByTagName("*");
    for (h = 0; h < newtag1.length; h++) {
        newtag1[h].removeAttribute("style");
    }

    var AlliFrame = document.getElementById("container").getElementsByTagName("iframe");
    firstCount = AlliFrame.length;
    //框架
    for (i = 0; i < firstCount; i++) {
        try {
            aAlliframeText[i] = AlliFrame[i].contentWindow.document.getElementsByTagName("body")[0].innerHTML;
        } catch (e) { }
    }

    for (i = 0; i < firstCount; i++) {
        var eDiv = document.createElement("div");
        if (aAlliframeText[i]) {
            eDiv.innerHTML = aAlliframeText[i];
        }
        if (eDiv.innerHTML != "") {
            AlliFrame[0].parentNode.replaceChild(eDiv, AlliFrame[0])
        }
    }
    var aAllImage = document.getElementsByTagName("img");
    //图片
    for (i = 0; i < aAllImage.length; i++) {

        aAllImage[i].setAttribute("src", "");
    }
    newCount = aAllImage.length;
    //提取所有图片的alt值;存放到aAllAlt的数组中.
    for (i = 0; i < newCount; i++) {
        aAllAlt[i] = document.createTextNode(aAllImage[i].getAttribute("alt"));
    }
    //替换所有的img;
    for (i = 0; i < newCount; i++) {
        var eSpan = document.createElement("span");
        eSpan.style.padding = 5 + "px";
        eSpan.appendChild(aAllAlt[i]);
        //		alert(aAllImage[0].parentNode.replaceChild);
        aAllImage[0].parentNode.replaceChild(eSpan, aAllImage[0]);
    }
}
//纯文本模式执行函数
var bNewTrue = true;
function changeStyle() {
    changePos = function() { };
    changeImage();
    var aAllStyle = document.getElementsByTagName("link");
    if (bNewTrue) {
        bNewTrue = false;
        changeImage();
        for (i = 0; i < aAllStyle.length; i++) {
            aAllStyle[i].setAttribute("href", "/css/nav.css");
            kqtrue = false;
        }
        document.getElementById("wcagnav").style.display = "block";
        document.getElementById("cwbtd").innerHTML = "<button style='border:1px solid #be0707; border-radius: 5px; margin:15px 15px; background-color:#FFF; color:#be0707; padding:10px 10px 10px 45px; font-size:20px; font-weight:bold; background: url(../media/images/ico-wza.png) 14px 8px no-repeat;' onclick=\"window.location.reload();SetCookie('skyest', '2')\">切换为可视模式</button>"
    } else {
        window.location.reload();
    }
}
//cookie功能 记录文本通道
//cookie模式记录
function SetCookie(name, value)//两个参数，一个是cookie的名子，一个是值
{
    var Days = 30; //此 cookie 将被保存 30 天
    var exp = new Date();    //new Date("December 31, 9998");
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
}
function getCookie(name)//取cookies函数        
{
    var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    if (arr != null) return unescape(arr[2]); return null;
}
function delCookie(name)//删除cookie
{
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval = getCookie(name);
    if (cval != null) document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
}
//无障碍工具条开启函数

function OpenNav() {
    OpenWcagNav();
    //样式表链接器
}
//打开工具条和关闭工具条
var bClose = false;
function OpenWcagNav() {
    creatWcagNav();
    if (!bClose) {
        document.getElementById("wcagnav").style.display = "none";
        bClose = true;
    } else {
        document.getElementById("wcagnav").style.display = "block";
        bClose = false;
    }
}
//当页面打开完毕，执行以下函数。
window.onload = function() {
   var clearhash = function()
{
if (window.location.hash) {
        window.location.hash = "";
        var temp = "";
        var texts = window.location.href;
        texts = texts.substr(0, texts.length - 1);
        window.location.href = texts;
//        alert(texts);
    }

};
    //判断cookie，以确定是否执行页面纯文本的函数；
    //解决首页焦点问题
    try {
        OpenNav(); //开启工具条
        skipToMain(); //设置主要内容的朗读功能
        yyskip(); //设置其他内容的朗读功能
        bb(); //设置栏目导航的朗读功能
        if (document.getElementById("footer")) {
            NewDiv2 = document.createElement("<div>");
            //NewDiv2.setAttribute("id","t"+j);
            NewDiv2.setAttribute("className", "lanmutiaozhuan");
            NewDiv2.setAttribute("classname", "lanmutiaozhuan");
            NewDiv2.setAttribute("class", "lanmutiaozhuan");
            //	NewDiv2.setAttribute("style","width: 0; height: 0; overflow: hidden;");
            NewDiv2.innerHTML = "<A class=\"lmmz\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' href=\"#t" + j + "\" id=\"t" + j + "\" tabindex=\"-1\" title=\"当前栏目为底部声明栏目\">当前栏目为底部声明栏目</A>";
            document.getElementById("footer").parentNode.insertBefore(NewDiv2, document.getElementById("footer"));
            j++;
        }
        xTeam(); //栏目跳转记忆功能
    } catch (e) { }; //开启无障碍工具条
    try {
        if (getCookie('skyest') == 3) {
            changeStyle();
            document.getElementById("wcagnav").style.display = "block";
        }
        if (getCookie('skyest1') == 3) {
            OpenWcagNav();
            kqtrue = false;
        }
    } catch (e) { };
    try { skiptomain(); } catch (e) { };
    //创建工具条
    //创建顶部弹出导航
    //创建快捷键
    //左侧栏ajax处理 .
    document.body.setAttribute("onkeydown", "hotKey(event)");
    document.body.onkeydown = function() {
        hotKey(event);
    }
    oFloatDiv();
    window.onscroll = function() {
        oFloatDiv();
    };
    document.body.onresize = function() {
        oFloatDiv();

    };
    document.body.setAttribute("onresize", "oFloatDiv()");
    document.body.setAttribute("onscroll", "oFloatDiv()");
}
/*跳过导航函数*/
function skiptomain() {

    if (document.getElementById("head") && document.getElementById("skip")) {
        document.getElementById("skip").innerHTML = "<ul style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF'><li><a href=\"#content\" title=\"跳过导航\" onclick=\"skipToMain1()\">跳过导航</a></li><li><a href=\"\/wza\/index.html\" id='wzaczsm' title=\"无障碍操作说明\" >无障碍操作说明</a></li><li><a href=\"#\" onclick=\"kqNav()\" title=\"无障碍浏览工具\" ><img src=\"/images/shanghai_s/index_0.gif\" alt=\"无障碍浏览工具\" width=\"82\" height=\"24\" title=\"无障碍浏览工具\" /></a></li></ul>"

    } else if (document.getElementById("skip")) {
        document.getElementById("skip").innerHTML = "<ul style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF'><li><a href=\"#\" title=\"跳转到正文内容区域\" onkeypress=\"skipToMain1()\">跳转到正文内容区域</a></li><li><a href=\"\/wza\/index.html\" title=\"无障碍操作说明\" >无障碍操作说明</a></li><li><a href=\"#\" onclick=\"kqNav()\" title=\"无障碍浏览工具\" ><img src=\"/images/shanghai_s/index_0.gif\" alt=\"无障碍浏览工具\" width=\"82\" height=\"24\" title=\"无障碍浏览工具\" /></a></li></ul>"
    }
}
//栏目跳转
var hidenav = true;
var c = 1;

var j = 1;
var k1 = 3;
var k2 = 3;
var allTeam = new Array;
function bb() {
    var nowtag = document.getElementsByTagName("*");
    if (document.getElementById("mainnavigation")) {

        NewDiv2 = document.createElement("<div>");
        //NewDiv2.setAttribute("id","t"+j);
        NewDiv2.setAttribute("className", "lanmutiaozhuan");
        NewDiv2.setAttribute("classname", "lanmutiaozhuan");
        NewDiv2.setAttribute("class", "lanmutiaozhuan");
        //	NewDiv2.setAttribute("style","width: 0; height: 0; overflow: hidden;");
        NewDiv2.innerHTML = "<A class=\"lmmz\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' href=\"#t" + j + "\" id=\"t" + j + "\" tabindex=\"-1\" title=\"当前栏目为站点栏目导航\">当前栏目为站点栏目导航</A>";
        document.getElementById("mainnavigation").parentNode.insertBefore(NewDiv2, document.getElementById("mainnavigation"));
        j++;
    }
    if (document.getElementById("leftcolumn")) {
        NewDiv2 = document.createElement("<div>");
        //NewDiv2.setAttribute("id","t"+j);
        NewDiv2.setAttribute("className", "lanmutiaozhuan");
        NewDiv2.setAttribute("classname", "lanmutiaozhuan");
        NewDiv2.setAttribute("class", "lanmutiaozhuan");
        //	NewDiv2.setAttribute("style","width: 0; height: 0; overflow: hidden;");
        NewDiv2.innerHTML = "<A class=\"lmmz\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' href=\"#t" + j + "\" id=\"t" + j + "\" tabindex=\"-1\" title=\"当前栏目为左侧栏目导航\">当前栏目为左侧栏目导航</A>";
        document.getElementById("leftcolumn").parentNode.insertBefore(NewDiv2, document.getElementById("leftcolumn"));
        j++;
    }

    for (i = 0; i < nowtag.length; i++) {
        if (k1 == k2) {

            if (nowtag[i].nodeName == "H3" || nowtag[i].nodeName == "H4" || nowtag[i].nodeName == "H5") {

                NewDiv2 = document.createElement("<div>");
                //	NewDiv2.setAttribute("id","t"+j);
                NewDiv2.setAttribute("className", "lanmutiaozhuan");
                NewDiv2.setAttribute("classname", "lanmutiaozhuan");
                NewDiv2.setAttribute("class", "lanmutiaozhuan");
                if (document.getElementById("mainpage") && (nowtag[i].nodeName == "H4" || nowtag[i].nodeName == "H5")) {
                    return false;
                }
                if (!nowtag[i].childNodes[0]) { }
                else if (nowtag[i].childNodes[0].nodeName == "IMG") {
                    if (nowtag[i].nodeName == "H3") {
                        NewDiv2.innerHTML = "<A class=\"lmmz\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id=\"t" + j + "\" href=\"#t" + j + "\" tabindex=\"-1\" title=\"" + nowtag[i].childNodes[0].getAttribute("alt") + "栏目分组\">" + nowtag[i].childNodes[0].getAttribute("alt") + "1级栏目分组</A>";
                        j++;
                    }
                    if (nowtag[i].nodeName == "H4") {
                        NewDiv2.innerHTML = "<A class=\"lmmz\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id=\"t" + j + "\" href=\"#t" + j + "\" tabindex=\"-1\" title=\"" + nowtag[i].childNodes[0].getAttribute("alt") + "栏目分组\">" + nowtag[i].childNodes[0].getAttribute("alt") + "2级栏目分组</A>";
                        j++;
                    }
                    if (nowtag[i].nodeName == "H5") {
                        NewDiv2.innerHTML = "<A class=\"lmmz\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id=\"t" + j + "\" href=\"#t" + j + "\" tabindex=\"-1\" title=\"" + nowtag[i].childNodes[0].getAttribute("alt") + "栏目分组\">" + nowtag[i].childNodes[0].getAttribute("alt") + "3级栏目分组</A>";
                        j++;
                    }
                } else if (nowtag[i].childNodes[0].nodeName == "A") {

                    //NewDiv2.innerHTML="<A class=\"lmmz\" id=\"t"+j+"\" href=\"#t"+j+"\"  title=\""+nowtag[i].childNodes[0].childNodes[0].nodeValue+"栏目分组\">"+nowtag[i].childNodes[0].childNodes[0].nodeValue+"栏目分组</A>";			
                }
                else if (nowtag[i].childNodes[0].nodeName == "SPAN") {
                    if (nowtag[i].nodeName == "H3") {
                        NewDiv2.innerHTML = "<A class=\"lmmz\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id=\"t" + j + "\" href=\"#t" + j + "\" tabindex=\"-1\" title=\"" + nowtag[i].childNodes[0].childNodes[0].nodeValue + "栏目分组\">" + nowtag[i].childNodes[0].childNodes[0].nodeValue + "1级栏目分组</A>";
                        j++;
                    }
                    if (nowtag[i].nodeName == "H4") {
                        NewDiv2.innerHTML = "<A class=\"lmmz\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id=\"t" + j + "\" href=\"#t" + j + "\" tabindex=\"-1\" title=\"" + nowtag[i].childNodes[0].childNodes[0].nodeValue + "栏目分组\">" + nowtag[i].childNodes[0].childNodes[0].nodeValue + "2级栏目分组</A>";
                        j++;
                    }
                    if (nowtag[i].nodeName == "H5") {
                        NewDiv2.innerHTML = "<A class=\"lmmz\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id=\"t" + j + "\" href=\"#t" + j + "\" tabindex=\"-1\" title=\"" + nowtag[i].childNodes[0].childNodes[0].nodeValue + "栏目分组\">" + nowtag[i].childNodes[0].childNodes[0].nodeValue + "3级栏目分组</A>";
                        j++;
                    }
                }
                else {
                    if (nowtag[i].nodeName == "H3") {
                        NewDiv2.innerHTML = "<A class=\"lmmz\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id=\"t" + j + "\" href=\"#t" + j + "\" tabindex=\"-1\" title=\"" + nowtag[i].childNodes[0].nodeValue + "栏目分组\">" + nowtag[i].childNodes[0].nodeValue + "1级栏目分组</A>";
                        j++;
                    }
                    if (nowtag[i].nodeName == "H4") {
                        NewDiv2.innerHTML = "<A class=\"lmmz\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id=\"t" + j + "\" href=\"#t" + j + "\" tabindex=\"-1\" title=\"" + nowtag[i].childNodes[0].nodeValue + "栏目分组\">" + nowtag[i].childNodes[0].nodeValue + "2级栏目分组</A>";
                        j++;
                    }
                    if (nowtag[i].nodeName == "H5") {
                        NewDiv2.innerHTML = "<A class=\"lmmz\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id=\"t" + j + "\" href=\"#t" + j + "\" tabindex=\"-1\" title=\"" + nowtag[i].childNodes[0].nodeValue + "栏目分组\">" + nowtag[i].childNodes[0].nodeValue + "3级栏目分组</A>";
                        j++;
                    }
                }

                nowtag[i].parentNode.insertBefore(NewDiv2, nowtag[i])

                k2 = 1;
            }
        } else {
            k2++;
        }
    }
}
//记录各种需要的栏目跳转记忆

var allLinkTeam = Array();
var allLinkcount = 0;

function xTeam() {
    var AallLink = document.getElementsByTagName("a");

    for (i = 0; i < AallLink.length; i++) {

        if (AallLink[i].getAttribute("className") == "lmmz" || AallLink[i].getAttribute("className") == "lmtz1") {
            //alert(AallLink[i].getAttribute("id"))
            allLinkTeam[allLinkcount] = AallLink[i];
            allLinkcount++;
        }
    }

}
//万能快捷键 处理器 
var lmNum = 1;
var pdfq = 1;
var cde = 1;
var qh = 1;
//创建各种语音语义
// 竖线高度100%有bug
var sLine = "<div id=\"lineX\" style='border-top:5px red solid;width:100%;position:absolute;z-index:999999;left:0px;display:none;line-height:0px;font-size:0px;height:1px;'></div><div id=\"lineY\" style='border-left:5px red solid;height:100%;position:absolute;z-index:99999;top:0px;left:0px;display:none;'></div>"
var ccenter = "<a href='' class='lmtz1' style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id='centerzooe' tabindex='-1'>已跳转到主要内容区域</a>";
var ctop = "<a href='' class='lmtz1' style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id='topzooe' tabindex='-1'>已跳转到顶部内容区域</a>" + sLine;
var cbottom = "<a href='' class='lmtz1' style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id='bottomzooe' tabindex='-1'>已跳转到底部内容区域</a>";
var cleft = "<a href='' class='lmtz1' style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF'  id='leftzooe' tabindex='-1'>已跳转到左侧区域</a>";
var cconternt = "<a href='' class='lmtz1' style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id='zyzooe' tabindex='-1'>已跳转到正文内容区域</a>"


function yyskip() {

    var Onewcreatdiv = document.createElement("div");
    if (document.getElementById("skiptomain")) {

    } else if (document.getElementById("maincontent")) {
        Onewcreatdiv = document.createElement("div");
        Onewcreatdiv.innerHTML = cconternt;
        document.getElementById('maincontent').parentNode.insertBefore(Onewcreatdiv, document.getElementById('maincontent'))

    } else if (document.getElementById("content")) {
        Onewcreatdiv = document.createElement("div");
        Onewcreatdiv.innerHTML = cconternt;
        document.getElementById('content').parentNode.insertBefore(Onewcreatdiv, document.getElementById('content'))
    } else if (document.getElementById("main")) {
        Onewcreatdiv = document.createElement("div");
        Onewcreatdiv.innerHTML = cconternt;
        document.getElementById('main').parentNode.insertBefore(Onewcreatdiv, document.getElementById('main'))
    }

    if (document.getElementById('head')) {
        Onewcreatdiv = document.createElement("div");
        Onewcreatdiv.innerHTML = ctop;
        document.getElementById('head').parentNode.insertBefore(Onewcreatdiv, document.getElementById('head'))
    }
    if (document.getElementById('header')) {
        Onewcreatdiv = document.createElement("div");
        Onewcreatdiv.innerHTML = ctop;
        document.getElementById('header').parentNode.insertBefore(Onewcreatdiv, document.getElementById('header'))
    }
    if (document.getElementById('leftcolumn')) {
        Onewcreatdiv = document.createElement("div");
        Onewcreatdiv.innerHTML = cleft;
        document.getElementById('leftcolumn').parentNode.insertBefore(Onewcreatdiv, document.getElementById('leftcolumn'))
    }
    if (document.getElementById('content')) {
        Onewcreatdiv = document.createElement("div");
        Onewcreatdiv.innerHTML = ccenter;
        document.getElementById('content').parentNode.insertBefore(Onewcreatdiv, document.getElementById('content'))
    }
    if (document.getElementById('footer')) {
        Onewcreatdiv = document.createElement("div");
        Onewcreatdiv.innerHTML = cbottom;
        document.getElementById('footer').parentNode.insertBefore(Onewcreatdiv, document.getElementById('footer'))
    }
}
var Fshowimg = 1;
//捕获快捷按钮
function hotKey(event) {
    if (navigator.userAgent.indexOf("MSIE") > 0) {
        var e = window.event ? window.event : e;
        var theEvent = window.event || e;
        var sKeycode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
    } else {
        var sKeycode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
    }
      //  alert(sKeycode);
    //    alert(code);
    if (event.altKey) {
        if (lmNum == 1) {
            bb();//开启栏目导航功能
            lmNum++;
        }
        if (sKeycode == 85) {
            if (Fshowimg == 1) {
                document.getElementById("img").style.display = "none";
                Fshowimg = 2;
            } else {
                document.getElementById("img").style.display = "block";
                Fshowimg = 1;
            }

        }
        //朱教授 提议的快捷键 

        if (sKeycode == 72) {
            if (document.getElementById('mainnavigation')) {
                window.location = "#mainnavigation";
            }
        }
        if (sKeycode == 75) {
            if (document.getElementById('head')) {
                window.location = "#topzooe";
                for (i = 0; i < allLinkTeam.length; i++) {
                    if (allLinkTeam[i].getAttribute("id") == "topzooe") {
                        //垃圾函数开始
                        if (allLinkTeam[i + 1].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 1].getAttribute("id").slice(1);

                        } else if (allLinkTeam[i + 2].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 2].getAttribute("id").slice(1);

                        } else {
                            m = allLinkTeam[i + 3].getAttribute("id").slice(1);
                        }
                        //垃圾函数结束
                    }
                }
            }
            if (document.getElementById('header')) {
                window.location = "#topzooe";
                for (i = 0; i < allLinkTeam.length; i++) {
                    if (allLinkTeam[i].getAttribute("id") == "topzooe") {
                        //垃圾函数开始
                        if (allLinkTeam[i + 1].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 1].getAttribute("id").slice(1);

                        } else if (allLinkTeam[i + 2].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 2].getAttribute("id").slice(1);
                        } else {
                            m = allLinkTeam[i + 3].getAttribute("id").slice(1);
                        }
                        //垃圾函数结束
                    }
                }
            }
        }
        if (sKeycode == 76) {
            if (document.getElementById('leftcolumn')) {
                window.location = "#leftzooe";

                for (i = 0; i < allLinkTeam.length; i++) {
                    if (allLinkTeam[i].getAttribute("id") == "leftzooe") {
                        //垃圾函数开始
                        if (allLinkTeam[i + 1].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 1].getAttribute("id").slice(1);
                        } else if (allLinkTeam[i + 2].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 2].getAttribute("id").slice(1);

                        } else {
                            m = allLinkTeam[i + 3].getAttribute("id").slice(1);
                        }
                        //垃圾函数结束
                    }

                }
            }
        }
        if (sKeycode == 77) {//M按键
            if (document.getElementById('content')) {
                window.location = "#centerzooe";

                for (i = 0; i < allLinkTeam.length; i++) {
                    if (allLinkTeam[i].getAttribute("id") == "centerzooe") {
                        //垃圾函数开始
                        if (allLinkTeam[i + 1].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 1].getAttribute("id").slice(1);

                        } else if (allLinkTeam[i + 2].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 2].getAttribute("id").slice(1);

                        } else {
                            m = allLinkTeam[i + 3].getAttribute("id").slice(1);
                        }
                        //垃圾函数结束
                    }
                }
            }
        }
        if (sKeycode == 66) {
            if (document.getElementById('footer')) {
                window.location = "#bottomzooe";

                for (i = 0; i < allLinkTeam.length; i++) {

                    if (allLinkTeam[i].getAttribute("id") == "bottomzooe") {

                        //垃圾函数开始
                        m = allLinkTeam[i + 1].getAttribute("id").slice(1);

                        //垃圾函数结束
                    }
                }
            }
        }
        if (sKeycode == 67) {
            if (document.getElementById("skiptomain")) {
                //	document.getElementById("skiptomain").innerHTML="<a href=\"#\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;' id=\"skiptomain1\" >已跳转到正文内容区域</a>";
                window.location = "#skiptomain1";

                for (i = 0; i < allLinkTeam.length; i++) {
                    if (allLinkTeam[i].getAttribute("id") == "skiptomain1") {
                        //垃圾函数开始
                        if (allLinkTeam[i + 1].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 1].getAttribute("id").slice(1);

                        } else if (allLinkTeam[i + 2].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 2].getAttribute("id").slice(1);

                        } else {
                            m = allLinkTeam[i + 3].getAttribute("id").slice(1);
                        }
                        //垃圾函数结束	
                    }

                }
            } else if (document.getElementById("maincontent")) {
                window.location = "#zyzooe";

                for (i = 0; i < allLinkTeam.length; i++) {
                    if (allLinkTeam[i].getAttribute("id") == "zyzooe") {
                        //垃圾函数开始
                        if (allLinkTeam[i + 1].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 1].getAttribute("id").slice(1);

                        } else if (allLinkTeam[i + 2].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 2].getAttribute("id").slice(1);
                        } else {
                            m = allLinkTeam[i + 3].getAttribute("id").slice(1);
                        }
                        //垃圾函数结束
                    }

                }
            } else if (document.getElementById("content")) {
                window.location = "#zyzooe";

                for (i = 0; i < allLinkTeam.length; i++) {
                    if (allLinkTeam[i].getAttribute("id") == "zyzooe") {
                        //垃圾函数开始
                        if (allLinkTeam[i + 1].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 1].getAttribute("id").slice(1);
                        } else if (allLinkTeam[i + 2].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 2].getAttribute("id").slice(1);

                        } else {
                            m = allLinkTeam[i + 3].getAttribute("id").slice(1);
                        }
                        //垃圾函数结束
                    }

                }
            } else if (document.getElementById("main")) {
                window.location = "#zyzooe";

                for (i = 0; i < allLinkTeam.length; i++) {
                    if (allLinkTeam[i].getAttribute("id") == "zyzooe") {
                        //垃圾函数开始
                        if (allLinkTeam[i + 1].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 1].getAttribute("id").slice(1);

                        } else if (allLinkTeam[i + 2].getAttribute("id").slice(0, 1) == "t") {
                            m = allLinkTeam[i + 2].getAttribute("id").slice(1);

                        } else {
                            m = allLinkTeam[i + 3].getAttribute("id").slice(1);
                        }

                        //垃圾函数结束
                    }
                }
            }
        }
        if (sKeycode == 49 && !event.ctrlKey && !event.shiftKey) {
            kqNav();
        }
        if (sKeycode == 74 && !event.ctrlKey && !event.shiftKey) {
            kqNav();
        }
        //无障碍 通道 。
        if (event.shiftKey) {
            if (sKeycode == 74) {
                if (qh == 1) {
                    changeStyle(); SetCookie('skyest', '3');
                    qh = 2;
                }
            }
        }
        if (event.ctrlKey) {
            if (qh == 2) {
                if (sKeycode == 74) {
                    window.location.reload(); SetCookie('skyest', '2')
                    qh = 1;
                }
            }
        }
        if (event.shiftKey) {
            if (sKeycode == 90) {

                if (pdfq == 1) { pdfq = 2; m = m - 2; }
                if (m < 1) { m = 1; }
                window.location = "#t" + m;

                if (m < 1) {
                    m = j;
                }
                m = m - 1;
            }

        } else if (sKeycode == 90) {
            //栏目跳转
            if (lmNum == 1) {
                //bb();//开启栏目导航功能
                lmNum++;
            }
            if (pdfq == 2) { pdfq = 1; m = m + 2; }
            if (m > j) { m = j; }

            window.location = "#t" + m;
            m++;
        }

        if (event.ctrlKey) {
            //				if(sKeycode==85){
            //		//	document.getElementById("kjgax").style.display="block"; 
            ////			if(event.shiftKey){
            ////		
            ////				document.getElementById("kjjcfq").focus();
            ////				
            ////s			}
            // document.getElementById("wcagnav").style.display="none"; //关闭工具条 w
            //		}
        }
    }
    if (event.ctrlKey) {
        if (lmNum == 1) {
            //bb();//开启栏目导航功能
            lmNum++;
        }
        if (event.shiftKey) {
            if (sKeycode == 90) {

                if (pdfq == 1) { pdfq = 2; m = m - 2; }
                if (m < 1) { m = 1; }
                window.location = "#t" + m;
                if (m < 1) {
                    m = j;
                }
                m = m - 1;
            }
        } else if (sKeycode == 90) {
            if (pdfq == 2) { pdfq = 1; m = m + 2; }
            if (m > j) { m = j; }
            window.location = "#t" + m;
            //document.getElementById("t"+m).firstChild.focus();
            m++; //创建栏目导航索引
        }
        if (sKeycode == 89) {
            if (document.getElementById("skiptomain")) {
                window.location = "#skiptomain"
            } else if (document.getElementById("maincontent")) {
                window.location = "#maincontent"
            } else if (document.getElementById("content")) {
                window.location = "#content"
            } else if (document.getElementById("main")) { window.location = "#main" }
        }
    }
}

//工具条关闭函数;
function colsenav1() {
    document.getElementById("wcagnav").style.display = "none";
    // window.location = "#gjtygb";
    document.body.style.paddingTop = 0;
    SetCookie('skyest1', 2);
    kqtrue = true;
    cde = 1;
}
//快捷键判断器
var m = 1;
var kqtrue = true;
function kqNav() {
    if (kqtrue) {
        if (!document.getElementById("wcagnav")) { OpenNav(); }
        SetCookie('skyest1', '3')
        document.getElementById("wcagnav").style.display = "block";
        document.body.style.paddingTop = document.getElementById("wcagnav").offsetHeight + "px";
        // window.location = "#fzgjt1";
        cde = 2;
        kqtrue = false;
    } else {
        SetCookie('skyest1', '2')
        document.getElementById("wcagnav").style.display = "none";
        cde = 1;
        document.body.style.paddingTop = 0;
        // window.location = "#gjtygb";
        kqtrue = true;
    }
}

function enter(e) {

    //    var e = window.event ? window.event : e;

    if (e.keyCode == 116) { changeStyle(); SetCookie('skyest', '3') };
    if (e.keyCode == 118) { window.location.reload(); SetCookie('skyest', '2') };
    //49 1 回到首页；
    //50 2 跳转到主要内容区域；
    //51 3 关闭工具条
    //52 4 打开工具条
    //53 5 栏目直接跳转。
}

function oFloatDiv() {
    if (document.getElementById("tempdiv")) {
        document.getElementById("tempdiv").style.top = document.documentElement.scrollTop + "px";
    }
    if (document.getElementById("wcagnav")) {
        document.getElementById("wcagnav").style.top = document.documentElement.scrollTop + "px";
        document.getElementById("wcagnav").style.position = "absolute";
        document.getElementById("wcagnav").style.left = "0"
        document.getElementById("wcagnav").style.zIndex = "999";
        document.getElementById("wcagnav").style.border = "none";
        document.getElementById("wcagnav").style.width = "100%"
        document.getElementById("wcagnav").style.backgroundColor = "#f5f5f5";
        document.getElementById("wcagnav").style.textAlign = "Center";

        document.body.style.paddingTop = document.getElementById("wcagnav").offsetHeight + "px";
    }
}
//顶部跳过导航函数
function skipToMain() {
    if (document.getElementById("skiptomain")) {
        document.getElementById("skiptomain").innerHTML = "<a href=\"#\" class='lmtz1' style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;position:absolute;z-index:-10;border:none;color:#FFF' id=\"skiptomain1\" >已跳转到正文内容区域</a>";

    }
}
function skipToMain1() {
    if (document.getElementById("skiptomain")) {
        //	document.getElementById("skiptomain").innerHTML="<a href=\"#\" style='width:0; height:0; overflow:hidden;font-size:0;line-height:0;' id=\"skiptomain1\" >已跳转到主要内容区域</a>";
        window.location = "#skiptomain1";

        for (i = 0; i < allLinkTeam.length; i++) {
            if (allLinkTeam[i].getAttribute("id") == "skiptomain1") {
                //垃圾函数开始
                if (allLinkTeam[i + 1].getAttribute("id").slice(0, 1) == "t") {
                    m = allLinkTeam[i + 1].getAttribute("id").slice(1);
                } else if (allLinkTeam[i + 2].getAttribute("id").slice(0, 1) == "t") {
                    m = allLinkTeam[i + 2].getAttribute("id").slice(1);
                } else {
                    m = allLinkTeam[i + 3].getAttribute("id").slice(1);
                }
                //垃圾函数结束	
            }
        }
    } else if (document.getElementById("maincontent")) {
        window.location = "#zyzooe";
        for (i = 0; i < allLinkTeam.length; i++) {
            if (allLinkTeam[i].getAttribute("id") == "zyzooe") {
                //垃圾函数开始
                if (allLinkTeam[i + 1].getAttribute("id").slice(0, 1) == "t") {
                    m = allLinkTeam[i + 1].getAttribute("id").slice(1);

                } else if (allLinkTeam[i + 2].getAttribute("id").slice(0, 1) == "t") {
                    m = allLinkTeam[i + 2].getAttribute("id").slice(1);
                } else {
                    m = allLinkTeam[i + 3].getAttribute("id").slice(1);
                }
                //垃圾函数结束
            }
        }
    } else if (document.getElementById("content")) {
        window.location = "#zyzooe";

        for (i = 0; i < allLinkTeam.length; i++) {
            if (allLinkTeam[i].getAttribute("id") == "zyzooe") {
                //垃圾函数开始
                if (allLinkTeam[i + 1].getAttribute("id").slice(0, 1) == "t") {
                    m = allLinkTeam[i + 1].getAttribute("id").slice(1);

                } else if (allLinkTeam[i + 2].getAttribute("id").slice(0, 1) == "t") {
                    m = allLinkTeam[i + 2].getAttribute("id").slice(1);

                } else {
                    m = allLinkTeam[i + 3].getAttribute("id").slice(1);
                }
                //垃圾函数结束
            }
        }
    } else if (document.getElementById("main")) {
        window.location = "#zyzooe";

        for (i = 0; i < allLinkTeam.length; i++) {
            if (allLinkTeam[i].getAttribute("id") == "zyzooe") {
                //垃圾函数开始
                if (allLinkTeam[i + 1].getAttribute("id").slice(0, 1) == "t") {
                    m = allLinkTeam[i + 1].getAttribute("id").slice(1);

                } else if (allLinkTeam[i + 2].getAttribute("id").slice(0, 1) == "t") {
                    m = allLinkTeam[i + 2].getAttribute("id").slice(1);

                } else {
                    m = allLinkTeam[i + 3].getAttribute("id").slice(1);
                }

                //垃圾函数结束
            }
        }
    }
}
//网关转换判断。
var Flink = 1;
function creatLink() {
    if (Flink == 1) {

        var anewlink = document.getElementsByTagName("a");
        for (z = 0; z < anewlink.length; z++) {
            if (anewlink[z].getAttribute("href")) {
                if (anewlink[z].getAttribute("href").indexOf("#") == -1 && anewlink[z].getAttribute("href").indexOf("sh.org") == -1 && anewlink[z].getAttribute("href").indexOf("shanghai") == -1 && anewlink[z].getAttribute("href").indexOf("javascript") == -1 && anewlink[z].getAttribute("href").indexOf(":") > 0) {
                    neww = anewlink[z].getAttribute("title");
                    var csharp = anewlink[z].getAttribute("href");
                    anewlink[z].setAttribute("href", "http://search.shanghai.gov.cn/sunlight/plain.plain?url=" + csharp);
                    anewlink[z].setAttribute("target", "_self");
                    anewlink[z].setAttribute("title", neww + "站外链接")
                    Flink = 2;
                }
            }
        }
    } else if (Flink == 2) {
        clearLink()
        Flink = 1;
    }
}
//去除
function clearLink() {
    var anewlink = document.getElementsByTagName("a");

    for (z = 0; z < anewlink.length; z++) {

        if (anewlink[z].getAttribute("href").indexOf("search.shanghai.gov.cn/sunlight/plain.plain?url=") > 0) {
            neww = anewlink[z].getAttribute("title");
            var csharp = anewlink[z].getAttribute("href");
            dsharp = csharp.replace("http://search.shanghai.gov.cn/sunlight/plain.plain?url=", "")
            anewlink[z].setAttribute("href", dsharp);
        }
    }
}
function resetToolbar() {
    delCookie("skyest");
    window.location.reload();
}
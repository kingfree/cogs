var Lat;
var Lng;
var City;
var offsetLat=0;
var offsetLng=0;
var gMap;
//var engineurl="http://www.kmwhedu.net/test/ajax_ipscan/ajax_ipscan.php";
var engineurl="queryip2.php";
var wyMap;
window.onload=function()
{
	wyMap=new LTMaps("wymap");gMap=new GMap2(document.getElementById("gmap")); 
};

function ShowWymap(f,g){
	wyMap.addControl(new LTStandMapControl());
	var h=new LTPoint(parseFloat(Lng)*100000,parseFloat(Lat)*100000);
	var j=new LTInfoWindow(h);j.setHeight(12);
	if(f!='')
		j.setTitle('行政地图：'+f);
	wyMap.centerAndZoom(h,g);
	wyMap.addOverLay(j);
	onMapDrag=function()
	{
		var a =wyMap.getCenterPoint();
		Lng=a.getLongitude()/100000;
		Lat=a.getLatitude()/100000;
		ShowGmap('',17-wyMap.getCurrentZoom())
	};
	LTEvent.addListener(wyMap,"mouseup",onMapDrag);
	LTEvent.addListener(wyMap,"zoom",onMapDrag);
	ShowSearch=function()
	{
		var a=document.getElementById("key").value;
		if(a!=''&&City!='')
		{
			var b=new LTLocalSearch(showPoint);
			b.setCity(City);
			b.search(a)
		}
	};
	showPoint=function(a)
	{
		if(a.searchPoints.length>0)
		{
			var b;
			var c;
			var d;
			wyMap.clearOverLays();
			var e=[];
			b=a.searchPoints[0];
			Lat=parseFloat(b.point[1]/100000);
			Lng=parseFloat(b.point[0]/100000);
			ShowGmap(b.name,13);
			ShowWymap(b.name,4);
			for(var i=0;i<a.count;i++)
			{
				b=a.searchPoints[i];
				c=new LTPoint(b.point[0],b.point[1]);
				d=new LTMarker(c);
				wyMap.addOverLay(d);
				d.name=b.name;
				LTEvent.bind(d,"click",d,function()
				{
					this.openInfoWinHtml(this.name)
				});
				e.push(c)
			}
		}
		else
		{
			alert('没有找到啊!');
		}
	}
};

function ShowGmap(a,b)
{
	if(GBrowserIsCompatible())
	{
		var c=new GMap2(document.getElementById("gmap"));
		c.setCenter
		(
			new GLatLng(parseFloat(Lat)-offsetLat,parseFloat(Lng)-offsetLng),
			b
		);
		c.setMapType(G_SATELLITE_MAP);
		c.addControl(new GLargeMapControl());
		c.addControl(new GMapTypeControl());
		if(a!='')
		c.openInfoWindow
		(
			c.getCenter(),
			document.createTextNode("卫星定位:"+a+" 北纬:"+Lat+" 东经:"+Lng)
		);
		var d=c.getCenter();
		GEvent.addListener
		(
			c,
			"click",
			function()
			{
				if(d!=c.getCenter())
				{
					d=c.getCenter();
					c.openInfoWindow(d,document.createTextNode("此地经纬度："+d))
				}
			}
		)
	}
};

function InitAjax()
{
	var a=false;
	try
	{
		a=new ActiveXObject("Msxml2.XMLHTTP")
	}
	catch(e)
	{
		try
		{
			a=new ActiveXObject("Microsoft.XMLHTTP")
		}
		catch(e)
		{
			a=false
		}
	}
	
	if(!a&&typeof XMLHttpRequest!='undefined')
	{
		a=new XMLHttpRequest()
	}
	return a
};

function GetUserInfo()
{
	document.getElementById("scan_ip").focus();
	ajax=InitAjax();
	ajax.open("GET",engineurl+"?my=ok",true);
	alert('dsf');
	ajax.send(null);
	ajax.onreadystatechange=function()
	{
		if(ajax.readyState==4&&ajax.status==200&&ajax.responseText.length>0)
		{
			var a=document.getElementById("user_info");
			var b=ajax.responseText.split('`');
			var c="";
			if(b[0]=="0")
			{
				var c="";
				c="<b>IP: </b>"+b[1]+" <b>来自: </b>"+b[2]+" <b>系统: </b>"+b[3]+" <b>浏览器: </b>"+b[4]+" <b>分辨率: </b>"  + escape
					(
						screen.width+
						"x"+
						screen.height
					);
					a.innerHTML=c;
					City=b[5];
					Lat=b[6];
					Lng=b[7];
					ShowGmap(City,13);
					ShowWymap(City,4)
			}
		}
	}
};

function GetChengShiInfo()
{
	var d=document.getElementById("cheng_shi");
	if(d.value.length>0)
	{
		var e=d.value.replace(/&/g,"@");
		e=e.replace(/`/g,"'");
		ajax=InitAjax();
		ajax.open("POST",engineurl,true);
		var f="cheng_shi="+e;ajax.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		ajax.send(f);
		ajax.onreadystatechange=function()
		{
			var a=document.getElementById("scan_info");
			if(ajax.readyState==4&&ajax.status==200&&ajax.responseText.length>0)
			{
				var b="";
				var c=ajax.responseText.split('`');
				b="<font color=\"red\">查找城市："+e+"</font>";
				a.innerHTML=b;
				City=c[0];
				Lat=c[1];
				Lng=c[2];
				ShowGmap(City,13);
				ShowWymap(City,4)
			}
		}
	}
	else
	{
		alert('请输入城市名称才能查询！');
	}
	d.focus()
};

function GetScanInfo()
{
	scanIp=document.getElementById("scan_ip");
	document.getElementById("query").disabled=true;
	if(scanIp.value.length>0)
	{
		var d=scanIp.value.replace(/&/g,"@");
		d=d.replace(/`/g,"'");
		ajax=InitAjax();
		ajax.open("GET",engineurl+"?ip="+d,true);
		ajax.send(null);
		ajax.onreadystatechange=function()
		{
			var a=document.getElementById("scan_info");
			if(ajax.readyState==4&&ajax.status==200&&ajax.responseText.length>0)
			{
				var b=ajax.responseText.split('`');
				var c="";
				if(b[0]=="1")
				{
					var c="";
					c="<font color=\"red\">查询结果：　<b>IP: </b>"+b[1]+"　<b>地址: </b>"+b[2]+"</font>";
					a.innerHTML=c;
					City=b[5];
					Lat=b[6];
					Lng=b[7];
					ShowGmap(City,13);
					ShowWymap(City,4);
					document.getElementById("query").disabled=false;
				}
			}
		}
	}
	else
		alert('请输入IP或域名才能查询！');
	scanIp.focus();
};
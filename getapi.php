<?php
$query = $_SERVER["QUERY_STRING"];//获取url后参数
$array = query_array($query);
if (array_key_exists("av",$array)) {//av参数存在
$av = $_GET['av'];
setcookie("av",$av);
	if (array_key_exists("q",$array)) {//q参数存在
	$q = $_GET['q'];
	} else {//q参数不存在
    $q = "64";
    }
  	if (array_key_exists("p",$array)) {//p参数存在
	$p = $_GET['p'];
	} else {//p参数不存在
    $p = "1";
    }
} else {//av参数不存在
echo('<script type="text/javascript"> alert("参数有误！！！");</script>');
exit;//结束所有脚本
}
//$av = $_COOKIE["av"];//"810872";//视频的av编号
//$q = $_COOKIE["q"];//"16";//视频的清晰度编号
$cid = getcid($av,$p);
$api = getapi($cid,$q);
$msg = getjson($api);
echo $api;//测试视频api能否解析
$json = json_decode($msg);//json字符串对象化获取相关数据
header("Content-Type: text/html; charset=UTF-8");//定义头文件，防止乱码
$durl_0 = $json->durl[0];
$q = $json->quality;
/*下略补充*/
$url = $durl_0->url;
$url = str_replace('http','https',$url);//修改为https
/*下略补充*/
$durl_json = array('url'=>$url);
$getjson = array('av'=>$av,'quality'=>$q,'durl'=>[$durl_json],'status'=>'ok');//json初始化
$getjson = json_encode($getjson);//php数组json字符串化
$file = "./geturl/".$av.".json";
writeurl($file ,$getjson);

function getcid($av,$p) {//已知av获取cid
	$api = "http://api.bilibili.com/view?type=&appkey=84956560bc028eb7&id=".$av."&page=".$p;
	$json = getjson($api);
	$result=array();
	preg_match_all("/(?:cid)(.*)(?:partname)/i",$json, $result);//匹配cid大致字符串
	$cid = $result[1][0];
	$cid = substr($cid, 2, strlen($cid)-4);//加工截取得到cid
	return $cid;
}
function getapi($cid,$q) {//核心代码————解析函数(cid编号，清晰度)
	//$q = "80";//数值表示清晰度(112|1080P+)/(80->1080P)/(64->720)/(32->480P)/(16->360P)//以最后返回为准，存在一定误差
	$SEC1 = "94aba54af9065f71de72f5508f1cd42e";//特殊密钥
	$api_url = "http://interface.bilibili.com/v2/playurl?";//去v2清晰度最高480或(64->720)
	$params_str = "appkey=84956560bc028eb7&cid=".$cid."&otype=json&qn=".$q."&quality=".$q."&type=flv";
	$sign = md5($params_str.$SEC1);
	$api_url = $api_url.$params_str."&sign=".$sign;
	return $api_url;
}
function getjson($url) {
	$curl = curl_init();//创建一个新的CURL资源
	$headers = randIP();
	curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);//伪造请求ip
	curl_setopt($curl,CURLOPT_REFERER,"http://bilibili.com");//伪造请求源referer
	curl_setopt($curl,CURLOPT_URL,$url);//设置URL和相应的选项
	curl_setopt($curl,CURLOPT_HEADER,0);//0表示不输出Header，1表示输出
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);//数据不输出到页面
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
	curl_setopt($curl,CURLOPT_ENCODING,'');//设置编码格式，为空表示支持所有格式的编码//header中“Accept-Encoding: ”部分的内容，支持的编码格式为："identity"，"deflate"，"gzip"
	$UserAgent = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.66 Safari/537.36";
	curl_setopt($curl,CURLOPT_USERAGENT,$UserAgent);//模拟windows用户正常访问
	$json = curl_exec($curl);
	curl_close($curl);
	return $json;
}
function writeurl($TxtFileName,$msg) {//服务器存放写入url的txt文件(名称,字符串)
	if(($TxtRes=fopen($TxtFileName,"w+")) === FALSE){//以读写方式打写指定文件，如果文件不存则创建
	//创建可写文件$TxtFileName失败
	exit();
	}
	//创建可写文件$TxtFileName成功
	$StrConents = $msg;//要写进文件的内容
	if(!fwrite($TxtRes,$StrConents)) {//将信息写入文件
	//尝试向文件$TxtFileName写入$StrConents失败
	fclose($TxtRes);
	exit();
	}
	//尝试向文件$TxtFileName写入$StrConents成功！
	fclose($TxtRes); //关闭指针
}
function randIP(){//随机ip
       $ip_long = array(
           array('607649792', '608174079'), //36.56.0.0-36.63.255.255
           array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
           array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
           array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
           array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
           array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
           array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
           array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
           array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
           array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
       );
       $rand_key = mt_rand(0, 9);
       $ip= long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
       $headers['CLIENT-IP'] = $ip; 
       $headers['X-FORWARDED-FOR'] = $ip; 
       $headerArr = array(); 
       foreach( $headers as $n => $v ) { 
           $headerArr[] = $n .':' . $v;  
       }
       return $headerArr;    
   }
function query_array($query) {
	$queryParts = explode('&', $query);
	$params = array();
	foreach ($queryParts as $param) {
	$item = explode('=', $param);
	$params[$item[0]] = $item[1];
	}
	return $params;
}
?>


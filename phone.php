<?php

include_once "simple_html_dom.php";

class PlatType{
    var  $platform;
    var  $type;
}

class Result{
    var  $phone;
    var  $where;
    var  $result=array();
}

function get($url,$cookie=""){
    $headers = array('User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.75 Safari/537.36',
        'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3');
    if(strlen($cookie)>20){
        array_push($headers, "Cookie: deviceid=76e95e62a71c4234b956109a88c3affd; bs_did=76e95e62-a71c-4234-b956-109a88c3affd|t=1539923208176; _pk_id.1.f11c=1edd67472c06042f.1539923211.2.1539933658.1539929460.; _pk_ses.1.f11c=*; ".$cookie.";");
    }
    $result="";
    try{
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  //SSL 报错时使用
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  //SSL 报错时使用
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
    }catch (Exception $ex){
        print_r($ex);
    }
    return $result;
}

function post($url,$post_data, $method=0){
    $headers = array('User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_2 like Mac OS X) AppleWebKit/603.2.4 (KHTML, like Gecko) Mobile/14F89 MicroMessenger/6.5.9 NetType/WIFI Language/en',
        'Accept:application/json, text/javascript, */*; q=0.01',
        'X-Requested-With: XMLHttpRequest');
    if($method){
        array_push($headers,"Content-Type: application/x-www-form-urlencoded; charset=UTF-8");
    }
    $result="";
    try{
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  //SSL 报错时使用
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  //SSL 报错时使用
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        if($post_data){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
        }
        $result = curl_exec($curl);
        curl_close($curl);
    }catch (Exception $ex){
        print_r($ex);
    }
    return $result;
}

function get360($phone,$result){
    $txt=get('https://www.so.com/s?q='.$phone,"");
    $html = new simple_html_dom();
    $html->load($txt);
    $divs = $html->find('td.mohe-mobileInfoContent');
    $type = new PlatType();
    $type->platform="360手机卫士";
    $type->type="";
    if(count($divs)>0 && count($divs[0]->find("div.mh-detail"))>0){
        $e = $divs[0]->find("div.mh-detail")[0];
        $phone_t = $e->childNodes(0);
        $phone_t = str_replace("&nbsp;","",preg_replace("/[\s]{2,}/","",strip_tags($phone_t)));
        if(strpos($phone_t,$phone)!==false) {
            $belong = $e->childNodes(1);
            $belong = str_replace(" ", "",str_replace("&nbsp;", "", preg_replace("/[\s]{2,}/", "", strip_tags($belong))));
            $type_t = $divs[0]->find("div.mohe-tips")[0]->firstChild();
            $type_t = str_replace("&nbsp;", "", preg_replace("/[\s]{2,}/", "", strip_tags($type_t)));
//        echo str_replace("&nbsp;","",preg_replace("/[\s]{2,}/","",strip_tags($phone)."(".strip_tags($belong).")".strip_tags($type_t)));
            $type->type = $type_t;
            if(empty($result->where)){
                $result->where = $belong;
            }
        }
    }
    $html->clear();
    return $type;
}

function getbd($phone,$result){
    $txt=get('https://www.baidu.com/s?wd='.$phone,"");
    $html = new simple_html_dom();
    $html->load($txt);
    $divs = $html->find('div.op_fraudphone_container');
    $type = new PlatType();
    $type->platform="百度手机卫士";
    $type->type="";
    if(count($divs)>0 ){
        $phone_t = $divs[0]->find("span.op_fraudphone_number")[0];
        $phone_t = str_replace("&nbsp;","",preg_replace("/[\s]{2,}/","",strip_tags($phone_t)));
        if(strpos($phone_t,$phone)!==false) {
            $belong = $divs[0]->find("span.op_fraudphone_addr")[0];
            $belong = str_replace(" ", "",str_replace("&nbsp;", "", preg_replace("/[\s]{2,}/", "", strip_tags($belong))));
            $type_t = $divs[0]->find("span.op_fraudphone_label")[0];
            $type_t = str_replace(" ", "",str_replace("&nbsp;", "", preg_replace("/[\s]{2,}/", "", strip_tags($type_t))));
//        echo str_replace("&nbsp;","",preg_replace("/[\s]{2,}/","",strip_tags($phone)."(".strip_tags($belong).")".strip_tags($type_t)));
            $type->type = $type_t;
            if(empty($result->where)){
                $result->where = $belong;
            }
        }
    }
    $html->clear();
    return $type;
}

function getww($phone,$result){
    $txt=post("https://www.iamwawa.cn/home/saoraodianhua/ajax",array("phone"=>$phone));
    $type = new PlatType();
    $type->platform="蛙蛙在线";
    $type->type="";
    $content = json_decode($txt);
    if($content->status==1){
        $data = $content->data;
        if(strpos($data,$phone)!==false) {
            $type_t = substr($data,strpos($data,"标记为 ")+strlen("标记为 "));
            $type_t = str_replace(" ", "",str_replace("&nbsp;", "", preg_replace("/[\s]{2,}/", "", strip_tags($type_t))));
            $type->type = $type_t;
        }
    }
    return $type;
}

header('Content-type:text/json; charset=UTF-8');
$phone=$_GET['phone'];
$result=new Result();
$result->phone=$phone;
$type=getww($phone,$result);
array_push($result->result,$type);
$type=get360($phone,$result);
array_push($result->result,$type);
$type=getbd($phone,$result);
array_push($result->result,$type);
$json=json_encode($result,JSON_UNESCAPED_UNICODE);
echo $json;

?>
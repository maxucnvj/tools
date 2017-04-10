<?php
// +----------------------------------------------------------------------
// | 2017年4月8日下午5:48:05 
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2017 All rights reserved.
// +----------------------------------------------------------------------
// | Author: cnvj <1403729427@qq.com>
// +----------------------------------------------------------------------
// | 说明: uploadfile.php
// +----------------------------------------------------------------------
ini_set('date.timezone','Asia/Shanghai');
error_reporting(0);
set_time_limit(120);
header("Content-type:text/html;charset=utf-8");
header('Content-type: application/json');
$config = array(
    'token' => '!!!!', //公共文件上传token
    'extfile' => array('jpg','png','gif'), //允许上传的文件类型
    'maxfilesize' => 1*1024*1024, //最大只可上传1M大小的文件
    'delfileexpire' => true, //上传时是否允许覆盖已有文件
);

try {
    if(!isset($_POST['filename']) || !$_POST['filename']){
        throw new Exception('没有提供文件保存路径名称');
    }
    $fileext = explode('.', $_POST['filename']);
    if(!in_array($fileext[count($fileext)-1], $config['extfile'])){
        throw new Exception('上传文件格式错误');
    }
    if(!isset($_POST['uptime']) || !$_POST['uptime'] || time() - strtotime($_POST['uptime']) > 300 || time() - strtotime($_POST['uptime']) < -300){
        throw new Exception('上传时间与服务器时间不能相差5分钟');
    }
    if(!isset($_POST['sign']) || !$_POST['sign'] || strtolower($_POST['sign']) != md5($_POST['filename'].$_POST['uptime'].$config['token'])){
        throw new Exception('数据签名错误');
    }
    $fileexpire = (isset($_POST['fileexpire']) && $_POST['fileexpire'])?false:$config['delfileexpire'];
    if(!$fileexpire && file_exists('./uploadfile/'.$_POST['filename'])){
        throw new Exception('要上传的文件已经存在');
    }
    if($_FILES["file"]["error"] != 'UPLOAD_ERR_OK'){
        throw new Exception('上传文件错误');
    }
    if($_FILES["file"]["size"] > $config['maxfilesize']){
        throw new Exception('上传的文件大于'.intval($config['maxfilesize']/1024).'k');
    }
    $filename = explode('/', str_replace('\\','/',$_POST['filename']));
    //建立文件夹
    if(!file_exists('./uploadfile/'.str_replace($filename[count($filename)-1], '', $_POST['filename']))){
        $dirpath = './uploadfile/';
        for ($i=0;$i< count($filename)-1;$i++){
            if(isset($filename[$i]) && $filename[$i]){
                $dirpath .= $filename[$i].'/';
                if(!file_exists($dirpath)){
                    mkdir($dirpath,0777);
                }
            }
        }
    }
    if(!move_uploaded_file($_FILES["file"]["tmp_name"], dirname(__FILE__).'/uploadfile/'.$_POST['filename'])){
        throw new Exception('文件上传失败');
    }
    echo json_encode(array('result'=>array('fileurl'=>'http://'.$_SERVER['HTTP_HOST'].'/uploadfile/'.$_POST['filename']),'error'=>null),JSON_UNESCAPED_UNICODE);
}catch (Exception $e){
    echo json_encode(array('result'=>null,'error'=>array('msg'=>$e->getMessage(),'code'=>$e->getCode())),JSON_UNESCAPED_UNICODE);
}
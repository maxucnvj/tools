<?php
// +----------------------------------------------------------------------
// | 2017年4月10日上午9:09:11 
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2017 All rights reserved.
// +----------------------------------------------------------------------
// | Author: cnvj <1403729427@qq.com>
// +----------------------------------------------------------------------
// | 说明: 客户端文件上传类
// +----------------------------------------------------------------------
namespace Uploadfilesystem;

class Uploadfilesystem{
    
    protected $config = array(
        'token' => '!!!!', //公共文件上传token
        'extfile' => array('jpg','png','gif'), //允许上传的文件类型
        'maxfilesize' => 1048576, //最大只可上传1M大小的文件
        'unallowexpirefile' => false, //允许上传已有的文件
        'appname' => 'apiupload/', //上传文件基本目录
        'isdeletelocalfile' => true, //是否删除本地文件
        'url' => 'http://xx.xx.xx/uploadfile.php' //上传地址
    );
    
    function __construct($config = array()){
        if($config){
            $this->config = $config;
        }
    }
    
    /**
     * 上传到文件服务器
     * @param unknown $filepath 本地地址（绝对）
     * @param unknown $upfilepath 上传地文件地址（相对）
     * @throws \Exception
     * @return number[]|string[]|unknown[]|mixed[]|number[]|NULL[]
     */
    public function upload($filepath,$upfilepath = ''){
        try {
            if(!$filepath){
                throw new \Exception('上传文件地址不能为空');
            }
            $explodefilepath = explode('.', $filepath);
            if(!$upfilepath){
                $upfilepath = date('Y').'/'.date('m').'/'.date('d').'/'.uniqid().'.'.$explodefilepath[count($explodefilepath)-1];
            }
            if(!in_array($explodefilepath[count($explodefilepath)-1], $this->config['extfile'])){
                throw new \Exception('不是一个可以上传的文凭类型');
            }
            if(!file_exists($filepath)){
                throw new \Exception('原始文件不存在');
            }
            if(filesize($filepath) > $this->config['maxfilesize']){
                throw new \Exception('上传文件的尺寸太大');
            }
            $uploadconfig = array(
                'uptime' => date('Y-m-d H:i:s'),
                'filename' => $this->config['appname'].$upfilepath,
                'fileexpire' => ($this->config['unallowexpirefile']?0:1)
            );
            $uploadconfig['sign'] =  md5($uploadconfig['filename'].$uploadconfig['uptime'].$this->config['token']);
            $uploadconfig['file'] = $filepath;
            return $this->curl($uploadconfig);
        } catch (\Exception $e) {
            return array(
                'status' => 0,
                'msg' => $e->getMessage()
            );
        }
    }
    
    /**
     * 上传文件
     * @param array $postdata
     */
    protected function curl($postdata = array()){
        $sourcepath = $postdata['file'];
        if(version_compare("5.5", PHP_VERSION, "<")){
           $postdata['file'] =  new \CurlFile($postdata['file'], 'image/png');
        }else{
           $postdata['file'] = '@'.$postdata['file'];
        }
        $ch = curl_init();
        curl_setopt($ch , CURLOPT_URL , $this->config['url']);
        curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch , CURLOPT_POST, 1);
        curl_setopt($ch , CURLOPT_POSTFIELDS, $postdata);
        $output = curl_exec($ch);
        if ($output === FALSE) {
            return array(
                'status' => 0,
                'msg' => 'cURL Error: ' . curl_error ( $ch )
            );
        }
        curl_close($ch);
        $output = json_decode($output,true);
        if($output['error']){
            return array(
                'status' => 0,
                'msg' => $output['error']['msg']
            );
        }else{
            if($this->config['isdeletelocalfile']){
                unlink($sourcepath);
            }
            return array(
                'status' => 1,
                'fileurl' => $output['result']['fileurl']
            );
        }
    }
    
}
<?php
// +----------------------------------------------------------------------
// | 2016-12-14 上午10:03:14
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 All rights reserved.
// +----------------------------------------------------------------------
// | Author: cnvj <1403729427@qq.com>
// +----------------------------------------------------------------------
// | Explian: 自定义加密解密函数
// +----------------------------------------------------------------------

class diyencode{
    //可修改区域开始======================================= //加盐 发布后不能更改
    protected static $hashkey = 'cnvj';
    protected static $arrays = [
        0 => [3,1,4,8,7,6,9,2,0,5],
        1 => [5,4,1,7,9,8,0,6,3,2],
        2 => [2,6,9,8,5,4,0,1,3,7],
        3 => [6,5,9,1,7,0,8,3,2,4],
        4 => [4,7,1,8,6,2,9,0,3,5],
        5 => [2,3,9,1,4,5,8,7,0,6],
        6 => [7,1,2,6,9,0,5,8,4,3],
        7 => [1,9,3,5,2,7,4,0,6,8],
        8 => [0,4,9,8,2,6,7,3,5,1],
        9 => [7,6,3,9,1,2,4,5,8,0]
    ];
    //可修改区域结束=======================================
    
    
    /**
     * 自定义加密的字符串
     * @param unknown $string
     * @return unknown
     */
    public static function encode($string){        
        if(!$string){
            return $string;
        }
        $hash = self::gethashkey();
        if(!$hash){
            return $string;
        }
        $str = '';
        foreach (str_split($string) as $v){
            $str .= chr(($hash['m']?ord($v)+$hash['r']:ord($v)-$hash['r']));
        }
        //重新组合排序
        $strs = '';
        $index = self::$arrays[$hash['c']];
        for($i=0;$i<intval(strlen($str)/10)+1;$i++){
            if(strlen(substr($str, 0+($i*10),10)) == 10){
                $strindex = str_split(substr($str, 0+($i*10),10));
                foreach ($index as $dd){
                    if(isset($strindex[$dd])){
                        $strs .= $strindex[$dd];
                    }
                }
            }else{
                $strs .= substr($str, 0+($i*10),10);    
            }            
        }
        return $strs;    
    }
    
    /**
     * 自定义解密的字符串
     * @param unknown $string
     * @return unknown
     */
    public static function decode($string){
        if(!$string){
            return $string;
        }
        $hash = self::gethashkey();
        if(!$hash){
            return $string;
        }
        //第一步得到正确的排序
        $strs = '';
        $index = self::$arrays[$hash['c']];
        for($i=0;$i<intval(strlen($string)/10)+1;$i++){
            if(strlen(substr($string, 0+($i*10),10)) == 10){
                $strindex = str_split(substr($string, 0+($i*10),10));
                for($z=0;$z < 10;$z++){
                    foreach ($index as $k=>$dd){
                        if(intval($dd) === intval($z)){
                            if(isset($strindex[$k])){
                                $strs .= $strindex[$k];                            
                            }
                            break;
                        }
                    }
                }
            }else{
                $strs .= substr($string, 0+($i*10),10);
            }            
        }
        $str = '';
        foreach (str_split($strs) as $v){
            $str .= chr(($hash['m']?ord($v)-$hash['r']:ord($v)+$hash['r']));
        }
        return $str;
    }
    
    /**
     * 取盐的hash得到偏移值
     */
    protected static function gethashkey(){
        foreach (self::$arrays as $k=>$v){
            $aa = 0;
            foreach ($v as $s){
                $aa += $s;
            }
            if($aa != 45){ //检查配置正确性
                //return $k.$aa;
                return false;
            }
        }
        $number = 0;
        foreach (str_split(self::$hashkey) as $v){
            $number += ord($v);
        }
        return array(
            'l' => intval(substr($number, 0,1)),
            'c' => intval(substr($number, 1,1)),
            'r' => intval(substr($number, -1)),
            'm' => (($number%strlen(self::$hashkey))%2?1:0)
        );
    }
}
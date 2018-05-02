<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/19 0019
 * Time: 下午 2:1
 */

 /**
  * 获取某个全部审批流程
  * @param string $mod_name 流程名
  *  @return array 
  */
 function GetAppFlow($mod_name)
{
    if(!$mod_name) return false;
    $res = M('yxhb_appflowtable a')
            ->field('a.pro_name,b.id,a.`condition`,b.name,b.avatar')
            ->join('yxhb_boss b on a.per_id = b.id')
            ->where(array('a.pro_mod' => $mod_name, 'a.stat' =>1))
            ->order('a.stage_id')
            ->select();
    if(!empty($res)){
        foreach($res as $k=>$v){
            $res[$k]['condition'] = json_decode($v['condition']);
        }
    }
    return $res;
}
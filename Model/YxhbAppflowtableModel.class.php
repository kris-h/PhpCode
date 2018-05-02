<?php
namespace Light\Model;
use Think\Model;
/**
 * 环保流程记录模型
 * @author 
 */

class YxhbAppflowtableModel extends Model {

    /**
     * 获取全流程
     * @param  string $modname 流程名
     * @return array   摘要数组
     */
    public function getAllProc($modname)
    {
        $proInfo = array();
        $res = $this->field('pro_name,pro_id,per_name,per_id,stage_id,point')->where(array('pro_mod'=>$modname, 'stat'=>1))->order('stage_id asc')->select();
        $boss = D('yxhb_boss');
        foreach ($res as $key => $value) {
            $procIDList = $value['per_id'].",";
            $procNameList = $value['per_name'].",";
            $wxid = $boss->getWXFromID($value['per_id']);
            $avatar = $boss->getAvatar($value['per_id']);
            $procWXIDList = $wxid['wxid'].",";
            $proInfo[] = array('id'=>$value['per_id'],
                               'realname'=>$value['per_name'],
                               'wxid'=>$wxid,
                               'avatar'=>$avatar
                              );
        }
        $firstProc = $proInfo[0];
        return array('first'=>$firstProc,
                     'proInfo'=>$proInfo,
                     'res'=>$res,
                     'title'=>$res[0]['pro_name']
                    );
    }

    public function getStepInfo($pro_id, $stage)
    {
        $map['pro_id'] = $pro_id;
        $map['stage_id'] = $stage;
        $map['stat'] = 1;
        $res = $this->field(true)->where($map)->find();
        return $res;
    }

    public function getAllStepInfo($mod_name, $stage)
    {
        $map['pro_mod'] = $mod_name;
        $map['stage_id'] = $stage;
        $map['stat'] = 1;
        $res = $this->field(true)->where($map)->select();
        return $res;
    }

    public function getTopStep($mod_name)
    {
        $map['pro_mod'] = $mod_name;
        $map['stat'] = 1;
        return $this->where($map)->max('stage_id');
    }
}

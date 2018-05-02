<?php
namespace Light\Model;
use Think\Model;
/**
 * 环保审批流程记录模型
 * @author 
 */

class YxhbAppflowprocModel extends Model {

    /**
     * 获取审批流程记录
     * @param  string $modname 流程名
     * @param  int    $aid 记录ID
     * @return array   摘要数组
     */
    public function contentProc($mod_name, $aid, $authArr)
    {
        $uid = session('yxhb_id');
        $resArr = $this->field(true)->where(array('aid'=>$aid, 'mod_name'=>$mod_name, 'app_stat'=>array(array('egt',0), array('lt',3), 'and')))->order('app_stat desc,app_stage asc,approve_time asc')->select();
        $isCopyto = 0;
        $isApplyer = 0;
        $isPasser = 0;
        $isRefuse = 0;
        $isFlowBegin = 0;
        $boss = D('yxhb_boss');
        foreach ($resArr as $k=> $val) {
            if ($val['app_stat']==0 && $val['per_id']==$uid) {
                $isApplyer = 1;
            }
            // 是否为审批人之一
            if ($val['per_id']==$uid) {
                $isPasser = 1;
            }
            $wxid = $boss->getWXFromID($val['per_id']);
            array_push($authArr, $wxid);
            $val['avatar'] = $boss->getAvatar($val['per_id']);
            $resApply[] = $val;
            // 是否被拒绝
            if ($val['app_stat']==1) {
                $isRefuse = 1;
            }
            // 审批是否已经开始
            if ($val['app_stat']>0) {
                $isFlowBegin = 1;
            }
        }
        $procArr = array('isCopyto'=>$isCopyto,'process'=>$resApply,'isApplyer'=>$isApplyer,'isPasser'=>$isPasser,'isRefuse'=>$isRefuse,'isFlowBegin'=>$isFlowBegin,'authArr'=>$authArr);
        return $procArr;
    }

    public function getWorkFlowStatus($mod_name, $aid){
      $appInfo = $this->field('app_stat,app_name,app_stage')->where(array('mod_name'=>$mod_name, 'aid'=>$aid, 'app_stat'=>array('egt',0), 'app_stat'=>array('lt',3)))->order('app_stat asc')->find();
      if($appInfo['app_stat']=='1'){
        $apply = array("stat"=>1, "content"=>"已退审", "stage"=>$appInfo['app_stage']);
      } elseif ($appInfo['app_stat']=='0'){
        $apply = array("stat"=>0, "content"=>$appInfo['app_name']."中", "stage"=>$appInfo['app_stage']);
      } elseif ($appInfo['app_stat']=='2'){
        $apply = array("stat"=>2, "content"=>"已通过", "stage"=>$appInfo['app_stage']);
      }else{
        $apply = array("stat"=>-1, "content"=>"系统出错", "stage"=>$appInfo['app_stage']);
      }
      return $apply;
    }

    /**
     * 获取实际单步审批流程信息
     * @param  [string] $mod_name [流程名]
     * @param  [integer] $aid      [记录ID]
     * @param  integer $uid      [用户ID]
     * @return [array]           [流程信息]
     */
    public function getStepInfo($mod_name, $aid, $uid='')
    {
        if (empty($uid)) {
            $uid = session('yxhb_id');
        }
        $map['mod_name'] = $mod_name;
        $map['aid'] = $aid;
        $map['per_id'] = $uid;
        $map['app_stat'] = array('lt',3);
        $res = $this->field(true)->where($map)->order('app_stage desc')->find();
        return $res;
    }

    // 审批记录保存
    public function updateProc($rid, $option, $word)
    {
        $map['id'] = $rid;
        $map['app_stat'] = 0;
        $data['app_stat'] = $option;
        $data['app_word'] = $word;
        $data['approve_time'] = date('Y-m-d H:i:s');
        return $this->where($map)->save($data);
    }

    //会签同级+996
    public function refuse($mod_name, $aid, $stage_id)
    {
        $map['mod_name'] = $mod_name;
        $map['aid'] = $aid;
        $map['app_stat'] = array('neq', 1);
        $data['app_stat'] = array('exp', 'app_stat+996');
        $data['approve_time'] = date('Y-m-d H:i:s');
        return $this->where($map)->save($data);
    }

    /**
     * 获取同级未签数
     * @param  [string] $mod_name [流程名]
     * @param  [integer] $stage_id      [审批ID]
     * @param  integer $uid      [用户ID]
     * @return [array]           [流程信息]
     */
    public function getSameProcNum($mod_name, $aid, $stage_id)
    {
        $map['mod_name'] = $mod_name;
        $map['aid'] = $aid;
        $map['app_stage'] = $stage_id;
        $map['app_stat'] = 0;
        $res = $this->field(true)->where($map)->count();
        return $res;
    }

    public function addProc($data, $aid, $per_name, $per_id, $stageID)
    {
      $record['pro_id'] = $data['pro_id'];
      $record['mod_name'] = $data['pro_mod'];
      $record['app_name'] = $data['stage_name'];
      $record['aid'] = $aid;
      $record['per_name'] = $per_name;
      $record['per_id'] = $per_id;
      $record['app_stat'] = 0;
      $record['app_stage'] = $stageID;
      $record['time'] = date('Y-m-d H:i:s');
      $res = $this->add($record);
      return $res;
    }

    public function reset($mod_name,$aid)
    {
        $res = $this -> where(array('mod_name'=>$mod_name,'aid'=>$aid)) -> setInc('app_stat', 3);
        return $res;
    }
}

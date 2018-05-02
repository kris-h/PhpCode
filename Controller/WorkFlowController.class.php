<?php
namespace Light\Controller;
use Think\Controller;

class WorkFlowController extends BaseController {
    private $WeChat = null;

    private $receiver = 'wk';

    private $urlHead = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx133a00915c785dec&redirect_uri=http%3a%2f%2fwww.fjyuanxin.com';

    private $urlEnd = '&response_type=code&scope=snsapi_base&state=YUANXIN#wechat_redirect';

  	public function __construct(){
        $this->WeChat = new \Org\Util\WeChat;
    }

    /**
     * 设置审批流程
     * @param flowName 流程名称
     * @param id 记录id即aid
     * @param pid 用户ID
     */
    public function setWorkFlowSV($flowName,$id,$pid,$system){
  	    $stageID=1;
  	    $this->nextStep($flowName,$id,$stageID,$pid,$system);
        $resArr = array(
          "option" => 0,
          "status" => "start",
          "aid" => $id
        );
        return $resArr;
    } 
    
    /*下一步工作流
        @ flowName 工作流名称
        @ id 实际工作流程对应ID
        @ pid 审批节点对应ID（防错验证）
        @ option 审批状态：0表示未审批，1表示审批拒绝，2表示审批通过
        @ word 审批意见
    */
    public function nextWorkFlowTH($flowName,$id,$pid,$option,$word,$applyUser,$system){
      $resArr = array();
      $systemU = ucfirst($system);
      //当前审批流程
      $appflowproc = D($systemU.'Appflowproc');
      $nowStepArr = $appflowproc->getStepInfo($flowName, $id, $pid);
      // 获取下一审批步骤数(似乎没用了)
      $appflowtable = D($systemU.'Appflowtable');
      $tableStepArr = $appflowtable->getStepInfo($nowStepArr['pro_id'], $nowStepArr['app_stage']);
      //获取下一审批流程(似乎没用了)
      $getNext = $appflowtable->getStepInfo($nowStepArr['pro_id'], $tableStepArr['stage_next']);
      // 更新当前审批记录
      $is_done2 = $appflowproc->updateProc($nowStepArr['id'], $option, $word);
      //申请人ID  
      // $applyUser=iconv('UTF-8', 'GBK', $applyUser);
      // $applyUserid=$this->getUserID($applyUser,$db);   
      $boss = D($systemU.'Boss');
      $applyUserid = $boss->getIDFromName($applyUser);
      $applyUserwxid = $boss->getWXFromID($applyUserid);
      //如果审批结果为拒绝
      if($option==1){
        //会签同级+996
        $is_done3 = $appflowproc->refuse($flowName, $id, $nowStepArr['app_stage']);
        // $content = $applyUser.iconv('UTF-8', 'GBK', '您好,您在建材ERP系统中有一个<').$tableStepArr['pro_name'].iconv('UTF-8', 'GBK', '>被拒绝');
        $msgInfo = $this->sendApplyMsg($flowName, $id, $pid, $applyUserid, $system, 'refuse');

        $resArr = array(
                    "option" => $option,
                    "status" => "end",
                    "aid" => $id
                  );
      } else {
        //审核通过，判断是否还有会签同级未审批
        $sameProcQuery = $appflowproc->getSameProcNum($flowName, $id, $nowStepArr['app_stage']);
        //不存在会签同级未审批
        if(!$sameProcQuery){
          if($tableStepArr['stage_next']!=0){
            $stageID = $tableStepArr['stage_next'];
            //没有下级插入记录，可能是最后一个条件不满足
            if(!$this->nextStep($flowName,$id,$stageID,$applyUserid,$system)){
              $msgInfo = $this->sendApplyMsg($flowName, $id, $pid, $applyUserid, $system, 'pass');

              $resArr = array(
                    "option" => $option,
                    "status" => "end",
                    "aid" => $id
              );
            } else {
              $resArr = array(
                          "option" => $option,
                          "status" => "ing",
                          "aid" => $id
                        );
            }
          } else {
            $msgInfo = $this->sendApplyMsg($flowName, $id, $pid, $applyUserid, $system, 'pass');
            $resArr = array(
                    "option" => $option,
                    "status" => "end",
                    "aid" => $id
            );
          }
        }//不存在会签情况END
      }
      return $resArr;
    }
    
    private function nextStep($flowName,$id,$stageID,$pid,$system){
        //读取审批流程
        $systemU = ucfirst($system);
        $boss = D($systemU.'Boss');
        $appflowproc = D($systemU.'Appflowproc');
        $appflowtable = D($systemU.'Appflowtable');
        $getInit = $appflowtable->getAllStepInfo($flowName, $stageID);
        $done=0;
        //初始化审批流程
        foreach($getInit as $values){
          //对应步骤条件判断
          if($values['condition']!=''){
            //{"name"=>"cost","table"=>"kk_peixun","field"=>"cost","conditions"=>"cost>0"}
            $conditions=explode(";",$values['condition']);
            // echo "conditions:<br>";
            // var_dump($conditions);
            // echo "<br>";
            /*多条件判断
              @对于多条件判断目前是做多条件的AND操作，未做OR操作
            */
            $con_flag=1;
            foreach($conditions as $cons){
              $cons=substr($cons,1,-1);
              //echo "cons:".$cons."<br>";
              $cons_sub=explode("|",$cons);
              //echo "cons_sub:<br>";
              //var_dump($cons_sub);
                //数据组装
                foreach($cons_sub as $c_s){
                  $cons_sub_sub=explode(":",$c_s);
                  $cons_array[$cons_sub_sub[0]]=$cons_sub_sub[1];
                }
              //echo "cons_array:<br>";
              //var_dump($cons_array);
              $con_query="SELECT 1 from ".$cons_array['table']." WHERE id=$id ".$cons_array['conditions'];
              $db->SetQuery($con_query);
              $db->Execute();
              if($db->GetTotalRow()){
                $con_flag*=1; //满足条件
              } else {
                $con_flag*=0;//不满足条件
                break;
              }
            }
            //不满足条件结束本次循环
            if($con_flag==0) continue;
            // echo "con_flag:<br>".$con_flag."<br>";
            // echo $con_query;
          }
          if($values['per_id']==0){
             // 解决手动指定固定步骤的审批流程，增加$id获取记录--用章申请--15.12.10
             // $per_id=$this->$values['per_name']($pid,$db);
             $per_id = $this->$values['per_name']($pid,$id);
             $per_name = $boss->getusername($per_id);
          } else {
             $per_id=$values['per_id'];
             $per_name=$values['per_name'];
          }
          //echo $setProcQuery;
          // 审批人为空则跳过当前审批人
          if(!empty($per_id)&&$per_id!=0){
            //防止记录重复插入
            $sameProcNum = $appflowproc->getSameProcNum($flowName, $id, $stageID);
            if(!$sameProcNum){
              $is_done = $appflowproc->addProc($values, $id, $per_name, $per_id, $stageID);
              $done++;
              $msgInfo = $this->sendApplyMsg($flowName, $id, $per_id, $pid, $system);
            }            
          }
        }

        if($done==0){
          //设置stage上限
          $top = $appflowtable->getTopStep($flowName);
          if($stageID<$top){
            $stageID++;
            return $this->nextStep($flowName,$id,$stageID,$pid,$system);
          } else {
            //如果达到上限返回插入记录失败
            return false;
          }

        } else {
          return true;
        }              
        //-----------------------END--------------------------------------------------------------------------------------------     
    }

    /*流程重置
    @ flowName 工作流名称
    @ aid 实际工作流程对应ID
    */
    public function workFlowSVReset($flowName,$aid,$system){
      $resetM = D(ucfirst($system).'Appflowproc');
      $res = $resetM -> reset($flowName,$aid);
      return $res;
    }
    /*流程是否已经开始
    @ flowName 工作流名称
    @ aid 实际工作流程对应ID
    */
    public function isWorkFlowStart($flowName,$aid,$system){
      $resetM = M($system.'_appflowproc');
      $res = $resetM -> where(array('mod_name'=>$flowName, 'aid'=>$aid, 'app_stat'=>array(array('gt',0),array('lt',3))))->count();
       return $res;
    }
    public function getWorkFlowSVCode($flowName,$aid,$system){
      $stat_zero=0;
      $stat_two=0;
      //获取最高一级的stage
      $stageInfo = M() -> query("select max(app_stage) as stage from ".$system."_appflowproc where mod_name='{$flowName}' and aid='{$aid}' and app_stat<3");
      $top_stage=$stageInfo[0]['stage'];
      $appInfo = M() -> query("select per_name,app_name,app_stat from ".$system."_appflowproc where mod_name='{$flowName}' and aid='{$aid}' and app_stat<3 and app_stage='{$top_stage}'");
      foreach($appInfo as $values){
        //拒绝情况直接返回状态
        if($values['app_stat']=='1'){
          return 1;
        }
        // 未审批数
        if($values['app_stat']=='0'){
          $stat_zero++;
        }
        // 审批通过数
        if($values['app_stat']=='2'){
          $stat_two++;
        }
      }
      // 返回状态码
      if($stat_zero!=0){
        return 0;
      }
      elseif($stat_two>=1){
        return 2;
      } else {
        return 3;      
      }
    }
    public function getWorkFlowSV($flowName,$aid,$system){
      //获取最高一级的stage
      $getStageQuery="select max(app_stage) as stage from ".$system."_appflowproc where mod_name='{$flowName}' and aid='{$aid}' and app_stat<3";
      $stageInfo=M()->query($getStageQuery);
      $top_stage=$stageInfo[0]['stage'];
      $getAppStatQuery="select per_name,app_name,app_stat from ".$system."_appflowproc where mod_name='{$flowName}' and aid='{$aid}' and app_stat<3 and app_stage='{$top_stage}'";
      $appInfo=M()->query($getAppStatQuery);
      if($appInfo[0]['app_stat']=='1'){
        $applyInfo=$appInfo[0]['per_name'].$appInfo[0]['app_name']."拒绝";
        return $applyInfo;
      }elseif($appInfo[0]['app_stat']=='0'){
        $applyInfo=$appInfo[0]['per_name'].$appInfo[0]['app_name']."中";
        return $applyInfo;
      }elseif($appInfo[0]['app_stat']=='2'){
        $applyInfo="审批完成";
        return $applyInfo;
      }else{
        $applyInfo="系统出错";
        return $applyInfo;
      }
    }

    public function sendApplyMsg($flowName, $id, $pid, $applyerid, $system, $type='')
    {
        $systemName = array('kk'=>'建材', 'yxhb'=>'环保');
        $flowTable = M($system.'_appflowtable');
        $mod_cname = $flowTable->getFieldByProMod($flowName, 'pro_name');
        $title = $mod_cname;
        $url = "http://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$id."&modname=".$flowName;
        $boss = D($system.'_boss');
        $applyerName = $boss->getusername($applyerid);
        $applyerwxid = $boss->getWXFromID($applyerid);
        $proName = $boss->getusername($pid);
        $prowxid = $boss->getWXFromID($pid);

        switch ($type) {
          case 'pass':
            $description = "已审批通过";
            $receviers = "wk|HuangShiQi|".$applyerwxid;
            break;
          case 'refuse':
            $description = "被拒绝";
            $receviers = "wk|HuangShiQi|".$applyerwxid;
            break;
          case 'other':
            $description = "需要处理";
            $receviers = "wk|HuangShiQi|".$prowxid;
            break;          
          default:
            $description = "需要审批";
            $receviers = "wk|HuangShiQi|".$prowxid;
            break;
        }
        $title = str_replace('表','',$title);
        $template = "您有一个{$systemName[$system]}系统流程{$description}!\n类&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;型：{$title}";

        $logic = D(ucfirst($system).$flowName,'Logic');
        $descriptionData = $logic->getDescription($id);

        $description = $this->ReDescription($descriptionData);
        $template =$template."\n".$description."<a href='".$url."'>点击查看</a>";
       
        // $template = $description."\n类型：".$title."\n申请人：".$applyerName."\n调休理由：".$record['content']."\n<a href=\"".$url."\">点击查看</a>";
        $agentid = 15;
        $info = $this->WeChat->sendMessage($receviers,$template,$agentid,$system);
        // $info=$this->WeChat->sendCardMessage($receivers,$title,$applyerName.$description,$url,14);
        return $info;
    }

  // TODO 删除
    // public function forTest(){
    //     $agentid = 15;
    //     $receviers = 'HuangShiQi';

    //     $template = "您有一个环保系统流程需要处理!\n类&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;型：临时额度申请";
    //     $flowName = 'TempCreditLineApply';
    //     $system = 'yxhb';

    //     $logic = D(ucfirst($system).$flowName,'Logic');
    //     $descriptionData = $logic->getDescription(571);
        
    //     $description = $this->ReDescription($descriptionData);
        
    //     $template =$template."\n".$description."<a>点击查看</a>"; 
        
    //     $info = $this->WeChat->sendMessage($receviers,$template,$agentid,$system);
    // }
    
    /**
     *  根据返回值，重组字符串
     * @param  array $data 重组数组
     * @return string       description
     */
    public function ReDescription($data){
        $description = '';
        foreach($data as $k =>$v){
          $description.=$v['name'].$v['value']."\n";
        }
        return $description;
    }

    public function sendApplyCardMsg($flowName, $id, $pid, $applyerid, $system, $type='', $subTitle='')
    {
      // 微信发送
        $flowTable = M($system.'_appflowtable');
        $mod_cname = $flowTable->getFieldByProMod($flowName, 'pro_name');
        $title = $mod_cname;
        $url = "http://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$id."&modname=".$flowName;
        //crontab(CLI模式)无法正确生产URL
        // if (PHP_SAPI=='cli') {
        //   $detailsURL = str_replace('_PHP_FILE_', '/WE/index.php', $detailsURL);
        // }
        $boss = D($system.'_boss');
        $proName = $boss->getusername($pid);

        $subName = '';
        if($subTitle == '（催审）'){
          $subName = $boss->getusername($applyerid);
          $applyerName='('.$subName.'提交)';
        } else {
          $applyerName = $boss->getusername($applyerid);
        }

        switch ($type) {
          case 'pass':
            $description = $applyerName."您好,您在".$systemName[$system]."ERP系统中有一个<".$title.">已审批通过";
            $receviers = "wk|".$applyerwxid;
            break;
          case 'refuse':
            $description = $applyerName."您好,您在".$systemName[$system]."ERP系统中有一个<".$title.">被拒绝";
            $receviers = "wk|".$applyerwxid;
            break;
          case 'other':
            $description = $applyerName."您好,您在".$systemName[$system]."ERP系统中有一个<".$title.">需要处理";
            $receviers = "wk|".$applyerwxid;
            break;          
          default:
            $description = "您好,您在".$systemName[$system]."ERP系统中有一个<".$title.">需要审批";
            $receviers = "wk|".$applyerwxid;
            break;
        }

        $agentid = 15;
        $info = $this->WeChat->sendCardMessage($receviers,$title,$proName.$description.$applyerName,$url,$agentid,$flowName,$system);
        return $info;
    }

    public function setOthersApply($mod_name, $pro_id, $aid, $stage_id, $others_id, $reason, $applyUserid, $system)
    {
      // 1.检查是否为流程最后一步
      // $next_step = M('oa_appflowtable')->field('stage_next,per_id')->where(array('pro_id'=>$pro_id, 'stage_id'=>$stage_id))->find();
      // if ($next_step['stage_next'] == 0 && $next_step['per_id'] == 37) {
      //   $flag = 1;
      // } else {
      //   $flag = 0;
      // }
      // 2.查找审批人姓名
        $boss = D($system.'_boss');
        $per_name = $boss->getusername($others_id);
      // 3.设置stage为当前审批步骤的前一步骤
      if ($stage_id > 1) {
        $pre_stage = $stage_id-1;
      } else {
        $pre_stage = $stage_id;
      }
      // 4.app_word和app_name为转审
      $setProcQuery = M($system."_appflowproc");
      $sdata['app_word'] = $reason.'(转审->'.$per_name.')';
      $sdata['app_stat'] = 2;
      $sdata['app_name'] = '已转审';
      $sdata['app_stage'] = $pre_stage; //将当前步骤退一个stage_id,为了排序 
      $sdata['approve_time'] = date('Y-m-d H:i',time());
      $setProcQuery->where(array('aid'=>$aid, 'pro_id'=>$pro_id, 'app_stat'=>0))->save($sdata);
      // 5.设置转审流程
      $data['pro_id']    = $pro_id;
      $data['aid']       = $aid;
      $data['per_name']  = $per_name;
      $data['per_id']    = $others_id;
      $data['app_stat']  = 0;
      $data['app_stage'] = $pre_stage;
      $data['time']      = date('Y-m-d H:i',time());
      $data['app_name']  = '转审';
      $data['mod_name']  = $mod_name;
      $setProcQuery->add($data);

      $this->sendApplyMsg($mod_name, $aid, $others_id, $applyUserid, $system);
    }

    public static function getWorkFlowStatus($flowName,$aid,$system){
      $appInfo = M($system.'_appflowproc')->field('app_stat,app_name,app_stage')->where(array('mod_name'=>$flowName, 'aid'=>$aid, 'app_stat'=>array('egt',0), 'app_stat'=>array('lt',3)))->order('app_stat asc')->find();
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

    public function urgeWorkFlow(){
      $aid = I('post.aid');
      $mod_name = I('post.mod_name');
      $system = I('post.system');
      $res = M($system.'_appflowproc')->field('per_id,urge,per_name')->where(array('aid'=>$aid, 'mod_name'=>$mod_name, 'app_stat'=>0))->find();
      if (!empty($res)) {
        $timestamp = time();
        if ($timestamp-$res['urge']>24*3600) {
          $this->sendApplyCardMsg($mod_name, $aid, $res['per_id'], $res['per_id'], $system, '', '（催审）');
          M($system.'_appflowproc')->where(array('aid'=>$aid, 'mod_name'=>$mod_name, 'app_stat'=>0))->setField('urge', $timestamp);
          // 自动评论
          $data['aid'] = $aid;
          $ctoid = $res['per_id'];
          // $data['comment_to_id'] = $res['per_id'];
          $data['mod_name'] = $mod_name;
          $data['per_id'] = session($system.'_id');
          $data['per_name'] = session('name');
          $data['app_word'] = $data['per_name']."向".$res['per_name']."发起了催审!";
          $data['app_stat'] = 1;
          $data['time'] = date('Y-m-d H:i:s');
          $commentRes = M($system.'_appflowcomment')->add($data);
          $result = 'success';
        } else {
          $result = "sended";
        }
      } else {
        $result = "error";
      }
      $this->ajaxReturn($result);
    }
}
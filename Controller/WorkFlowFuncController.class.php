<?php
namespace Light\Controller;
use Think\Controller;

class WorkFlowFuncController extends Controller {

	private $WeChat = null;

  	public function __construct(){
        $this->WeChat = new \Org\Util\WeChat;
    }
	public function __call($name, $arguments)
	{
		$receivers='wk';
		$content = $name."审批后方法不存在！";
		$info=$this->WeChat->sendMessage($receivers,$content,15,$arguments[1]);
		return array("status"=>"error");
	}

	/**
	* 合同审批通过后调用函数
	* @param  [integre] $aid [合同记录父ID]
	* @return [array]      [状态]
	*/
	public function YxhbContractApplyEnd($aid)
	{
		$updateQuery="update #@__ht set ht_stat=2 where ht_stat=1 and pid=$aid";
		$res = $this->db->ExecuteNoneQuery($updateQuery);
		if ($res) {
	  		$resArr = array("status"=>"success");
		} else {
	  		$resArr = array("status"=>"failure");
		}
		return $resArr;
	}
	/**
	* 销售日计划审批通过后调用函数
	* @param  [integre] $aid [记录ID]
	* @return [array]      [状态]
	*/
	public function YxhbSalesPlantApplyEnd($aid)
	{
		$sql=new dedesql(false);
		$q="SELECT relationid FROM yxhb_sale_plan where id=$aid";
		$sql->setquery($q);
		$sql->execute();
		$g=$sql->getOne();
		$updateQuery="update #@__sale_plan set stat=1 where stat=2 and id=$aid";
		$updateQuery="update #@__sales_plan set stat=1 where stat=2 and relationid='{$g['relationid']}'";
		$res = $this->db->ExecuteNoneQuery($updateQuery);
		if ($res) {
	  		$resArr = array("status"=>"success");
		} else {
  			$resArr = array("status"=>"failure");
		}
		$jssdk = new JSSDK();
		$receiver='csl|wk|shh';
		$sj=date('m月d日',strtotime($g['dtime']));
		$rq=date('m月d日',strtotime($g['date']));
		$fbsj=date('m月d日H点',strtotime($g['dtime']));
		$title=$sj.' '."新销售日计划";
		$description =  '环保'.$rq.'销售计划<br><div class=\"highlight\">发布时间：'.$fbsj.'</div>';
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx133a00915c785dec&redirect_uri=http%3a%2f%2fwww.fjyuanxin.com%2fyxhb/add_sale_plan_wx.php?params='.$aid.'&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
		//新闻消息模式
		$info=$jssdk->sendCardMessage($receiver,$title,$description,$url,16);
		$sendStat=json_decode($info);
		if($sendStat->errcode==0){
	  		$stat2='Success!';
	  	} else {
		  	$stat2='ErrorCode'.$sendStat->errcode;
	  	}
		return $resArr;
	}
	  /**
	* 销售月计划审批通过后调用函数
	* @param  [integre] $aid [记录ID]
	* @return [array]      [状态]
	*/
	public function YxhbSalesPlanApply_monthEnd($aid)
	{
		$sql=new dedesql(false);
		$q="SELECT * FROM yxhb_sale_plan_month where id=$aid";
		$sql->setquery($q);
		$sql->execute();
		$g=$sql->getOne();
		$updateQuery="update #@__sale_plan_month set stat=1 where stat=2 and id=$aid";
		$updateQuery="update #@__sales_planmonth set stat=1 where stat=2 and relationid='{$g['relationid']}'";
		$res = $this->db->ExecuteNoneQuery($updateQuery);
		if ($res) {
			$resArr = array("status"=>"success");
		} else {
			$resArr = array("status"=>"failure");
		}
		$jssdk = new JSSDK();
		$receiver='csl|wk|shh';
		$sj=date('m月d日',strtotime($g['dtime']));
		$month=date('m月',strtotime($g['date']));
		$fbsj=date('m月d日H点',strtotime($g['dtime']));
		$title=$sj.' '."新销售月计划";
		$description = '环保'.$month.'销售计划<br><div class=\"highlight\">发布时间：'.$fbsj.'</div>';
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx133a00915c785dec&redirect_uri=http%3a%2f%2fwww.fjyuanxin.com%2fyxhb/add_sale_plan_month_wx.php?params='.$aid.'&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
		//新闻消息模式
		$info=$jssdk->sendCardMessage($receiver,$title,$description,$url,16);
		return $resArr;
	}
	//发货修改
	public function Yxhbfh_edit_ApplyEnd($aid)
	{
		$xg_date=getDatetimeMk(time()+8*60*60);
		$sql=new dedesql(false);
		$q="SELECT fh_num FROM yxhb_fh where id=$aid";
		$sql->setquery($q);
		$sql->execute();
		$g=$sql->getOne();
		$q2="SELECT * FROM yxhb_fhxg where fh_num='".$g['fh_num']."'";
		$sql->setquery($q2);
		$sql->execute();
		$row=$sql->getOne();
		$sql->close();
		$addQuery="insert into yxhb_fh(fh_num,fh_client,fh_anname,fh_cate,fh_kh,fh_snbh,fh_carnum,fh_thr,fh_bs,fh_kpy,fh_show,fh_qy,fh_bzfs,fh_wlfs,fh_pp,fh_stat,fh_zl,fh_bz,xg_date,fh_da,fh_date,fh_dfzl,fh_stat2,fh_wlname,fh_bid,fh_flag,fh_pz,fh_mz,fh_jz,fh_pass,fh_passtime,fh_stat4)  values('".$row['fh_num']."','".$row['fh_client']."','".$row['fh_anname']."','".$row['fh_cate']."','".$row['fh_kh']."','".$row['fh_snbh']."','".$row['fh_carnum']."','".$row['fh_thr']."','".$row['fh_bs']."','".$row['fh_kpy']."','".$row['fh_show']."','".$row['fh_qy']."','".$row['fh_bzfs']."','".$row['fh_wlfs']."','".$row['fh_pp']."','1','".$row['fh_zl']."','".$row['fh_bz']."',NOW(),'".$row['fh_da']."','".$row['fh_date']."','".$row['fh_dfzl']."','".$row['fh_stat2']."','".$row['fh_wlname']."','".$row['fh_bid']."','".$row['fh_flag']."','".$row['fh_pz']."','".$row['fh_mz']."','".$row['fh_jz']."','".$row['fh_pass']."','".$row['fh_passtime']."','2')";
		$this->db->ExecuteNoneQuery($addQuery);
		$updateQuery="update yxhb_fh set fh_stat='0' where id='".$aid."'";
		$res = $this->db->ExecuteNoneQuery($updateQuery);
		return $resArr;
	}

	/**
	* 临时额度审批通过后调用函数
	* @param  [integre] $aid [临时额度记录ID]
	* @return [array]      [状态]
	*/
	public function YxhbTempCreditLineApplyEnd($aid)
	{
		$updateQuery="update yxhb_tempcreditlineconfig set stat=1 where stat=2 and id=$aid";
		$res = $this->db->ExecuteNoneQuery($updateQuery);
  		$resArr = $res?array("status"=>"success"):array("status"=>"failure");
		return $resArr;
	}
// -----END------
}
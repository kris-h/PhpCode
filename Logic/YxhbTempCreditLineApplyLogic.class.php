<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbTempCreditLineApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_tempcreditlineconfig';

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->find();
    }

    public function getTableName()
    {
        return $this->trueTableName;
    }

    public function recordContent($id)
    {
        $res = $this->record($id);
        $info = $this->getInfo($res['clientid'],$res['date']);
        $result = array();
        $result['content']['date'] = $res['date'];
        $clientname = M('yxhb_guest2')->field('g_khjc')->where(array('id' => $res['clientid']))->find();
        $result['content']['clientname'] = $clientname['g_khjc'];

        $result['content']['ye'] = number_format($res['ye'],2,'.',',')."元";
        $result['content']['ed'] = number_format($res['ed'],2,'.',',')."元";
        $result['content']['line'] = number_format($res['line'],2,'.',',')."元";
        $result['content']['yxq'] = $res['yxq'];
        $result['content']['notice'] = $res['notice'];
        $result['content']['info'] = $info;
        $result['imgsrc'] = '';
        $result['applyerID'] = $res['salesid'];
        $result['applyerName'] = $res['sales'];
        $result['stat'] = $res['stat'];
        return $result;
    }

    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    {
        // $map = array('id' => $id);
        // return $this->field(true)->where($map)->setField('stat',0);
    }

    public function getInfo($clientid,$date){
        $result = array();
        $temp = A('tempQuote');

        $info = $temp->getQuoteTimes($clientid,$date);
        $result['two'] = 5-count($info[0]);
        $result['five'] = 3-count($info[1]);
        $result['ten'] = 1-count($info[2]);

        $ye = $temp->getClientFHYE($clientid,$date);
        $result['ye'] =  number_format($ye['ysye'],2,'.',',')."元";
        $result['line'] =  number_format($ye['line'],2,'.',',')."元";
        return $result;
    }

     /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();
        $clientname = M('yxhb_guest2')->field('g_khjc')->where(array('id' => $res['clientid']))->find();
        $result[] = array('name'=>'申请日期：',
                                     'value'=>$res['date'],
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_khjc'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'客户余额：',
                                     'value'=>number_format($res['ye'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'已有临额：',
                                     'value'=>number_format($res['ed'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申请额度：',
                                     'value'=>number_format($res['line'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申&nbsp;&nbsp;请&nbsp;&nbsp;人：',
                                     'value'=>$res['sales'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'申请理由：',
                                     'value'=>$res['notice'],
                                     'type'=>'text'
                                    );
        return $result;
    }



    /**
     * 获取申请人名/申请人ID（待定）
     * @param  integer $id 记录ID
     * @return string      申请人名
     */
    public function getApplyer($id)
    {
        // $map = array('id' => $id);
        // return $this->field(true)->where($map)->getField('jbr');
    }


      // $result['content'][] = array('name'=>'执行日期：',
        //                              'value'=>$res['date'],
        //                              'type'=>'date'
        //                             );
        // $result['content'][] = array('name'=>'客户名称：',
        //                              'value'=>$res['clientname'],
        //                              'type'=>'string'
        //                             );
        // $result['content'][] = array('name'=>'客户余额：',
        //                              'value'=>number_format($res['ye'],2,'.',',')."元",
        //                              'type'=>'number'
        //                             );
        // $result['content'][] = array('name'=>'已有临额：',
        //                              'value'=>number_format($res['ed'],2,'.',',')."元",
        //                              'type'=>'number'
        //                             );
        // $result['content'][] = array('name'=>'临时额度：',
        //                              'value'=>number_format($res['line'],2,'.',',')."元",
        //                              'type'=>'number'
        //                             );
        // $result['content'][] = array('name'=>'有&nbsp;&nbsp;效&nbsp;&nbsp;期：',
        //                              'value'=>$res['yxq'],
        //                              'type'=>'number'
        //                             );
        // $result['content'][] = array('name'=>'销&nbsp;&nbsp;售&nbsp;&nbsp;员：',
        //                              'value'=>$res['sales'],
        //                              'type'=>'string'
        //                             );
        // $result['content'][] = array('name'=>'申请理由：',
        //                              'value'=>$res['notice'],
        //                              'type'=>'text'
        //                             );
}
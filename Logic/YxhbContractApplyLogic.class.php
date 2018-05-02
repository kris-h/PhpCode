<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbContractApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_ht';

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array('pid' => $id);
        return $this->field(true)->where($map)->find();
    }

    public function getTableName()
    {
        return $this->trueTableName;
    }

    public function recordContent($id)
    {
        $res = $this->record($id);
        $result = array();
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>D('yxhb_guest2')->getName($res['ht_khmc']),
                                     'type'=>'string'
                                    );
        // $result['content'][] = array('name'=>'销售方式：',
        //                              'value'=>$res['stday'],
        //                              'type'=>'string'
        //                             );
        $result['content'][] = array('name'=>'开票方式：',
                                     'value'=>$res['ht_kpfs'],
                                     'type'=>'string'
                                    );
        $result['content'][] = array('name'=>'数量结算：',
                                     'value'=>$res['ht_sljsfs'],
                                     'type'=>'string'
                                    );
        $result['content'][] = array('name'=>'有&nbsp;&nbsp;效&nbsp;&nbsp;期：',
                                     'value'=>$res['ht_stday'].'至'.$res['ht_enday'],
                                     'type'=>'string'
                                    );
        // $result['content'][] = array('name'=>'申&nbsp;&nbsp;请&nbsp;&nbsp;人：',
        //                              'value'=>$res['kp_jbr'],
        //                              'type'=>'string'
        //                             );
        // $result['content'][] = array('name'=>'汇款单位：',
        //                              'value'=>$res['kp_hkdw'],
        //                              'type'=>'string'
        //                             );
        $result['content'][] = array('name'=>'品&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;种：',
                                     'value'=>$res['ht_bzfs'].$res['ht_cate'],
                                     'type'=>'string'
                                    );
        $result['content'][] = array('name'=>'单&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;价：',
                                     'value'=>$res['ht_dj'],
                                     'type'=>'number'
                                    );
        $result['content'][] = array('name'=>'运&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;费：',
                                     'value'=>$res['ht_yf'],
                                     'type'=>'number'
                                    );
        // $result['content'][] = array('name'=>'&nbsp;&nbsp;额：',
        //                              'value'=>$res['kp_je'],
        //                              'type'=>'number'
        //                             );
        // $result['content'][] = array('name'=>'销&nbsp;&nbsp;售&nbsp;&nbsp;员：',
        //                              'value'=>$res['sales'],
        //                              'type'=>'string'
        //                             );
        // $result['content'][] = array('name'=>'备注：',
        //                              'value'=>$res['notice'],
        //                              'type'=>'text'
        //                             );
        $result['imgsrc'] = '';
        // $result['applyerID'] = $res['applyerus'];
        // $result['applyerName'] = D('yxhb_boss')->getusername($res['applyerus']);
        $result['applyerName'] = $res['ht_rdy'];
        $result['applyerID'] = D('yxhb_boss')->getIDFromName($res['ht_rdy']);
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
    
}
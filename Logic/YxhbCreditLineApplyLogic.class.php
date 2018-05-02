<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbCreditLineApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_creditlineconfig';

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array('aid' => $id);
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
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=>$res['date'],
                                     'type'=>'date'
                                    );
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>$res['clientname'],
                                     'type'=>'string'
                                    );
        $result['content'][] = array('name'=>'当前额度：',
                                     'value'=>number_format($res['online'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result['content'][] = array('name'=>'发货下限：',
                                     'value'=>number_format($res['lower'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result['content'][] = array('name'=>'信用额度：',
                                     'value'=>number_format($res['line'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        // $result['content'][] = array('name'=>'有&nbsp;&nbsp;效&nbsp;&nbsp;期：',
        //                              'value'=>$res['yxq'],
        //                              'type'=>'number'
        //                             );
        // $result['content'][] = array('name'=>'销&nbsp;&nbsp;售&nbsp;&nbsp;员：',
        //                              'value'=>$res['sales'],
        //                              'type'=>'string'
        //                             );
        $result['content'][] = array('name'=>'备&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;注：',
                                     'value'=>$res['notice'],
                                     'type'=>'text'
                                    );
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
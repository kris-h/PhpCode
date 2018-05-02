<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbGuestJsApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_js';

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
        $jslx = array('1'=>'磅差调整', '3'=>'手续费', '4'=>'其他', '5'=>'价差调整', '6'=>'业务费调整');
        $res = $this->record($id);
        $result = array();
        $result['content'][] = array('name'=>'执行日期：',
                                     'value'=>$res['js_date'],
                                     'type'=>'date'
                                    );
        $result['content'][] = array('name'=>'结算日期：',
                                     'value'=>$res['js_stday']."至".$res['js_enday'],
                                     'type'=>'string'
                                    );
        $result['content'][] = array('name'=>'结算类型：',
                                     'value'=>$jslx[$res['jslx']],
                                     'type'=>'string'
                                    );
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>D('yxhb_guest2')->getName($res['client']),
                                     'type'=>'string'
                                    );
        $result['content'][] = array('name'=>'品&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;种：',
                                     'value'=>$res['js_cate'],
                                     'type'=>'number'
                                    );
        $result['content'][] = array('name'=>'价&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;差：',
                                     'value'=>$res['js_dj']."元",
                                     'type'=>'number'
                                    );
        $result['content'][] = array('name'=>'重&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;量：',
                                     'value'=>$res['js_zl']."吨",
                                     'type'=>'number'
                                    );
        $result['content'][] = array('name'=>'合&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;计：',
                                     'value'=>number_format($res['js_je'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result['content'][] = array('name'=>'申&nbsp;&nbsp;请&nbsp;&nbsp;人：',
                                     'value'=>$res['rdy'],
                                     'type'=>'string'
                                    );
        $result['content'][] = array('name'=>'备&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;注：',
                                     'value'=>$res['js_bz'],
                                     'type'=>'text'
                                    );
        $result['imgsrc'] = '';
        $result['applyerName'] = $res['rdy'];
        $result['applyerID'] = D('yxhb_boss')->getIDFromName($res['rdy']);
        $result['stat'] = $res['js_stat'];
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
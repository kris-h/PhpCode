<?php
namespace Light\Model;
use Think\Model;

/**
 * 部门模型
 * @author 
 */

class DepartmentModel extends Model {
    
    protected $autoCheckFields = false;
    /**
     * 获取微信部门信息
     * @param  integer $id 部门ID
     * @return array       部门详情
     */
    public function getWXDeptInfo($id=''){
        $wxInfoArr = array();
        $WeChat = new \Org\Util\WeChat;
        $wxInfo = json_decode($WeChat->getDeptInfo($id));
        if ( 'ok'==$wxInfo->errmsg ) {
            // 对象转数组
            foreach ($wxInfo->department as $value) {
                $wxInfoArr[] = array( 'id'=>$value->id,
                                      'name'=>$value->name,
                                      'parentid'=>$value->parentid,
                                      'order'=>$value->order,
                                    );
            }
        }
        return $wxInfoArr;
    }

    /**
     * 仅获取微信指定部门信息（不含子部门）
     * @param  integer $id 部门ID
     * @return array       部门详情
     */
    public function getWXDeptInfoOnly($id){
        $wxInfoArr = array();
        $WeChat = new \Org\Util\WeChat;
        $wxInfo = json_decode($WeChat->getDeptInfo($id));
        if ( 'ok'==$wxInfo->errmsg ) {
            // 对象转数组
            foreach ($wxInfo->department as $value) {
                if ($value->id==$id) {
                    $wxInfoArr = array( 'id'=>$value->id,
                                      'name'=>$value->name,
                                      'parentid'=>$value->parentid,
                                      'order'=>$value->order,
                                    );
                }
            }
        }
        return $wxInfoArr;
    }

    /**
     * 获取部门树
     * @param array $DeptArr 部门数组
     * @param integer $pk  对比字段
     * @param integer $pid 父字段
     * @return array 部门树数组
     */
    public function getDeptTree($DeptArr, $pk='id', $pid='parentid')
    {
        $wxInfoTree = list_to_tree($DeptArr, $pk, $pid);
        return $wxInfoTree;
    }

    /**
     * 获取子部门（排序）
     * @param array $DeptArr 部门数组
     * @param integer $pid 父部门ID
     * @return array 子部门数组
     */
    public function getChildDept($DeptArr, $pid = 1)
    {
        $childDept = array();
        foreach ($DeptArr as $key => $value) {
            if ($value['parentid'] == $pid) {
                $childDept[] = $value;
            }
        }
        $res = list_sort_by($childDept, 'order', 'desc');
        return $res;
    }

    /**
     * 获取部门Html
     */
    public function genDeptHtml($DeptArr)
    {
        $html = '';
        foreach ($DeptArr as $key => $value) {
            $html .= '<a class="weui-cell weui-cell_access select-department" href="javascript:;" data-id="'.$value['id'].'" data-type="dept" style="text-decoration:none;"><div class="weui-cell__hd"><img src="/fjyxoaSV/Public/assets/i/weui/icon_nav_button.png" alt="" style="width:20px;margin-right:5px;display:block"></div><div class="weui-cell__bd"><p style="margin-bottom: 0px;">'.$value['name'].'</p></div><div class="weui-cell__ft"></div></a>';
        }
        return $html;
    }

    /**
     * 获取微信部门成员
     * @param  integer $id 部门ID
     * @return array       部门成员详情
     */
    public function getWXDeptUserInfo($id){
        $wxInfoArr = array();
        $WeChat = new \Org\Util\WeChat;
        $wxInfo = json_decode($WeChat->getDeptUserInfo($id));
        if ( 'ok'==$wxInfo->errmsg ) {
            // 对象转数组
            foreach ($wxInfo->userlist as $value) {
                if (1==$value->status) {
                    $wxInfoArr[] = array( 'id'=>$value->userid,
                                      'name'=>$value->name,
                                      'avatar'=>$value->avatar,
                                      'order'=>$value->order[0],
                                    );
                }
            }
        }
        $res = list_sort_by($wxInfoArr, 'order', 'desc');
        // dump($wxInfoArr);
        return $res;
    }

    /**
     * 获取部门成员Html
     */
    public function genDeptUserHtml($DeptUserArr)
    {
        $html = '';
        foreach ($DeptUserArr as $key => $value) {
            $html .= '<a class="weui-cell weui-cell_access select-user" href="javascript:;" data-id="'.$value['id'].'" data-type="user" data-img="'.$value['avatar'].'" data-name="'.$value['name'].'" style="text-decoration:none;"><div class="weui-cell__hd"><img src="'.$value['avatar'].'" alt="" style="width:20px;margin-right:5px;display:block"></div><div class="weui-cell__bd"><p style="margin-bottom: 0px;">'.$value['name'].'</p></div><div class="weui-cell__ft"></div></a>';
        }
        return $html;
    }

}

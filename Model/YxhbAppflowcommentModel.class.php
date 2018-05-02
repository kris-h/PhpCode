<?php
namespace Light\Model;
use Think\Model;
/**
 * 环保审批评论记录模型
 * @author 
 */

class YxhbAppflowcommentModel extends Model {

    /**
     * 获取审批评论记录
     * @param  string $modname 流程名
     * @param  int    $aid 记录ID
     * @return array   摘要数组
     */
    public function contentComment($mod_name, $aid)
    {
        // 评论名单
        $comment_list = array();
        $cl = $this->field('id,app_word,time,per_name,per_id,comment_to_id')->where(array('aid'=>$aid, 'mod_name'=>$mod_name, 'app_stat'=>1))->order('time desc')->select();
        $boss = D('yxhb_boss');
        foreach ($cl as $v) {
              $cwxUID = $boss->getWXFromID($v['per_id']);
              $avatar = $boss->getAvatar($v['per_id']);
              
              if (!empty($v['comment_to_id'])) {
                  $commentUserArr = explode(',', $v['comment_to_id']);
                  $commentUserArr = array_map(function($wxid) use ($boss) {
                      $cid = $boss->getIDFromWX($wxid);
                      $crealname = $boss->getusername($cid);
                      return $crealname;
                  }, $commentUserArr);
                  // dump($commentUserArr);
                  $commentUser = "@".implode('@', $commentUserArr)." ";
              } else {
                  $commentUser = " ";
              }
              // 超过2小时不能删除
              if (time()-strtotime($v['time'])>7200) {
                  $v['del_able'] = 0;
              } else {
                  $v['del_able'] = 1;
              }
              $comment_list[] = array('id'=>$v['id'], 'pid'=>$v['per_id'], 'avatar'=>$avatar, 'name'=>$v['per_name'], 'time'=>$v['time'], 'word'=>$commentUser.$v['app_word'], 'del_able'=>$v['del_able'],'wxid'=>$cwxUID);
        }

        return $comment_list;
    }

}

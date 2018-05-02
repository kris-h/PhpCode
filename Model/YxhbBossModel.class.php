<?php
namespace Light\Model;
use Think\Model;

/**
 * 用户模型
 * @author 
 */

class YxhbBossModel extends Model {

    // protected $tablePrefix = 'oa_';
    protected $_validate = array(
        array('username', '1,16', '昵称长度为1-16个字符', self::EXISTS_VALIDATE, 'length'),
        array('username', '', '昵称被占用', self::EXISTS_VALIDATE, 'unique'), //用户名被占用
    );

    public function lists($state = 1, $order = 'id DESC', $field = true){
        $map = array('state' => $state);
        return $this->field($field)->where($map)->order($order)->select();
    }

    /**
     * 登录指定用户
     * @param  integer $id 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($id){
        /* 检测是否在当前应用注册 */
        $user = $this->field("id as yxhb_id,name,wxid,boss,rank as yxhb_rank,'yxhb' as system")->where(array('id'=>(int)$id))->find();
        $snglUser = M('kk_boss')->field("id as kk_id,rank as kk_rank,'kk' as system")->where(array('wxid'=>$user['wxid']))->find();
        $user['kk_id'] = $snglUser['kk_id'];
        $user['kk_rank'] = $snglUser['kk_rank'];
        if(!$user) {
            $this->error = '用户不存在或已被禁用！'; //应用级别禁用
            return false;
        }

        //记录行为
        action_log('user_login', 'YxrtBoss', $id, $id);

        /* 登录用户 */
        $this->autoLogin($user);
        return true;
    }

    /**
     * 注销当前用户
     * @return void
     */
    public function logout(){
        session('user_auth', null);
        session('user_auth_sign', null);
    }

    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user){
        /* 更新登录信息 */
        $data = array(
            'id'             => $user['yxhb_id'],
            'logintime' => date("Y-m-d H:i:s",time()),
            // 'loginip'   => get_client_ip(0),
        );
        $this->save($data);

        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'yxhb_id'     => $user['yxhb_id'],
            'yxhb_rank'   => $user['yxhb_rank'],
            'name'        => $user['name'],
            'boss'        => $user['boss'],
            'kk_id'       => $user['kk_id'],
            'kk_rank'     => $user['kk_rank'],
            'wxid'        => $user['wxid'],
        );

        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));
        // PC端OA注册
        session ('yxhb_id'    , $user['yxhb_id'] );           // 当前用户id
        session ('yxhb_rank' , $user['yxhb_rank']); 
        session ('name'  , $user['name'] );     // 当前用户昵称
        session ('wxid' , $user['wxid']); 
        session ('kk_id' , $user['kk_id']); 
        session ('kk_rank' , $user['kk_rank']); 
    }

    public function getusername($id){
        return $this->where(array('id'=>(int)$id))->getField('name');
    }


    /**
     * 微信登录
     * @param  
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function loginWX(){
        $code = I('get.code');
        if (empty($code)) {
            return false;
        }
        $WeChat = new \Org\Util\WeChat;
        $access_token = $WeChat->getAccessToken();
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=".$access_token."&code=".$code;
        $res = $WeChat->httpGet($url);
        $result = json_decode($res);
        // dump($result);
        if ($result->errmsg != 'ok') {
            return false;
        }
        $wxID = $result->UserId;
        $uid = $this->getIDFromWX($wxID);
        // dump($uid);
        // exit();
        if (empty($uid)) {
            return false;
        }
        // dump($uid);
        // exit();
        $this->login($uid);
        return true;
    }

    /**
     * 由微信ID获取用户ID
     * @param $wxid 用户微信ID
     * @retrun integer $id 用户ID
     */
    public function getIDFromWX($id){
        return $this->where(array('wxid'=>$id))->getField('id');
    }

    public function getWXFromID($id){
        return $this->where(array('id'=>$id))->getField('wxid');
    }

    public function getIDFromName($name){
        return $this->where(array('name'=>$name))->getField('id');
    }

    public function getWXInfo($wxid='')
    {
        $wxInfoArr = array();
        $WeChat = new \Org\Util\WeChat;
        $wxInfo = json_decode($WeChat->getUserInfo($wxid));
        if ( 'ok'==$wxInfo->errmsg && 1==$wxInfo->status) {
          $wxInfoArr = array( 'id'=>$wxInfo->userid,
                                'name'=>$wxInfo->name,
                                'avatar'=>$wxInfo->avatar,
                                'order'=>$wxInfo->order[0],
                              );
        }
        return $wxInfoArr;
    }

    public function getAvatar($id)
    {
        $avatar = $this->where(array('id'=>$id))->getField('avatar');
        if (empty($avatar)) {
            $wxid = $this->getWXFromID($id);
            $wxInfo = $this->getWXInfo($wxid);
            $avatar = $wxInfo['avatar'];
            if (empty($avatar)) {
                $avatar = "Public/assets/i/defaulthead.png";
            }
            $data['avatar'] = $avatar;
            $this->where(array('id'=>$id))->save($data);
        }
        return $avatar;
    }
}

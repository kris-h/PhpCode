<?php
namespace Light\Controller;

class LoginController extends \Think\Controller {
	public function index() {
        $this->display ();
	}
    /**
     * 用户登录
     * 
     */
    public function login($username = null, $password = null){
        if(IS_POST){
            /* 获取用户数据 */
            $username = I('post.username');
            $verify = I('post.verify');
            $password = md5(I('post.password'));
            $user = M('yxhb_boss')->where("boss='".$username."'")->find();
            if(!$this->check_verify($verify)){
                $uid = -3; //验证码错误
            } elseif(is_array($user)){
                /* 验证用户密码 */
                if($password === $user['password']) {
                    // $this->updateLogin($user['id']); //更新用户登录信息
                    $uid = $user['id']; //登录成功，返回用户ID
                } else {
                    $uid = -2; //密码错误
                }
            } else {
                $uid = -1; //用户不存在或被禁用
            }
            //登录成功
            if(0 < $uid){ 
                /* 登录用户 */
                $User = D('yxhb_boss');
                if($User->login($uid)){ //登录用户
                    //TODO:跳转到登录前页面
                    $this->success('登录成功！', U('Light/Index/index'));
                } else {
                    $this->error('登录失败！', U('Light/Login/index'));
                }

            } else { //登录失败
                switch($uid) {
                    case -1: $error = '用户不存在或密码错误！'; break; //系统级别禁用
                    case -2: $error = '用户不存在或密码错误！'; break;
                    case -3: $error = '验证码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }
        } else {
            if(is_login()){
                $this->redirect('Light/Index/index');
            }else{
                $this->error('非法登录！', U('Light/Login/index'));
            }
        }
    }
	public function logout() {

		session(null);

		$this->success ( '已注销登录！', U ( "Light/Login/index" ) );
	}

    public function verify(){
        $config =    array(
            'fontSize'    =>    20,    // 验证码字体大小
            'length'      =>    4    // 验证码位数
        );
        $verify = new \Think\Verify($config);
        $verify->entry();
    }

    // 检测输入的验证码是否正确，$code为用户输入的验证码字符串
    public function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }
}
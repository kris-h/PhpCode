<?php
namespace Light\Controller;
use Think\Controller;

class BaseController extends Controller {

    private $urlHead = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx133a00915c785dec&redirect_uri=http%3a%2f%2fwww.fjyuanxin.com';

    private $urlEnd = '&response_type=code&scope=snsapi_base&state=YUANXIN#wechat_redirect';/**
	 * 判断是否登录
	 */
	public function _initialize(){
    // // 判断用户是否登陆
    // $id = session('user_auth.id');
    // if(!$id){
    //     $this->success('请先登录',U('Login/index'),2);
    //     exit;
    // }
    // 获取当前用户ID
    define('WXID',is_login());
    if( !WXID ){
      $url = MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
      $realURL = __SELF__;
      $redirectURL = $this->urlHead.urlencode($realURL).$this->urlEnd;
      // 微信登录判断
      $state = I('get.state');
      $system = I('get.system');
      // 是否来自微信
      if ($state == 'YUANXIN') {
        $User = D($system.'Boss');
        if($User->loginWX()){
            // $this->redirect('WeChat/Index/index');
            $this->redirect($realURL);
        } else {
            $this->error('用户不存在或已被禁用！', $redirectURL, 5 );
        }
      }
      // 还没登录 跳转到登录页面
       // $this->error ( '登录过期，自动重新登录！', $redirectURL, 0 );
      redirect($redirectURL);
    }
    //检测权限
    //动态配置用户表
    // C('DB_PREFIX', $system);
    $rule  = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
    if ( !$this->checkRule($rule,array('in','1,2')) ){
        $this->error('无访问权限!'.$rule);
    }
  }

  /**
   * 权限检测
   * @param string  $rule    检测的规则
   * @param string  $mode    check模式
   * @return boolean
   */
  final protected function checkRule($rule, $type=AuthRuleModel::RULE_URL, $mode='url'){
      static $Auth    =   null;
      if (!$Auth) {
          $Auth       =   new \Think\Auth();
      }
      if(!$Auth->check($rule,WXID,$type,$mode)){
          return false;
      }
      return true;
  }

}
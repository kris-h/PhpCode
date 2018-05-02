<?php
namespace Light\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
    	$this->assign('time', date('Y-m-d H:i'));
    	$this->assign('exp', C('SESSION_OPTIONS.expire'));
        $this->display();
    }

    public function home(){
        $this->display('index');
    }

	public function logout() {

		session(null);

		$this->success ( '已注销登录！', U ( "Light/Login/index" ) );
	}
}
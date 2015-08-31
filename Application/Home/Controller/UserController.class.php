<?php
/*
*    Online Service Center for Chongqing Jiaotong University 
*    Copyright (C) 2015 freyhsiao@gmail.com
*
*    This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License along
*    with this program; if not, write to the Free Software Foundation, Inc.,
*    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA. 	
*/

namespace Home\Controller;
use Think\Controller;
class UserController extends SimpleController {
    public function index(){
    	$this->redirect('login');
    }

    //用户登录
    public function login(){
    	if(session('?uid'))$this->redirect('order');

        //是否允许注册
        $this->assign('allowregister',F('settings')['global']['allowregister']);
        $this->assign('quickreport',F('settings')['global']['quickreport']);

    	if(IS_POST){
            $database = D('user');  
            if(!$database->create()){
                $this->error($database->getError()); 
            }         
            if(!$this->checkVerify(I('post.verify'))){
                $this->error('验证码错误');
            } 
			
			//UC登陆（用户名+密码模式）
			if(F('settings')['global']['quickreport']=='false' && F('settings')['global']['uclogin']=='normal'){
				$this->ucLogin();
			}
			
			//自定义验证
			if(F('settings')['global']['quickreport']=='true'  && F('settings')['global']['uclogin']=='diy'){
				$this->diyLogin();
			}
            
            if(F('settings')['global']['quickreport']=='true'){ //姓名+识别码
	            $user = $database->where('uid = :uid and username = :username')
								 ->bind(array(':uid'=>I('post.uid'),':username'=>strtolower(I('post.username'))))
								 ->find();
	    		if(empty($user))$this->error('信息不匹配');				
            }else{ //识别码+密码	
				$user = $database->where('uid = :uid')->bind(array(':uid'=>I('post.uid')))->find();
				if(empty($user))$this->error('用户不存在');
	            $user = $database->where('uid = :uid and password = :password')
								 ->bind(array(':uid'=>I('post.uid'),':password'=>sha1(C('DB_PREFIX').I('post.password').'_'.$user['salt'])))
								 ->find();
	    		if(empty($user))$this->error('密码错误');
            }                		
			
			//更新登陆信息
			$data['lastip'] = get_client_ip();
			$data['lasttime'] = time();		
			$database->where('uid=:uid')->bind(':uid',$user['uid'])->save($data);
			
			session('uid',$user['uid']);
			if(!empty($user['username'])){
				session('username',$user['username']);
			}
			if(!empty(I('get.returnURL'))){
				redirect(base64_decode(base64_decode(I('get.returnURL'))));
			}
			$this->redirect('Main/index');
    	}else{
	        $this->assign('tips',F('settings')['tips']['login']);     		
    		$this->display('login');
    	}
    }

    //用户注册
    public function register(){
    	if(session('?uid'))$this->redirect('order');

        //是否允许注册
        $this->assign('allowregister',F('settings')['global']['allowregister']);
        $this->assign('quickreport',F('settings')['global']['quickreport']);

        if(F('settings')['global']['allowregister']=='false')$this->error('站点已关闭注册');  	
		
    	if(IS_POST){
    		$database = D("User");          
            if(!$database->create()){
                $this->error($database->getError());
            }
            if(!$this->checkVerify(I('post.verify'))){
                $this->error('验证码错误');
            }
            $data['uid'] = I('post.uid');
			if($database->where($data)->find())$this->error('用户已存在');
            if(F('settings')['global']['quickreport']=='false'){
                $salt = salt();
                $data['salt'] = $salt;
                $data['password'] = sha1(C('DB_PREFIX').I('post.password').'_'.$salt);               
            }else{
                $data['username'] = I('post.username');
            }

            $add = $database->strict(true)->data($data)->filter('strip_tags')->add();
            if($add){
            	$this->success('注册成功',U('User/login'));
            }else{
            	$this->error('注册失败');
            }
    	}else{
	        $this->assign('tips',F('settings')['tips']['register']);    		
    		$this->display('register');
    	}
    }

    //找回密码
    public function findpsw(){
    	$this->error('请联系网站管理员解决');
    }

    //用户注销
    public function logout(){
    	session(null);
    	$this->redirect('login');
    }

    //个人报修记录
    public function order(){
    	if(!session('?uid'))$this->redirect('login');   
    	$database = M('order');	
    	$list = $database->where('user=:user')->bind(':user',session('uid'))->order('time desc')->page(I('get.p/d').',25')->select();
    	$this->assign('list',$list);
		$count = $database->where('user=:user')->bind(':user',session('uid'))->count();// 查询满足要求的总记录数
		$show = pagination($count);// 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
    	$this->display('order'); 
    }

    //个人信息设置
    public function setting(){
    	if(!session('?uid'))$this->redirect('login');

        $this->assign('quickreport',F('settings')['global']['quickreport']);//快速报修
		
    	$database = M('user');
    	if(IS_POST){   
            if(!$database->autoCheckToken($_POST)){
				$this->error('令牌验证错误');
			}               
    		$data['area'] = I('post.area/d');
            if(!selectCheck($data['area'])){
				$this->error('参数非法');
			}
    		$data['building'] = I('post.building/d');
            if(!selectCheck($data['area'],$data['building'])){
				$this->error('参数非法');
			}
    		$data['location'] = I('post.location');
    		$data['tel'] = I('post.tel');
    		if(!empty(I('post.password'))){
                $data['salt'] = salt();
                $data['pssword'] = sha1(C('DB_PREFIX').I('post.password').'_'.$data['salt']);
            }
    		$update = $database->where('uid=:uid')->bind(':uid',session('uid'))->filter('strip_tags')->save($data);
    		if($update){
    			$this->success('个人信息更新成功');
    		}else{
    			$this->error('个人信息无更新或失败');
    		}
    	}else{
	    	$user = $database->where('uid=:uid')->bind(':uid',session('uid'))->find();
            $data = menu();
            $this->assign('data',json_encode($data));
	    	$this->assign('user',$user);
	    	$this->display('setting');
    	}
    }     
	
	//用户取消报修
    public function cancel(){
    	if(!session('?uid'))$this->redirect('login');
    	if(IS_AJAX && IS_POST){
    		$database = M('order');
    		$map['order'] = ':order';
    		$map['user'] = ':user';
    		$bind[':order'] = I('post.order');
    		$bind[':user'] = session('uid');
    		$order = $database->where($map)->bind($bind)->find();
    		if(!empty($order) AND $order['status']==0){
    			$data['status'] = -1;
    			$data['canceltime'] = time();
    			$cancel = $database->lock(true)->where($map)->bind($bind)->save($data);
    			echo $cancel;
    		}
    	}
    }

	//显示验证码
    public function showVerify(){
        $Verify =     new \Think\Verify();
        $Verify->codeSet = '0123456789';  // 设置验证码字符为纯数字
        $Verify->entry();
    }

    // 检测输入的验证码是否正确，$code为用户输入的验证码字符串
    private function checkVerify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }
	
	private function ucLogin(){
		$uc = new \Ucenter\Client\Client();
		require_cache(MODULE_PATH."Conf/uc.php");
		$name = UC_DBCHARSET=='gbk'?mb_convert_encoding(I('post.username'),'gbk','utf-8'):I('post.username');
		$password = UC_DBCHARSET=='gbk'?mb_convert_encoding(I('post.password'),'gbk','utf-8'):I('post.password');
		list($uid, $username, $uc_password, $email) = $uc -> uc_user_login($name, $password);
		$_POST['uid'] = UC_DBCHARSET=='gbk'?mb_convert_encoding($uid,'utf-8','gbk'):$uid;
		
		if(I('post.uid') > 0) {
			$_POST['username'] = strtolower(I('post.username'));
			$this->createUser();
		} elseif(I('post.uid') == -1) {
			$this -> error('登陆失败:用户不存在,或者被删除');
		} elseif(I('post.uid') == -2) {
			$this -> error('登陆失败:密码错');
		} else {
			$this -> error('登陆失败:未定义');
		}				
	}
	
	private function diyLogin(){
		$this->error('未定义验证方法');
		/* 以下仅供参考 验证通过后创建/更新用户
		* $oauth = A('Oauth','Event'); //验证方法
		* $oauth->tablename = '';
		* $oauth->map = array('id'=>':id');
		* $oauth->bind = array(':id'=>I('post.id'));
		* $info = $oauth->uc();
		* if(!$info)$this->error('用户不存在');
		* $_POST['uid'] = '';
		* $_POST['username'] = '';
		* $_POST['password'] = '';
		* 
		* $this->createUser();	
		**/
	}
		
	//UC或自定义验证通过写入/更新用户数据
	private function createUser(){
		$add['uid'] = I('post.uid');
		$add['username'] = I('post.username');
		$add['salt'] = salt();	
		$add['password'] = sha1(C('DB_PREFIX').I('post.password').'_'.$add['salt']);					
		M('user')->add($this->add,$options=array(),$replace=true);		
	}
}
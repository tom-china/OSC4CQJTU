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

namespace Admin\Controller;
use Think\Controller;
class MainController extends SimpleController {
    //登录
    public function index(){
        if(session('?admin')){
			$this->redirect('Main/dashboard');
		}
    	if(IS_POST){
    		$database = D('admin');
            if(!$database->create()){
                $this->error($database->getError()); 
            } 	
			if(!$this->checkVerify(I('post.verify'))){
				$this->error('验证码错误');
			}
            $bind[':username'] = I('post.username'); 
            $admin = $database->where('username=:username')->bind($bind)->find();             		
    		if(empty($admin))$this->error('用户不存在');
    		$bind[':password'] = sha1(C('DB_PREFIX').I('post.password').'_'.$admin['salt']);
    		$admin = $database->where('username=:username and password=:password')->bind($bind)->find();
    		if(empty($admin)){
    			$this->error('密码错误');
    		}else{
	            session('admin',$admin['username']);
                session('right',$admin['right']);
                $data['lastip'] = get_client_ip();
                $data['lasttime'] = time();
                $database->where('username=:username and password=:password')->bind($bind)->save($data);
				session('security',sha1($data['lastip'].$_SERVER['HTTP_USER_AGENT']));
	    		$this->redirect('dashboard');
    		}
    	}else{
    		$this->display('admin-login');
    	}
    }	

    //Echart.js 7日统计数据
    public function dashboard(){
        if(!session('?admin')){
			$this->redirect('Main/index');
		}
		$database = M('order');
		for($i=7;$i>0;$i--){
			$_category[] = date('y-m-d',strtotime('-'.$i.' days'));
			$_todo[] = $database->cache(true,60)->where('status=0 and time>'.strtotime('-'.$i.' days').' and time<'.strtotime('-'.($i-1).' days'))->count();
			$_doing[] = $database->cache(true,60)->where('status=1 and time>'.strtotime('-'.$i.' days').' and time<'.strtotime('-'.($i-1).' days'))->count();
			$_done[] = $database->cache(true,60)->where('status=2 and time>'.strtotime('-'.$i.' days').' and time<'.strtotime('-'.($i-1).' days'))->count();		
		}
        $stat['category'] = json_encode($_category);	
        $stat['todo'] = json_encode($_todo);
        $stat['doing'] = json_encode($_doing);
        $stat['done'] = json_encode($_done);
        $this->assign('stat',$stat);

        //仅显示范围内的紧急报修
        $admin = M('admin')->where('username=:username')->bind(':username',session('admin'))->find();
        $admin = json_decode($admin['location'],true);
        if(!empty($admin['area']) && !empty($admin['building'])){
            $where['area'] = array('in',$admin['area']);
            $where['building'] = array('in',$admin['building']);
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        elseif(!empty($admin['area'])){
			$map['area'] = array('in',$admin['area']);
		}
        elseif(!empty($admin['building'])){
			$map['building'] = array('in',$admin['building']);
		}
        $map['emerg'] = 1;
        $map['status'] = array('neq',-1);
        $list = $database->where($map)->order('time desc')->limit(5)->select();
        $this->assign('list',$list);
    	$this->display('admin-index');
    }

    //密码修改
    public function user(){
        if(!session('?admin')){
			$this->redirect('Main/index');
		}
        if(IS_POST){
            $database = M('admin');
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误');
            }           
            $bind[':username'] = session('admin');
            $admin = $database->where('username=:username')->bind($bind)->find();
            if(empty($admin))$this->error('用户不存在');
            $data['password'] = sha1(C('DB_PREFIX').I('post.n-password').'_'.$admin['salt']);
            $update = $database->where('username=:username')->bind($bind)->save($data);
            if($update){
                $this->success('密码已更新',U('Main/user'));
            }else{
                $this->error('原密码不匹配',U('Main/user'));
            }           
        }else{
            $this->display('admin-user');
        }
    }  

    //注销
    public function logout(){
        session(null);
    	$this->redirect('Main/index');
    }

    //验证码
    public function showVerify(){
		$Verify =     new \Think\Verify();
		//中文验证码字体使用 ThinkPHP/Library/Think/Verify/ttfs/5.ttf
		//$Verify->useZh = true; 
		$Verify->entry();    	
    }

	// 检测输入的验证码是否正确，$code为用户输入的验证码字符串
	private function checkVerify($code, $id = ''){
		$verify = new \Think\Verify();
		return $verify->check($code, $id);
	}

}
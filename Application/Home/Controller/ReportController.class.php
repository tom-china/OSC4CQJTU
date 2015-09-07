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
class ReportController extends SimpleController {
	public function __construct(){
		parent::__construct();
	}

	//普通报修
    public function index(){
    	if(session('?uid')){
    		$database = M('user');
 			$user = $database->where('uid = :uid')->bind(':uid',session('uid'))->find(); 
 			$this->assign('user',$user);
    	}else{
			session('returnURL',__SELF__);
    		$this->redirect('User/login');
    	}
    	if(IS_POST){
			if(cookie('last_normal_report')){
				$this->error('操作频繁请休息片刻~');
			}
    		$database = D('order');
            if (!$database->create()){
                $this->error($database->getError());
            }			
            if(!empty($_POST['tel'])){
            	M('user')->where('uid=:uid')->bind(':uid',session('uid'))->save(array('tel'=>I('post.tel')));
            }
    		$data['area'] = I('post.area/d');//校区
    		if(!selectCheck($data['area']))$this->error('参数非法');
			$data['building'] = I('post.building/d');//楼栋
			if(!selectCheck($data['area'],$data['building']))$this->error('参数非法');
    		$data['location'] = I('post.location');//寝室
    		$data['good'] = I('post.good');//物品
    		$data['description'] = I('post.description');//描述
    		$data['user'] = session('uid');//用户
    		$data['order'] = creatOrderSn($data['area']);//工单号
    		$data['time'] = time();//时间
    		$data['status'] = 0;//状态 未处理0 处理中1 已处理2
    		$data['emerg'] = 0;//是否紧急 普通0 紧急1
			if($_FILES){
				$info = $this->upload();
				foreach($info as $file){
					$img[] = $file['savepath'].$file['savename'];
				}
				$data['img'] = json_encode($img);				
			}    		
    		$add = $database->strict(true)->data($data)->filter('strip_tags')->add();
    		if($add){
				cookie('last_normal_report',time(),array('expire'=>60));
    			$this->success('报修提交成功',U('User/order'));
    		}else{
    			$this->error('报修提交失败');
    		}
    	}else{
            $this->assign('tips',F('settings')['tips']['report']);             
            $data = menu();
            $this->assign('data',json_encode($data));
    		$this->display('report');
    	}
    }	

    //紧急报修
    public function emerg(){
    	if(session('?uid')){
    		$database = M('user');
 			$user = $database->where('uid = :uid')->bind(':uid',session('uid'))->find(); 
 			$this->assign('user',$user); 
    	}else{
			session('returnURL',__SELF__);
    		$this->redirect('User/login');
    	}	
    	if(IS_POST){
			if(cookie('last_emerg_report')){
				$this->error('操作频繁请休息片刻~');
			}			
    		$database = D('order');
            if (!$database->create()){
                $this->error($database->getError());
            }			
            if(!empty(I('post.tel'))){
            	M('user')->where('uid=:uid')->bind(':uid',session('uid'))->save(array('tel'=>I('post.tel')));
            }                        
    		$data['area'] = I('post.area/d');//校区
    		if(!selectCheck($data['area']))$this->error('参数非法');
    		$data['location'] = I('post.location');//地点
    		$data['description'] = I('post.description');//描述
    		$data['user'] = session('uid');//用户
    		$data['order'] = creatOrderSn($data['area']);//工单号
    		$data['time'] = time();//时间
    		$data['status'] = 0;//状态 未处理0 处理中1 已处理2
    		$data['emerg'] = 1;//是否紧急 普通0 紧急1
			if($_FILES){
				$info = $this->upload();
				foreach($info as $file){
					$img[] = $file['savepath'].$file['savename'];
				}
				$data['img'] = json_encode($img);				
			}    		
    		$add = $database->strict(true)->data($data)->filter('strip_tags')->add();
    		if($add){
				cookie('last_emerg_report',time(),array('expire'=>60));
    			$this->success('报修提交成功',U('User/order'));
    		}else{
    			$this->error('报修提交失败');
    		}
    	}else{
            $this->assign('tips',F('settings')['tips']['emerg']);             
            $data = menu();
            $this->assign('data',json_encode($data));            
    		$this->display('emerg');
    	}    	   	       
    }

    //工单详情
    public function detail(){
    	$detail = M('order')->where('`order`=:order')->bind(':order',I('get.order'))->find();
    	if(empty($detail)){
    		$this->error('该工单不存在');
    	}else{
			//是否开启用户评价	
			$this->assign('allowrank',F('settings')['global']['allowrank']); 

            $this->assign('tips',F('settings')['tips']['detail']);  
			
            $user = M('user')->where('uid = :uid')->bind(':uid',$detail['user'])->find(); 
			$this->assign('user',$user); 
						
			$rank['user'] = M('rank')->where("`order` = :order and `type`='0'")->bind(':order',I('get.order'))->order('time desc')->find();//用户最新评价
			$rank['admin'] = M('rank')->where("`order` = :order and `type`='1'")->bind(':order',I('get.order'))->order('time desc')->find();//管理最新回复
			$this->assign('rank',$rank);

			$detail['img'] = json_decode($detail['img'],true);
			$this->assign('detail',$detail); 
    		$this->display('detail');
    	}
    }
	
	//评价
	public function rank(){
		if(!session('?admin') and !session('?uid')){
			$this->error('非法访问');
		}
		
		//是否开启用户评价	
		if(F('settings')['global']['allowrank']=='false'){
			$this->error('用户评价未开启');
		}
		
		if(IS_POST){			
			$data['order'] = I('get.order');			
			$order = M('order')->where($data)->find();
			if(empty($order)){
				$this->error('工单不存在');
			}
			if(cookie('last_rank_'.$order['order'])){
				$this->error('操作频繁请休息片刻~');
			}			
			if($order['status'] != 2){
				$this->error('工单未完成');
			}			
			if(time()-$order['donetime'] > 3600*24*3){
				$this->error('评价超时关闭');
			}
			if(session('?uid') AND I('get.type')==0){
				$data['user']=session('uid');
				if(session('uid') != $order['user'])$this->error('操作无权限');
				$data['type']=0;
			}
			elseif(session('?admin') AND I('get.type')==1){
				$data['user']=session('admin');
				$data['type']=1;
			}
			else{
				$this->error('参数错误');
			}
			
			$database = D('rank');
            if (!$database->create()){
                $this->error($database->getError());
            }			
			$data['content'] = I('post.content');
			$data['time'] = time();			
			if($database->data($data)->filter('strip_tags')->add()){
				if(session('?uid') AND !session('?admin')){
					cookie('last_rank_'.$order['order'],time(),array('expire'=>60));
				}
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
			
		}
	}
	
	//评价删除
	public function rankDel(){
		if(!session('?admin') AND !session('?uid')){
			$this->error('非法访问');
		}
		
		//是否开启用户评价		
		if(F('settings')['global']['allowrank'] == 'false'){
			$this->error('用户评价未开启');	
		}
	
		if(IS_POST && IS_AJAX){
			$data['order'] = I('post.order');
			$order = M('order')->where($data)->find();	
			if($order['status'] != 2){
				$this->error('工单未完成');
			}
			if(time()-$order['donetime'] > 3600*24*3){
				$this->error('评价超时关闭');
			}
			if(session('?uid') AND session('uid') != $order['user']){
				$this->error('操作无权限');
			}
			if(!session('?admin') AND I('post.type')==1){
				$this->error('操作无权限');
			}
			$data['type'] = I('post.type');
			if(M('rank')->where($data)->order('time desc')->limit(1)->delete()){
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}		
		}
	}
	
	//头像
	public function avatar(){
		$data = I('get.data');
		$identicon = new \Org\Identicon\Identicon();
		$identicon->displayImage($data,256);
	}	
	
	private function upload(){
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     3145728 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
		$upload->savePath  =     ''; // 设置附件上传（子）目录
		// 上传文件 
		$info   =   $upload->upload();
		if(!$info){
			$this->error($upload->getError());
		}
		return $info;
	}	
}
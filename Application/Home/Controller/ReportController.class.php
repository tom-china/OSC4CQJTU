<?php
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
    		$this->redirect('User/login',array('returnURL'=>base64_encode(base64_encode(__SELF__))));
    	}
    	if(IS_POST){
    		$database = D('order');
            if (!$database->create()){
                $this->error($database->getError());
            }
            if(!empty(I('post.tel'))){
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
    		$add = $database->strict(true)->data($data)->filter('strip_tags')->add();
    		if($add){
				cookie('last_report',time(),array('expire'=>60,'prefix'=>'think_'));
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
    		$this->redirect('User/login',array('returnURL'=>base64_encode(base64_encode(__SELF__))));
    	}	
    	if(IS_POST){
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
    		$add = $database->strict(true)->data($data)->filter('strip_tags')->add();
    		if($add){
				cookie('last_report',time(),array('expire'=>60,'prefix'=>'think_'));
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

			$this->assign('detail',$detail); 
    		$this->display('detail');
    	}
    }
	
	//评价
	public function rank(){
		if(!session('?admin') and !session('?uid'))$this->error('非法访问');
		
		//是否开启用户评价	
		if(F('settings')['global']['allowrank']=='false')$this->error('用户评价未开启');
		
		if(IS_POST){
			$database = D('rank');
            if (!$database->create()){
                $this->error($database->getError());
            }
			$data['order'] = I('get.order');
			$order = M('order')->where($data)->find();
			if(time()-$order['time']>3600*24*3)$this->error('评价超时');
			if(session('?uid') and I('get.type')==0){
				$data['user']=session('uid');
				if(session('uid') != $order['user'])$this->error('操作无权限');
				$data['type']=0;
			}
			elseif(session('?admin') and I('get.type')==1){
				$data['user']=session('admin');
				$data['type']=1;
			}
			else{
				$this->error('参数错误');
			}
			$data['content'] = I('post.content');
			$data['time'] = time();
			if($database->data($data)->filter('strip_tags')->add()){
				cookie('last_rank',time(),array('expire'=>60,'prefix'=>'think_'));
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
		}
	}
	
	//评价删除
	public function rankDel(){
		if(!session('?admin') and !session('?uid'))$this->error('非法访问');
		
		//是否开启用户评价		
		if(F('settings')['global']['allowrank']=='false')$this->error('用户评价未开启');	
	
		if(IS_POST && IS_AJAX){
			$data['order'] = I('post.order');
			$order = M('order')->where($data)->find();	
			if(session('?uid') && session('uid') != $order['user'])$this->error('操作无权限');
			if(!session('?admin') and I('post.type')==1)$this->error('操作无权限');
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
}
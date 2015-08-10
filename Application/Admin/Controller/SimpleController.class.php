<?php
namespace Admin\Controller;
use Think\Controller;
class SimpleController extends Controller {
    /**
     * 空操作处理
     * @return custom or 404 page
     */
    public function _empty() {
        $this->redirect('Main/index');
    }	

    //统计
    public function __construct(){
        parent::__construct();
        if(session('?admin') && session('?security')){
			
			//安全验证
			if(sha1(get_client_ip().$_SERVER['HTTP_USER_AGENT']) != session('security')){
				session(null);
				$this->redirect('Main/index');
			}

			$database = M('order');

			$admin = M('admin')->where('username=:username')->bind(':username',session('admin'))->find();
			$admin = json_decode($admin['location'],true);
			if(!empty($admin['area']) && !empty($admin['building'])){
				$where['area'] = array('in',$admin['area']);
				$where['building'] = array('in',$admin['building']);
				$where['_logic'] = 'or';
				$map['_complex'] = $where;
			}
			elseif(!empty($admin['area']))$map['area'] = array('in',$admin['area']);
			elseif(!empty($admin['building']))$map['building'] = array('in',$admin['building']);

			$map['status'] = 0;//待处理
			$count = $database->where($map)->count();
			$this->assign('countTodo',$count); 

			$map['status'] = 1;//处理中
			$count = $database->where($map)->count();
			$this->assign('countDoing',$count); 

			$map['status'] = 2;//已处理
			$count = $database->where($map)->count();
			$this->assign('countDone',$count);
			
			//今日有效报修
			$map['time'] = array('gt',strtotime(date('Y-m-d')));
			$map['status'] = array('neq',-1);//隐藏已取消报修的
			$count = $database->where($map)->count();
			$this->assign('countToday',$count);                      
        }
		
    }
}
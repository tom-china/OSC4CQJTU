<?php
namespace Home\Controller;
use Think\Controller;
class SimpleController extends Controller {
    /**
     * 空操作处理
     * @return custom or 404 page
     */
    public function _empty() {
        $this->redirect('Main/index');
    }

    //站点信息
    public function __construct(){
        parent::__construct();
		
		//获取站点设置
        $database = M('setting');
        $global = $database->where("`key`='global'")->find();
        $global = json_decode($global['value'],true);
		
		//是否开启站点
        if($global['isopen']=='false')$this->error('站点已经关闭，请稍后访问~');
		
		//版权信息
        $copyright = $database->where("`key`='copyright'")->find();
        $copyright = json_decode($copyright['value'],true);
        $this->assign('copyright',$copyright);   
    }        
}
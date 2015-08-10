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

		//站点信息		
		if(empty(F('settings'))){
			foreach (M('setting')->select() as $key=>$value){
				$settings[$value['key']] = json_decode($value['value'],true);
			}	
			F('settings',$settings);			
		}
			
		//是否开启站点
        if(F('settings')['global']['isopen']=='false')$this->error('站点已经关闭，请稍后访问~');
		 
		//加载版权信息
        $this->assign('copyright',F('settings')['copyright']);   
    }        
}
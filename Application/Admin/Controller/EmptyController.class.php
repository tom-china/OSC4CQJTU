<?php
namespace Admin\Controller;
use Think\Controller;
class EmptyController extends SimpleController{
	//空模块返回
    public function index(){
        $this->redirect('Main/index');
    }
}

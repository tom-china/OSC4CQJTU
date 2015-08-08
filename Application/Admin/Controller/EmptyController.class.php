<?php
namespace Admin\Controller;
use Think\Controller;
class EmptyController extends SimpleController{
    public function index(){
        $this->redirect('Main/index');
    }
}

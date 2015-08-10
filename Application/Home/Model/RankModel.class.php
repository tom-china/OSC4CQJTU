<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model{

   protected $_validate = array(
     array('order','require','工单号必须'),
     array('content','require','内容必须')
   );
   
}

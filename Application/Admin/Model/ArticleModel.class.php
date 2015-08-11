<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model{

   protected $_validate = array(
     array('title','require','键名必须'),
     array('content','require','键值必须')
   );
   
}

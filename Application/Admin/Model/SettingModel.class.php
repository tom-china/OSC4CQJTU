<?php
namespace Home\Model;
use Think\Model;
class SettingModel extends Model{

   protected $_validate = array(
     array('key','require','键名必须'),
     array('value','require','键值必须')
   );
   
}

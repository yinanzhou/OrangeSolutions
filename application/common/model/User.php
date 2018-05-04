<?php
namespace app\common\model;
use think\Model;

/**
* User Data Model
* @author   Yinan Zhou
*/
class User extends Model
{
  protected $pk = 'user_id';
  protected $auto = ['user_email','user_firstname','user_lastname','user_middlename'];
  protected $insert = ['user_enabled' => 1];
  protected $readonly = ['user_id'];
  protected $type = [
    'user_enabled' => 'boolean',
  ];

  public function memberships() {
    return $this->hasMany('Membership');
  }

  protected function setUserFirstNameAttr($value) {
    return trim($value);
  }

  protected function setUserLastNameAttr($value) {
    return trim($value);
  }

  protected function setUserMiddleNameAttr($value) {
    return trim($value);
  }

  protected function setUserEmailAttr($value) {
    return strtolower($value);
  }

  public function getGravatarHashAttr($value,$data) {
    return md5(strtolower(trim($this->getAttr('user_email'))));
  }
}

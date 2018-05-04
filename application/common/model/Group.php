<?php
namespace app\common\model;
use think\Model;

/**
* Group Data Model
* @author   Yinan Zhou
*/
class Group extends Model
{
  protected $pk = 'group_id';
  protected $readonly = ['group_id'];

  public function memberships() {
    return $this->hasMany('Membership');
  }
}

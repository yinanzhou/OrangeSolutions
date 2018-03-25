<?php

namespace app\common\model;

use think\Model;

class Group extends Model
{
  protected $pk = 'group_id';
  protected $readonly = ['group_id'];

  public function memberships() {
    return $this->hasMany('Membership');
  }
}

<?php

namespace app\common\model;

use think\Model;

class VolunteerProfile extends Model
{
  protected $pk = 'user_id';

  public function user() {
    return $this->belongsTo('User','user_id','user_id');
  }
}

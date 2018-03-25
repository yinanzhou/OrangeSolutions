<?php

namespace app\common\model;

use think\Model;

class MedicalSpecialty extends Model
{
  protected $pk = 'medical_specialty_id';
  protected $readonly = ['medical_specialty_id', 'medical_specialty_name'];

  public function MedicalSpecialtyMastery() {
    return $this->hasMany('MedicalSpecialtyMastery');
  }
}

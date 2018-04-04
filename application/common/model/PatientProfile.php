<?php

namespace app\common\model;

use think\Model;

class PatientProfile extends Model
{
  protected $pk = 'user_id';
  protected $auto = ['patient_gender'];
  const GENDER_OPTIONS = ['Male','Female','Intersex','FtM Male','MtF Female'];

  protected function getPatientGender($value) {
    if ($value == null) {
      return 'Prefer not to answer';
    }
    return $value;
  }

  protected function setPatientGender($value = null) {
    if (!in_array($value, PatientProfile::GENDER_OPTIONS, true)) {
      return null;
    }
    return $value;
  }

  public function user() {
    return $this->belongsTo('User','user_id','user_id');
  }
}

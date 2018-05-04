<?php
namespace app\common\model;
use think\Model;

/**
* PatientProfile Data Model
* @author   Yinan Zhou
*/
class PatientProfile extends Model
{
  protected $pk = 'user_id';
  protected $auto = ['patient_gender'];
  const GENDER_OPTIONS = ['Male','Female','Intersex','FtM Male','MtF Female'];

  protected function setPatientGender($value = null) {
    if (!in_array($value, PatientProfile::GENDER_OPTIONS, true)) {
      return null;
    }
    return $value;
  }

  protected function setPatientBirthYear($value = null) {
    if (!is_numeric($value)) {
      return null;
    }
    if ($value > date('Y')) {
      return null;
    }
    if ($value < 1870) {
      return null;
    }
    return $value;
  }

  public function user() {
    return $this->belongsTo('User','user_id','user_id');
  }
}

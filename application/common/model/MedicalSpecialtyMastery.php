<?php
namespace app\common\model;
use think\Model;

/**
* MedicalSpecialtyMastery Data Model
* @author   Yinan Zhou
*/
class MedicalSpecialtyMastery extends Model
{
  protected $pk = 'user_id,medical_specialty_id';

  public function user() {
    return $this->belongsTo('user','user_id','user_id');
  }

  public function medical_specialty() {
    return $this->belongsTo('MedicalSpecialty','medical_specialty_id','medical_specialty_id');
  }

}

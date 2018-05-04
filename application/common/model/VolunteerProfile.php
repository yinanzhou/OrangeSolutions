<?php
namespace app\common\model;
use think\Model;
use app\common\model\ServiceRequest;

/**
* Volunteer Profile Data Model
* @author   Yinan Zhou
*/
class VolunteerProfile extends Model
{
  protected $pk = 'user_id';

  public function user() {
    return $this->belongsTo('User','user_id','user_id');
  }

  public function getRatingAttr($value,$data) {
    $result = ServiceRequest::where('volunteer_user_id',$this->getAttr('user_id'))->whereNotNull('service_request_rating')->avg('service_request_rating');
    if ($result == 0) {
      if (ServiceRequest::where('volunteer_user_id',$this->getAttr('user_id'))->whereNotNull('service_request_rating')->count() < 1) {
        return null;
      }
    }
    return $result;
  }
}

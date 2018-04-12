<?php

namespace app\portal\controller\dashboard;

use app\common\model\Membership;
use app\common\model\MedicalSpecialty;
use app\common\model\MedicalSpecialtyMastery;
use app\common\model\User;
use app\common\model\ServiceRequest;
use app\portal\controller\Auth;
use think\Controller;

class Volunteer extends Controller {

  protected $beforeActionList = [
    'passUserGroupInfo'
  ];

  protected function passUserGroupInfo() {
    $this->assign('user_group_ids', Auth::getUserGroupsId());
  }

  protected function checkVolunteerMembership() {
    if (!Auth::isVolunteer()) {
      abort(403);
    }
  }

  public function specialties() {
    $uid = Auth::getUserId();
    $this->assign('active_menu','volunteer-specialties');
    $this->checkVolunteerMembership();
    $this->assign('specialties',MedicalSpecialty::order('medical_specialty_name','asc')->select());
    $this->assign('masteries', MedicalSpecialtyMastery::where('user_id', $uid)->column('medical_specialty_id'));
    return view();
  }

  public function addSpecialty($medical_specialty_id) {
    $this->checkVolunteerMembership();
    $uid = Auth::getUserId();
    $ms = MedicalSpecialty::find($medical_specialty_id);
    if ($ms == null) {
      return json('The medical specialty specified does not exist.', 400);
    }
    // Remove existing entries, if there exists one.
    MedicalSpecialtyMastery::where('user_id', $uid)
        ->where('medical_specialty_id', $medical_specialty_id)
        ->delete();
    $msm = new MedicalSpecialtyMastery;
    $msm->user_id = $uid;
    $msm->medical_specialty_id = $medical_specialty_id;
    $msm->save();
    return json("Successfully added $ms->medical_specialty_name to your specialties.", 200);
  }

  public function removeSpecialty($medical_specialty_id) {
    $this->checkVolunteerMembership();
    $uid = Auth::getUserId();
    $ms = MedicalSpecialty::find($medical_specialty_id);
    if ($ms == null) {
      return json('The medical specialty specified does not exist.', 400);
    }
    // Remove existing entries, if there exists one.
    MedicalSpecialtyMastery::where('user_id', $uid)
        ->where('medical_specialty_id', $medical_specialty_id)
        ->delete();
    return json("Successfully removed $ms->medical_specialty_name from your specialties.", 200);
  }

  public function checkPendingRequests(){
    $this->checkVolunteerMembership();
    $uid = Auth::getUserId();
    $pending_requests = ServiceRequest::where('volunteer_user_id', $uid)
        ->where('service_request_status', 'Pending')
        ->select();
    $this->assign('active_menu','volunteer-pending');
    $this->assign('pending', $pending_requests);
    return view();
  }
}
?>

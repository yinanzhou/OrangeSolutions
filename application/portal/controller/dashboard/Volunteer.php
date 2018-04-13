<?php

namespace app\portal\controller\dashboard;

use app\common\model\Membership;
use app\common\model\MedicalSpecialty;
use app\common\model\MedicalSpecialtyMastery;
use app\common\model\User;
use app\common\model\ServiceRequest;
use app\common\model\VolunteerProfile;
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

  public function updateLastAvailableTime() {
    $this->checkVolunteerMembership();
    $uid = Auth::getUserId();
    $volunteer_profile = VolunteerProfile::find($uid);
    if ($volunteer_profile == null) {
      $volunteer_profile = new VolunteerProfile;
      $volunteer_profile->user_id = $uid;
    }
    $volunteer_profile->volunteer_last_available_time = date('Y-m-d H:i:s');
    $volunteer_profile->save();
  }

  public function setAvailability($status) {
    $this->checkVolunteerMembership();
    $uid = Auth::getUserId();
    $volunteer_profile = VolunteerProfile::find($uid);
    if ($volunteer_profile == null) {
      $volunteer_profile = new VolunteerProfile;
      $volunteer_profile->user_id = $uid;
    }
    $volunteer_profile->volunteer_available = $status;
    $volunteer_profile->save();
  }

  public function getAvailability() {
    $this->checkVolunteerMembership();
    $uid = Auth::getUserId();
    $volunteer_profile = VolunteerProfile::find($uid);
    if ($volunteer_profile == null) {
      return json(0);
    }
    return json($volunteer_profile->volunteer_available);
  }
}

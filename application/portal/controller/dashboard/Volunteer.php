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

  public function enroll() {
    $this->assign('active_menu','volunteer-enroll');
    if (!Auth::isLogin()) {
      return Auth::redirectToLogin($this->request);
    }
    if (Auth::isVolunteer()) {
      $this->assign('message','The operation cannot be proceeded because you are already granted volunteer access to the system.');
      return view();
    }
    if (!$this->request->isPost()) {
      $this->assign('message','By clicking "Continue", you will be registered as a volunteer.');
      $this->assign('showEnrollButton', true);
      return view();
    }
    $uid = Auth::getUserId();
    Membership::where('user_id', $uid)
                ->where('group_id', Auth::VOLUNTEER_GROUP_ID)
                ->delete();
    $membership = new Membership;
    $membership->user_id = $uid;
    $membership->group_id = Auth::VOLUNTEER_GROUP_ID;
    $membership->save();
    // Refresh the user group information passed to view.
    $this->assign('user_group_ids', Auth::getUserGroupsId());
    $this->assign('message','<div class="alert alert-success" role="alert"><h4 class="alert-heading">You got it!</h4>You are now granted volunteer access to the system.</div>You can start by setting your volunteer profiles.');
    return view();
  }

  public function ipad() {
    if (!Auth::isLogin()) {
      return Auth::redirectToLogin($this->request);
    }
    $this->checkVolunteerMembership();
    $this->assign('background_images', $this->getBingPictureOfTheDay());
    return view();
  }

  private function getBingPictureOfTheDay() {
    $ch = curl_init();
    curl_setopt_array($ch,[
      CURLOPT_URL => 'https://www.bing.com/HPImageArchive.aspx?format=js&idx=0&n=8&mkt=en-US',
      CURLOPT_RETURNTRANSFER => true,
    ]);
    $rt = json_decode(curl_exec($ch), true);
    $result = [];
    foreach($rt['images'] as $k => $image) {
      $result[] = ['url' => str_replace('1920x1080','1024x768',$image['url'])];
    }
    return $result;
  }

  public function ring($service_request_id) {
    $this->checkVolunteerMembership();
    $uid = Auth::getUserId();
    $service_request = ServiceRequest::where('service_request_id', $service_request_id)
        ->where('service_request_status','Pending')
        ->where('volunteer_user_id',$uid)
        ->find();
    if ($service_request == null) {
      abort(404);
      return;
    }
    $this->assign('service_request_id', $service_request_id);
    $patient = User::find($service_request->patient_user_id);
    if ($patient == null) {
      abort(404);
      return;
    }
    $this->assign('patient_name', "$patient->user_firstname $patient->user_lastname");
    return view();
  }

  public function getStatus() {
    $this->checkVolunteerMembership();
    $uid = Auth::getUserId();
    $volunteer_profile = VolunteerProfile::find($uid);
    if ($volunteer_profile == null) {
      $volunteer_profile = new VolunteerProfile;
      $volunteer_profile = $uid;
      $volunteer_profile->save();
    }
    $rtn['availability'] = $volunteer_profile->volunteer_available;
    if ($volunteer_profile->volunteer_available) {
      $volunteer_profile->volunteer_last_available_time = date('Y-m-d H:i:s');
      $volunteer_profile->save();
    }
    $service_request = ServiceRequest::where('service_request_status','Pending')
        ->where('volunteer_user_id',$uid)
        ->find();
    if (!$service_request == null) {
      $rtn['pending_service_request_id'] = $service_request->service_request_id;
    } else {
      $rtn['pending_service_request_id'] = null;
    }
    return json($rtn);
  }

  public function updateServiceRequestStatus($service_request_id) {
    $this->checkVolunteerMembership();
    $uid = Auth::getUserId();
    $service_request = ServiceRequest::where('service_request_id', $service_request_id)
        ->where('volunteer_user_id',$uid)
        ->find();
    if ($service_request == null) {
      abort(404);
      return;
    }
    if (in_array($service_request->service_request_status, ['Rejected','Expired','Completed','Rejected (Volunteer Busy)'])) {
      // No changes allowed to finalized service requests
      return;
    }
    if ($service_request->service_request_status == 'Accepted') {
      $allowable_new_states = ['Completed'];
    } else {
      $allowable_new_states = ['Accepted','Rejected','Expired','Rejected (Volunteer Busy)'];
    }
    if (!in_array(input('post.service_request_status'),$allowable_new_states)) {
      // New state is not allowed
      return;
    }
    $service_request->service_request_status = input('post.service_request_status');
    $service_request->save();
  }
}

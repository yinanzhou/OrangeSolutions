<?php

namespace app\portal\controller\dashboard;

use app\common\model\Appointment;
use app\common\model\Membership;
use app\common\model\PatientProfile;
use app\common\model\ServiceRequest;
use app\common\model\User;
use app\common\model\VolunteerProfile;
use app\portal\controller\Auth;
use think\Controller;

class Patient extends Controller {

  protected $beforeActionList = [
    'passUserGroupInfo'
  ];

  protected function passUserGroupInfo() {
    $this->assign('user_group_ids', Auth::getUserGroupsId());
  }

  protected function checkPatientMembership() {
    if (!Auth::isPatient()) {
      abort(403);
    }
  }

  public function enroll() {
    $this->assign('active_menu','');
    if (!Auth::isLogin()) {
      return Auth::redirectToLogin($this->request);
    }
    if (Auth::isPatient()) {
      $this->assign('message','The operation cannot be proceeded because you are already registered as a patient in the system.');
      return view();
    }
    if (!$this->request->isPost()) {
      $this->assign('message','By clicking "Continue", you will be able to use patient services');
      $this->assign('showEnrollButton', true);
      return view();
    }
    $uid = Auth::getUserId();
    Membership::where('user_id', $uid)
                ->where('group_id', Auth::PATIENT_GROUP_ID)
                ->delete();
    $membership = new Membership;
    $membership->user_id = $uid;
    $membership->group_id = Auth::PATIENT_GROUP_ID;
    $membership->save();
    $profile = PatientProfile::find($uid);
    if ($profile == null) {
      $profile = new PatientProfile;
      $profile->user_id = $uid;
      $profile->save();
      $profile = PatientProfile::find($uid);
    }
    // Refresh the user group information passed to view.
    $this->assign('user_group_ids', Auth::getUserGroupsId());
    $this->assign('message','<div class="alert alert-success" role="alert"><h4 class="alert-heading">You got it!</h4>You are now granted patient access to the system.</div>You should able to see the patient service menu on the left. You can start by updating your patient profiles.');
    return view();
  }

  public function profile() {
    if (!Auth::isLogin()) {
      return Auth::redirectToLogin($this->request);
    }
    $this->checkPatientMembership();
    $uid = Auth::getUserId();
    $profile = PatientProfile::find($uid);
    if ($profile == null) {
      $profile = new PatientProfile;
      $profile->user_id = $uid;
      $profile->save();
      $profile = PatientProfile::find($uid);
    }
    if ($this->request->isPost()) {
      $profile->patient_gender = input('post.patient_gender');
      $profile->patient_birth_year = input('post.patient_birth_year');
      $profile->patient_conditions = input('post.patient_conditions');
      $profile->patient_allergies = input('post.patient_allergies');
      $profile->patient_medications = input('post.patient_medications');
      $profile->save();
      $this->assign('alert','Profile update succeeds.');
    }
    $this->assign('active_menu','patient-profile');
    $genderOptions = PatientProfile::GENDER_OPTIONS;
    array_unshift($genderOptions, 'Prefer not to answer');
    $this->assign('gender_options', $genderOptions);
    $this->assign('p', $profile);
    return view();
  }

  public function donate() {
    if (!Auth::isLogin()) {
      return Auth::redirectToLogin($this->request);
    }
    $this->assign('active_menu','patient-donation');
    $this->checkPatientMembership();
    return view();
  }

  public function redirectToPaypal() {
    $this->checkPatientMembership();
    return redirect('https://paypal.me/yinan/' . input('post.amount/f') . 'USD');
  }

  public function newServiceRequest($volunteer_user_id) {
    if (!Auth::isLogin()) {
      return Auth::redirectToLogin($this->request);
    }
    $uid = Auth::getUserId();
    $this->checkPatientMembership();
    if(!Auth::isVolunteer($volunteer_user_id)) {
      abort(404);
      return;
    }
    $service_request = new ServiceRequest;
    $service_request->patient_user_id = $uid;
    $service_request->volunteer_user_id = $volunteer_user_id;
    $service_request->save();
    $volunteer = User::find($volunteer_user_id);
    echo "Email Address: $volunteer->user_email\n";
    $volunteer_profile = VolunteerProfile::find($volunteer_user_id);
    echo "Email Address: $volunteer_profile->volunteer_phone\n";
  }

  public function listVolunteers() {
    if (!Auth::isLogin()) {
      return Auth::redirectToLogin($this->request);
    }
    $this->assign('active_menu','volunteer-list');
    $this->assign('volunteers',VolunteerProfile::alias('vp')
        ->whereTime('volunteer_last_available_time','>=','-20 seconds')
        ->where('volunteer_available', true)
        ->join('user u','vp.user_id=u.user_id','LEFT')
        ->select());
    $uid = Auth::getUserId();
    $this->checkPatientMembership();
    return view('volunteer_list');
  }
}

<?php

namespace app\portal\controller\dashboard;

use app\common\model\Appointment;
use app\common\model\Membership;
use app\common\model\PatientProfile;
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

  public function profile() {
    $this->checkPatientMembership();
    $this->assign('active_menu','patient-profile');
    $genderOptions = PatientProfile::GENDER_OPTIONS;
    array_unshift($genderOptions, 'Prefer not to answer');
    $this->assign('gender_options', $genderOptions);
    return view();
  }
}

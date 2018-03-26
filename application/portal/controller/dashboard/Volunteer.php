<?php

namespace app\portal\controller\dashboard;

use app\common\model\Membership;
use app\common\model\MedicalSpecialty;
use app\common\model\MedicalSpecialtyMastery;
use app\common\model\User;
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
    $this->assign('active_menu','tutor-subjects');
    $this->checkVolunteerMembership();
    $this->assign('specialties',MedicalSpecialty::order('medical_specialty_name','asc')->select());
    $this->assign('masteries', MedicalSpecialtyMastery::where('user_id', $uid)->column('medical_specialty_id'));
    return view();
  }
}

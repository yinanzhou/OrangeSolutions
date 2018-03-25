<?php

namespace app\portal\controller\dashboard;

use app\common\model\Appointment;
use app\common\model\Membership;
use app\common\model\VolunteerSubject;
use app\common\model\VolunteerSubjectMastery;
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
}

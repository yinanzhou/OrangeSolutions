<?php

namespace app\portal\controller\dashboard;

use app\portal\controller\Auth;
use think\Controller;

class General extends Controller {

  protected $beforeActionList = [
    'passUserGroupInfo'
  ];

  protected function passUserGroupInfo() {
    $this->assign('user_group_ids', Auth::getUserGroupsId());
  }

  public function home() {
    $this->assign('active_menu','dashboard');
    if (!Auth::isLogin()) {
      return Auth::redirectToLogin($this->request);
    }
    return view();
  }

  public function account() {
    $this->assign('active_menu','profile');
    if (!Auth::isLogin()) {
      return Auth::redirectToLogin($this->request);
    }
    $this->assign('group_memberships',db('membership')
        ->alias('m')
        ->where('m.user_id', Auth::getUserId())
        ->join('group g', 'm.group_id = g.group_id')
        ->field('g.group_name as name,m.membership_validfrom as validfrom,m.membership_expiration as expiration')
        ->select());
    $this->assign('empty_membership_message', '<tr><td colspan="3">You do not belong to any group.</td></tr>');
    return view();
  }
}

<?php

namespace app\portal\controller\dashboard;

use app\common\model\Membership;
use app\common\model\User;
use app\portal\controller\Auth;
use think\Controller;

class Admin extends Controller {

  protected $beforeActionList = [
    'passUserGroupInfo'
  ];

  protected function passUserGroupInfo() {
    $this->assign('user_group_ids', Auth::getUserGroupsId());
  }

  protected function checkAdminMembership() {
    if (!Auth::isAdmin()) {
      abort(403);
    }
  }

  public function users() {
    $this->assign('active_menu','admin-users');
    $this->checkAdminMembership();
    $this->assign('users', User::select());
    return view();
  }

  public function setUserStatus() {
    $this->checkAdminMembership();
    $user = User::find(input('post.user_id/d'));
    if ($user === null) {
      return json('There is no user associated with the specified user id.', 400);
    }
    $user->user_enabled = input('post.user_enabled/b')?1:0;
    $user->save();
    if ($user->user_enabled == 0) {
      Auth::resetUserLoginToken($user->user_id);
    }
    return json('Transaction succeeds.', 200);
  }

  public function user($user_id) {
    $this->assign('active_menu','admin-users');
    $this->checkAdminMembership();
    $user = User::find($user_id);
    if ($user == null) {
      abort(404);
    }
    $this->assign('u', $user);
    $this->assign('group_memberships',db('membership')
        ->alias('m')
        ->where('m.user_id', $user_id)
        ->join('group g', 'm.group_id = g.group_id')
        ->field('g.group_name as name,m.membership_validfrom as validfrom,m.membership_expiration as expiration,m.group_id as group_id')
        ->select());
    $this->assign('empty_membership_message', '<tr><td colspan="4">The user does not belong to any group.</td></tr>');
    return view();
  }

  public function removeUserMembership() {
    $this->checkAdminMembership();
    Membership::where('user_id', input('post.user_id/d'))->where('group_id', input('post.group_id/d'))->delete();
    return json('Transaction succeeds.', 200);
  }

  public function enroll() {
    $this->assign('active_menu','');
    if (!Auth::isLogin()) {
      return Auth::redirectToLogin($this->request);
    }
    if (Auth::isAdmin()) {
      $this->assign('message','The operation cannot be proceeded because you are already granted admin access to the system.');
      return view();
    }
    if (Auth::isAdminExists()) {
      $this->assign('message','Self enroll for administator is disabled.');
      return view();
    }
    if (!$this->request->isPost()) {
      $this->assign('message','By clicking "Continue", you will be granted access to tutor services.');
      $this->assign('showEnrollButton', true);
      return view();
    }
    $uid = Auth::getUserId();
    Membership::where('user_id', $uid)
                ->where('group_id', Auth::ADMIN_GROUP_ID)
                ->delete();
    $membership = new Membership;
    $membership->user_id = $uid;
    $membership->group_id = Auth::ADMIN_GROUP_ID;
    $membership->save();
    // Refresh the user group information passed to view.
    $this->assign('user_group_ids', Auth::getUserGroupsId());
    $this->assign('message','<div class="alert alert-success" role="alert"><h4 class="alert-heading">You got it!</h4>You are now granted admin access to the system.</div>You should able to see the admin menu on the left.');
    return view();
  }
}

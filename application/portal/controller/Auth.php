<?php

namespace app\portal\controller;

use app\common\model\Membership;
use app\common\model\User;
use think\Controller;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Session;
use think\facade\Request;
use think\Validate;
use think\validate\ValidateRule;

/**
* Authentication Controller
* @author Yinan Zhou
*/
class Auth extends Controller {

  const ADMIN_GROUP_ID = 1;
  const PATIENT_GROUP_ID = 2;
  const VOLUNTEER_GROUP_ID = 3;

  /**
   * Login function handles the user login logic.
   * @author Yinan Zhou
   */
  public function login() {
    Cookie::init([
      'prefix' => 'login_',
      'path' => '/login',
      'secure' => true,
      'setcookie' => true
    ]);
    if (Cookie::has('email')) {
      $this->assign('prefilledEmail', Cookie::get('email'));
      $this->assign('checkRememberEmail', true);
    }
    if (!$this->request->isPost()) {
      return view()->code(401);
    }

    $email = $this->request->post('user_email','invalid',FILTER_VALIDATE_EMAIL);
    $this->assign('prefilledEmail', $this->request->post('user_email'));

    $rules = [
      'user_email' => 'require|email|token:login',
      'user_password' => 'require'
    ];
    $errorMessages = [
      'user_email.require' => 'Email is required.',
      'user_email.email' => 'The email address is invalid.',
      'user_password.require' => 'Password is required.',
      'user_email.token' => 'Invalid form token, this may be caused by opening multiple login pages, or using back button on browser, please try again.',
    ];

    $validate = Validate::make($rules,$errorMessages);
    $validationResult = $validate->batch(true)->check($this->request->post());
    if (!$validationResult) {
      $errorMessage = "Authentication cannot be completed due to the following error(s):";
      foreach($validate->getError() as $field => $message) {
        $errorMessage = $errorMessage . "\n" . $message;
      }
      $this->assign('alert', $errorMessage);
      return view()->code(400);
    }

    if ($this->isLoginCaptchaRequired($email) && !$this->verifyRecaptcha()) {
      $this->assign('alert', 'Cannot validate ReCaptcha response, please try again.');
      return view()->code(400);
    }
    $user = User::where('user_email', $email)->find();
    $emailOrPasswordError = false;

    if ($user === null) { // No such user
      $emailOrPasswordError = true;
    } elseif (!password_verify($this->request->post('user_password'), $user->user_password)) {
      // Password error
      $emailOrPasswordError = true;
    }

    if ($emailOrPasswordError) {
      $this->assign('alert', 'Incorrect email or password');
      return view()->code(401);
    }

    // In case PHP changes its hashing algo in the future
    if (password_needs_rehash($user->user_password, PASSWORD_DEFAULT)) {
      $user->user_password = password_hash($this->request->post('user_password'), PASSWORD_DEFAULT);
      $user->save();
    }

    if (!$user->user_enabled) {
      $this->assign('alert', 'The account is locked or not activated.');
      return view()->code(401);
    }

    Auth::setUser($user);
    if ($this->request->post('remember_email/b', false)) {
      Cookie::forever('email', $email);
    } else {
      Cookie::delete('email');
    }
    return redirect('https://orangesolutions.tk' . $this->request->get('returnTo','/dashboard'));
  }

  /**
   * API endpoint for frontend to query whether the speficific account login
   * behavior requires reCAPTCHA check.
   * @author Yinan Zhou
   */
  public function checkLoginCaptchaRequired() {
    if (!$this->request->isAjax()) {
      return json(['error'=>'Unanthorized request'])->code(403);
    }
    return json($this->isLoginCaptchaRequired($this->request->post('user_email')));
  }

  /**
   * Is the recaptcha required for the specific account
   * @return boolean representing whether a reCAPTCHA is required.
   * @author Yinan Zhou
   */
  protected function isLoginCaptchaRequired($email) {
    return false; // turn off reCAPTCHA temporarily for easier testing
  }

  /**
   * Register function handles the user registration logic.
   * @author Yinan Zhou
   */
  public function register() {
    if (!$this->request->isPost()) {
      return view();
    }
    $data = $this->request->post();
    $rules = [
      'user_firstname' => 'require|max:40',
      'user_lastname' => 'require|max:40',
      'user_middlename' => 'max:40',
      'user_email' => 'require|email|unique:user',
      'user_password' => 'require|length:8,24|token:register'
    ];
    $errorMessages = [
      'user_firstname.require' => 'First name is required.',
      'user_firstname.max' => 'First name must be within 40 characters.',
      'user_lastname.require' => 'Last name is required.',
      'user_lastname.max' => 'Last name must be within 40 characters.',
      'user_middlename.max' => 'Middle name must be within 40 characters.',
      'user_email.require' => 'Email is required.',
      'user_email.email' => 'The email address is invalid.',
      'user_email.unique' => 'There exists a user with the same email address.',
      'user_password.require' => 'Password is required.',
      'user_password.length' => 'The password length must be within 8 and 24 characters.',
      'user_password.token' => 'Invalid form token, this may be caused by refreshing submitted forms, opening multiple registration forms, or using back button on browser, please try again.',
    ];
    $validate = Validate::make($rules,$errorMessages);
    $validationResult = $validate->batch(true)->check($data);
    if (!$validationResult) {
      $errorMessage = "Registration cannot be completed due to the following error(s):";
      foreach($validate->getError() as $field => $message) {
        unset($data[$field]);
        $errorMessage = $errorMessage . "\n" . $message;
      }
      $this->assign('prefilledData', $data);
      $this->assign('alert', $errorMessage);
      return view()->code(400);
    }
    if (!$this->verifyRecaptcha()) {
      $this->assign('prefilledData', $data);
      $this->assign('alert', 'Cannot validate ReCaptcha response, please try again.');
      return view()->code(400);
    }
    $data['user_password'] = password_hash($data['user_password'], PASSWORD_DEFAULT);
    $user = new User;
    if($user->allowField(['user_firstname','user_lastname','user_middlename','user_email','user_password'])->save($data) > 0) {
      Auth::setUser($user);
      return redirect('/dashboard');
    } else {
      $this->assign('prefilledData', $data);
      $this->assign('alert', 'We are experiencing technical difficulties on creating new users, please try again later.');
      return view()->code(400);
    }
  }

  /**
   * Connect with Google to check reCAPTCHA result
   * @author Yinan Zhou
   * @return boolean representing valid or not
   */
  private function verifyRecaptcha() {
    $ch = curl_init();
    curl_setopt_array ($ch ,[
      CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => [
        'secret' => Config::get('app.recaptcha.secret'),
        'response' => $this->request->post('g-recaptcha-response'),
        'remoteip' => $this->request->ip(0, true),
      ],
      CURLOPT_RETURNTRANSFER => true,
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    if ($result === false) {
      return false;
    }
    return json_decode($result)->success;
  }

  /**
   * Set the current user of the session
   * @author Yinan Zhou
   */
  private static function setUser($user) {
    Session::delete('user');
    Session::set('user.user_id', $user->user_id);
    Session::set('user.user_login_token', Auth::getUserLoginToken($user->user_id));
    Session::set('user.model', $user);
  }

  /**
   * Log the user out by clearing the user session information
   * @author Yinan Zhou
   */
  public function logout() {
    Session::delete('user');
    return redirect('/');
  }

  /**
   * Check whether the user is logined.
   * @author Yinan Zhou
   * @return boolean boolean value represent user login status
   */
  public static function isLogin() {
    if(!Session::has('user.user_id') || !Session::has('user.user_login_token')) {
      return false;
    }
    if(Session::get('user.user_login_token')!==Auth::getUserLoginToken(Session::get('user.user_id'))) {
      return false;
    }
    return true;
  }

  /**
   * The user login token provides a way we can sign user out
   * If a token have already exists, during login the previous
   * token will be used.
   * When we want to log a specific user out, we reassign the
   * user login token and all session with previous token will
   * be invalid.
   *
   * @author Yinan Zhou
   * @return String the user login token
   */
  public static function getUserLoginToken($user_id) {
    if(!Cache::has('user_login_token:' . $user_id)) {
      Cache::tag('user_login_token')->set('user_login_token:' . $user_id, uniqid());
    }
    return Cache::get('user_login_token:' . $user_id);
  }

  public static function resetUserLoginToken($user_id) {
    Cache::tag('user_login_token')->set('user_login_token:' . $user_id, uniqid());
  }

  /**
   * Return the redirect response object to redirect user to login pages
   * @author Yinan Zhou
   */
  public static function redirectToLogin($request) {
    return redirect('/login?returnTo=' . urlencode($request->url()));
  }

  /**
   * Get the current user id
   * @author Yinan Zhou
   */
  public static function getUserId() {
    if (!Auth::isLogin()) {
      return null;
    }
    return Session::get('user.user_id');
  }

  /**
   * Check whether a specific user belongs to a group. If user_id is not
   * specified, current user's user_id will be used.
   * @author Yinan Zhou
   */
  public static function isMemberOf($group_id, $user_id = null) {
    if(is_null($user_id)) {
      $user_id = Auth::getUserId();
    }
    if(empty($group_id) || empty($user_id)) {
      return false;
    }
    return !is_null(Membership::where('group_id=:group AND user_id=:user
        AND (membership_validfrom IS NULL OR membership_validfrom <= NOW())
        AND (membership_expiration IS NULL OR membership_expiration > NOW())')
        ->bind(['group'=>[$group_id, \PDO::PARAM_INT],'user'=>[$user_id, \PDO::PARAM_INT]])
        ->find());
  }

  /**
   * Shortcut function for checking whether user is an administrator
   * @author Yinan Zhou
   */
  public static function isAdmin($user_id = null) {
    return Auth::isMemberOf(Auth::ADMIN_GROUP_ID, $user_id);
  }

  /**
   * Shortcut function for checking whether user is a patient
   * @author Yinan Zhou
   */
  public static function isPatient($user_id = null) {
    return Auth::isMemberOf(Auth::PATIENT_GROUP_ID, $user_id);
  }

  /**
   * Shortcut function for checking whether user is a volunteer
   * @author Yinan Zhou
   */
  public static function isVolunteer($user_id = null) {
    return Auth::isMemberOf(Auth::VOLUNTEER_GROUP_ID, $user_id);
  }

  /**
   * Check whether there exists an admin in the system
   * @author Yinan Zhou
   */
  public static function isAdminExists() {
    return !is_null(Membership::where('group_id=:group
        AND (membership_validfrom IS NULL OR membership_validfrom <= NOW())
        AND (membership_expiration IS NULL OR membership_expiration > NOW())')
        ->bind(['group'=>[Auth::ADMIN_GROUP_ID, \PDO::PARAM_INT]])
        ->find());
  }

  /**
   * Get group_id of groups a specific user belongs to
   * @author Yinan Zhou
   */
  public static function getUserGroupsId($user_id = null) {
    if(is_null($user_id)) {
      $user_id = Auth::getUserId();
    }
    if(empty($user_id)) {
      return [];
    }
    return Membership::where('user_id=:user
        AND (membership_validfrom IS NULL OR membership_validfrom <= NOW())
        AND (membership_expiration IS NULL OR membership_expiration > NOW())')
        ->bind(['user'=>[$user_id, \PDO::PARAM_INT]])
        ->column('group_id');
    return [];
  }
}

<?php
Route::get('/','portal/index/index');
Route::rule('login', 'portal/auth/login', 'GET|POST');
Route::post('login/is_recaptcha_required', 'portal/auth/checkLoginCaptchaRequired');
Route::rule('register', 'portal/auth/register', 'GET|POST');
Route::get('/logout','portal/auth/logout');

Route::get('dashboard', 'portal/dashboard.general/home');
Route::get('dashboard/account', 'portal/dashboard.general/account');

Route::rule('patient/enroll', 'portal/dashboard.patient/enroll', 'GET|POST');
Route::rule('patient/profile', 'portal/dashboard.patient/profile', 'GET|POST');
Route::get('patient/donate', 'portal/dashboard.patient/donate');
Route::post('patient/donate/paypal', 'portal/dashboard.patient/redirectToPaypal');
Route::get('patient/service_request', 'portal/dashboard.patient/demoRequest');

Route::rule('volunteer/enroll', 'portal/dashboard.volunteer/enroll', 'GET|POST');
Route::get('volunteer/specialties', 'portal/dashboard.volunteer/specialties');
Route::post('volunteer/specialties/:medical_specialty_id', 'portal/dashboard.volunteer/addSpecialty');
Route::delete('volunteer/specialties/:medical_specialty_id', 'portal/dashboard.volunteer/removeSpecialty');
Route::get('volunteer/status', 'portal/dashboard.volunteer/getStatus');
Route::post('volunteer/availability/:status', 'portal/dashboard.volunteer/setAvailability')->pattern(['status' => '[01]']);
Route::get('ipad', 'portal/dashboard.volunteer/ipad');
Route::get('volunteer/ring/:service_request_id', 'portal/dashboard.volunteer/ring')->pattern(['service_request_id' => '\d+']);
Route::post('volunteer/service-requests/:service_request_id', 'portal/dashboard.volunteer/updateServiceRequestStatus')->pattern(['service_request_id' => '\d+']);
Route::get('volunteer/profile', 'portal/dashboard.volunteer/publicProfile');
Route::get('volunteer/service-request/in/:service_request_id', 'portal/dashboard.volunteer/inServiceRequest')->pattern(['service_request_id' => '\d+']);

return [];

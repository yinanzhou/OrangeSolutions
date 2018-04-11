<?php
Route::get('/','portal/index/index');
Route::rule('login', 'portal/auth/login', 'GET|POST');
Route::post('login/is_recaptcha_required', 'portal/auth/checkLoginCaptchaRequired');
Route::rule('register', 'portal/auth/register', 'GET|POST');
Route::get('/logout','portal/auth/logout');

Route::get('dashboard', 'portal/dashboard.general/home');
Route::get('dashboard/account', 'portal/dashboard.general/account');

Route::rule('patient/profile', 'portal/dashboard.patient/profile', 'GET|POST');

Route::get('volunteer/specialties', 'portal/dashboard.volunteer/specialties');
Route::post('volunteer/specialties/:medical_specialty_id', 'portal/dashboard.volunteer/addSpecialty');
Route::delete('volunteer/specialties/:medical_specialty_id', 'portal/dashboard.volunteer/removeSpecialty');
Route::get('ipad', 'portal/dashboard.volunteer/ipad');

return [];

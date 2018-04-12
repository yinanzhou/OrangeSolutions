<?php

namespace app\common\model;

class ServiceRequest extends Model{
  protected $pk = 'service_request_id';
  protected $readonly = ['service_request_id', 'patient_user_id', 'volunteer_user_id', 'service_request_time', 'service_request_status'];
}
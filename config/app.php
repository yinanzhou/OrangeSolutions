<?php
// Application Settings
// Modified and translated by Yinan Zhou (yzhou109@syr.edu)

// Original Copyright Notice
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +---------------------------------------------------------------------

return [

    // debug mode
    'app_debug'              => false,
    // show trace information
    'app_trace'              => false,
    // application status
    'app_status'             => '',
    // support multi-modules
    'app_multi_module'       => true,
    // automatically bind module
    'auto_bind_module'       => false,
    // registered root namespace
    'root_namespace'         => [],
    // default view return type
    'default_return_type'    => 'html',
    // default ajax request return type
    'default_ajax_return'    => 'json',
    // default handler for JSONP request
    'default_jsonp_handler'  => 'jsonpReturn',
    // JSONP handler
    'var_jsonp_handler'      => 'callback',
    // timezone
    'default_timezone'       => 'America/New_York',
    // multi-language support
    'lang_switch_on'         => false,
    // default view filters separated by commas
    'default_filter'         => '',
    // default language
    'default_lang'           => 'en-us',
    // suffix for classes
    'class_suffix'           => false,
    // controller suffix
    'controller_suffix'      => false,

    // default module for displaying
    'default_module'         => 'index',
    // blocked module (cannot be visited externally)
    'deny_module_list'       => ['common'],
    // default controller name
    'default_controller'     => 'Index',
    // default action name
    'default_action'         => 'index',
    // default validator name
    'default_validate'       => '',
    // default empty controller
    'empty_controller'       => 'Error',
    // suffix for action
    'action_suffix'          => '',
    // auto searching for controller
    'controller_auto_search' => false,

    // var carrying path info
    'var_pathinfo'           => 's',
    // order for taking PATHINFO
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo delimiter
    'pathinfo_depr'          => '/',
    // URL fake suffix
    'url_html_suffix'        => 'html',
    // URL common parameter
    'url_common_param'       => false,
    // URL parameter type 0 by name 1 order
    'url_param_type'         => 0,
    // force complete route matching
    'route_complete_match'   => true,
    // force route to be used
    'url_route_must'         => true,
    // use annotation route
    'route_annotation'       => false,
    // url domain root
    'url_domain_root'        => '',
    // auto convert controllers in URL
    'url_convert'            => true,
    // default layer for controllers
    'url_controller_layer'   => 'controller',
    // variable for manupulating http method
    'var_method'             => '_method',
    // variable for detecting ajax request
    'var_ajax'               => '_ajax',
    // variable for detecting pjax request
    'var_pjax'               => '_pjax',
    // should request be cached
    'request_cache'          => false,
    // ttl for request cache
    'request_cache_expire'   => null,
    // exemption for global cache
    'request_cache_except'   => [],

    // default target page for redirecting
    'dispatch_success_tmpl'  => Env::get('think_path') . 'tpl/dispatch_jump.tpl',
    'dispatch_error_tmpl'    => Env::get('think_path') . 'tpl/dispatch_jump.tpl',

    // error page for system errors
    'exception_tmpl'         => Env::get('think_path') . 'tpl/think_exception.tpl',

    // default error message
    'error_message'          => 'System Error',
    // show error message or not
    'show_error_msg'         => false,
    // default exception handler, empty goes to \think\exception\Handle
    'exception_handle'       => '',
    // Customize 404 Request
    'http_exception_template'=>  [
      403 => Env::get('app_path') . 'error_pages/403.html',
      404 => Env::get('app_path') . 'error_pages/404.html',
    ],

    'recaptcha' => [
      'sitekey' => getenv("RECAPTCHA_PUBLIC"),
      'secret' => getenv("RECAPTCHA_SECRET"),
    ],
];

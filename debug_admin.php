<?php
/**
 * 3차 진단: common.php 직접 include
 * 확인 후 반드시 삭제해 주세요.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$g5_path = dirname(dirname(dirname(dirname(__FILE__))));
$result = array();

// ★ 핵심: common.php를 절대경로로 직접 include
$common_path = $g5_path . '/common.php';
$result['common_php_path'] = $common_path;
$result['common_php_exists'] = file_exists($common_path);

if (!file_exists($common_path)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('error' => 'common.php not found', 'path' => $common_path));
    exit;
}

if (!defined('_GNUBOARD_')) define('_GNUBOARD_', true);

ob_start();
$include_error = array();
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$include_error) {
    $include_error[] = array('errno' => $errno, 'errstr' => $errstr, 'file' => $errfile, 'line' => $errline);
    return true;
});
include_once($common_path);
restore_error_handler();
$include_output = ob_get_clean();

$result['include_errors'] = $include_error;
$result['include_output_length'] = strlen($include_output);
$result['include_output_preview'] = substr($include_output, 0, 300);

// 세션
$result['session'] = array(
    'session_status' => function_exists('session_status') ? session_status() : 'N/A',
    'session_id'     => session_id(),
    'ss_mb_id'       => isset($_SESSION['ss_mb_id']) ? $_SESSION['ss_mb_id'] : '(not set)',
    'ss_mb_key'      => isset($_SESSION['ss_mb_key']) ? '(exists)' : '(not set)',
);

// 전역변수
global $member, $is_admin, $config, $g5;
$result['globals'] = array(
    'is_admin'       => isset($is_admin) ? var_export($is_admin, true) : '(not set)',
    'member_mb_id'   => isset($member['mb_id']) ? $member['mb_id'] : '(not set)',
    'member_mb_level'=> isset($member['mb_level']) ? $member['mb_level'] : '(not set)',
    'cf_admin'       => isset($config['cf_admin']) ? $config['cf_admin'] : '(not set)',
);

// G5 상수
$all_constants = get_defined_constants(true);
$user_constants = isset($all_constants['user']) ? $all_constants['user'] : array();
$g5_constants = array();
foreach ($user_constants as $name => $val) {
    if (strpos($name, 'G5_') === 0) {
        $g5_constants[$name] = (is_string($val) && strlen($val) > 100) ? substr($val, 0, 100) . '...' : $val;
    }
}
$result['g5_constants_count'] = count($g5_constants);
$result['g5_constants'] = $g5_constants;

// is_admin 함수
$result['is_admin_function_exists'] = function_exists('is_admin');

// main_skin_is_admin
include_once(dirname(__FILE__) . '/main.lib.php');
$result['main_skin_is_admin'] = main_skin_is_admin();

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;
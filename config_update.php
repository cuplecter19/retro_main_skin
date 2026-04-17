<?php
$g5_path = dirname(dirname(dirname(dirname(__FILE__))));
if (!defined('_GNUBOARD_')) define('_GNUBOARD_', true);
@ob_start();
include_once($g5_path . '/common.php');
while (ob_get_level()) ob_end_clean();
include_once(dirname(__FILE__) . '/main.lib.php');

if (!main_skin_is_admin()) {
    main_skin_json_error('권한이 없습니다.');
}

$token = isset($_POST['token']) ? $_POST['token'] : '';
if (!main_skin_check_token($token)) {
    main_skin_json_error('보안 토큰이 유효하지 않습니다.');
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$config = get_main_skin_config();

function main_skin_resolve_image($file_key, $url_key, $type, $prefix, $current_value) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $uploaded = main_skin_upload_image($_FILES[$file_key], $type, $prefix);
        if ($uploaded !== false) {
            return $uploaded;
        }
    }

    if (!empty($_POST[$url_key])) {
        return main_skin_image_url($_POST[$url_key]);
    }

    return $current_value;
}

switch ($action) {
    case 'update_images':
        if (
            isset($_FILES['visual_file']) ||
            isset($_POST['visual_url']) ||
            isset($_POST['visual_alt'])
        ) {
            $config['visual_image'] = main_skin_resolve_image('visual_file', 'visual_url', 'visual', 'visual', isset($config['visual_image']) ? $config['visual_image'] : '');
            $config['visual_alt'] = main_skin_limit_text(isset($_POST['visual_alt']) ? $_POST['visual_alt'] : '', 100);
        }

        $config['polaroid_1_image'] = main_skin_resolve_image('pol1_file', 'pol1_url', 'polaroid', 'pol1', isset($config['polaroid_1_image']) ? $config['polaroid_1_image'] : '');
        $config['polaroid_1_alt'] = main_skin_limit_text(isset($_POST['pol1_alt']) ? $_POST['pol1_alt'] : '', 100);
        $config['polaroid_1_caption'] = main_skin_limit_text(isset($_POST['pol1_caption']) ? $_POST['pol1_caption'] : '', 100);
        $config['polaroid_1_rotate'] = (string)round((float)(isset($_POST['pol1_rotate']) ? $_POST['pol1_rotate'] : 0), 2);

        $config['polaroid_2_image'] = main_skin_resolve_image('pol2_file', 'pol2_url', 'polaroid', 'pol2', isset($config['polaroid_2_image']) ? $config['polaroid_2_image'] : '');
        $config['polaroid_2_alt'] = main_skin_limit_text(isset($_POST['pol2_alt']) ? $_POST['pol2_alt'] : '', 100);
        $config['polaroid_2_caption'] = main_skin_limit_text(isset($_POST['pol2_caption']) ? $_POST['pol2_caption'] : '', 100);
        $config['polaroid_2_rotate'] = (string)round((float)(isset($_POST['pol2_rotate']) ? $_POST['pol2_rotate'] : 0), 2);

        if (!save_main_skin_config($config)) {
            main_skin_json_error('이미지 설정 저장에 실패했습니다.');
        }

        main_skin_json_ok(array());
        break;

    case 'update_window':
        $config['window_title'] = main_skin_limit_text(isset($_POST['win_title']) ? $_POST['win_title'] : '', 40);
        if ($config['window_title'] === '') {
            $config['window_title'] = '최신글';
        }

        $config['banner_title'] = main_skin_limit_text(isset($_POST['banner_title']) ? $_POST['banner_title'] : '', 40);
        if ($config['banner_title'] === '') {
            $config['banner_title'] = '배너';
        }

        $config['latest_rows'] = max(1, min(20, (int)(isset($_POST['limit']) ? $_POST['limit'] : 8)));
        $config['latest_boards'] = main_skin_normalize_board_ids_text(isset($_POST['board_ids']) ? $_POST['board_ids'] : 'free');

        if (!save_main_skin_config($config)) {
            main_skin_json_error('창 설정 저장에 실패했습니다.');
        }

        main_skin_json_ok(array());
        break;

    default:
        main_skin_json_error('알 수 없는 액션입니다.');
}
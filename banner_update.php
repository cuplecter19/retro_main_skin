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
$banners = get_main_banners();

switch ($action) {
    case 'add_banner':
        $image = '';

        if (!empty($_FILES['banner_file']['tmp_name'])) {
            $image = main_skin_upload_image($_FILES['banner_file'], 'banner', 'banner');
            if ($image === false) {
                main_skin_json_error('배너 업로드에 실패했습니다.');
            }
        } else {
            $image = main_skin_image_url(isset($_POST['banner_url']) ? $_POST['banner_url'] : '');
        }

        if ($image === '') {
            main_skin_json_error('배너 이미지를 입력해 주세요.');
        }

        $banner = main_skin_normalize_banner(array(
            'image' => $image,
            'link' => isset($_POST['banner_link']) ? $_POST['banner_link'] : '',
            'target' => isset($_POST['banner_target']) ? $_POST['banner_target'] : '_blank',
            'alt' => isset($_POST['banner_alt']) ? $_POST['banner_alt'] : '',
            'enabled' => isset($_POST['enabled']) ? 1 : 0,
            'sort' => isset($_POST['sort']) ? $_POST['sort'] : count($banners)
        ));

        $banners[] = $banner;
        if (!save_main_banners($banners)) {
            main_skin_json_error('배너 저장에 실패했습니다.');
        }

        main_skin_json_ok(array('banner' => $banner));
        break;

    case 'update_banner':
        $index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
        if (!isset($banners[$index])) {
            main_skin_json_error('해당 배너를 찾을 수 없습니다.');
        }

        $banner = $banners[$index];
        $banner['link'] = isset($_POST['banner_link']) ? $_POST['banner_link'] : $banner['link'];
        $banner['target'] = isset($_POST['banner_target']) ? $_POST['banner_target'] : $banner['target'];
        $banner['alt'] = isset($_POST['banner_alt']) ? $_POST['banner_alt'] : $banner['alt'];
        $banner['enabled'] = isset($_POST['enabled']) ? 1 : 0;
        $banner['sort'] = isset($_POST['sort']) ? $_POST['sort'] : $banner['sort'];
        $banners[$index] = main_skin_normalize_banner($banner);

        if (!save_main_banners($banners)) {
            main_skin_json_error('배너 수정에 실패했습니다.');
        }

        main_skin_json_ok(array('banner' => $banners[$index]));
        break;

    case 'delete_banner':
        $index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
        if (!isset($banners[$index])) {
            main_skin_json_error('해당 배너를 찾을 수 없습니다.');
        }

        if (!empty($banners[$index]['image']) && strpos($banners[$index]['image'], main_skin_asset_url('banner')) === 0) {
            main_skin_delete_uploaded_asset($banners[$index]['image'], 'banner');
        }

        array_splice($banners, $index, 1);
        if (!save_main_banners($banners)) {
            main_skin_json_error('배너 삭제에 실패했습니다.');
        }

        main_skin_json_ok(array());
        break;

    default:
        main_skin_json_error('알 수 없는 액션입니다.');
}
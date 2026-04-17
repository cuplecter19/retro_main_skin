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
$stickers = get_main_stickers();

function main_skin_find_sticker_index($stickers, $id) {
    foreach ($stickers as $index => $sticker) {
        if (isset($sticker['id']) && $sticker['id'] === $id) {
            return $index;
        }
    }

    return -1;
}

function main_skin_sticker_payload($base) {
    return main_skin_normalize_sticker(array(
        'id' => isset($base['id']) ? $base['id'] : '',
        'source_type' => isset($base['source_type']) ? $base['source_type'] : (isset($base['src_type']) ? $base['src_type'] : 'url'),
        'image' => isset($base['image']) ? $base['image'] : (isset($base['src']) ? $base['src'] : ''),
        'left' => isset($_POST['left']) ? $_POST['left'] : (isset($base['left']) ? $base['left'] : '100px'),
        'top' => isset($_POST['top']) ? $_POST['top'] : (isset($base['top']) ? $base['top'] : '100px'),
        'width' => isset($_POST['width']) ? $_POST['width'] : (isset($base['width']) ? $base['width'] : '160px'),
        'height' => isset($_POST['height']) ? $_POST['height'] : (isset($base['height']) ? $base['height'] : 'auto'),
        'rotate' => isset($_POST['rotate']) ? $_POST['rotate'] : (isset($base['rotate']) ? $base['rotate'] : 0),
        'z_index' => isset($_POST['z_index']) ? $_POST['z_index'] : (isset($base['z_index']) ? $base['z_index'] : 20),
        'enabled' => isset($_POST['enabled']) ? 1 : (isset($base['enabled']) ? $base['enabled'] : 1),
        'alt' => isset($_POST['alt']) ? $_POST['alt'] : (isset($base['alt']) ? $base['alt'] : '')
    ));
}

switch ($action) {
    case 'add_sticker':
        $image = '';
        $source_type = (isset($_POST['src_type']) && $_POST['src_type'] === 'upload') ? 'file' : 'url';

        if ($source_type === 'file') {
            if (empty($_FILES['sticker_file']['tmp_name'])) {
                main_skin_json_error('업로드할 스티커 파일을 선택해 주세요.');
            }

            $image = main_skin_upload_image($_FILES['sticker_file'], 'sticker', 'sticker');
            if ($image === false) {
                main_skin_json_error('스티커 업로드에 실패했습니다.');
            }
        } else {
            $image = main_skin_image_url(isset($_POST['src_url']) ? $_POST['src_url'] : '');
            if ($image === '') {
                main_skin_json_error('스티커 이미지 URL을 입력해 주세요.');
            }
        }

        $sticker = main_skin_normalize_sticker(array(
            'id' => 'sticker_' . main_skin_generate_id(),
            'source_type' => $source_type,
            'image' => $image,
            'left' => isset($_POST['left']) ? $_POST['left'] : '100px',
            'top' => isset($_POST['top']) ? $_POST['top'] : '100px',
            'width' => isset($_POST['width']) ? $_POST['width'] : '160px',
            'height' => isset($_POST['height']) ? $_POST['height'] : 'auto',
            'rotate' => isset($_POST['rotate']) ? $_POST['rotate'] : 0,
            'z_index' => isset($_POST['z_index']) ? $_POST['z_index'] : 20,
            'enabled' => isset($_POST['enabled']) ? 1 : 0,
            'alt' => isset($_POST['alt']) ? $_POST['alt'] : ''
        ));

        $stickers[] = $sticker;
        if (!save_main_stickers($stickers)) {
            main_skin_json_error('스티커 저장에 실패했습니다.');
        }

        main_skin_json_ok(array('sticker' => $sticker));
        break;

    case 'update_sticker':
        $id = isset($_POST['id']) ? trim($_POST['id']) : '';
        $index = main_skin_find_sticker_index($stickers, $id);
        if ($index < 0) {
            main_skin_json_error('해당 스티커를 찾을 수 없습니다.');
        }

        $stickers[$index] = main_skin_sticker_payload($stickers[$index]);
        if (!save_main_stickers($stickers)) {
            main_skin_json_error('스티커 수정에 실패했습니다.');
        }

        main_skin_json_ok(array('sticker' => $stickers[$index]));
        break;

    case 'move_sticker':
        $id = isset($_POST['id']) ? trim($_POST['id']) : '';
        $index = main_skin_find_sticker_index($stickers, $id);
        if ($index < 0) {
            main_skin_json_error('해당 스티커를 찾을 수 없습니다.');
        }

        $stickers[$index]['left'] = main_skin_normalize_length(isset($_POST['left']) ? $_POST['left'] : $stickers[$index]['left'], $stickers[$index]['left'], false);
        $stickers[$index]['top'] = main_skin_normalize_length(isset($_POST['top']) ? $_POST['top'] : $stickers[$index]['top'], $stickers[$index]['top'], false);
        $stickers[$index] = main_skin_normalize_sticker($stickers[$index]);

        if (!save_main_stickers($stickers)) {
            main_skin_json_error('스티커 위치 저장에 실패했습니다.');
        }

        main_skin_json_ok(array('sticker' => $stickers[$index]));
        break;

    case 'delete_sticker':
        $id = isset($_POST['id']) ? trim($_POST['id']) : '';
        $index = main_skin_find_sticker_index($stickers, $id);
        if ($index < 0) {
            main_skin_json_error('해당 스티커를 찾을 수 없습니다.');
        }

        if (!empty($stickers[$index]['image']) && isset($stickers[$index]['source_type']) && $stickers[$index]['source_type'] === 'file') {
            main_skin_delete_uploaded_asset($stickers[$index]['image'], 'sticker');
        }

        array_splice($stickers, $index, 1);
        if (!save_main_stickers($stickers)) {
            main_skin_json_error('스티커 삭제에 실패했습니다.');
        }

        main_skin_json_ok(array());
        break;

    default:
        main_skin_json_error('알 수 없는 액션입니다.');
}
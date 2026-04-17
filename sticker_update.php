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

function main_skin_max_z_index($stickers) {
    $max = 0;
    foreach ($stickers as $sticker) {
        $z = isset($sticker['z_index']) ? (int)$sticker['z_index'] : 0;
        if ($z > $max) {
            $max = $z;
        }
    }
    return $max;
}

switch ($action) {
    case 'add_sticker':
        $source_type = (isset($_POST['src_type']) && $_POST['src_type'] === 'upload') ? 'file' : 'url';
        $added = array();
        $max_z = main_skin_max_z_index($stickers);

        if ($source_type === 'file') {
            $files = array();
            if (!empty($_FILES['sticker_files']) && is_array($_FILES['sticker_files']['name'])) {
                $count = min(5, count($_FILES['sticker_files']['name']));
                for ($i = 0; $i < $count; $i++) {
                    $files[] = array(
                        'name'     => $_FILES['sticker_files']['name'][$i],
                        'type'     => $_FILES['sticker_files']['type'][$i],
                        'tmp_name' => $_FILES['sticker_files']['tmp_name'][$i],
                        'error'    => $_FILES['sticker_files']['error'][$i],
                        'size'     => $_FILES['sticker_files']['size'][$i]
                    );
                }
            } elseif (!empty($_FILES['sticker_file']['tmp_name'])) {
                $files[] = $_FILES['sticker_file'];
            }

            if (empty($files)) {
                main_skin_json_error('업로드할 스티커 파일을 선택해 주세요.');
            }

            foreach ($files as $file_arr) {
                if ($file_arr['error'] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $image = main_skin_upload_image($file_arr, 'sticker', 'sticker');
                if ($image === false) {
                    continue;
                }
                $max_z++;
                $sticker = main_skin_normalize_sticker(array(
                    'id'          => 'sticker_' . main_skin_generate_id(),
                    'source_type' => 'file',
                    'image'       => $image,
                    'left'        => '50%',
                    'top'         => '50%',
                    'width'       => '160px',
                    'height'      => 'auto',
                    'rotate'      => '0',
                    'z_index'     => $max_z,
                    'enabled'     => 1,
                    'alt'         => isset($_POST['alt']) ? $_POST['alt'] : ''
                ));
                $stickers[] = $sticker;
                $added[] = $sticker;
            }
        } else {
            $urls = array();
            if (!empty($_POST['src_urls']) && is_array($_POST['src_urls'])) {
                $urls = array_slice($_POST['src_urls'], 0, 5);
            } elseif (!empty($_POST['src_url'])) {
                $urls[] = $_POST['src_url'];
            }

            if (empty($urls)) {
                main_skin_json_error('스티커 이미지 URL을 입력해 주세요.');
            }

            foreach ($urls as $url) {
                $image = main_skin_image_url(trim((string)$url));
                if ($image === '') {
                    continue;
                }
                $max_z++;
                $sticker = main_skin_normalize_sticker(array(
                    'id'          => 'sticker_' . main_skin_generate_id(),
                    'source_type' => 'url',
                    'image'       => $image,
                    'left'        => '50%',
                    'top'         => '50%',
                    'width'       => '160px',
                    'height'      => 'auto',
                    'rotate'      => '0',
                    'z_index'     => $max_z,
                    'enabled'     => 1,
                    'alt'         => isset($_POST['alt']) ? $_POST['alt'] : ''
                ));
                $stickers[] = $sticker;
                $added[] = $sticker;
            }
        }

        if (empty($added)) {
            main_skin_json_error('추가된 스티커가 없습니다. 파일을 선택하거나 URL을 입력해 주세요.');
        }

        if (!save_main_stickers($stickers)) {
            main_skin_json_error('스티커 저장에 실패했습니다.');
        }

        main_skin_json_ok(array('stickers' => $added));
        break;

    case 'update_sticker':
        $id    = isset($_POST['id']) ? trim($_POST['id']) : '';
        $index = main_skin_find_sticker_index($stickers, $id);
        if ($index < 0) {
            main_skin_json_error('해당 스티커를 찾을 수 없습니다.');
        }

        $base = $stickers[$index];
        if (isset($_POST['left']))    { $base['left']    = $_POST['left']; }
        if (isset($_POST['top']))     { $base['top']     = $_POST['top']; }
        if (isset($_POST['width']))   { $base['width']   = $_POST['width']; }
        if (isset($_POST['height']))  { $base['height']  = $_POST['height']; }
        if (isset($_POST['rotate']))  { $base['rotate']  = $_POST['rotate']; }
        if (isset($_POST['z_index'])) {
            $z = (int)$_POST['z_index'];
            $base['z_index'] = max(1, min(9999, $z));
        }
        if (isset($_POST['alt']))     { $base['alt']     = $_POST['alt']; }
        if (array_key_exists('enabled', $_POST)) {
            $base['enabled'] = empty($_POST['enabled']) ? 0 : 1;
        }

        $stickers[$index] = main_skin_normalize_sticker($base);
        if (!save_main_stickers($stickers)) {
            main_skin_json_error('스티커 수정에 실패했습니다.');
        }

        main_skin_json_ok(array('sticker' => $stickers[$index]));
        break;

    case 'move_sticker':
        $id    = isset($_POST['id']) ? trim($_POST['id']) : '';
        $index = main_skin_find_sticker_index($stickers, $id);
        if ($index < 0) {
            main_skin_json_error('해당 스티커를 찾을 수 없습니다.');
        }

        $stickers[$index]['left'] = main_skin_normalize_length(
            isset($_POST['left']) ? $_POST['left'] : $stickers[$index]['left'],
            $stickers[$index]['left'],
            false
        );
        $stickers[$index]['top'] = main_skin_normalize_length(
            isset($_POST['top']) ? $_POST['top'] : $stickers[$index]['top'],
            $stickers[$index]['top'],
            false
        );
        $stickers[$index] = main_skin_normalize_sticker($stickers[$index]);

        if (!save_main_stickers($stickers)) {
            main_skin_json_error('스티커 위치 저장에 실패했습니다.');
        }

        main_skin_json_ok(array('sticker' => $stickers[$index]));
        break;

    case 'delete_sticker':
        $id    = isset($_POST['id']) ? trim($_POST['id']) : '';
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

    case 'add_asset':
        $assets      = get_main_assets();
        $source_type = (isset($_POST['src_type']) && $_POST['src_type'] === 'upload') ? 'file' : 'url';
        $image       = '';

        if ($source_type === 'file') {
            if (empty($_FILES['asset_file']['tmp_name'])) {
                main_skin_json_error('업로드할 에셋 파일을 선택해 주세요.');
            }
            $image = main_skin_upload_image($_FILES['asset_file'], 'sticker', 'asset');
            if ($image === false) {
                main_skin_json_error('에셋 업로드에 실패했습니다.');
            }
        } else {
            $image = main_skin_image_url(isset($_POST['src_url']) ? trim($_POST['src_url']) : '');
            if ($image === '') {
                main_skin_json_error('에셋 이미지 URL을 입력해 주세요.');
            }
        }

        $asset = main_skin_normalize_asset(array(
            'id'          => 'asset_' . main_skin_generate_id(),
            'image'       => $image,
            'alt'         => isset($_POST['alt']) ? $_POST['alt'] : '',
            'source_type' => $source_type
        ));

        $assets[] = $asset;
        if (!save_main_assets($assets)) {
            main_skin_json_error('에셋 저장에 실패했습니다.');
        }

        main_skin_json_ok(array('asset' => $asset));
        break;

    case 'delete_asset':
        $assets = get_main_assets();
        $id     = isset($_POST['id']) ? trim($_POST['id']) : '';
        $found  = -1;
        foreach ($assets as $i => $a) {
            if (isset($a['id']) && $a['id'] === $id) {
                $found = $i;
                break;
            }
        }
        if ($found < 0) {
            main_skin_json_error('해당 에셋을 찾을 수 없습니다.');
        }
        if (!empty($assets[$found]['image']) && $assets[$found]['source_type'] === 'file') {
            main_skin_delete_uploaded_asset($assets[$found]['image'], 'sticker');
        }
        array_splice($assets, $found, 1);
        if (!save_main_assets($assets)) {
            main_skin_json_error('에셋 삭제에 실패했습니다.');
        }
        main_skin_json_ok(array());
        break;

    case 'place_asset':
        $assets = get_main_assets();
        $id     = isset($_POST['id']) ? trim($_POST['id']) : '';
        $asset  = null;
        foreach ($assets as $a) {
            if (isset($a['id']) && $a['id'] === $id) {
                $asset = $a;
                break;
            }
        }
        if (!$asset) {
            main_skin_json_error('해당 에셋을 찾을 수 없습니다.');
        }

        $max_z   = main_skin_max_z_index($stickers);
        $sticker = main_skin_normalize_sticker(array(
            'id'          => 'sticker_' . main_skin_generate_id(),
            'source_type' => $asset['source_type'],
            'image'       => $asset['image'],
            'left'        => '50%',
            'top'         => '50%',
            'width'       => '160px',
            'height'      => 'auto',
            'rotate'      => '0',
            'z_index'     => $max_z + 1,
            'enabled'     => 1,
            'alt'         => $asset['alt']
        ));

        $stickers[] = $sticker;
        if (!save_main_stickers($stickers)) {
            main_skin_json_error('스티커 배치에 실패했습니다.');
        }

        main_skin_json_ok(array('sticker' => $sticker));
        break;

    default:
        main_skin_json_error('알 수 없는 액션입니다.');
}

<?php
if (!defined('_GNUBOARD_')) exit;

if (!defined('MAIN_SKIN_NAME')) {
    define('MAIN_SKIN_NAME', 'retro_main');
}

if (!defined('MAIN_SKIN_DIR')) {
    define('MAIN_SKIN_DIR', dirname(__FILE__));
}

if (!defined('MAIN_SKIN_URL')) {
    if (defined('G5_SKIN_URL')) {
        define('MAIN_SKIN_URL', G5_SKIN_URL . '/board/' . MAIN_SKIN_NAME);
    } elseif (defined('G5_URL')) {
        define('MAIN_SKIN_URL', G5_URL . '/skin/board/' . MAIN_SKIN_NAME);
    } else {
        define('MAIN_SKIN_URL', '/skin/board/' . MAIN_SKIN_NAME);
    }
}

function main_skin_storage_root_path() {
    if (defined('G5_DATA_PATH')) {
        return G5_DATA_PATH . '/file/main_skin';
    }

    return MAIN_SKIN_DIR . '/data';
}

function main_skin_storage_root_url() {
    if (defined('G5_DATA_URL')) {
        return G5_DATA_URL . '/file/main_skin';
    }

    return MAIN_SKIN_URL . '/data';
}

function main_skin_asset_types() {
    return array('visual', 'polaroid', 'banner', 'sticker');
}

function main_skin_asset_dir($type) {
    return main_skin_storage_root_path() . '/' . $type;
}

function main_skin_asset_url($type) {
    return main_skin_storage_root_url() . '/' . $type;
}

function main_skin_write_security_file($dir) {
    $htaccess = $dir . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents(
            $htaccess,
            "Options -Indexes\n" .
            "<FilesMatch \"\\.(json|php)$\">\n" .
            "  Deny from all\n" .
            "</FilesMatch>\n"
        );
    }
}

function main_skin_ensure_storage() {
    $root = main_skin_storage_root_path();

    if (!is_dir($root)) {
        mkdir($root, 0755, true);
    }

    main_skin_write_security_file($root);

    foreach (main_skin_asset_types() as $type) {
        $dir = main_skin_asset_dir($type);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (!file_exists($dir . '/.gitkeep')) {
            file_put_contents($dir . '/.gitkeep', '');
        }
        main_skin_write_security_file($dir);
    }

    return $root;
}

function main_skin_default_config() {
    return array(
        'visual_image' => '',
        'visual_alt' => '메인 비주얼',
        'polaroid_1_image' => '',
        'polaroid_1_alt' => '폴라로이드 1',
        'polaroid_1_caption' => '',
        'polaroid_1_rotate' => '-3',
        'polaroid_2_image' => '',
        'polaroid_2_alt' => '폴라로이드 2',
        'polaroid_2_caption' => '',
        'polaroid_2_rotate' => '2',
        'window_title' => '최신글',
        'banner_title' => '배너',
        'latest_rows' => 8,
        'latest_boards' => 'free'
    );
}

function main_skin_legacy_config_to_current($config) {
    $legacy = main_skin_default_config();

    if (isset($config['visual']['src'])) {
        $legacy['visual_image'] = $config['visual']['src'];
        $legacy['visual_alt'] = isset($config['visual']['alt']) ? $config['visual']['alt'] : $legacy['visual_alt'];
    }

    if (isset($config['polaroid1']['src'])) {
        $legacy['polaroid_1_image'] = $config['polaroid1']['src'];
        $legacy['polaroid_1_alt'] = isset($config['polaroid1']['alt']) ? $config['polaroid1']['alt'] : $legacy['polaroid_1_alt'];
        $legacy['polaroid_1_caption'] = isset($config['polaroid1']['caption']) ? $config['polaroid1']['caption'] : '';
        $legacy['polaroid_1_rotate'] = isset($config['polaroid1']['rotate']) ? $config['polaroid1']['rotate'] : $legacy['polaroid_1_rotate'];
    }

    if (isset($config['polaroid2']['src'])) {
        $legacy['polaroid_2_image'] = $config['polaroid2']['src'];
        $legacy['polaroid_2_alt'] = isset($config['polaroid2']['alt']) ? $config['polaroid2']['alt'] : $legacy['polaroid_2_alt'];
        $legacy['polaroid_2_caption'] = isset($config['polaroid2']['caption']) ? $config['polaroid2']['caption'] : '';
        $legacy['polaroid_2_rotate'] = isset($config['polaroid2']['rotate']) ? $config['polaroid2']['rotate'] : $legacy['polaroid_2_rotate'];
    }

    if (isset($config['retro_window'])) {
        $legacy['window_title'] = isset($config['retro_window']['title']) ? $config['retro_window']['title'] : $legacy['window_title'];
        $legacy['latest_rows'] = isset($config['retro_window']['limit']) ? (int)$config['retro_window']['limit'] : $legacy['latest_rows'];
        if (!empty($config['retro_window']['board_ids']) && is_array($config['retro_window']['board_ids'])) {
            $legacy['latest_boards'] = implode(',', $config['retro_window']['board_ids']);
        }
    }

    return array_replace($legacy, $config);
}

function get_main_skin_config() {
    $file = main_skin_storage_root_path() . '/config.json';
    if (!file_exists($file)) {
        return main_skin_default_config();
    }

    $config = json_decode(file_get_contents($file), true);
    if (!is_array($config)) {
        return main_skin_default_config();
    }

    if (isset($config['retro_window']) || isset($config['visual']) || isset($config['polaroid1'])) {
        $config = main_skin_legacy_config_to_current($config);
    }

    return array_replace(main_skin_default_config(), $config);
}

function save_main_skin_config($config) {
    main_skin_ensure_storage();
    return file_put_contents(
        main_skin_storage_root_path() . '/config.json',
        json_encode(array_replace(main_skin_default_config(), $config), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    ) !== false;
}

function main_skin_default_banner() {
    return array(
        'image' => '',
        'link' => '',
        'target' => '_blank',
        'alt' => '',
        'enabled' => 1,
        'sort' => 0
    );
}

function main_skin_normalize_banner($banner) {
    $normalized = array_replace(main_skin_default_banner(), is_array($banner) ? $banner : array());

    if (isset($normalized['src']) && empty($normalized['image'])) {
        $normalized['image'] = $normalized['src'];
    }
    if (isset($normalized['href']) && empty($normalized['link'])) {
        $normalized['link'] = $normalized['href'];
    }

    $normalized['image'] = main_skin_image_url($normalized['image']);
    $normalized['link'] = main_skin_sanitize_link($normalized['link']);
    $normalized['target'] = ($normalized['target'] === '_self') ? '_self' : '_blank';
    $normalized['alt'] = main_skin_limit_text(isset($normalized['alt']) ? $normalized['alt'] : '', 100);
    $normalized['enabled'] = empty($normalized['enabled']) ? 0 : 1;
    $normalized['sort'] = (int)$normalized['sort'];

    return $normalized;
}

function get_main_banners() {
    $file = main_skin_storage_root_path() . '/banners.json';
    if (file_exists($file)) {
        $banners = json_decode(file_get_contents($file), true);
    } else {
        $config = get_main_skin_config();
        $banners = isset($config['retro_window']['banners']) ? $config['retro_window']['banners'] : array();
    }

    if (!is_array($banners)) {
        return array();
    }

    $normalized = array();
    foreach ($banners as $banner) {
        $banner = main_skin_normalize_banner($banner);
        if (!empty($banner['image'])) {
            $normalized[] = $banner;
        }
    }

    usort($normalized, 'main_skin_compare_banner');

    return $normalized;
}

function main_skin_compare_banner($left, $right) {
    $left_sort = isset($left['sort']) ? (int)$left['sort'] : 0;
    $right_sort = isset($right['sort']) ? (int)$right['sort'] : 0;
    if ($left_sort === $right_sort) {
        return 0;
    }

    return ($left_sort < $right_sort) ? -1 : 1;
}

function save_main_banners($banners) {
    main_skin_ensure_storage();

    $normalized = array();
    foreach ((array)$banners as $banner) {
        $banner = main_skin_normalize_banner($banner);
        if (!empty($banner['image'])) {
            $normalized[] = $banner;
        }
    }

    usort($normalized, 'main_skin_compare_banner');

    return file_put_contents(
        main_skin_storage_root_path() . '/banners.json',
        json_encode(array_values($normalized), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    ) !== false;
}

function main_skin_default_sticker() {
    return array(
        'id' => '',
        'source_type' => 'url',
        'image' => '',
        'left' => '100px',
        'top' => '100px',
        'width' => '160px',
        'height' => 'auto',
        'rotate' => '0',
        'z_index' => 20,
        'enabled' => 1,
        'alt' => ''
    );
}

function main_skin_normalize_length($value, $default, $allow_auto) {
    $value = trim((string)$value);
    if ($value === '') {
        return $default;
    }

    if ($allow_auto && strtolower($value) === 'auto') {
        return 'auto';
    }

    if (preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
        return $value . 'px';
    }

    if (preg_match('/^-?\d+(?:\.\d+)?(?:px|%)$/i', $value)) {
        return strtolower($value);
    }

    return $default;
}

function main_skin_length_to_number($value, $default) {
    if (preg_match('/-?\d+(?:\.\d+)?/', (string)$value, $match)) {
        return (int)round((float)$match[0]);
    }

    return (int)$default;
}

function main_skin_normalize_sticker($sticker) {
    $normalized = array_replace(main_skin_default_sticker(), is_array($sticker) ? $sticker : array());

    if (isset($normalized['src']) && empty($normalized['image'])) {
        $normalized['image'] = $normalized['src'];
    }
    if (isset($normalized['src_type']) && empty($normalized['source_type'])) {
        $normalized['source_type'] = $normalized['src_type'];
    }

    $normalized['image'] = main_skin_image_url($normalized['image']);
    $normalized['source_type'] = ($normalized['source_type'] === 'file' || $normalized['source_type'] === 'upload') ? 'file' : 'url';
    $normalized['left'] = main_skin_normalize_length($normalized['left'], '100px', false);
    $normalized['top'] = main_skin_normalize_length($normalized['top'], '100px', false);
    $normalized['width'] = main_skin_normalize_length($normalized['width'], '160px', true);
    $normalized['height'] = main_skin_normalize_length($normalized['height'], 'auto', true);
    $normalized['rotate'] = (string)round((float)$normalized['rotate'], 2);
    $normalized['z_index'] = (int)$normalized['z_index'];
    $normalized['enabled'] = empty($normalized['enabled']) ? 0 : 1;
    $normalized['alt'] = main_skin_limit_text(isset($normalized['alt']) ? $normalized['alt'] : '', 100);

    if (empty($normalized['id'])) {
        $normalized['id'] = 'sticker_' . main_skin_generate_id();
    }

    return $normalized;
}

function get_main_stickers() {
    $file = main_skin_storage_root_path() . '/stickers.json';
    if (!file_exists($file)) {
        return array();
    }

    $stickers = json_decode(file_get_contents($file), true);
    if (!is_array($stickers)) {
        return array();
    }

    $normalized = array();
    foreach ($stickers as $sticker) {
        $sticker = main_skin_normalize_sticker($sticker);
        if (!empty($sticker['image'])) {
            $normalized[] = $sticker;
        }
    }

    return $normalized;
}

function save_main_stickers($stickers) {
    main_skin_ensure_storage();

    $normalized = array();
    foreach ((array)$stickers as $sticker) {
        $sticker = main_skin_normalize_sticker($sticker);
        if (!empty($sticker['image'])) {
            $normalized[] = $sticker;
        }
    }

    return file_put_contents(
        main_skin_storage_root_path() . '/stickers.json',
        json_encode(array_values($normalized), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    ) !== false;
}

function main_skin_limit_text($value, $length) {
    $value = trim(strip_tags((string)$value));
    if ($value === '') {
        return '';
    }

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $length, 'UTF-8');
    }

    if (preg_match_all('/./us', $value, $chars) && isset($chars[0])) {
        return implode('', array_slice($chars[0], 0, $length));
    }

    return substr($value, 0, $length);
}

function main_skin_sanitize_link($value) {
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    if (preg_match('/^(https?:)?\/\//i', $value)) {
        return $value;
    }

    if ($value[0] === '/') {
        return $value;
    }

    return '';
}

function main_skin_image_url($path_or_url) {
    $path_or_url = trim((string)$path_or_url);
    if ($path_or_url === '') {
        return '';
    }

    if (preg_match('/^(https?:)?\/\//i', $path_or_url)) {
        return $path_or_url;
    }

    if ($path_or_url[0] === '/' || strpos($path_or_url, './') === 0 || strpos($path_or_url, '../') === 0) {
        return $path_or_url;
    }

    return $path_or_url;
}

function main_skin_generate_id() {
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes(8));
    }

    $strong = false;
    $bytes = function_exists('openssl_random_pseudo_bytes') ? openssl_random_pseudo_bytes(8, $strong) : false;
    if ($bytes !== false && $strong) {
        return bin2hex($bytes);
    }

    return substr(md5(uniqid('', true) . mt_rand()), 0, 16);
}

function main_skin_upload_image($file_arr, $type, $prefix) {
    if (!isset($file_arr['error']) || $file_arr['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp');
    $ext = strtolower(pathinfo($file_arr['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return false;
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $file_arr['tmp_name']);
            finfo_close($finfo);
            $allowed_mimes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/x-ms-bmp', 'image/x-bmp');
            if (!in_array($mime, $allowed_mimes)) {
                return false;
            }
        }
    }

    main_skin_ensure_storage();
    $filename = $prefix . '_' . date('YmdHis') . '_' . main_skin_generate_id() . '.' . $ext;
    $destination = main_skin_asset_dir($type) . '/' . $filename;

    if (!move_uploaded_file($file_arr['tmp_name'], $destination)) {
        return false;
    }

    return main_skin_asset_url($type) . '/' . $filename;
}

function main_skin_delete_uploaded_asset($src, $type) {
    $src = trim((string)$src);
    if ($src === '') {
        return;
    }

    $base_url = main_skin_asset_url($type);
    if (strpos($src, $base_url) !== 0) {
        return;
    }

    $file = main_skin_asset_dir($type) . '/' . basename($src);
    if (file_exists($file)) {
        @unlink($file);
    }
}

function main_skin_board_ids_from_config($config) {
    $board_text = isset($config['latest_boards']) ? $config['latest_boards'] : '';
    $parts = explode(',', $board_text);
    $board_ids = array();

    foreach ($parts as $part) {
        $part = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($part)));
        if ($part !== '') {
            $board_ids[] = $part;
        }
    }

    return empty($board_ids) ? array('free') : array_values(array_unique($board_ids));
}

function main_skin_normalize_board_ids_text($board_text) {
    return implode(',', main_skin_board_ids_from_config(array('latest_boards' => $board_text)));
}

function main_skin_get_latest_posts($bo_table, $limit) {
    $bo_table = preg_replace('/[^a-z0-9_]/', '', strtolower($bo_table));
    if ($bo_table === '') {
        return array();
    }

    $limit = max(1, min(20, (int)$limit));
    $prefix = defined('G5_TABLE_PREFIX') ? G5_TABLE_PREFIX : 'g5_';
    $table = $prefix . 'write_' . $bo_table;

    $check = sql_query("SHOW TABLES LIKE '" . addslashes($table) . "'", false);
    if (!$check || sql_num_rows($check) === 0) {
        return array();
    }

    $result = sql_query(
        "SELECT wr_id, wr_subject, wr_datetime, wr_name FROM `" . $table . "` WHERE wr_is_comment = 0 ORDER BY wr_datetime DESC LIMIT " . $limit,
        false
    );

    $rows = array();
    if ($result) {
        while ($row = sql_fetch_array($result)) {
            $row['bo_table'] = $bo_table;
            $rows[] = $row;
        }
    }

    return $rows;
}

function main_skin_get_board_name($bo_table) {
    $bo_table = preg_replace('/[^a-z0-9_]/', '', strtolower($bo_table));
    if ($bo_table === '') {
        return '';
    }

    $prefix = defined('G5_TABLE_PREFIX') ? G5_TABLE_PREFIX : 'g5_';
    $row = sql_fetch("SELECT bo_subject FROM `" . $prefix . "board` WHERE bo_table = '" . addslashes($bo_table) . "'", false);

    return (!empty($row['bo_subject'])) ? $row['bo_subject'] : $bo_table;
}

function main_skin_cut_str($str, $len, $suffix) {
    if (function_exists('cut_str')) {
        return cut_str($str, $len, $suffix);
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($str, 'UTF-8') <= $len) {
            return $str;
        }

        return mb_substr($str, 0, $len, 'UTF-8') . $suffix;
    }

    if (strlen($str) <= $len) {
        return $str;
    }

    return substr($str, 0, $len) . $suffix;
}

function render_main_latest($config) {
    $board_ids = main_skin_board_ids_from_config($config);
    $limit = isset($config['latest_rows']) ? (int)$config['latest_rows'] : 8;
    $bbs_url = defined('G5_BBS_URL') ? G5_BBS_URL : (defined('G5_URL') ? G5_URL . '/bbs' : '/bbs');

    // 모든 선택된 게시판에서 글을 가져와 합침
    $all_posts = array();
    foreach ($board_ids as $bo_table) {
        $posts = main_skin_get_latest_posts($bo_table, $limit);
        foreach ($posts as $post) {
            $all_posts[] = $post;
        }
    }

    // 시간순 내림차순 정렬
    usort($all_posts, 'main_skin_compare_post_datetime');

    // limit 개수만큼 자르기
    $all_posts = array_slice($all_posts, 0, $limit);

    ob_start();

    if (empty($all_posts)) {
        echo '<p class="win95-no-posts win95-no-posts-empty">게시판 설정을 확인해 주세요.</p>';
    } else {
    ?>
    <ul class="win95-post-list">
      <?php foreach ($all_posts as $post) {
          $bo_table = isset($post['bo_table']) ? $post['bo_table'] : '';
          $post_url = $bbs_url . '/board.php?bo_table=' . urlencode($bo_table) . '&wr_id=' . (int)$post['wr_id'];

          $subject = trim(strip_tags($post['wr_subject']));
          if ($subject === '' || $subject === null) {
              $subject = '제목 없음';
          }
          $subject = main_skin_cut_str($subject, 24, '…');

          $date = !empty($post['wr_datetime']) ? date('Y.m.d H:i', strtotime($post['wr_datetime'])) : '';
          $name = !empty($post['wr_name']) ? $post['wr_name'] : '';
      ?>
      <li class="win95-post-item">
        <a href="<?php echo main_skin_esc($post_url); ?>" class="win95-post-link">
          <div class="win95-post-front">
            <span class="win95-post-subject"><?php echo main_skin_esc($subject); ?></span>
            <span class="win95-post-date"><?php echo main_skin_esc($date); ?></span>
          </div>
          <div class="win95-post-back">
            <span class="win95-post-name"><?php echo main_skin_esc($name); ?></span>
          </div>
        </a>
      </li>
      <?php } ?>
    </ul>
    <?php
    }

    return ob_get_clean();
}

function main_skin_compare_post_datetime($a, $b) {
    $a_time = isset($a['wr_datetime']) ? $a['wr_datetime'] : '';
    $b_time = isset($b['wr_datetime']) ? $b['wr_datetime'] : '';
    if ($a_time === $b_time) {
        return 0;
    }
    return ($a_time > $b_time) ? -1 : 1;
}

function main_skin_is_admin() {
    global $is_admin, $member, $config, $g5;

    // 1) 그누보드 기본: $is_admin === 'super'
    if (isset($is_admin) && (string)$is_admin === 'super') {
        return true;
    }

    // 2) $is_admin이 truthy한 다른 값 (아보카도 퍼스널 등)
    if (!empty($is_admin)) {
        return true;
    }

    // 3) 그누보드5 내장 is_admin() 함수
    if (function_exists('is_admin')) {
        $chk = is_admin('super');
        if ($chk) {
            return true;
        }
    }

    // 4) 로그인된 회원의 mb_id가 그누보드 최고관리자 ID와 일치하는지
    if (!empty($member['mb_id'])) {
        // 4-a) $config['cf_admin'] (그누보드 기본설정의 최고관리자 ID)
        if (!empty($config['cf_admin']) && $member['mb_id'] === $config['cf_admin']) {
            return true;
        }
        // 4-b) mb_id가 'admin'인 경우
        if ($member['mb_id'] === 'admin') {
            return true;
        }
        // 4-c) 회원 레벨이 최고 레벨(10)
        if (isset($member['mb_level']) && (int)$member['mb_level'] >= 10) {
            return true;
        }
    }

    // 5) 세션에서 직접 확인 (위 방법 모두 실패 시 최종 폴백)
    if (isset($_SESSION['ss_mb_id']) && $_SESSION['ss_mb_id'] === 'admin') {
        return true;
    }

    return false;
}

function main_skin_esc($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function main_skin_get_token() {
    if (function_exists('get_token')) {
        return get_token();
    }

    if (!isset($_SESSION['main_skin_token'])) {
        $_SESSION['main_skin_token'] = main_skin_generate_id() . main_skin_generate_id();
    }

    return $_SESSION['main_skin_token'];
}

function main_skin_check_token($token) {
    if (function_exists('check_token')) {
        check_token();
        return true;
    }

    if (!isset($_SESSION['main_skin_token'])) {
        return false;
    }

    return hash_equals($_SESSION['main_skin_token'], (string)$token);
}

function main_skin_json_response($data) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function main_skin_json_ok($extra = array()) {
    main_skin_json_response(array_merge(array('ok' => true), $extra));
}

function main_skin_json_error($message) {
    main_skin_json_response(array('ok' => false, 'error' => $message));
}

function retro_main_get_config() { return get_main_skin_config(); }
function retro_main_save_config($config) { return save_main_skin_config($config); }
function retro_main_get_stickers() { return get_main_stickers(); }
function retro_main_save_stickers($stickers) { return save_main_stickers($stickers); }
function retro_main_get_latest($bo_table, $limit) { return main_skin_get_latest_posts($bo_table, $limit); }
function retro_main_get_board_name($bo_table) { return main_skin_get_board_name($bo_table); }
function retro_main_cut_str($str, $len = 25, $suffix = '…') { return main_skin_cut_str($str, $len, $suffix); }
function retro_main_is_admin() { return main_skin_is_admin(); }
function retro_main_esc($value) { return main_skin_esc($value); }
function retro_main_get_token() { return main_skin_get_token(); }
function retro_main_check_token($token) { return main_skin_check_token($token); }
function retro_main_data_dir() { return main_skin_storage_root_path(); }
function retro_main_data_url() { return main_skin_storage_root_url(); }
function retro_main_ensure_data_dir() { return main_skin_ensure_storage(); }
function retro_main_upload_image($file_arr, $prefix = 'img') {
    $map = array(
        'visual' => 'visual',
        'pol1' => 'polaroid',
        'pol2' => 'polaroid',
        'banner' => 'banner',
        'sticker' => 'sticker',
        'img' => 'visual'
    );
    $type = isset($map[$prefix]) ? $map[$prefix] : 'visual';
    return main_skin_upload_image($file_arr, $type, $prefix);
}
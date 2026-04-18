<?php
if (!defined('_GNUBOARD_')) exit;
include_once(dirname(__FILE__) . '/main.lib.php');

$main_skin_config   = get_main_skin_config();
$main_skin_banners  = get_main_banners();
$main_skin_stickers = get_main_stickers();
$main_skin_assets   = get_main_assets();
$main_skin_is_admin = main_skin_is_admin();
$main_skin_token    = $main_skin_is_admin ? main_skin_get_token() : '';
$main_skin_latest_html = render_main_latest($main_skin_config);
$window_title = !empty($main_skin_config['window_title']) ? $main_skin_config['window_title'] : '최신글';
$banner_title = !empty($main_skin_config['banner_title']) ? $main_skin_config['banner_title'] : '배너';
?>
<link rel="stylesheet" href="<?php echo main_skin_esc(MAIN_SKIN_URL); ?>/main.css">

<div id="retro-main-wrapper">
  <?php if (!empty($main_skin_config['parallax_bg_image'])) { ?>
  <div id="parallax-bg-layer" class="parallax-layer parallax-bg-layer"
       data-pos-v="<?php echo main_skin_esc($main_skin_config['parallax_bg_pos_v']); ?>"
       data-pos-h="<?php echo main_skin_esc($main_skin_config['parallax_bg_pos_h']); ?>"
       data-offset-x="<?php echo (int)$main_skin_config['parallax_bg_offset_x']; ?>"
       data-offset-y="<?php echo (int)$main_skin_config['parallax_bg_offset_y']; ?>"
       aria-hidden="true">
    <img src="<?php echo main_skin_esc($main_skin_config['parallax_bg_image']); ?>"
         alt=""
         style="<?php echo main_skin_parallax_img_style($main_skin_config, 'parallax_bg'); ?>">
  </div>
  <?php } ?>
  <div id="retro-sticker-overlay" aria-hidden="true">
    <?php foreach ($main_skin_stickers as $sticker) {
        if (empty($sticker['enabled']) || empty($sticker['image'])) {
            continue;
        }
        $is_pct = (strpos($sticker['left'], '%') !== false && strpos($sticker['top'], '%') !== false);
        $rotate_deg = main_skin_esc($sticker['rotate']);
        $transform_val = $is_pct
            ? 'translate(-50%,-50%) rotate(' . $rotate_deg . 'deg)'
            : 'rotate(' . $rotate_deg . 'deg)';
        $sticker_w = main_skin_esc($sticker['width']);
        $sticker_h = main_skin_esc($sticker['height']);
    ?>
    <div class="retro-sticker<?php echo $main_skin_is_admin ? ' admin-sticker' : ''; ?>"
         id="sticker-<?php echo main_skin_esc($sticker['id']); ?>"
         data-id="<?php echo main_skin_esc($sticker['id']); ?>"
         data-rotate="<?php echo $rotate_deg; ?>"
         data-z-index="<?php echo (int)$sticker['z_index']; ?>"
         style="left:<?php echo main_skin_esc($sticker['left']); ?>;top:<?php echo main_skin_esc($sticker['top']); ?>;z-index:<?php echo (int)$sticker['z_index']; ?>;width:<?php echo $sticker_w; ?>;height:<?php echo $sticker_h; ?>;transform:<?php echo $transform_val; ?>;">
      <img src="<?php echo main_skin_esc($sticker['image']); ?>"
           alt="<?php echo main_skin_esc($sticker['alt']); ?>">
      <?php if ($main_skin_is_admin) { ?>
      <div class="sticker-handles">
        <button type="button" class="sticker-zup-btn"  data-id="<?php echo main_skin_esc($sticker['id']); ?>" title="z-index 올리기">▲</button>
        <button type="button" class="sticker-zdown-btn" data-id="<?php echo main_skin_esc($sticker['id']); ?>" title="z-index 내리기">▼</button>
        <button type="button" class="sticker-del-btn"  data-id="<?php echo main_skin_esc($sticker['id']); ?>" title="스티커 삭제">×</button>
        <div class="sticker-rotate-handle" title="드래그하여 회전"></div>
        <div class="sticker-resize-handle" title="드래그하여 크기 조절"></div>
      </div>
      <?php } ?>
    </div>
    <?php } ?>
  </div>

  <?php if ($main_skin_is_admin) { ?>
  <div id="retro-admin-buttons">
    <button type="button"
            id="retro-sticker-edit-btn"
            class="win95-window win95-action-btn"
            title="스티커 편집 모드 전환">
      ✏️ 스티커 편집
    </button>
    <button type="button"
            id="retro-admin-open-btn"
            class="win95-window win95-action-btn"
            aria-label="스킨 관리 패널 열기"
            aria-haspopup="dialog"
            aria-controls="retro-admin-modal"
            aria-expanded="false">
      🔧 스킨 관리
    </button>
  </div>
  <?php } ?>

  <div id="retro-main-layout">
    <div id="retro-content-row">
      <div id="retro-polaroids">
        <div class="retro-polaroid polaroid-primary" style="transform:rotate(<?php echo main_skin_esc($main_skin_config['polaroid_1_rotate']); ?>deg);">
          <?php if (!empty($main_skin_config['polaroid_1_image'])) { ?>
          <img src="<?php echo main_skin_esc($main_skin_config['polaroid_1_image']); ?>" alt="<?php echo main_skin_esc($main_skin_config['polaroid_1_alt']); ?>">
          <?php } else { ?>
          <div class="polaroid-placeholder">폴라로이드 1</div>
          <?php } ?>
          <?php if (!empty($main_skin_config['polaroid_1_caption'])) { ?>
          <p class="polaroid-caption"><?php echo main_skin_esc($main_skin_config['polaroid_1_caption']); ?></p>
          <?php } ?>
        </div>

        <div class="retro-polaroid polaroid-secondary" style="transform:rotate(<?php echo main_skin_esc($main_skin_config['polaroid_2_rotate']); ?>deg);">
          <?php if (!empty($main_skin_config['polaroid_2_image'])) { ?>
          <img src="<?php echo main_skin_esc($main_skin_config['polaroid_2_image']); ?>" alt="<?php echo main_skin_esc($main_skin_config['polaroid_2_alt']); ?>">
          <?php } else { ?>
          <div class="polaroid-placeholder">폴라로이드 2</div>
          <?php } ?>
          <?php if (!empty($main_skin_config['polaroid_2_caption'])) { ?>
          <p class="polaroid-caption"><?php echo main_skin_esc($main_skin_config['polaroid_2_caption']); ?></p>
          <?php } ?>
        </div>
      </div>

      <div id="retro-win95-window">
        <div class="win95-flip-container">
          <div class="win95-flipper" id="win95-flipper">
            <div class="win95-face win95-front">
              <div class="win95-window win95-face-window">
                <div class="win95-titlebar" tabindex="0" role="button" aria-pressed="false" data-win95-toggle="latest">
                  <span class="win95-title-icon">💾</span>
                  <span class="win95-title-text"><?php echo main_skin_esc($window_title); ?></span>
                  <span class="win95-title-hint">click to toggle</span>
                  <div class="win95-buttons"><span class="win95-btn">_</span><span class="win95-btn">□</span><span class="win95-btn">×</span></div>
                </div>
                <div class="win95-inner-border"><?php echo $main_skin_latest_html; ?></div>
              </div>
            </div>
            <div class="win95-face win95-back">
              <div class="win95-window win95-face-window">
                <div class="win95-titlebar" tabindex="0" role="button" aria-pressed="true" data-win95-toggle="banner">
                  <span class="win95-title-icon">🖼</span>
                  <span class="win95-title-text"><?php echo main_skin_esc($banner_title); ?></span>
                  <span class="win95-title-hint">click to toggle</span>
                  <div class="win95-buttons"><span class="win95-btn">_</span><span class="win95-btn">□</span><span class="win95-btn">×</span></div>
                </div>
                <div class="win95-inner-border">
                  <div class="win95-banner-area">
                    <?php $visible_banners = array(); foreach ($main_skin_banners as $banner) { if (!empty($banner['enabled']) && !empty($banner['image'])) $visible_banners[] = $banner; } ?>
                    <?php if (empty($visible_banners)) { ?>
                    <p class="win95-no-posts">등록된 배너가 없습니다.</p>
                    <?php } else { foreach ($visible_banners as $banner) { ?>
                    <?php $banner_link = !empty($banner['link']) ? $banner['link'] : '#'; ?>
                    <a href="<?php echo main_skin_esc($banner_link); ?>" target="<?php echo main_skin_esc($banner['target']); ?>" rel="noopener noreferrer" class="banner-link">
                      <img src="<?php echo main_skin_esc($banner['image']); ?>" alt="<?php echo main_skin_esc($banner['alt']); ?>" class="banner-img">
                    </a>
                    <?php } } ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($main_skin_config['parallax_ng_image'])) { ?>
  <div id="parallax-ng-layer" class="parallax-layer parallax-above-layer"
       data-pos-v="<?php echo main_skin_esc($main_skin_config['parallax_ng_pos_v']); ?>"
       data-pos-h="<?php echo main_skin_esc($main_skin_config['parallax_ng_pos_h']); ?>"
       data-offset-x="<?php echo (int)$main_skin_config['parallax_ng_offset_x']; ?>"
       data-offset-y="<?php echo (int)$main_skin_config['parallax_ng_offset_y']; ?>"
       aria-hidden="true">
    <img src="<?php echo main_skin_esc($main_skin_config['parallax_ng_image']); ?>"
         alt=""
         style="<?php echo main_skin_parallax_img_style($main_skin_config, 'parallax_ng'); ?>">
  </div>
  <?php } ?>

  <?php if (!empty($main_skin_config['parallax_fg_image'])) { ?>
  <div id="parallax-fg-layer" class="parallax-layer parallax-above-layer"
       data-pos-v="<?php echo main_skin_esc($main_skin_config['parallax_fg_pos_v']); ?>"
       data-pos-h="<?php echo main_skin_esc($main_skin_config['parallax_fg_pos_h']); ?>"
       data-offset-x="<?php echo (int)$main_skin_config['parallax_fg_offset_x']; ?>"
       data-offset-y="<?php echo (int)$main_skin_config['parallax_fg_offset_y']; ?>"
       aria-hidden="true">
    <img src="<?php echo main_skin_esc($main_skin_config['parallax_fg_image']); ?>"
         alt=""
         style="<?php echo main_skin_parallax_img_style($main_skin_config, 'parallax_fg'); ?>">
  </div>
  <?php } ?>

  <?php if ($main_skin_is_admin) { ?>
  <div id="retro-admin-modal" class="admin-modal" hidden>
    <div class="admin-modal-backdrop" data-admin-close="true"></div>
    <div id="retro-admin-panel" class="win95-window admin-panel admin-panel-modal" role="dialog" aria-modal="true" aria-labelledby="retro-admin-panel-title" tabindex="-1">
      <div class="win95-titlebar admin-panel-titlebar">
        <span class="win95-title-icon">🔧</span>
        <span class="win95-title-text" id="retro-admin-panel-title">메인 스킨 관리 패널</span>
        <button type="button" class="win95-btn admin-panel-close-btn" id="admin-panel-close-btn" aria-label="스킨 관리 패널 닫기">&times;</button>
      </div>
      <div id="admin-panel-body" class="admin-panel-body">
      <div class="admin-tabs">
        <button type="button" class="admin-tab active" data-tab="tab-stickers">🎨 스티커</button>
        <button type="button" class="admin-tab" data-tab="tab-images">📷 폴라로이드</button>
        <button type="button" class="admin-tab" data-tab="tab-parallax">🏔️ 패럴랙스</button>
        <button type="button" class="admin-tab" data-tab="tab-window">🪟 최신글/배너</button>
      </div>

      <div class="admin-tab-pane" id="tab-stickers">
        <form id="sticker-add-form" enctype="multipart/form-data">
          <h3 class="admin-section-title">새 스티커 추가</h3>
          <input type="hidden" name="action" value="add_sticker">
          <input type="hidden" name="token" value="<?php echo main_skin_esc($main_skin_token); ?>">
          <div class="admin-field-row">
            <label>이미지 종류</label>
            <span>
              <label><input type="radio" name="src_type" value="url" checked> URL</label>
              &nbsp;<label><input type="radio" name="src_type" value="upload"> 파일</label>
            </span>
          </div>
          <div id="sticker-url-rows">
            <div class="admin-field-row"><label>URL 1</label><input type="text" name="src_urls[]" style="width:300px;" placeholder="https://example.com/sticker.gif"></div>
            <div class="admin-field-row"><label>URL 2</label><input type="text" name="src_urls[]" style="width:300px;"></div>
            <div class="admin-field-row"><label>URL 3</label><input type="text" name="src_urls[]" style="width:300px;"></div>
            <div class="admin-field-row"><label>URL 4</label><input type="text" name="src_urls[]" style="width:300px;"></div>
            <div class="admin-field-row"><label>URL 5</label><input type="text" name="src_urls[]" style="width:300px;"></div>
          </div>
          <div id="sticker-file-row" style="display:none;">
            <div class="admin-field-row"><label>이미지 파일</label><input type="file" name="sticker_files[]" accept="image/*" multiple></div>
            <p class="admin-hint">최대 5개까지 선택 가능합니다.</p>
          </div>
          <div class="admin-field-row"><label>설명(alt)</label><input type="text" name="alt" style="width:200px;"></div>
          <div class="admin-field-row"><label></label><button type="submit" class="win95-action-btn">스티커 추가 (화면 중앙 배치)</button></div>
        </form>
        <div id="sticker-add-msg" class="admin-msg" style="display:none;"></div>

        <form id="asset-add-form" enctype="multipart/form-data">
          <h3 class="admin-section-title">에셋 관리 <span class="admin-hint" style="display:inline;">(자주 쓰는 이미지를 저장해두고 배치 버튼으로 스티커 추가)</span></h3>
          <input type="hidden" name="action" value="add_asset">
          <input type="hidden" name="token" value="<?php echo main_skin_esc($main_skin_token); ?>">
          <div class="admin-field-row">
            <label>이미지 종류</label>
            <span>
              <label><input type="radio" name="src_type" value="url" checked> URL</label>
              &nbsp;<label><input type="radio" name="src_type" value="upload"> 파일</label>
            </span>
          </div>
          <div id="asset-url-row"><div class="admin-field-row"><label>이미지 URL</label><input type="text" name="src_url" style="width:300px;" placeholder="https://example.com/image.gif"></div></div>
          <div id="asset-file-row" style="display:none;"><div class="admin-field-row"><label>이미지 파일</label><input type="file" name="asset_file" accept="image/*"></div></div>
          <div class="admin-field-row"><label>설명(alt)</label><input type="text" name="alt" style="width:200px;"></div>
          <div class="admin-field-row"><label></label><button type="submit" class="win95-action-btn">에셋 저장</button></div>
        </form>
        <div id="asset-add-msg" class="admin-msg" style="display:none;"></div>

        <div id="admin-asset-list">
          <?php if (empty($main_skin_assets)) { ?>
          <p class="win95-no-posts">등록된 에셋이 없습니다.</p>
          <?php } else { foreach ($main_skin_assets as $asset) { ?>
          <div class="admin-asset-item" id="admin-asset-<?php echo main_skin_esc($asset['id']); ?>">
            <img src="<?php echo main_skin_esc($asset['image']); ?>" alt="<?php echo main_skin_esc($asset['alt']); ?>" class="admin-asset-thumb">
            <span class="admin-asset-alt"><?php echo main_skin_esc($asset['alt']); ?></span>
            <div class="admin-item-actions" style="flex-direction:row;">
              <button type="button" class="win95-action-btn asset-place-btn" data-id="<?php echo main_skin_esc($asset['id']); ?>">배치</button>
              <button type="button" class="win95-action-btn asset-del-btn"   data-id="<?php echo main_skin_esc($asset['id']); ?>">삭제</button>
            </div>
          </div>
          <?php } } ?>
        </div>
        <div id="asset-msg" class="admin-msg" style="display:none;"></div>

        <h3 class="admin-section-title">등록된 스티커</h3>
        <p class="admin-hint">스티커 편집 모드에서 화면 위 핸들로 이동·크기·회전·z-index를 직접 조절하세요.</p>
        <div id="admin-sticker-list">
          <?php if (empty($main_skin_stickers)) { ?>
          <p class="win95-no-posts">등록된 스티커가 없습니다.</p>
          <?php } else { foreach ($main_skin_stickers as $sticker) { ?>
          <form class="admin-sticker-item sticker-edit-form" id="admin-item-<?php echo main_skin_esc($sticker['id']); ?>">
            <input type="hidden" name="action" value="update_sticker">
            <input type="hidden" name="token" value="<?php echo main_skin_esc($main_skin_token); ?>">
            <input type="hidden" name="id" value="<?php echo main_skin_esc($sticker['id']); ?>">
            <img src="<?php echo main_skin_esc($sticker['image']); ?>" alt="<?php echo main_skin_esc($sticker['alt']); ?>" class="admin-sticker-thumb">
            <div class="admin-item-fields">
              <div class="admin-inline-fields">
                <input type="text" name="alt" value="<?php echo main_skin_esc($sticker['alt']); ?>" placeholder="설명(alt)" style="min-width:160px;">
                <label class="inline-check"><input type="checkbox" name="enabled"<?php echo !empty($sticker['enabled']) ? ' checked' : ''; ?>> 노출</label>
              </div>
            </div>
            <div class="admin-item-actions">
              <button type="submit" class="win95-action-btn">저장</button>
              <button type="button" class="win95-action-btn admin-sticker-delete" data-id="<?php echo main_skin_esc($sticker['id']); ?>">삭제</button>
            </div>
          </form>
          <?php } } ?>
        </div>
        <div id="sticker-edit-msg" class="admin-msg" style="display:none;"></div>
      </div>

      <div class="admin-tab-pane" id="tab-images" style="display:none;">
        <form id="config-images-form" enctype="multipart/form-data">
          <input type="hidden" name="action" value="update_images">
          <input type="hidden" name="token" value="<?php echo main_skin_esc($main_skin_token); ?>">
          <h3 class="admin-section-title">폴라로이드 1</h3>
          <div class="admin-field-row"><label>현재 이미지</label><?php if (!empty($main_skin_config['polaroid_1_image'])) { ?><img src="<?php echo main_skin_esc($main_skin_config['polaroid_1_image']); ?>" class="admin-preview-img"><?php } else { ?><span class="admin-none">없음</span><?php } ?></div>
          <div class="admin-field-row"><label>URL</label><input type="text" name="pol1_url" value="<?php echo main_skin_esc($main_skin_config['polaroid_1_image']); ?>" style="width:320px;"></div>
          <div class="admin-field-row"><label>업로드</label><input type="file" name="pol1_file" accept="image/*"></div>
          <div class="admin-field-row"><label>alt</label><input type="text" name="pol1_alt" value="<?php echo main_skin_esc($main_skin_config['polaroid_1_alt']); ?>"></div>
          <div class="admin-field-row"><label>캡션</label><input type="text" name="pol1_caption" value="<?php echo main_skin_esc($main_skin_config['polaroid_1_caption']); ?>"></div>
          <div class="admin-field-row"><label>회전</label><input type="number" step="0.1" name="pol1_rotate" value="<?php echo main_skin_esc($main_skin_config['polaroid_1_rotate']); ?>"></div>

          <h3 class="admin-section-title">폴라로이드 2</h3>
          <div class="admin-field-row"><label>현재 이미지</label><?php if (!empty($main_skin_config['polaroid_2_image'])) { ?><img src="<?php echo main_skin_esc($main_skin_config['polaroid_2_image']); ?>" class="admin-preview-img"><?php } else { ?><span class="admin-none">없음</span><?php } ?></div>
          <div class="admin-field-row"><label>URL</label><input type="text" name="pol2_url" value="<?php echo main_skin_esc($main_skin_config['polaroid_2_image']); ?>" style="width:320px;"></div>
          <div class="admin-field-row"><label>업로드</label><input type="file" name="pol2_file" accept="image/*"></div>
          <div class="admin-field-row"><label>alt</label><input type="text" name="pol2_alt" value="<?php echo main_skin_esc($main_skin_config['polaroid_2_alt']); ?>"></div>
          <div class="admin-field-row"><label>캡션</label><input type="text" name="pol2_caption" value="<?php echo main_skin_esc($main_skin_config['polaroid_2_caption']); ?>"></div>
          <div class="admin-field-row"><label>회전</label><input type="number" step="0.1" name="pol2_rotate" value="<?php echo main_skin_esc($main_skin_config['polaroid_2_rotate']); ?>"></div>

          <div class="admin-field-row"><label></label><button type="submit" class="win95-action-btn">이미지 설정 저장</button></div>
        </form>
        <div id="config-images-msg" class="admin-msg" style="display:none;"></div>
      </div>

      <div class="admin-tab-pane" id="tab-parallax" style="display:none;">
        <form id="config-parallax-form" enctype="multipart/form-data">
          <input type="hidden" name="action" value="update_parallax">
          <input type="hidden" name="token" value="<?php echo main_skin_esc($main_skin_token); ?>">
          <p class="admin-hint">마우스 움직임에 따라 각 레이어가 반대 방향으로 이동합니다. 초근경이 가장 크게, 원경이 가장 작게 움직입니다.</p>

          <?php
          $parallax_layers = array(
              'fg' => array('label' => '초근경 (Foreground)', 'desc' => '메인 레이아웃 위 — 가장 크게 움직임'),
              'ng' => array('label' => '근경 (Near-ground)', 'desc' => '메인 레이아웃 위 — 중간 정도 움직임'),
              'bg' => array('label' => '원경 (Background)', 'desc' => '메인 레이아웃 뒤 — 가장 작게 움직임')
          );
          foreach ($parallax_layers as $pl_key => $pl_info) {
              $img_key = 'parallax_' . $pl_key . '_image';
              $src_key = 'parallax_' . $pl_key . '_source_type';
              $pv_key = 'parallax_' . $pl_key . '_pos_v';
              $ph_key = 'parallax_' . $pl_key . '_pos_h';
              $ox_key = 'parallax_' . $pl_key . '_offset_x';
              $oy_key = 'parallax_' . $pl_key . '_offset_y';
          ?>
          <h3 class="admin-section-title"><?php echo main_skin_esc($pl_info['label']); ?> <span class="admin-hint" style="display:inline;">(<?php echo main_skin_esc($pl_info['desc']); ?>)</span></h3>
          <div class="admin-field-row">
            <label>현재 이미지</label>
            <?php if (!empty($main_skin_config[$img_key])) { ?>
            <img src="<?php echo main_skin_esc($main_skin_config[$img_key]); ?>" class="admin-preview-img" id="parallax-preview-<?php echo $pl_key; ?>">
            <button type="button" class="win95-action-btn parallax-del-btn" data-layer="<?php echo $pl_key; ?>">삭제</button>
            <?php } else { ?>
            <span class="admin-none" id="parallax-preview-<?php echo $pl_key; ?>">없음</span>
            <?php } ?>
          </div>
          <div class="admin-field-row"><label>이미지 URL</label><input type="text" name="parallax_<?php echo $pl_key; ?>_url" value="<?php echo main_skin_esc(isset($main_skin_config[$img_key]) ? $main_skin_config[$img_key] : ''); ?>" style="width:320px;"></div>
          <div class="admin-field-row"><label>업로드</label><input type="file" name="parallax_<?php echo $pl_key; ?>_file" accept="image/*"></div>
          <div class="admin-field-row">
            <label>세로 위치</label>
            <select name="parallax_<?php echo $pl_key; ?>_pos_v">
              <option value="top"<?php echo (isset($main_skin_config[$pv_key]) && $main_skin_config[$pv_key] === 'top') ? ' selected' : ''; ?>>상단</option>
              <option value="center"<?php echo (!isset($main_skin_config[$pv_key]) || $main_skin_config[$pv_key] === 'center') ? ' selected' : ''; ?>>중앙</option>
              <option value="bottom"<?php echo (isset($main_skin_config[$pv_key]) && $main_skin_config[$pv_key] === 'bottom') ? ' selected' : ''; ?>>하단</option>
            </select>
          </div>
          <div class="admin-field-row">
            <label>가로 위치</label>
            <select name="parallax_<?php echo $pl_key; ?>_pos_h">
              <option value="left"<?php echo (isset($main_skin_config[$ph_key]) && $main_skin_config[$ph_key] === 'left') ? ' selected' : ''; ?>>좌측</option>
              <option value="center"<?php echo (!isset($main_skin_config[$ph_key]) || $main_skin_config[$ph_key] === 'center') ? ' selected' : ''; ?>>중앙</option>
              <option value="right"<?php echo (isset($main_skin_config[$ph_key]) && $main_skin_config[$ph_key] === 'right') ? ' selected' : ''; ?>>우측</option>
            </select>
          </div>
          <div class="admin-field-row"><label>가로 미세조정 (px)</label><input type="number" name="parallax_<?php echo $pl_key; ?>_offset_x" value="<?php echo (int)(isset($main_skin_config[$ox_key]) ? $main_skin_config[$ox_key] : 0); ?>" style="width:80px;"></div>
          <div class="admin-field-row"><label>세로 미세조정 (px)</label><input type="number" name="parallax_<?php echo $pl_key; ?>_offset_y" value="<?php echo (int)(isset($main_skin_config[$oy_key]) ? $main_skin_config[$oy_key] : 0); ?>" style="width:80px;"></div>
          <?php } ?>

          <div class="admin-field-row"><label></label><button type="submit" class="win95-action-btn">패럴랙스 설정 저장</button></div>
        </form>
        <div id="config-parallax-msg" class="admin-msg" style="display:none;"></div>
      </div>

      <div class="admin-tab-pane" id="tab-window" style="display:none;">
        <form id="config-window-form">
          <input type="hidden" name="action" value="update_window">
          <input type="hidden" name="token" value="<?php echo main_skin_esc($main_skin_token); ?>">
          <h3 class="admin-section-title">레트로 창 설정</h3>
          <div class="admin-field-row"><label>최신글 타이틀</label><input type="text" name="win_title" value="<?php echo main_skin_esc($window_title); ?>"></div>
          <div class="admin-field-row"><label>배너 타이틀</label><input type="text" name="banner_title" value="<?php echo main_skin_esc($banner_title); ?>"></div>
          <div class="admin-field-row"><label>게시판 ID</label><input type="text" name="board_ids" value="<?php echo main_skin_esc($main_skin_config['latest_boards']); ?>" style="width:240px;" placeholder="free,gallery"></div>
          <div class="admin-field-row"><label>게시글 수</label><input type="number" name="limit" min="1" max="20" value="<?php echo (int)$main_skin_config['latest_rows']; ?>"></div>
          <div class="admin-field-row"><label></label><button type="submit" class="win95-action-btn">창 설정 저장</button></div>
        </form>
        <div id="config-window-msg" class="admin-msg" style="display:none;"></div>

        <h3 class="admin-section-title">배너 관리</h3>
        <form id="banner-add-form" enctype="multipart/form-data">
          <input type="hidden" name="action" value="add_banner">
          <input type="hidden" name="token" value="<?php echo main_skin_esc($main_skin_token); ?>">
          <div class="admin-field-row"><label>이미지 URL</label><input type="text" name="banner_url" style="width:320px;" placeholder="https://example.com/banner.gif"></div>
          <div class="admin-field-row"><label>업로드</label><input type="file" name="banner_file" accept="image/*"></div>
          <div class="admin-field-row"><label>링크</label><input type="text" name="banner_link" style="width:320px;"></div>
          <div class="admin-field-row"><label>alt</label><input type="text" name="banner_alt"></div>
          <div class="admin-field-row"><label>target / sort</label><select name="banner_target"><option value="_blank">_blank</option><option value="_self">_self</option></select> <input type="number" name="sort" value="0" style="width:60px;"></div>
          <div class="admin-field-row"><label>노출</label><label class="inline-check"><input type="checkbox" name="enabled" checked> 사용</label></div>
          <div class="admin-field-row"><label></label><button type="submit" class="win95-action-btn">배너 추가</button></div>
        </form>
        <div id="banner-add-msg" class="admin-msg" style="display:none;"></div>

        <div id="admin-banner-list">
          <?php if (empty($main_skin_banners)) { ?>
          <p class="win95-no-posts">등록된 배너가 없습니다.</p>
          <?php } else { foreach ($main_skin_banners as $index => $banner) { ?>
          <form class="admin-banner-item banner-edit-form" id="admin-banner-<?php echo (int)$index; ?>">
            <input type="hidden" name="action" value="update_banner">
            <input type="hidden" name="token" value="<?php echo main_skin_esc($main_skin_token); ?>">
            <input type="hidden" name="index" value="<?php echo (int)$index; ?>">
            <img src="<?php echo main_skin_esc($banner['image']); ?>" alt="<?php echo main_skin_esc($banner['alt']); ?>" class="admin-sticker-thumb">
            <div class="admin-item-fields">
              <div class="admin-inline-fields">
                <input type="text" name="banner_link" value="<?php echo main_skin_esc($banner['link']); ?>" placeholder="링크 URL">
                <input type="text" name="banner_alt" value="<?php echo main_skin_esc($banner['alt']); ?>" placeholder="alt 텍스트">
              </div>
              <div class="admin-inline-fields">
                <select name="banner_target"><option value="_blank"<?php echo $banner['target'] === '_blank' ? ' selected' : ''; ?>>_blank</option><option value="_self"<?php echo $banner['target'] === '_self' ? ' selected' : ''; ?>>_self</option></select>
                <input type="number" name="sort" value="<?php echo (int)$banner['sort']; ?>" placeholder="sort">
                <label class="inline-check"><input type="checkbox" name="enabled"<?php echo !empty($banner['enabled']) ? ' checked' : ''; ?>> 노출</label>
              </div>
            </div>
            <div class="admin-item-actions">
              <button type="submit" class="win95-action-btn">저장</button>
              <button type="button" class="win95-action-btn banner-del-btn" data-index="<?php echo (int)$index; ?>">삭제</button>
            </div>
          </form>
          <?php } } ?>
        </div>
      </div>
    </div>
  </div>
  </div>
  <?php } ?>
<!-- ★ script 블록을 최상단으로 이동: 어떤 조건과도 무관하게 항상 출력 -->
<script>
window.RETRO_SKIN_URL = '<?php echo addslashes(MAIN_SKIN_URL); ?>';
window.RETRO_IS_ADMIN = <?php echo $main_skin_is_admin ? 'true' : 'false'; ?>;
window.RETRO_TOKEN = '<?php echo addslashes($main_skin_token); ?>';
</script>

<script src="<?php echo main_skin_esc(MAIN_SKIN_URL); ?>/main.js"></script>
</div>
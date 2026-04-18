(function ($) {
  'use strict';
  var RELOAD_DELAY = 700;

  var SKIN_URL = (typeof window.RETRO_SKIN_URL !== 'undefined') ? window.RETRO_SKIN_URL : '';
  var IS_ADMIN = (typeof window.RETRO_IS_ADMIN !== 'undefined') ? window.RETRO_IS_ADMIN : false;
  var TOKEN    = (typeof window.RETRO_TOKEN    !== 'undefined') ? window.RETRO_TOKEN    : '';

  function ajaxPost(url, formData, onSuccess, onError) {
    $.ajax({
      url: url,
      type: 'POST',
      data: formData,
      dataType: 'json',
      contentType: false,
      processData: false,
      success: function (data) {
        if (data && data.ok) {
          onSuccess(data);
        } else {
          onError(data && data.error ? data.error : '요청 처리에 실패했습니다.');
        }
      },
      error: function (xhr) {
        onError('서버 오류: ' + xhr.status + ' ' + xhr.statusText);
      }
    });
  }

  function showMsg(selector, msg, ok) {
    $(selector).removeClass('success error').addClass(ok ? 'success' : 'error').text(msg).show();
  }

  function reloadSoon() {
    window.setTimeout(function () { window.location.reload(); }, RELOAD_DELAY);
  }

  /* ── 최신글/배너 flip 토글 ── */
  var flipper    = $('#win95-flipper');
  var titlebars  = $('[data-win95-toggle]');
  var flipEnabled = true;

  function syncFlipState(flipped) {
    titlebars.each(function () {
      var isBanner = $(this).data('win95-toggle') === 'banner';
      $(this).attr('aria-pressed', flipped === isBanner ? 'true' : 'false');
    });
  }

  if (flipper.length && titlebars.length) {
    syncFlipState(false);
  }

  /* ── 윈도우 드래그 (모바일 밀림 수정) ── */
  var winEl = $('#retro-win95-window');
  if (winEl.length) {
    var winDragging  = false;
    var winMoved     = false;
    var winStartX    = 0, winStartY = 0;
    var winOriginLeft = 0, winOriginTop = 0;
    var DRAG_THRESHOLD = 5;

    winEl.find('.win95-titlebar').on('mousedown touchstart', function (e) {
      var isTouch = (e.type === 'touchstart');
      var clientX = isTouch ? e.originalEvent.touches[0].clientX : e.clientX;
      var clientY = isTouch ? e.originalEvent.touches[0].clientY : e.clientY;
      if (!isTouch && e.which !== 1) return;

      winDragging = true;
      winMoved    = false;
      winStartX   = clientX;
      winStartY   = clientY;

      var rect       = winEl[0].getBoundingClientRect();
      var parentRect = winEl.offsetParent()[0].getBoundingClientRect();
      winOriginLeft  = rect.left - parentRect.left;
      winOriginTop   = rect.top  - parentRect.top;
      /* NOTE: do NOT apply left/right/transform here — wait until drag threshold is exceeded.
         This prevents mobile position drift caused by setting left px while translateX(50%)
         is still active, which would double the horizontal offset on each touch/mousedown. */
      if (!isTouch) { e.preventDefault(); }
    });

    $(document).on('mousemove.windrag touchmove.windrag', function (e) {
      if (!winDragging) return;
      var isTouch = (e.type === 'touchmove');
      var clientX = isTouch ? e.originalEvent.touches[0].clientX : e.clientX;
      var clientY = isTouch ? e.originalEvent.touches[0].clientY : e.clientY;
      var dx = clientX - winStartX;
      var dy = clientY - winStartY;
      if (!winMoved && Math.abs(dx) < DRAG_THRESHOLD && Math.abs(dy) < DRAG_THRESHOLD) return;
      if (!winMoved) {
        /* First real drag movement: switch from CSS-centered positioning to absolute px.
           Apply transform:none BEFORE setting left to avoid the 50% translation doubling. */
        winEl.css({ transform: 'none', left: winOriginLeft + 'px', top: winOriginTop + 'px', right: 'auto', position: 'absolute' });
        winMoved = true;
      }
      winEl.addClass('is-win-dragging');
      winEl.css({
        left: (winOriginLeft + dx) + 'px',
        top:  (winOriginTop  + dy) + 'px'
      });
      if (isTouch) { e.preventDefault(); }
    });

    $(document).on('mouseup.windrag touchend.windrag', function () {
      if (!winDragging) return;
      winDragging = false;
      winEl.removeClass('is-win-dragging');
      if (!winMoved && flipper.length && flipEnabled) {
        /* Tap without drag: toggle flip, restore the CSS-centered position */
        flipper.toggleClass('flipped');
        syncFlipState(flipper.hasClass('flipped'));
      }
    });

    /* Keyboard: titlebar acts as a button */
    titlebars.on('keydown', function (e) {
      if (e.which !== 13 && e.which !== 32) return;
      e.preventDefault();
      if (flipper.length) {
        flipper.toggleClass('flipped');
        syncFlipState(flipper.hasClass('flipped'));
      }
    });
  } else if (flipper.length && titlebars.length) {
    titlebars.on('click keydown', function (e) {
      if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) return;
      if (e.type === 'keydown') { e.preventDefault(); }
      flipper.toggleClass('flipped');
      syncFlipState(flipper.hasClass('flipped'));
    });
  }

  /* ── 관리자 탭 전환 ── */
  $(document).on('click', '.admin-tab', function () {
    $('.admin-tab').removeClass('active');
    $(this).addClass('active');
    $('.admin-tab-pane').hide();
    $('#' + $(this).data('tab')).show();
  });

  /* ── 패럴랙스 마우스 효과 ── */
  var parallaxLayers = $('.parallax-layer');
  if (parallaxLayers.length) {
    var PARALLAX_FACTORS = {
      'parallax-fg-layer': 0.04,
      'parallax-ng-layer': 0.025,
      'parallax-bg-layer': 0.01
    };

    function updateParallax(clientX, clientY) {
      var centerX = window.innerWidth / 2;
      var centerY = window.innerHeight / 2;
      var deltaX = clientX - centerX;
      var deltaY = clientY - centerY;

      parallaxLayers.each(function () {
        var layer = $(this);
        var img = layer.find('img');
        if (!img.length) return;

        var factor = PARALLAX_FACTORS[this.id] || 0.02;
        var moveX = -deltaX * factor;
        var moveY = -deltaY * factor;

        var posV = layer.attr('data-pos-v') || 'center';
        var posH = layer.attr('data-pos-h') || 'center';
        var offsetX = parseInt(layer.attr('data-offset-x') || 0, 10);
        var offsetY = parseInt(layer.attr('data-offset-y') || 0, 10);

        var parts = [];
        if (posH === 'center') parts.push('translateX(-50%)');
        if (posV === 'center') parts.push('translateY(-50%)');
        parts.push('translate(' + (offsetX + moveX) + 'px,' + (offsetY + moveY) + 'px)');

        img.css('transform', parts.join(' '));
      });
    }

    $(document).on('mousemove.parallax', function (e) {
      updateParallax(e.clientX, e.clientY);
    });
  }

  /* ── 이하 관리자 전용 ── */
  if (!IS_ADMIN) {
    return;
  }

  var adminModal   = $('#retro-admin-modal');
  var adminOpenBtn = $('#retro-admin-open-btn');
  var adminCloseBtn = $('#admin-panel-close-btn');
  var adminPanel   = $('#retro-admin-panel');

  function setAdminModal(open) {
    if (!adminModal.length || !adminOpenBtn.length) return;
    adminModal.prop('hidden', !open);
    adminOpenBtn.attr('aria-expanded', open ? 'true' : 'false');
    if (open) {
      adminPanel.trigger('focus');
    } else {
      adminOpenBtn.trigger('focus');
    }
  }

  adminOpenBtn.on('click', function () { setAdminModal(true); });
  adminCloseBtn.on('click', function () { setAdminModal(false); });
  adminModal.on('click', '[data-admin-close]', function () { setAdminModal(false); });

  adminPanel.on('keydown', function (e) {
    if (e.key === 'Escape' && adminModal.length && !adminModal.prop('hidden')) {
      setAdminModal(false);
    }
  });

  /* ── 스티커 추가 폼 src_type 토글 ── */
  $('#sticker-add-form').on('change', 'input[name="src_type"]', function () {
    $('#sticker-url-rows').toggle(this.value === 'url');
    $('#sticker-file-row').toggle(this.value === 'upload');
  });

  /* ── 에셋 추가 폼 src_type 토글 ── */
  $('#asset-add-form').on('change', 'input[name="src_type"]', function () {
    $('#asset-url-row').toggle(this.value === 'url');
    $('#asset-file-row').toggle(this.value === 'upload');
  });

  /* ══════════════════════════════════════════════
     스티커 편집 모드
  ══════════════════════════════════════════════ */
  var stickerEditMode = false;
  var stickerEditBtn  = $('#retro-sticker-edit-btn');

  function setStickerEditMode(active) {
    stickerEditMode = active;
    if (active) {
      stickerEditBtn.addClass('active').text('✏️ 편집 중...');
      $('.admin-sticker').addClass('sticker-edit-mode');
    } else {
      stickerEditBtn.removeClass('active').text('✏️ 스티커 편집');
      $('.admin-sticker').removeClass('sticker-edit-mode');
    }
  }

  if (stickerEditBtn.length) {
    stickerEditBtn.on('click', function () {
      setStickerEditMode(!stickerEditMode);
    });
  }

  /* ── transform 적용 헬퍼 ── */
  function applyStickerTransform(el, angleDeg) {
    var leftVal = el[0].style.left || '';
    var hasPercent = leftVal.indexOf('%') !== -1;
    var tStr = (hasPercent ? 'translate(-50%,-50%) ' : '') + 'rotate(' + angleDeg + 'deg)';
    el.css('transform', tStr);
  }

  /* ══════════════════════════════════════════════
     스티커 드래그 이동 (편집 모드에서만)
  ══════════════════════════════════════════════ */
  var dragging   = null;
  var dragStartX = 0, dragStartY = 0;
  var originLeft = 0, originTop  = 0;

  $(document).on('mousedown', '.admin-sticker.sticker-edit-mode', function (e) {
    if ($(e.target).closest('.sticker-handles').length) return;
    dragging   = $(this);
    dragStartX = e.clientX;
    dragStartY = e.clientY;

    /* Convert current (possibly %-based) position to absolute px */
    var rect       = dragging[0].getBoundingClientRect();
    var parentRect = dragging.offsetParent()[0].getBoundingClientRect();
    originLeft = rect.left - parentRect.left;
    originTop  = rect.top  - parentRect.top;

    var rotate = parseFloat(dragging.attr('data-rotate') || 0);
    dragging.css({ left: originLeft + 'px', top: originTop + 'px', transform: 'rotate(' + rotate + 'deg)' });
    dragging.addClass('is-dragging');
    e.preventDefault();
  });

  $(document).on('mousemove.stickerdrag', function (e) {
    if (!dragging) return;
    var newLeft = originLeft + (e.clientX - dragStartX);
    var newTop  = originTop  + (e.clientY - dragStartY);
    dragging.css({ left: newLeft + 'px', top: newTop + 'px' });
  });

  $(document).on('mouseup.stickerdrag', function () {
    if (!dragging) return;
    var stickerId = dragging.data('id');
    var newLeft   = parseInt(dragging.css('left'), 10) || 0;
    var newTop    = parseInt(dragging.css('top'),  10) || 0;
    dragging.removeClass('is-dragging');

    var fd = new FormData();
    fd.append('action', 'move_sticker');
    fd.append('token',  TOKEN);
    fd.append('id',     stickerId);
    fd.append('left',   newLeft + 'px');
    fd.append('top',    newTop  + 'px');
    ajaxPost(SKIN_URL + '/sticker_update.php', fd, function () {}, function () {});
    dragging = null;
  });

  /* ══════════════════════════════════════════════
     스티커 크기 조절 핸들 (우하단)
  ══════════════════════════════════════════════ */
  var resizing       = null;
  var resizeStartX   = 0, resizeStartY = 0;
  var resizeOriginW  = 0, resizeOriginH = 0;
  var MIN_STICKER_SIZE = 20;

  $(document).on('mousedown', '.sticker-resize-handle', function (e) {
    e.stopPropagation();
    e.preventDefault();
    resizing      = $(this).closest('.admin-sticker');
    resizeStartX  = e.clientX;
    resizeStartY  = e.clientY;
    resizeOriginW = resizing.outerWidth();
    resizeOriginH = resizing.outerHeight();

    /* Convert to px before resizing (may be % positioned) */
    var rect       = resizing[0].getBoundingClientRect();
    var parentRect = resizing.offsetParent()[0].getBoundingClientRect();
    var rotate     = parseFloat(resizing.attr('data-rotate') || 0);
    resizing.css({ left: (rect.left - parentRect.left) + 'px', top: (rect.top - parentRect.top) + 'px', transform: 'rotate(' + rotate + 'deg)' });
  });

  $(document).on('mousemove.stickerresize', function (e) {
    if (!resizing) return;
    var newW = Math.max(MIN_STICKER_SIZE, resizeOriginW + (e.clientX - resizeStartX));
    var newH = Math.max(MIN_STICKER_SIZE, resizeOriginH + (e.clientY - resizeStartY));
    resizing.css({ width: newW + 'px', height: newH + 'px' });
  });

  $(document).on('mouseup.stickerresize', function () {
    if (!resizing) return;
    var stickerId = resizing.data('id');
    var newW = resizing.outerWidth();
    var newH = resizing.outerHeight();
    var newL = parseInt(resizing.css('left'), 10) || 0;
    var newT = parseInt(resizing.css('top'),  10) || 0;

    var fd = new FormData();
    fd.append('action',  'update_sticker');
    fd.append('token',   TOKEN);
    fd.append('id',      stickerId);
    fd.append('width',   newW + 'px');
    fd.append('height',  newH + 'px');
    fd.append('left',    newL + 'px');
    fd.append('top',     newT + 'px');
    ajaxPost(SKIN_URL + '/sticker_update.php', fd, function () {}, function () {});
    resizing = null;
  });

  /* ══════════════════════════════════════════════
     스티커 회전 핸들 (상단 중앙)
  ══════════════════════════════════════════════ */
  var rotating          = null;
  var rotateCenterX     = 0, rotateCenterY = 0;
  var rotateStartMouse  = 0;
  var rotateBaseAngle   = 0;
  var rotateLiveAngle   = 0;

  $(document).on('mousedown', '.sticker-rotate-handle', function (e) {
    e.stopPropagation();
    e.preventDefault();
    rotating = $(this).closest('.admin-sticker');

    /* Convert to px before rotating */
    var rect       = rotating[0].getBoundingClientRect();
    var parentRect = rotating.offsetParent()[0].getBoundingClientRect();
    var curRotate  = parseFloat(rotating.attr('data-rotate') || 0);
    rotating.css({ left: (rect.left - parentRect.left) + 'px', top: (rect.top - parentRect.top) + 'px', transform: 'rotate(' + curRotate + 'deg)' });

    /* Reread after CSS change */
    var rect2      = rotating[0].getBoundingClientRect();
    rotateCenterX  = rect2.left + rect2.width  / 2;
    rotateCenterY  = rect2.top  + rect2.height / 2;
    rotateStartMouse = Math.atan2(e.clientY - rotateCenterY, e.clientX - rotateCenterX) * 180 / Math.PI;
    rotateBaseAngle  = curRotate;
    rotateLiveAngle  = curRotate;
  });

  $(document).on('mousemove.stickerrotate', function (e) {
    if (!rotating) return;
    var mouseAngle  = Math.atan2(e.clientY - rotateCenterY, e.clientX - rotateCenterX) * 180 / Math.PI;
    rotateLiveAngle = rotateBaseAngle + (mouseAngle - rotateStartMouse);
    rotating.css('transform', 'rotate(' + rotateLiveAngle + 'deg)');
  });

  $(document).on('mouseup.stickerrotate', function () {
    if (!rotating) return;
    var stickerId  = rotating.data('id');
    var finalAngle = Math.round(rotateLiveAngle * 100) / 100;
    rotating.attr('data-rotate', finalAngle);

    var newL = parseInt(rotating.css('left'), 10) || 0;
    var newT = parseInt(rotating.css('top'),  10) || 0;

    var fd = new FormData();
    fd.append('action',  'update_sticker');
    fd.append('token',   TOKEN);
    fd.append('id',      stickerId);
    fd.append('rotate',  finalAngle);
    fd.append('left',    newL + 'px');
    fd.append('top',     newT + 'px');
    ajaxPost(SKIN_URL + '/sticker_update.php', fd, function () {}, function () {});
    rotating = null;
  });

  /* ══════════════════════════════════════════════
     Z-index 증가/감소 버튼
  ══════════════════════════════════════════════ */
  var MIN_Z = 1;
  var MAX_Z = 9999;

  function saveStickerZIndex(id, z) {
    var fd = new FormData();
    fd.append('action',  'update_sticker');
    fd.append('token',   TOKEN);
    fd.append('id',      id);
    fd.append('z_index', z);
    ajaxPost(SKIN_URL + '/sticker_update.php', fd, function () {}, function () {});
  }

  $(document).on('click', '.sticker-zup-btn', function (e) {
    e.stopPropagation();
    var sticker = $(this).closest('.admin-sticker');
    var z = parseInt(sticker.attr('data-z-index') || sticker.css('z-index') || MIN_Z, 10);
    z = Math.min(MAX_Z, z + 1);
    sticker.css('z-index', z).attr('data-z-index', z);
    saveStickerZIndex(sticker.data('id'), z);
  });

  $(document).on('click', '.sticker-zdown-btn', function (e) {
    e.stopPropagation();
    var sticker = $(this).closest('.admin-sticker');
    var z = parseInt(sticker.attr('data-z-index') || sticker.css('z-index') || MIN_Z, 10);
    z = Math.max(MIN_Z, z - 1);
    sticker.css('z-index', z).attr('data-z-index', z);
    saveStickerZIndex(sticker.data('id'), z);
  });

  /* ══════════════════════════════════════════════
     스티커 삭제 버튼
  ══════════════════════════════════════════════ */
  $(document).on('click', '.sticker-del-btn, .admin-sticker-delete', function (e) {
    e.stopPropagation();
    var stickerId = $(this).data('id');
    if (!stickerId || !window.confirm('이 스티커를 삭제하시겠습니까?')) return;
    var fd = new FormData();
    fd.append('action', 'delete_sticker');
    fd.append('token',  TOKEN);
    fd.append('id',     stickerId);
    ajaxPost(SKIN_URL + '/sticker_update.php', fd, function () {
      $('#sticker-' + stickerId).remove();
      $('#admin-item-' + stickerId).remove();
      showMsg('#sticker-edit-msg', '스티커가 삭제되었습니다.', true);
    }, function (msg) { showMsg('#sticker-edit-msg', msg, false); });
  });

  /* ══════════════════════════════════════════════
     스티커 추가 폼 제출
  ══════════════════════════════════════════════ */
  $('#sticker-add-form').on('submit', function (e) {
    e.preventDefault();
    ajaxPost(SKIN_URL + '/sticker_update.php', new FormData(this), function () {
      showMsg('#sticker-add-msg', '스티커가 추가되었습니다. 화면 중앙에 배치됩니다.', true);
      reloadSoon();
    }, function (msg) { showMsg('#sticker-add-msg', msg, false); });
  });

  /* ── 등록 스티커 목록 저장 (alt, enabled) ── */
  $('#admin-sticker-list').on('submit', '.sticker-edit-form', function (e) {
    e.preventDefault();
    ajaxPost(SKIN_URL + '/sticker_update.php', new FormData(this), function () {
      showMsg('#sticker-edit-msg', '스티커가 저장되었습니다.', true);
    }, function (msg) { showMsg('#sticker-edit-msg', msg, false); });
  });

  /* ══════════════════════════════════════════════
     에셋 관리
  ══════════════════════════════════════════════ */
  $('#asset-add-form').on('submit', function (e) {
    e.preventDefault();
    ajaxPost(SKIN_URL + '/sticker_update.php', new FormData(this), function () {
      showMsg('#asset-add-msg', '에셋이 저장되었습니다.', true);
      reloadSoon();
    }, function (msg) { showMsg('#asset-add-msg', msg, false); });
  });

  $(document).on('click', '.asset-place-btn', function () {
    var assetId = $(this).data('id');
    if (!assetId) return;
    var fd = new FormData();
    fd.append('action', 'place_asset');
    fd.append('token',  TOKEN);
    fd.append('id',     assetId);
    ajaxPost(SKIN_URL + '/sticker_update.php', fd, function () {
      showMsg('#asset-msg', '스티커로 배치되었습니다. 편집 모드에서 위치를 조정하세요.', true);
      reloadSoon();
    }, function (msg) { showMsg('#asset-msg', msg, false); });
  });

  $(document).on('click', '.asset-del-btn', function () {
    var assetId = $(this).data('id');
    if (!assetId || !window.confirm('이 에셋을 삭제하시겠습니까?')) return;
    var fd = new FormData();
    fd.append('action', 'delete_asset');
    fd.append('token',  TOKEN);
    fd.append('id',     assetId);
    ajaxPost(SKIN_URL + '/sticker_update.php', fd, function () {
      $('#admin-asset-' + assetId).remove();
      showMsg('#asset-msg', '에셋이 삭제되었습니다.', true);
    }, function (msg) { showMsg('#asset-msg', msg, false); });
  });

  /* ── 폴라로이드 설정 ── */
  $('#config-images-form').on('submit', function (e) {
    e.preventDefault();
    ajaxPost(SKIN_URL + '/config_update.php', new FormData(this), function () {
      showMsg('#config-images-msg', '이미지 설정이 저장되었습니다.', true);
      reloadSoon();
    }, function (msg) { showMsg('#config-images-msg', msg, false); });
  });

  /* ── 창 설정 ── */
  $('#config-window-form').on('submit', function (e) {
    e.preventDefault();
    ajaxPost(SKIN_URL + '/config_update.php', new FormData(this), function () {
      showMsg('#config-window-msg', '창 설정이 저장되었습니다.', true);
      reloadSoon();
    }, function (msg) { showMsg('#config-window-msg', msg, false); });
  });

  /* ── 배너 CRUD ── */
  $('#banner-add-form').on('submit', function (e) {
    e.preventDefault();
    ajaxPost(SKIN_URL + '/banner_update.php', new FormData(this), function () {
      showMsg('#banner-add-msg', '배너가 추가되었습니다.', true);
      reloadSoon();
    }, function (msg) { showMsg('#banner-add-msg', msg, false); });
  });

  $('#admin-banner-list').on('submit', '.banner-edit-form', function (e) {
    e.preventDefault();
    ajaxPost(SKIN_URL + '/banner_update.php', new FormData(this), function () {
      showMsg('#banner-add-msg', '배너가 저장되었습니다.', true);
      reloadSoon();
    }, function (msg) { showMsg('#banner-add-msg', msg, false); });
  });

  $('#admin-banner-list').on('click', '.banner-del-btn', function () {
    if (!window.confirm('이 배너를 삭제하시겠습니까?')) return;
    var fd = new FormData();
    fd.append('action', 'delete_banner');
    fd.append('token',  TOKEN);
    fd.append('index',  $(this).data('index'));
    ajaxPost(SKIN_URL + '/banner_update.php', fd, function () {
      showMsg('#banner-add-msg', '배너가 삭제되었습니다.', true);
      reloadSoon();
    }, function (msg) { showMsg('#banner-add-msg', msg, false); });
  });

  /* ── 패럴랙스 설정 ── */
  $('#config-parallax-form').on('submit', function (e) {
    e.preventDefault();
    ajaxPost(SKIN_URL + '/config_update.php', new FormData(this), function () {
      showMsg('#config-parallax-msg', '패럴랙스 설정이 저장되었습니다.', true);
      reloadSoon();
    }, function (msg) { showMsg('#config-parallax-msg', msg, false); });
  });

  $(document).on('click', '.parallax-del-btn', function () {
    var layer = $(this).data('layer');
    if (!layer || !window.confirm('이 레이어 이미지를 삭제하시겠습니까?')) return;
    var fd = new FormData();
    fd.append('action', 'delete_parallax_image');
    fd.append('token', TOKEN);
    fd.append('layer', layer);
    ajaxPost(SKIN_URL + '/config_update.php', fd, function () {
      showMsg('#config-parallax-msg', '이미지가 삭제되었습니다.', true);
      reloadSoon();
    }, function (msg) { showMsg('#config-parallax-msg', msg, false); });
  });

}(jQuery));

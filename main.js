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
  var flipper = $('#win95-flipper');
  var titlebars = $('[data-win95-toggle]');
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

  /* ── 윈도우 드래그 (누구나) ── */
  var winEl = $('#retro-win95-window');
  if (winEl.length) {
    var winDragging = false;
    var winMoved = false;
    var winStartX = 0, winStartY = 0;
    var winOriginLeft = 0, winOriginTop = 0;
    var DRAG_THRESHOLD = 5;

    winEl.find('.win95-titlebar').on('mousedown', function (e) {
      if (e.which !== 1) return;
      winDragging = true;
      winMoved = false;
      winStartX = e.clientX;
      winStartY = e.clientY;

      var rect = winEl[0].getBoundingClientRect();
      var parentRect = winEl.offsetParent()[0].getBoundingClientRect();
      winOriginLeft = rect.left - parentRect.left;
      winOriginTop = rect.top - parentRect.top;

      winEl.css({ left: winOriginLeft + 'px', top: winOriginTop + 'px', right: 'auto', position: 'absolute' });
      e.preventDefault();
    });

    $(document).on('mousemove.windrag', function (e) {
      if (!winDragging) return;
      var dx = e.clientX - winStartX;
      var dy = e.clientY - winStartY;
      if (!winMoved && Math.abs(dx) < DRAG_THRESHOLD && Math.abs(dy) < DRAG_THRESHOLD) return;
      winMoved = true;
      winEl.addClass('is-win-dragging');
      winEl.css({
        left: (winOriginLeft + dx) + 'px',
        top: (winOriginTop + dy) + 'px'
      });
    });

    $(document).on('mouseup.windrag', function () {
      if (!winDragging) return;
      winDragging = false;
      winEl.removeClass('is-win-dragging');
      if (!winMoved && flipper.length && flipEnabled) {
        flipper.toggleClass('flipped');
        syncFlipState(flipper.hasClass('flipped'));
      }
    });

    /* titlebar 클릭은 드래그에서 처리하므로 키보드만 남김 */
    titlebars.on('keydown', function (e) {
      if (e.which !== 13 && e.which !== 32) return;
      e.preventDefault();
      if (flipper.length) {
        flipper.toggleClass('flipped');
        syncFlipState(flipper.hasClass('flipped'));
      }
    });
  } else if (flipper.length && titlebars.length) {
    /* 윈도우 요소가 없으면 기존 flip 토글 유지 */
    titlebars.on('click keydown', function (e) {
      if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) return;
      if (e.type === 'keydown') e.preventDefault();
      flipper.toggleClass('flipped');
      syncFlipState(flipper.hasClass('flipped'));
    });
  }

  /* ── 관리자 탭 전환 (누구나 접근 가능한 UI) ── */
  $(document).on('click', '.admin-tab', function () {
    $('.admin-tab').removeClass('active');
    $(this).addClass('active');
    $('.admin-tab-pane').hide();
    $('#' + $(this).data('tab')).show();
  });

  /* ── 이하 관리자 전용 ── */
  if (!IS_ADMIN) {
    return;
  }

  var adminModal = $('#retro-admin-modal');
  var adminOpenBtn = $('#retro-admin-open-btn');
  var adminCloseBtn = $('#admin-panel-close-btn');
  var adminPanel = $('#retro-admin-panel');

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

  $('input[name="src_type"]').on('change', function () {
    $('#sticker-url-row').toggle(this.value === 'url');
    $('#sticker-file-row').toggle(this.value === 'upload');
  });

  /* ── 스티커 드래그 (관리자) ── */
  var dragging = null;
  var dragStartX = 0, dragStartY = 0;
  var originLeft = 0, originTop = 0;

  $(document).on('mousedown', '.admin-sticker', function (e) {
    if ($(e.target).hasClass('sticker-del-btn')) return;
    dragging = $(this);
    dragStartX = e.clientX;
    dragStartY = e.clientY;
    originLeft = parseInt(dragging.attr('data-left'), 10) || 0;
    originTop = parseInt(dragging.attr('data-top'), 10) || 0;
    dragging.addClass('is-dragging');
    e.preventDefault();
  });

  $(document).on('mousemove', function (e) {
    if (!dragging) return;
    var newLeft = originLeft + (e.clientX - dragStartX);
    var newTop = originTop + (e.clientY - dragStartY);
    dragging.css({ left: newLeft + 'px', top: newTop + 'px' });
  });

  $(document).on('mouseup', function () {
    if (!dragging) return;
    var stickerId = dragging.data('id');
    var newLeft = parseInt(dragging.css('left'), 10) || 0;
    var newTop = parseInt(dragging.css('top'), 10) || 0;
    dragging.attr('data-left', newLeft).attr('data-top', newTop).removeClass('is-dragging');

    var editForm = $('#admin-item-' + stickerId);
    editForm.find('input[name="left"]').val(newLeft + 'px');
    editForm.find('input[name="top"]').val(newTop + 'px');

    var fd = new FormData();
    fd.append('action', 'move_sticker');
    fd.append('token', TOKEN);
    fd.append('id', stickerId);
    fd.append('left', newLeft + 'px');
    fd.append('top', newTop + 'px');

    ajaxPost(SKIN_URL + '/sticker_update.php', fd, function () {}, function () {});
    dragging = null;
  });

  /* ── 스티커 CRUD ── */
  $('#sticker-add-form').on('submit', function (e) {
    e.preventDefault();
    ajaxPost(SKIN_URL + '/sticker_update.php', new FormData(this), function () {
      showMsg('#sticker-add-msg', '스티커가 추가되었습니다.', true);
      reloadSoon();
    }, function (msg) { showMsg('#sticker-add-msg', msg, false); });
  });

  $('#admin-sticker-list').on('submit', '.sticker-edit-form', function (e) {
    e.preventDefault();
    ajaxPost(SKIN_URL + '/sticker_update.php', new FormData(this), function () {
      showMsg('#sticker-edit-msg', '스티커가 저장되었습니다.', true);
      reloadSoon();
    }, function (msg) { showMsg('#sticker-edit-msg', msg, false); });
  });

  $(document).on('click', '.sticker-del-btn, .admin-sticker-delete', function () {
    var stickerId = $(this).data('id');
    if (!stickerId || !window.confirm('이 스티커를 삭제하시겠습니까?')) return;
    var fd = new FormData();
    fd.append('action', 'delete_sticker');
    fd.append('token', TOKEN);
    fd.append('id', stickerId);
    ajaxPost(SKIN_URL + '/sticker_update.php', fd, function () {
      $('#sticker-' + stickerId).remove();
      $('#admin-item-' + stickerId).remove();
      showMsg('#sticker-edit-msg', '스티커가 삭제되었습니다.', true);
    }, function (msg) { showMsg('#sticker-edit-msg', msg, false); });
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
    fd.append('token', TOKEN);
    fd.append('index', $(this).data('index'));
    ajaxPost(SKIN_URL + '/banner_update.php', fd, function () {
      showMsg('#banner-add-msg', '배너가 삭제되었습니다.', true);
      reloadSoon();
    }, function (msg) { showMsg('#banner-add-msg', msg, false); });
  });

}(jQuery));
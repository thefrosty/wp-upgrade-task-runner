/* global jQuery, wpUpgradeTaskRunnerDialog */
(function ($) {
  'use strict';

  $('[id^="upgrade-task-dialog"]').dialog({
    title: wpUpgradeTaskRunnerDialog.i18n.title.replace(/&amp;/g, '&'),
    dialogClass: 'wp-dialog',
    autoOpen: false,
    draggable: false,
    width: 'auto',
    modal: true,
    resizable: false,
    closeOnEscape: true,
    position: {
      my: 'center',
      at: 'center',
      of: window
    },
    open: function () {
      $('.ui-widget-overlay').bind('click', function () {
        $('[id^="upgrade-task-dialog"]').dialog('close');
      });
    }
  });

  $('a.open-upgrade-task-dialog').on('click', function (e) {
    e.preventDefault();
    $($(this).data('id')).dialog('open');
  });
})(jQuery);

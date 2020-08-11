/* global jQuery, wpUpgradeTaskRunner */
(function ($) {
  'use strict'

  const component = {
    anchor: {},
    settings: {
      currentUserId: 0,
      nonceKeyName: ''
    }
  }

  if (typeof wpUpgradeTaskRunner !== 'undefined') {
    $.extend(component.settings, wpUpgradeTaskRunner)
  }

  /**
   * Init component.
   */
  component.init = function () {
    $(function () {
      $(document).on('click', 'a[class="wp-upgrade-task-runner-item"]', function (e) {
        component.submitTaskRunner($(this))
        e.preventDefault()
        return false
      })
    })
  }

  /**
   * Submit the task via AJAX.
   * @param {object} element
   */
  component.submitTaskRunner = function (element) {
    let data

    data = component.setData(component.settings.nonceKeyName, element.data('nonce'), ',')
    data += component.setData('action', element.data('action'), ',')
    data += component.setData('item', element.data('item'), ',')
    data += component.setData('userId', component.settings.currentUserId)

    $.ajax({
      type: 'POST',
      data: JSON.parse('{' + data + '}'),
      dataType: 'json',
      url: window.ajaxurl,
      beforeSend: function () {
        component.toggleIcon(element)
      }
    }).done(function (response) {
      if (response.success) {
        element.attr('href', 'javascript:void(0)').attr('data-nonce', '')
        const el = element.closest('tr').find('td.executed span')
        if (typeof response.data.event === 'object') {
          element.text('Scheduled')
          el.attr('title', 'Task successfully scheduled')
        }
        setTimeout(function () {
          component.toggleIcon(element, true)
        }, 5000)
      } else {
        element.after('&nbsp;&nbsp;<time>' + response.error + '</time>')
      }
    }).fail(function () {
      throw new Error('An unknown error occurred.')
    })
  }

  /**
   * Toggle the icon class.
   * @param {object} element
   * @param {boolean} completed
   */
  component.toggleIcon = function (element, completed = false) {
    const el = element.closest('tr').find('td.executed span')
    if (completed) {
      el.removeClass('spin')
      el.toggleClass('dashicons-no').toggleClass('dashicons-clock')
    } else {
      el.addClass('spin')
    }
    el.toggleClass('dashicons-update').toggleClass('dashicons-no');
  };

  /**
   * Helper to get the correct JSON format for the key/value pair.
   * @param {string} key
   * @param {string} value
   * @param {string} delimiter
   * @return {string}
   */
  component.setData = function (key, value, delimiter = '') {
    return '"' + key + '":"' + value + '"' + delimiter
  }

  $(document).ready(component.init())

})(jQuery)

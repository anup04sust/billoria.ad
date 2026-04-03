/**
 * @file
 * JavaScript for Send Notification form.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.notificationSendForm = {
    attach: function (context, settings) {
      // Auto-update preview when fields change
      var $form = $('#billoria-notifications-send-form', context);
      
      if ($form.length) {
        var updatePreview = function() {
          var title = $('input[name="content_section[title]"]', $form).val() || 'Notification Title';
          var message = $('textarea[name="content_section[message]"]', $form).val() || 'Notification message';
          var type = $('select[name="content_section[type]"]', $form).val() || 'announcement';
          var priority = $('select[name="content_section[priority]"]', $form).val() || 'normal';
          
          var icons = {
            'low': '📋',
            'normal': '🔔',
            'high': '⚠️',
            'urgent': '🚨'
          };
          
          var icon = icons[priority] || '🔔';
          
          var previewHtml = 
            '<div class="preview-label">Preview (' + type.charAt(0).toUpperCase() + type.slice(1) + ' - ' + priority.charAt(0).toUpperCase() + priority.slice(1) + ')</div>' +
            '<div class="preview-icon">' + icon + '</div>' +
            '<div class="preview-title"><strong>' + $('<div>').text(title).html() + '</strong></div>' +
            '<div class="preview-message">' + $('<div>').text(message).html().replace(/\n/g, '<br>') + '</div>';
          
          $('#notification-preview')
            .html(previewHtml)
            .removeClass('notification-preview-low notification-preview-normal notification-preview-high notification-preview-urgent')
            .addClass('notification-preview-' + priority);
        };
        
        // Debounce function
        var debounceTimer;
        var debouncedUpdate = function() {
          clearTimeout(debounceTimer);
          debounceTimer = setTimeout(updatePreview, 300);
        };
        
        // Attach change listeners
        $('input[name="content_section[title]"]', $form).once('preview-update').on('input', debouncedUpdate);
        $('textarea[name="content_section[message]"]', $form).once('preview-update').on('input', debouncedUpdate);
        $('select[name="content_section[type]"]', $form).once('preview-update').on('change', debouncedUpdate);
        $('select[name="content_section[priority]"]', $form).once('preview-update').on('change', debouncedUpdate);
        
        // Initial preview
        updatePreview();
      }
      
      // Confirmation for sending to all users
      $('input[name="recipient_section[recipient_type]"][value="all"]', context).once('send-all-confirm').on('change', function() {
        if (this.checked) {
          var confirmed = confirm(Drupal.t('You are about to send a notification to ALL active users. Are you sure?'));
          if (!confirmed) {
            $('input[name="recipient_section[recipient_type]"][value="user"]').prop('checked', true).trigger('change');
          }
        }
      });
    }
  };

})(jQuery, Drupal);

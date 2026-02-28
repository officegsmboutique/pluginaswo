/* global aswoAdmin */
(function ($) {
    'use strict';

    $('#aswo-test-connection').on('click', function () {
        var $btn    = $(this).prop('disabled', true).text(aswoAdmin.testing);
        var $result = $('#aswo-test-result').text('').css('color', '');

        $.post(aswoAdmin.ajaxUrl, {
            action: 'aswo_test_connection',
            nonce:  aswoAdmin.nonce
        })
        .done(function (res) {
            if (res.success) {
                $result.text(aswoAdmin.success).css('color', '#16a34a');
            } else {
                var msg = (res.data && res.data.message) ? res.data.message : 'Error';
                $result.text(aswoAdmin.error + msg).css('color', '#dc2626');
            }
        })
        .fail(function () {
            $result.text(aswoAdmin.error + 'Request failed.').css('color', '#dc2626');
        })
        .always(function () {
            $btn.prop('disabled', false).text('Test Connection');
        });
    });

}(jQuery));

+ function($) {
    'use strict';

    $(".accept").click(function(){
        var closestCard     = $(this).closest(".card");
        var imageUuid       = closestCard.attr('id');
        var ewayId          = closestCard.find('#' + imageUuid + '_eway_id').val() || 0;
        var type            = closestCard.find('#' + imageUuid + '_type').val();
        var verifyToken     = closestCard.find('#' + imageUuid + '_verify_token').val() || 0;
        var imageName       = closestCard.find('#' + imageUuid + '_name').val() || 0;
        var transportType   = closestCard.find('#' + imageUuid + '_list option:selected').val() || 0;
        var formAction      = closestCard.find('.card-form').attr('action');

        if (ewayId !== 0) {
            sendImage(imageUuid, ewayId, type, verifyToken, imageName, formAction, transportType);
        } else {
            alert('Не забудьте вказати Eway ID');
        }
    });

    var sendImage = function (imageUuid, ewayId, type, verifyToken, imageName, formAction, transportType) {
        var request = new XMLHttpRequest();
        request.open("POST", formAction, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        var body = 'imageUuid=' + encodeURIComponent(imageUuid) +
            '&imageName=' + encodeURIComponent(imageName) +
            '&ewayId=' + encodeURIComponent(ewayId) +
            '&transportType=' + encodeURIComponent(transportType) +
            '&type=' + encodeURIComponent(type) +
            '&verifyToken=' + encodeURIComponent(verifyToken);

        request.onreadystatechange = function() {
            if (this.readyState != 4) return;
            if (this.responseText == 'EVENT_RECEIVED') {
                $("#" + imageUuid).remove()
            }
        }

        request.send(body);
    };

    $("#control_type").change(function(){
        var type = $( this ).val();
        var location = window.location;
        if (type === 'stop') {
            window.location = location.href.replace('transport', 'stop');
        } else {
            window.location = location.href.replace('stop', 'transport');
        }
    });

}(jQuery);

+ function($) {
    'use strict';

    $(".accept").click(function(){
        var closestCard = $(this).closest(".card");
        var imageUuid   = closestCard.attr('id');
        var ewayId      = closestCard.find('#' + imageUuid + '_eway_id').val() || 0;
        var type        = closestCard.find('#' + imageUuid + '_type').val();
        var verifyToken = closestCard.find('#' + imageUuid + '_verify_token').val() || 0;
        var imageName   = closestCard.find('#' + imageUuid + '_name').val() || 0;
        var formAction  = closestCard.find('.card-form').attr('action');

        if (ewayId !== 0) {
            sendImage(imageUuid, ewayId, type, verifyToken, imageName, formAction);
        }
    });

    var sendImage = function (imageUuid, ewayId, type, verifyToken, imageName, formAction) {
        var request = new XMLHttpRequest();
        request.open("POST", formAction, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        var body = 'imageUuid=' + encodeURIComponent(imageUuid) +
            '&imageName=' + encodeURIComponent(imageName) +
            '&ewayId=' + encodeURIComponent(ewayId) +
            '&type=' + encodeURIComponent(type) +
            '&verifyToken=' + encodeURIComponent(verifyToken);

        request.onreadystatechange = function() {
            if (this.readyState != 4) return;
            //alert( this.responseText );
        }

        request.send(body);
    };

}(jQuery);
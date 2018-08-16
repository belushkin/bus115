+ function($) {
    'use strict';

    $(".accept").click(function(){
        var closestCard = $(this).closest(".card");
        var imageId     = closestCard.attr('id');
        var ewayId      = closestCard.find('#' + imageId + '_eway_id').val() || 0;
        var type        = closestCard.find('#' + imageId + '_type').val();
        var verifyToken = closestCard.find('#' + imageId + '_verify_token').val() || 0;
        var formAction  = closestCard.find('.card-form').attr('action');

        if (ewayId !== 0) {
            sendImage(imageId, ewayId, type, verifyToken, formAction);
        }
    });

    var sendImage = function (imageId, ewayId, type, verifyToken, formAction) {
        var request = new XMLHttpRequest();
        request.open("POST", formAction, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        var body = 'imageId=' + encodeURIComponent(imageId) +
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
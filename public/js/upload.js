+ function($) {
    'use strict';

    // UPLOAD CLASS DEFINITION
    // ======================

    var dropZone = document.getElementById('drop-zone');
    var uploadForm = document.getElementById('js-upload-form');

    var startUpload = function(files) {
        console.log(files)

        Array.prototype.forEach.call(files , function(file) {
            sendFile(file);
        })
    }

    var sendFile = function (file) {
        var formData = new FormData();
        var request = new XMLHttpRequest();
        formData.set('file', file);
        formData.set('arr', [document.getElementById('formGroupInput').value]);
        request.open("POST", document.getElementById('js-upload-form').action);
        request.send(formData);
    };

    uploadForm.addEventListener('submit', function(e) {
        var uploadFiles = document.getElementById('js-upload-files').files;
        e.preventDefault()

        startUpload(uploadFiles)
    })

    dropZone.ondrop = function(e) {
        e.preventDefault();
        this.className = 'upload-drop-zone';

        startUpload(e.dataTransfer.files)
    }

    dropZone.ondragover = function() {
        this.className = 'upload-drop-zone drop';
        return false;
    }

    dropZone.ondragleave = function() {
        this.className = 'upload-drop-zone';
        return false;
    }

}(jQuery);
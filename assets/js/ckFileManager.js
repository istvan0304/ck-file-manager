'use strict';

$(document).ready(function () {
    var $body = $('body'),
        $fileId = null,
        $search = $('#ck-search'),
        $noFiles = $('#no-file'),
        $sidebar = $('#ck-sidebar'),
        $uploadBtn = $('#ck-file-upload'),
        $form = $('#file-upload-form'),
        $fileInput = $('input[name="CkFileForm[uploaded_files][]"]'),
        $progressBar = $('.ck-progress'),
        $details = $('.ck-details'),
        $uploadStatus = $('#ck-upload-status'),
        $detailsOpen = $('#ck-upload-details'),
        $progressClose = $('.ck-progress-close'),
        $detailsClose = $('.ck-details-close'),
        $loader = $('#ck-list-loader'),
        typingTimer,
        doneTypingInterval = 800;


    $fileInput.change(function () {
        $form.submit();
    });

    $body.on('beforeSubmit', $form, function () {
        ckFile.upload();

        return false;
    }).on('click', '.ck-file-box', function () {
        if ($(this).hasClass('active')) {
            $('.ck-file-box').removeClass('active');
            sidebar.emptySelect($sidebar);
        } else {
            $('.ck-file-box').removeClass('active');
            $(this).addClass('active');
            ckFile.getDetails($(this).data());
        }
    }).on('dblclick', '.ck-file-box', function () {
        ckFile.select($(this).data('id'));
    }).on('click', '#ck-select', function () {
        ckFile.select($(this).data('id'));
    }).on('click', '#ck-delete', function () {
        ckFile.delete($(this).data('id'));
    });

    $uploadBtn.click(function () {
        $('#ckfileform-uploaded_files').trigger('click');
    });

    $detailsOpen.click(function () {
        $details.animate({width: 'toggle', display: 'inline-table'});
    });

    $detailsClose.click(function () {
        $details.animate({width: 'toggle'});
    });

    $progressClose.click(function () {
        $uploadStatus.css('display', 'none');
    });

    $search.keyup(function () {
        let search = $(this).val();
        loader.show($loader);
        clearTimeout(typingTimer);
        typingTimer = setTimeout(function () {
            doneTyping(search)
        }, doneTypingInterval);
    });

    $search.keydown(function () {
        clearTimeout(typingTimer);
    });

    var doneTyping = (search) => {
        if (search !== undefined) {

            $.ajax('/ckfilemanager/ck-file/ajax-search', {
                method: "get",
                dataType: "json",
                data: {name: search},
                async: true,
            }).then(function (response) {
                if (response['success'] && response['result'] !== undefined) {
                    $('#ck-pjax-file-list').empty().append(response['result']);
                }
                loader.hide($loader);
            }).done(function () {
                ckFile.emptyDetails();
            });
        }
    };

    var ckFile = {
        upload: () => {
            let form = document.getElementById('file-upload-form');
            let formData = new FormData(form);

            if (formData !== undefined) {
                $('#ck-upload-status').css('display', 'block');
                loader.show($loader);

                $.ajax('/ckfilemanager/ck-file/upload', {
                    xhr: function () {
                        var xhr = new window.XMLHttpRequest();

                        xhr.upload.addEventListener('progress', function (evt) {
                            if (evt.lengthComputable) {
                                // var percentComplete = evt.loaded / evt.total;
                                let percentComplete = Math.round((evt.loaded * 100) / evt.total);
                                $progressBar.css('width', percentComplete + '%');
                                $('#ck-percentage').text(percentComplete + '%');
                            }
                        }, false);

                        return xhr;
                    },
                    method: 'post',
                    dataType: 'json',
                    data: formData,
                    processData: false,
                    contentType: false,
                    cache: false,
                }).then(function (response) {
                    if (response['class'] === 'load-error') {
                        $details.find('.ck-details-body').empty().append(response['message']);
                    } else {
                        $details.find('.ck-details-body').empty().append(response);
                        $.pjax.reload({container: '#ck-pjax-file-list'});
                        files.checkCount($noFiles);
                    }

                    $uploadStatus.css('display', 'block');
                    $search.val('');
                }).catch(function errorHandler(e) {
                    console.log(e.responseText);
                }).done(function () {
                    loader.hide($loader);
                });
            }

            return false;
        },

        getDetails: (data) => {
            if (data !== undefined && typeof data === 'object') {
                $fileId = data.id;

                $.ajax('/ckfilemanager/ck-file/get-details', {
                    method: 'GET',
                    dataType: 'json',
                    data: {id: data.id},
                    cache: false,
                    success: function (response) {
                        if (response['success']) {
                            $sidebar.find('.ck-no-select').css('display', 'none');
                            $sidebar.find('.ck-sidebar-content').empty().append(response['template']);
                        } else if (response['success'] === false) {
                            $sidebar.find('.ck-sidebar-content').append(response['message']);
                        }
                    }
                });
            }
        },

        emptyDetails: () => {
            $fileId = null;
            $sidebar.find('.ck-sidebar-content').empty();
            $sidebar.find('.ck-no-select').css('display', 'block');
        },

        select: (fileId) => {
            if (fileId !== null && typeof fileId === 'number') {
                var sField = window.queryStringParameter.get(window.location.href, 'CKEditorFuncNum');
                window.top.opener.CKEDITOR.tools.callFunction(sField, '/ckfilemanager/ck-file/get-file?id=' + $fileId);
                window.self.close();
            }
        },

        delete: (fileId) => {
            if (fileId !== null && typeof fileId === 'number') {
                $.ajax('/ckfilemanager/ck-file/delete', {
                    method: 'post',
                    dataType: 'json',
                    data: {id: fileId},
                    cache: false,
                    success: function (response) {
                        if (response['success']) {
                            $.pjax.reload({container: '#ck-pjax-file-list'});
                            sidebar.emptySelect($sidebar);
                        }
                    }
                });
            }
        }
    };
});

var sidebar = {
    emptySelect: ($sidebar) => {
        $sidebar.find('.ck-sidebar-content').empty();
        $sidebar.find('.ck-no-select').css('display', 'block');
    },
};

var files = {
    checkCount: ($noFiles) => {
        let filesLength = $('.ck-img-box').length;

        if ($noFiles !== undefined) {
            if (filesLength === 0) {
                $noFiles.css('display', 'none');
            } else {
                $noFiles.css('display', 'block');
            }
        }
    }
};

var loader = {
    show: ($loader) => {
        $loader.css('display', 'block');
    },
    hide: ($loader) => {
        $loader.css('display', 'none');
    },
};

window.queryStringParameter = {
    get: function (uri, key) {
        var reParam = new RegExp('(?:[\?&]|&amp;)' + key + '=([^&]+)', 'i');
        var match = uri.match(reParam);
        return (match && match.length > 1) ? match[1] : null;
    },
    set: function (uri, key, value) {
        //replace brackets
        var keyReplace = key.replace('[]', '').replace(/\[/g, '%5B').replace(/\]/g, '%5D');
        //replace data
        var re = new RegExp('([?&])' + keyReplace + '=.*?(&|$)', 'i');
        var separator = uri.indexOf('?') !== -1 ? '&' : '?';
        if (uri.match(re)) {
            return uri.replace(re, '$1' + keyReplace + '=' + value + '$2');
        } else {
            return uri + separator + keyReplace + '=' + value;
        }
    }
};

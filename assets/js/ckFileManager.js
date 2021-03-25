'use strict';

$(document).ready(function () {
    var $body = $('body'),
        $imgId = null,
        $search = $('#ck-search'),
        $noImg = $('#no-image'),
        $sidebar = $('#ck-sidebar'),
        $uploadBtn = $('#ck-img-upload'),
        $form = $('#img-upload-form'),
        $fileInput = $('input[name="CkImageForm[img_files][]"]'),
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
        ckImage.upload();

        return false;
    });

    $uploadBtn.click(function () {
        $('#ckimageform-img_files').trigger('click');
    });

    $body.on('click', '.ck-img-box', function () {
        if ($(this).hasClass('active')) {
            $('.ck-img-box').removeClass('active');
            sidebar.emptySelect($sidebar);
        } else {
            $('.ck-img-box').removeClass('active');
            $(this).addClass('active');
            ckImage.getDetails($(this).data());
        }
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

    $body.on('dblclick', '.ck-img-box', function () {
        ckImage.select($(this).data('id'));
    });

    $body.on('click', '#ck-select', function () {
        ckImage.select($(this).data('id'));
    });

    $body.on('click', '#ck-delete', function () {
        ckImage.delete($(this).data('id'));
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

            $.ajax('/imagemanager/ck-image/ajax-search', {
                type: "GET",
                dataType: "json",
                data: {name: search},
                async: true,
                success: function (response) {
                    if (response['success'] && response['result'] !== undefined) {
                        $('#ck-pjax-image-list').empty().append(response['result']);
                    }
                    loader.hide($loader);
                }
            }).done(function () {
                ckImage.emptyDetails();
            });
        }
    };

    var ckImage = {
        upload: () => {
            let form = document.getElementById('img-upload-form');
            let formData = new FormData(form);

            if (formData !== undefined) {
                $('#ck-upload-status').css('display', 'block');
                loader.show($loader);

                $.ajax('/imagemanager/ck-image/upload', {
                    xhr: function () {
                        var xhr = new window.XMLHttpRequest();

                        xhr.upload.addEventListener("progress", function (evt) {
                            if (evt.lengthComputable) {
                                // var percentComplete = evt.loaded / evt.total;
                                let percentComplete = Math.round((evt.loaded * 100) / evt.total);
                                $progressBar.css('width', percentComplete + '%');
                                $('#ck-percentage').text(percentComplete + '%');
                            }
                        }, false);

                        return xhr;
                    },
                    method: "POST",
                    dataType: 'json',
                    data: formData,
                    processData: false,
                    contentType: false,
                    cache: false,
                    success: function (response) {
                        if (response['class'] === 'load-error') {
                            $details.find('.ck-details-body').empty().append(response['message']);
                        } else {
                            $details.find('.ck-details-body').empty().append(response);
                            $.pjax.reload({container: "#ck-pjax-image-list"});
                            images.checkCount($noImg);
                        }

                        $uploadStatus.css('display', 'block');
                        $search.val('');
                        loader.hide($loader);
                    },
                    error: function (xhr) {
                        console.log(xhr);
                    }
                });
            }

            return false;
        },

        getDetails: (data) => {
            if (data !== undefined && typeof data === 'object') {
                $imgId = data.id;

                $.ajax('/imagemanager/ck-image/get-details', {
                    method: "GET",
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
            $imgId = null;
            $sidebar.find('.ck-sidebar-content').empty();
            $sidebar.find('.ck-no-select').css('display', 'block');
        },

        select: (imgId) => {
            if (imgId !== null && typeof imgId === 'number') {
                var sField = window.queryStringParameter.get(window.location.href, "CKEditorFuncNum");
                window.top.opener.CKEDITOR.tools.callFunction(sField, '/imagemanager/ck-image/get-image?id=' + $imgId);
                window.self.close();
            }
        },

        delete: (imgId) => {
            if (imgId !== null && typeof imgId === 'number') {
                $.ajax('/imagemanager/ck-image/delete', {
                    method: "POST",
                    dataType: 'json',
                    data: {id: imgId},
                    cache: false,
                    success: function (response) {
                        if (response['success']) {
                            $.pjax.reload({container: "#ck-pjax-image-list"});
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

var images = {
    checkCount: ($noImage) => {
        let imagesLength = $('.ck-img-box').length;

        if ($noImage !== undefined) {
            if (imagesLength === 0) {
                $noImage.css('display', 'none');
            } else {
                $noImage.css('display', 'block');
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
        var keyReplace = key.replace("[]", "").replace(/\[/g, "%5B").replace(/\]/g, "%5D");
        //replace data
        var re = new RegExp("([?&])" + keyReplace + "=.*?(&|$)", "i");
        var separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + keyReplace + "=" + value + '$2');
        } else {
            return uri + separator + keyReplace + "=" + value;
        }
    }
};

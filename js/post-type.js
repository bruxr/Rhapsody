(function($) {

    var mediaUploader = false;
    function mediaUploaderChecker() {

        var checker = $('#__wp-uploader-id-2');
        if (checker.length > 0) {
            if (checker.is(':visible')) {
                mediaUploader = true;
            }
            else if(!checker.is(':visible') && mediaUploader) {
                mediaUploader = false;
                $(document).trigger('wp-uploader-change');
            }
        }

    }

    $(function() {

        setInterval(mediaUploaderChecker, 100);

        $('.rhap-image-selector').on('change', function() {
            var select = $(this),
                selected = $('option:selected', select);
            if (select.val() == '') return;
            select.parent().siblings('span').html('<img src="'+ selected.data('thumb') +'">');
        });

        $(document).on('wp-uploader-change', function(evt) {

            var selectors = $('.rhap-image-selector').html('<option>Refreshing list...</option>').prop('disabled', true);

            $.getJSON(ajaxurl, {action: 'rhap_get_attachments', _wpnonce: RHAPSODY.getAttachmentsNonce}, function(res) {
                
                selectors.append('<option value="">Select an Image...</option>');
                $.each(res.results, function(id, attach) {
                    selectors.append('<option value="'+ id +'" data-thumb="'+ attach.thumb +'">'+ attach.basename +'</option>');
                });

                selectors.each(function() {
                    var select = $(this);
                    if (select.data('value')) {
                        select.val(select.data('value'));
                    }
                }).prop('disabled', false)
                  .children(':first').remove();

            });

        });

    });

})(jQuery);
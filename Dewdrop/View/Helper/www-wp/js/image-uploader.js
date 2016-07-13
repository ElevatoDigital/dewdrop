jQuery(function () {
    jQuery('button.dewdrop-image-picker').click(function (e) {
        var button = jQuery(this);

        var frame = wp.media({
            title: 'Select Image',
            multiple: false,
            library: { type: 'image' },
            button : { text : 'Select Image' }
        });

        frame.on( 'select', function () {
            var selection = frame.state().get('selection');

            selection.each(function (attachment) {
                jQuery(button.data('target')).val(attachment.attributes.url);

                var container = button.parent(),
                    image     = container.find('img');

                if (!image.length) {
                    image = jQuery(
                        '<div class="row"><div class="col-md-4"><span class="thumbnail">' +
                        '<img alt="" src="" class="featured_image" /></span></div></div>'
                    );

                    image.prependTo(container);

                    image = image.find('img');
                }

                image.attr('src', attachment.attributes.url)
            });
        });

        frame.open();

        return false;
    });
});

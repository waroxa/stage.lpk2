(function ($) {
  const imageIdInput = $('#lpk-map-image-id');
  const previewWrap = $('#lpk-map-image-preview');
  const previewImage = previewWrap.find('img');
  const removeButton = $('#lpk-map-image-remove');

  if (!imageIdInput.length) {
    return;
  }

  let mediaFrame;

  const setPreview = (id, url) => {
    imageIdInput.val(id || '');

    if (url) {
      previewImage.attr('src', url);
      previewWrap.show();
      removeButton.prop('disabled', false);
    } else {
      previewImage.attr('src', '');
      previewWrap.hide();
      removeButton.prop('disabled', true);
    }
  };

  $('#lpk-map-image-upload').on('click', (event) => {
    event.preventDefault();

    if (mediaFrame) {
      mediaFrame.open();
      return;
    }

    mediaFrame = wp.media({
      title: 'Select map placeholder image',
      button: { text: 'Use this image' },
      multiple: false,
    });

    mediaFrame.on('select', () => {
      const attachment = mediaFrame.state().get('selection').first().toJSON();
      setPreview(attachment.id, attachment.url);
    });

    mediaFrame.open();
  });

  removeButton.on('click', (event) => {
    event.preventDefault();
    setPreview('', '');
  });
})(jQuery);

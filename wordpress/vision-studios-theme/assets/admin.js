// Media picker for the studio gallery / floor plan / city image fields.
jQuery(function ($) {
  $(document).on('click', '.vs-media-pick', function () {
    var wrap = $(this).closest('.vs-media'), multiple = wrap.data('multiple') === 1, input = wrap.find('input'), preview = wrap.find('.vs-media-preview')
    var frame = wp.media({ title: multiple ? 'Select images' : 'Select image', multiple: multiple ? 'add' : false, library: { type: 'image' } })
    frame.on('select', function () {
      var ids = multiple ? input.val().split(',').filter(Boolean) : []
      frame.state().get('selection').each(function (att) {
        var a = att.toJSON(), url = (a.sizes && (a.sizes.thumbnail || a.sizes.medium) || {}).url || a.url
        if (ids.indexOf(String(a.id)) === -1) {
          ids.push(String(a.id))
          preview.append('<span data-id="' + a.id + '" style="position:relative"><img src="' + url + '" style="display:block;width:90px;height:90px;object-fit:cover"/><button type="button" class="vs-media-remove" style="position:absolute;top:0;right:0;background:#000;color:#fff;border:0;cursor:pointer">×</button></span>')
        }
      })
      if (!multiple) { preview.children().not(':last').remove() }
      input.val(ids.join(','))
    })
    frame.open()
  })
  $(document).on('click', '.vs-media-remove', function () {
    var wrap = $(this).closest('.vs-media'), item = $(this).closest('span'), id = String(item.data('id'))
    item.remove()
    wrap.find('input').val(wrap.find('input').val().split(',').filter(function (x) { return x && x !== id }).join(','))
  })
})

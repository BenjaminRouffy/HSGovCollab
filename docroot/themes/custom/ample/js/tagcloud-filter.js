(function ($) {
  Drupal.behaviors.linksExposedFilter = {
    attach: function (context, settings) {
      var $forms = $('[data-links-filter]', context);

      $.each($forms, function (i, v) {
        var $form = $(v),
          $form_id = $form.attr('id');

        $form.find('select').each(function () {
          var $filter = $(this),
            name = $filter.attr('name'),
            className = '.' + name.replace(/_/g, '-').replace(/\[]/, '');

          $(className + ' .filter-tab a').on('click', function (e) {
            e.preventDefault();

            // Get ID of clicked item
            var id = $(e.target).attr('id');
            $filter.val(id);

            // Unset and then set the active class
            $(className + ' .filter-tab a').removeClass('active');

            // Do it! Trigger the select box.
            $filter.trigger('change');
            $('#' + $form_id + ' input.form-submit').trigger('click');
          });

          $(document).ajaxComplete(function (event, xhr, settings) {
            var filter_id;

            if (
              typeof(settings.extraData) !== 'undefined' &&
              typeof(settings.extraData.view_name) !== 'undefined' &&
              settings.extraData.view_name == settings.blog_view
            ) {
              filter_id = $('select[name="' + name + '"]').find(":selected").val();

              $(className + ' .filter-tab a').removeClass('active');
              $(className + ' .filter-tab').find('#' + filter_id).addClass('active');
            }
          });
        });
      });
    }
  };
})(jQuery);

/**
 * Color Field jQuery
 */
(function ($, Drupal) {

  Drupal.behaviors.color_field_box = {
    attach: function (context, settings) {

      jQuery.fn.addColorPicker = function (props) {
        if (!props) { props = []; }

        props = jQuery.extend({
          currentColor:'',
          blotchElemType: 'span',
          blotchClass:'colorBox',
          blotchTransparentClass:'transparentBox',
          clickCallback: function(ignoredColor) {},
          iterationCallback: null,
          fillString: '&nbsp;',
          fillStringX: '?',
          colors: settings.color_field.color_field_widget_box.settings.palette
        }, props);

        var count = props.colors.length;
        for (var i = 0; i < count; ++i) {
          var color = props.colors[i];
          var elem = jQuery('<' + props.blotchElemType + '/>')
            .addClass(props.blotchClass)
            .attr('color',color)
            .css('background-color',color);
          // jq bug: chaining here fails if color is null b/c .css() returns (new String('transparent'))!
          if (props.currentColor == color) {
            elem.addClass('active');
          }
          if (props.clickCallback) {
            elem.click(function() {
              jQuery(this).parent().children('span').removeClass('active');
              jQuery(this).addClass('active');
              props.clickCallback(jQuery(this).attr('color'));
            });
          }
          this.append(elem);
          if (props.iterationCallback) {
            props.iterationCallback(this, elem, color, i);
          }
        }

        // for transparent option box
        var elem = jQuery('<' + props.blotchElemType + '/>')
          .addClass(props.blotchTransparentClass)
          .attr('color', '')
          .css('background-color', '');

        if (props.currentColor == '') {
          elem.addClass('active');
        }
        if (props.clickCallback) {
          elem.click(function() {
            jQuery(this).parent().children('span').removeClass('active');
            jQuery(this).addClass('active');
            props.clickCallback(jQuery(this).attr('color'));
          });
        }
        this.append(elem);
        if (props.iterationCallback) {
          props.iterationCallback(this, elem, color, i);
        }

        return this;
      };
    }
  };

})(jQuery, Drupal);

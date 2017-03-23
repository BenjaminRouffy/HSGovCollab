/**
 * @file
 * blocktabs behaviors.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Add jquery ui tabs effect.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *
   */
  Drupal.behaviors.blocktabs = {
    attach: function (context, settings) {
      $(context).find('div.blocktabs-mouseover').each(function () {
        $(this).tabs({
         event: "mouseover"
        });
      });
      $(context).find('div.blocktabs-click').each(function () {
        $(this).tabs({
          event: "click"
        });
      });
    }
  };

}(jQuery, Drupal));

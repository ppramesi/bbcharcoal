$(document).ready(function($) {
  
  $('.modalClose').click(function() {
    $('.PHPurchaseUnavailable').fadeOut(800);
  });
  
});


var $pj = jQuery.noConflict();

function getCartButtonFormData(formId) {
  var theForm = $pj('#' + formId);
  var str = '';
  $pj('input:not([type=checkbox], :radio), input[type=checkbox]:checked, input:radio:checked, select, textarea', theForm).each(
      function() {
        var name = $pj(this).attr('name');
        var val = $pj(this).val();
        str += name + '=' + encodeURIComponent(val) + '&';
      }
  );

  return str.substring(0, str.length-1);
}
(function($){
  function refreshDates(){
    if(!window.SDFW) return;
    $.post(SDFW.ajax,{action:'sdfw_dates',nonce:SDFW.nonce}).always(function(){});
  }
  $(document.body).on('updated_checkout', refreshDates);
})(jQuery);
jQuery(function($){
  function setState($picker,text,type){
    var $state=$picker.find('.sdw-save-state');
    $state.removeClass('is-visible is-error is-saving').text(text||'');
    if(text){$state.addClass('is-visible '+(type||''));}
  }
  function saveDate($el){
    if(typeof sdwData==='undefined') return;
    var value=$el.val()||'', $picker=$el.closest('.sdw-picker');
    setState($picker,sdwData.saving,'is-saving');
    var label=$el.data('label')||$el.find('option:selected').text()||'';
    if(label){$picker.find('.sdw-selected-label').text(label);}
    $.post(sdwData.ajaxUrl,{action:'sdw_set_delivery_date',nonce:sdwData.nonce,date:value})
      .done(function(res){
        if(res&&res.success){setState($picker,sdwData.saved,'');}
        else {setState($picker,(res&&res.data&&res.data.message)?res.data.message:sdwData.error,'is-error');}
      })
      .fail(function(){setState($picker,sdwData.error,'is-error');});
  }
  function showPanel($cal,index){
    var count=$cal.find('.sdw-cal-panel').length;
    index=Math.max(0,Math.min(count-1,index));
    $cal.attr('data-panel',index);
    $cal.find('.sdw-cal-panel,.sdw-cal-month-label').removeClass('is-active');
    $cal.find('.sdw-cal-panel[data-index="'+index+'"],.sdw-cal-month-label[data-index="'+index+'"]').addClass('is-active');
    $cal.find('.sdw-cal-prev').prop('disabled',index===0);
    $cal.find('.sdw-cal-next').prop('disabled',index===count-1);
  }
  function initCalendars(scope){
    $(scope||document).find('.sdw-mini-calendar').each(function(){
      var $cal=$(this), index=parseInt($cal.attr('data-panel')||'0',10); showPanel($cal,index);
    });
  }
  $(document).on('click','.sdw-cal-prev,.sdw-cal-next',function(){
    var $cal=$(this).closest('.sdw-mini-calendar'), index=parseInt($cal.attr('data-panel')||'0',10);
    showPanel($cal,index+($(this).hasClass('sdw-cal-next')?1:-1));
  });
  $(document).on('change','.sdw-date-control',function(){ saveDate($(this)); });
  $(document.body).on('updated_checkout updated_wc_div',function(){ initCalendars(document); });
  initCalendars(document);
});

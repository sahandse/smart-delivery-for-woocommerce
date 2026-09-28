jQuery(function($){
  function setState($picker,text,type){
    var $state=$picker.find('.sdw-save-state');
    $state.removeClass('is-visible is-error is-saving').text(text||'');
    if(text){$state.addClass('is-visible '+(type||''));}
  }

  function ensureSelectedSummary($picker,label){
    if(!$picker.length||!label) return;
    var $label=$picker.find('.sdw-selected-label');
    if(!$label.length){
      var title=(typeof sdwData!=='undefined'&&sdwData.selected)?sdwData.selected:'تاریخ انتخاب‌شده';
      var $summary=$('<div class="sdw-cal-summary sdw-runtime-summary"><span></span><strong class="sdw-selected-label"></strong></div>');
      $summary.find('span').text(title);
      var $footer=$picker.find('.sdw-picker__footer').first();
      if($footer.length){$summary.insertBefore($footer);}else{$picker.append($summary);}
      $label=$summary.find('.sdw-selected-label');
    }
    $label.text(label);
  }

  function controlLabel($el){
    if(!$el||!$el.length) return '';
    var label=$el.attr('data-label')||$el.data('label')||'';
    if(!label&&$el.is('select')) label=$el.find('option:selected').text()||'';
    return $.trim(label);
  }

  function syncPicker($picker){
    if(!$picker||!$picker.length) return;
    var $select=$picker.find('select.sdw-date-control').first();
    if($select.length&&$select.val()){
      ensureSelectedSummary($picker,controlLabel($select));
      return;
    }
    var $checked=$picker.find('input.sdw-date-control:checked').first();
    if($checked.length) ensureSelectedSummary($picker,controlLabel($checked));
  }

  function syncAll(scope){
    $(scope||document).find('.sdw-picker').each(function(){syncPicker($(this));});
  }

  function saveDate($el){
    if(typeof sdwData==='undefined') return;
    var value=$el.val()||'', $picker=$el.closest('.sdw-picker');
    var label=controlLabel($el);

    if(label&&value) ensureSelectedSummary($picker,label);
    setState($picker,sdwData.saving,'is-saving');

    $.post(sdwData.ajaxUrl,{action:'sdw_set_delivery_date',nonce:sdwData.nonce,date:value})
      .done(function(res){
        if(res&&res.success){
          if(label&&value) ensureSelectedSummary($picker,label);
          setState($picker,sdwData.saved,'');
        } else {
          setState($picker,(res&&res.data&&res.data.message)?res.data.message:sdwData.error,'is-error');
        }
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

  $(document.body).on('updated_checkout updated_wc_div',function(){
    initCalendars(document);
    syncAll(document);
  });

  initCalendars(document);
  syncAll(document);
});

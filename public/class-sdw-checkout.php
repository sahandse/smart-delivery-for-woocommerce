<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class SDW_Checkout {
    public function __construct(){
        add_action('wp_enqueue_scripts',array($this,'assets'));
        add_action('woocommerce_after_cart_table',array($this,'cart_field'));
        add_action('woocommerce_after_order_notes',array($this,'checkout_field'));
        add_action('wp_ajax_sdw_set_delivery_date',array($this,'ajax_set_date'));
        add_action('wp_ajax_nopriv_sdw_set_delivery_date',array($this,'ajax_set_date'));
        add_action('woocommerce_checkout_process',array($this,'validate'));
        add_action('woocommerce_checkout_create_order',array($this,'save'),20,2);
        add_action('woocommerce_admin_order_data_after_shipping_address',array($this,'admin_order'));
        add_filter('woocommerce_email_order_meta_fields',array($this,'email_meta'),10,3);
    }
    public function assets(){
        $on_cart=function_exists('is_cart')&&is_cart(); $on_checkout=function_exists('is_checkout')&&is_checkout();
        if(!$on_cart&&!$on_checkout)return;
        wp_enqueue_style('sdw-checkout',SDW_URL.'assets/css/checkout.css',array(),SDW_VERSION);
        wp_enqueue_script('sdw-checkout',SDW_URL.'assets/js/checkout.js',array('jquery'),SDW_VERSION,true);
        wp_localize_script('sdw-checkout','sdwData',array(
            'ajaxUrl'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('sdw_set_date'),
            'saved'=>SDW_Core::text('تاریخ تحویل ذخیره شد','Delivery date saved'),
            'saving'=>SDW_Core::text('در حال ذخیره…','Saving…'),
            'error'=>SDW_Core::text('ذخیره تاریخ انجام نشد. دوباره تلاش کنید.','Could not save the date. Please try again.'),
            'selected'=>SDW_Core::text('تاریخ انتخاب‌شده','Selected date')
        ));
    }
    private function shipping(){ if(!function_exists('WC')||!WC()->session)return ''; $chosen=(array)WC()->session->get('chosen_shipping_methods',array()); if(empty($chosen[0]))return ''; $packages=WC()->shipping()->get_packages(); foreach($packages as $package)foreach($package['rates'] as $id=>$rate)if($id===$chosen[0])return $rate->get_label().' '.$id; return $chosen[0]; }
    private function dates(){ $ship=$this->shipping(); return SDW_Delivery::available_dates(SDW_Delivery::is_motorcycle_method($ship,$ship)); }
    private function selected(){
        if(isset($_POST['sdw_delivery_date'])) return sanitize_text_field(wp_unslash($_POST['sdw_delivery_date']));
        if(function_exists('WC')&&WC()->session) return (string)WC()->session->get('sdw_delivery_date','');
        return '';
    }
    private function month_name($month,$calendar,$lang){
        if('jalali'===$calendar){
            $fa=array(1=>'فروردین',2=>'اردیبهشت',3=>'خرداد',4=>'تیر',5=>'مرداد',6=>'شهریور',7=>'مهر',8=>'آبان',9=>'آذر',10=>'دی',11=>'بهمن',12=>'اسفند');
            $en=array(1=>'Farvardin',2=>'Ordibehesht',3=>'Khordad',4=>'Tir',5=>'Mordad',6=>'Shahrivar',7=>'Mehr',8=>'Aban',9=>'Azar',10=>'Dey',11=>'Bahman',12=>'Esfand');
            $map='fa'===$lang?$fa:$en; return $map[(int)$month];
        }
        $names='fa'===$lang?array(1=>'ژانویه',2=>'فوریه',3=>'مارس',4=>'آوریل',5=>'مه',6=>'ژوئن',7=>'ژوئیه',8=>'اوت',9=>'سپتامبر',10=>'اکتبر',11=>'نوامبر',12=>'دسامبر'):array(1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December');
        return $names[(int)$month];
    }
    private function display_num($value,$lang){ return 'fa'===$lang?SDW_Calendar::fa_digits($value):(string)$value; }
    private function jalali_month_length($jy,$jm){
        $next_y=$jy; $next_m=$jm+1; if($next_m>12){$next_m=1;$next_y++;}
        list($gy1,$gm1,$gd1)=SDW_Calendar::jalali_to_gregorian($jy,$jm,1);
        list($gy2,$gm2,$gd2)=SDW_Calendar::jalali_to_gregorian($next_y,$next_m,1);
        $a=new DateTimeImmutable(sprintf('%04d-%02d-%02d',$gy1,$gm1,$gd1),wp_timezone());
        $b=new DateTimeImmutable(sprintf('%04d-%02d-%02d',$gy2,$gm2,$gd2),wp_timezone());
        return (int)$a->diff($b)->days;
    }
    private function render_calendar($dates,$selected,$context){
        if(!$dates)return;
        $o=SDW_Core::options(); $lang=SDW_Core::language(); $calendar=$o['calendar'];
        $available=array(); foreach($dates as $d)$available[$d->format('Y-m-d')]=$d; $first_available=!empty($dates)?$dates[0]->format('Y-m-d'):'';
        $months=array();
        foreach($dates as $d){
            if('jalali'===$calendar){ list($y,$m)=SDW_Calendar::gregorian_to_jalali((int)$d->format('Y'),(int)$d->format('n'),(int)$d->format('j')); $key=sprintf('%04d-%02d',$y,$m); $months[$key]=array($y,$m); }
            else { $key=$d->format('Y-m'); $months[$key]=array((int)$d->format('Y'),(int)$d->format('n')); }
        }
        ksort($months); $month_values=array_values($months); $selected_panel=0;
        if($selected&&isset($available[$selected])){
            $sd=$available[$selected]; if('jalali'===$calendar){list($sy,$sm)=SDW_Calendar::gregorian_to_jalali((int)$sd->format('Y'),(int)$sd->format('n'),(int)$sd->format('j'));$skey=sprintf('%04d-%02d',$sy,$sm);}else{$skey=$sd->format('Y-m');}
            $keys=array_keys($months); $pos=array_search($skey,$keys,true); if(false!==$pos)$selected_panel=(int)$pos;
        }
        $weekdays='jalali'===$calendar
            ? ('fa'===$lang?array('ش','ی','د','س','چ','پ','ج'):array('Sa','Su','Mo','Tu','We','Th','Fr'))
            : ('fa'===$lang?array('ی','د','س','چ','پ','ج','ش'):array('Su','Mo','Tu','We','Th','Fr','Sa'));
        echo '<div class="sdw-mini-calendar sdw-density-'.esc_attr($o['calendar_density']).'" data-panel="'.esc_attr($selected_panel).'">';
        echo '<div class="sdw-cal-nav"><button type="button" class="sdw-cal-arrow sdw-cal-prev" aria-label="'.esc_attr(SDW_Core::text('ماه قبل','Previous month')).'">‹</button><div class="sdw-cal-months">';
        foreach($month_values as $i=>$ym){$label=$this->month_name($ym[1],$calendar,$lang).' '.$this->display_num($ym[0],$lang);echo '<strong class="sdw-cal-month-label'.($i===$selected_panel?' is-active':'').'" data-index="'.esc_attr($i).'">'.esc_html($label).'</strong>';}
        echo '</div><button type="button" class="sdw-cal-arrow sdw-cal-next" aria-label="'.esc_attr(SDW_Core::text('ماه بعد','Next month')).'">›</button></div>';
        echo '<div class="sdw-cal-weekdays">';foreach($weekdays as $w)echo '<span>'.esc_html($w).'</span>';echo '</div>';
        echo '<div class="sdw-cal-panels">';
        foreach($month_values as $i=>$ym){
            $y=$ym[0];$m=$ym[1]; echo '<div class="sdw-cal-panel'.($i===$selected_panel?' is-active':'').'" data-index="'.esc_attr($i).'">';
            if('jalali'===$calendar){
                $days_in=$this->jalali_month_length($y,$m); list($gy,$gm,$gd)=SDW_Calendar::jalali_to_gregorian($y,$m,1); $first=new DateTimeImmutable(sprintf('%04d-%02d-%02d',$gy,$gm,$gd),wp_timezone()); $first_w=(int)$first->format('w'); $offset=($first_w+1)%7;
            } else {
                $first=new DateTimeImmutable(sprintf('%04d-%02d-01',$y,$m),wp_timezone()); $days_in=(int)$first->format('t'); $first_w=(int)$first->format('w'); $offset=$first_w;
            }
            for($blank=0;$blank<$offset;$blank++) echo '<span class="sdw-cal-empty" aria-hidden="true"></span>';
            for($day=1;$day<=$days_in;$day++){
                if('jalali'===$calendar){ list($gy,$gm,$gd)=SDW_Calendar::jalali_to_gregorian($y,$m,$day); $date=new DateTimeImmutable(sprintf('%04d-%02d-%02d',$gy,$gm,$gd),wp_timezone()); }
                else $date=new DateTimeImmutable(sprintf('%04d-%02d-%02d',$y,$m,$day),wp_timezone());
                $iso=$date->format('Y-m-d'); $is_available=isset($available[$iso]); $occasion=SDW_Delivery::occasion($date); $holiday=('yes'===$o['iran_holidays']&&SDW_Iran_Calendar::is_holiday($date)); $today=$iso===(new DateTimeImmutable('today',wp_timezone()))->format('Y-m-d');
                if($is_available){
                    $aria=SDW_Delivery::weekday((int)$date->format('w')).' '.SDW_Delivery::formatted_date($date); if($occasion)$aria.=' - '.$occasion;
                    echo '<label class="sdw-cal-day'.($today?' is-today':'').($occasion?' has-occasion':'').(('yes'===$o['show_first_available']&&$iso===$first_available)?' is-first-available':'').'" title="'.esc_attr($occasion).'">';
                    echo '<input class="sdw-date-control" data-context="'.esc_attr($context).'" type="radio" name="sdw_delivery_date" value="'.esc_attr($iso).'" data-label="'.esc_attr($aria).'" '.checked($selected,$iso,false).'>';
                    echo '<span><b>'.esc_html($this->display_num($day,$lang)).'</b>'.($occasion?'<i></i>':'').'</span></label>';
                } else {
                    $reason=$occasion?$occasion:SDW_Core::text('غیرقابل ارسال','Unavailable');
                    echo '<span class="sdw-cal-day is-disabled'.($holiday?' is-holiday':'').($occasion?' has-occasion':'').'" title="'.esc_attr($reason).'" aria-disabled="true"><span><b>'.esc_html($this->display_num($day,$lang)).'</b>'.($occasion?'<i></i>':'').'</span></span>';
                }
            }
            echo '</div>';
        }
        echo '</div>';
        if('yes'===$o['show_calendar_legend']){
            echo '<div class="sdw-cal-legend"><span><i class="is-available"></i>'.esc_html(SDW_Core::text('قابل ارسال','Available')).'</span><span><i class="is-occasion"></i>'.esc_html(SDW_Core::text('مناسبت','Occasion')).'</span><span><i class="is-holiday"></i>'.esc_html(SDW_Core::text('تعطیل','Holiday')).'</span></div>';
        }
        $selected_label=''; if($selected&&isset($available[$selected]))$selected_label=SDW_Delivery::weekday((int)$available[$selected]->format('w')).'، '.SDW_Delivery::formatted_date($available[$selected]);
        if('yes'===$o['show_selected_summary']) echo '<div class="sdw-cal-summary"><span>'.esc_html(SDW_Core::text('تاریخ انتخاب‌شده','Selected date')).'</span><strong class="sdw-selected-label">'.esc_html($selected_label?:SDW_Core::text('هنوز انتخاب نشده','Not selected yet')).'</strong></div>';
        echo '</div>';
    }
    private function render($context='checkout'){
        $o=SDW_Core::options(); if('yes'!==$o['enabled'])return; $dates=$this->dates(); if(!$dates)return;
        $lang=SDW_Core::language(); $rtl='fa'===$lang; $selected=$this->selected(); if(!$selected&&'yes'===$o['required']&&!empty($dates))$selected=$dates[0]->format('Y-m-d');
        $title='fa'===$lang?$o['title_fa']:$o['title_en']; $sub='fa'===$lang?$o['subtitle_fa']:$o['subtitle_en'];
        echo '<section class="sdw-picker sdw-context-'.esc_attr($context).' sdw-style-'.esc_attr($o['display_style']).'" dir="'.($rtl?'rtl':'ltr').'" style="--sdw-accent:'.esc_attr($o['accent']).'">';
        echo '<div class="sdw-picker__header"><div class="sdw-picker__icon" aria-hidden="true"><span></span></div><div class="sdw-picker__copy"><strong>'.esc_html($title).'</strong><p>'.esc_html($sub).'</p></div><span class="sdw-picker__badge">'.esc_html(SDW_Core::text('ارسال','Delivery')).'</span></div>';
        if('calendar'===$o['display_style']){
            $this->render_calendar($dates,$selected,$context);
        } elseif('select'===$o['display_style']){
            echo '<select name="sdw_delivery_date" class="sdw-select sdw-date-control" data-context="'.esc_attr($context).'">';
            echo '<option value="">'.esc_html(SDW_Core::text('انتخاب تاریخ…','Choose a date…')).'</option>';
            foreach($dates as $date){$value=$date->format('Y-m-d');$occasion=SDW_Delivery::occasion($date);$label=SDW_Delivery::weekday((int)$date->format('w')).' — '.SDW_Delivery::formatted_date($date);if($occasion)$label.=' • '.$occasion;echo '<option value="'.esc_attr($value).'" '.selected($selected,$value,false).'>'.esc_html($label).'</option>';}
            echo '</select>';
        } else {
            echo '<div class="sdw-date-grid">';
            foreach($dates as $i=>$date){
                $value=$date->format('Y-m-d'); $checked=checked($selected,$value,false); $occasion=SDW_Delivery::occasion($date); $isSoon=$i<2;
                echo '<label class="sdw-date-card'.($isSoon?' sdw-date-card--soon':'').'">';
                echo '<input class="sdw-date-control" data-context="'.esc_attr($context).'" type="radio" name="sdw_delivery_date" value="'.esc_attr($value).'" data-label="'.esc_attr(SDW_Delivery::weekday((int)$date->format('w')).'، '.SDW_Delivery::formatted_date($date)).'" '.$checked.'>';
                echo '<span class="sdw-date-card__surface">';
                echo '<span class="sdw-date-card__top"><b>'.esc_html(SDW_Delivery::weekday((int)$date->format('w'))).'</b>'.($isSoon?'<em>'.esc_html($i===0?SDW_Core::text('اولین ارسال','Earliest'):SDW_Core::text('سریع','Fast')).'</em>':'').'</span>';
                echo '<span class="sdw-date-card__date">'.esc_html(SDW_Delivery::formatted_date($date)).'</span>';
                if('yes'===$o['show_occasion']&&$occasion) echo '<small class="sdw-date-card__occasion" title="'.esc_attr($occasion).'">'.esc_html($occasion).'</small>';
                else echo '<small class="sdw-date-card__occasion sdw-date-card__occasion--muted">'.esc_html(SDW_Core::text('روز کاری','Working day')).'</small>';
                echo '<i class="sdw-date-card__check">✓</i></span></label>';
            }
            echo '</div>';
        }
        echo '<div class="sdw-picker__footer"><span class="sdw-dot"></span>'.esc_html(SDW_Core::text('روزهای تعطیل و غیرفعال قابل انتخاب نیستند.','Closed and unavailable days cannot be selected.')).'<span class="sdw-save-state" aria-live="polite"></span></div>';
        echo '</section>';
    }
    public function cart_field(){ $o=SDW_Core::options(); if('yes'===$o['cart_enabled'])$this->render('cart'); }
    public function checkout_field($checkout){ $this->render('checkout'); }
    public function ajax_set_date(){ check_ajax_referer('sdw_set_date','nonce'); $date=isset($_POST['date'])?sanitize_text_field(wp_unslash($_POST['date'])):''; $allowed=array_map(function($d){return $d->format('Y-m-d');},$this->dates()); if($date&&!in_array($date,$allowed,true))wp_send_json_error(array('message'=>SDW_Core::text('این تاریخ دیگر در دسترس نیست.','This date is no longer available.'))); if(function_exists('WC')&&WC()->session)WC()->session->set('sdw_delivery_date',$date); wp_send_json_success(); }
    public function validate(){ $o=SDW_Core::options(); if('yes'!==$o['enabled'])return; $date=$this->selected(); if('yes'===$o['required']&&!$date){wc_add_notice(SDW_Core::text('لطفاً روز تحویل سفارش را انتخاب کنید.','Please choose a delivery date.'),'error');return;} if(!$date)return; $allowed=array_map(function($d){return $d->format('Y-m-d');},$this->dates()); if(!in_array($date,$allowed,true))wc_add_notice(SDW_Core::text('تاریخ تحویل انتخاب‌شده معتبر یا در دسترس نیست.','The selected delivery date is not valid or available.'),'error'); }
    public function save($order,$data){ $date=$this->selected(); if($date){$order->update_meta_data('_sdw_delivery_date',$date); if(function_exists('WC')&&WC()->session)WC()->session->__unset('sdw_delivery_date');} }
    public function admin_order($order){$date=$order->get_meta('_sdw_delivery_date');if($date)echo '<p><strong>'.esc_html(SDW_Core::text('روز تحویل:','Delivery date:')).'</strong> '.esc_html(SDW_Delivery::format_stored_date($date)).'</p>';}
    public function email_meta($fields,$sent_to_admin,$order){$date=$order?$order->get_meta('_sdw_delivery_date'):'';if($date)$fields['sdw_delivery_date']=array('label'=>SDW_Core::text('روز تحویل','Delivery date'),'value'=>SDW_Delivery::format_stored_date($date));return $fields;}
}

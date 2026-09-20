<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class SDW_Admin {
    public function __construct(){add_action('admin_menu',array($this,'menu'));add_action('admin_enqueue_scripts',array($this,'assets'));add_action('admin_init',array($this,'register_settings'));}
    public function menu(){add_menu_page('Smart Delivery','Smart Delivery','manage_woocommerce','sdw-smart-delivery',array($this,'page'),'dashicons-calendar-alt',56);}
    public function assets($hook){if(false===strpos($hook,'sdw-smart-delivery'))return;wp_enqueue_style('sdw-admin',SDW_URL.'assets/css/admin.css',array(),SDW_VERSION);}
    public function register_settings(){register_setting('sdw_group','sdw_settings',array($this,'sanitize'));}
    public function sanitize($input){
        $d=SDW_Core::defaults();$out=array();
        foreach(array('enabled','required','iran_holidays','iran_occasions','show_occasion','motorcycle_all_days','cart_enabled','show_calendar_legend','show_selected_summary','show_first_available') as $k)$out[$k]=!empty($input[$k])?'yes':'no';
        $out['language']=in_array($input['language']??'',array('fa','en','auto'),true)?$input['language']:$d['language'];
        $out['calendar']=in_array($input['calendar']??'',array('jalali','gregorian'),true)?$input['calendar']:$d['calendar'];
        $out['delay_days']=max(0,min(30,intval($input['delay_days']??$d['delay_days'])));$out['max_days']=max(1,min(60,intval($input['max_days']??$d['max_days'])));
        $out['cutoff_time']=preg_match('/^([01]\d|2[0-3]):[0-5]\d$/',$input['cutoff_time']??'')?$input['cutoff_time']:$d['cutoff_time'];
        $out['weekend_days']=array_values(array_intersect(array_map('strval',(array)($input['weekend_days']??array())),array('0','1','2','3','4','5','6')));
        $out['holidays']=sanitize_textarea_field($input['holidays']??'');$out['motorcycle_keywords']=sanitize_text_field($input['motorcycle_keywords']??'');
        $out['display_style']=in_array($input['display_style']??'',array('calendar','cards','pills','select','list'),true)?$input['display_style']:$d['display_style'];
        $out['calendar_density']=in_array($input['calendar_density']??'',array('compact','comfortable'),true)?$input['calendar_density']:$d['calendar_density'];
        foreach(array('title_fa','subtitle_fa','title_en','subtitle_en') as $k)$out[$k]=sanitize_text_field($input[$k]??$d[$k]);
        $out['accent']=sanitize_hex_color($input['accent']??$d['accent'])?:$d['accent'];return $out;
    }
    private function txt($fa,$en){return 'fa'===SDW_Core::language()?$fa:$en;}
    private function toggle($key,$label,$value,$hint=''){?><label class="sdw-toggle-row"><span><b><?php echo esc_html($label);?></b><?php if($hint):?><small><?php echo esc_html($hint);?></small><?php endif;?></span><span class="sdw-switch"><input type="checkbox" name="sdw_settings[<?php echo esc_attr($key);?>]" value="1" <?php checked('yes',$value);?>><i></i></span></label><?php }
    public function page(){
        if(!current_user_can('manage_woocommerce'))return;$o=SDW_Core::options();$preview=SDW_Delivery::available_dates(false);$rtl='fa'===SDW_Core::language();
        ?>
        <div class="wrap sdw-wrap" dir="<?php echo $rtl?'rtl':'ltr';?>">
            <div class="sdw-hero"><div><span class="sdw-badge">Smart Delivery</span><h1><?php echo esc_html($this->txt('تحویل هوشمند ووکامرس','Smart Delivery for WooCommerce'));?></h1><p><?php echo esc_html($this->txt('تقویم ایران، تعطیلات رسمی و انتخاب تاریخ مدرن برای سبد خرید و تسویه‌حساب.','Iran calendar, official holidays and a modern delivery selector for cart and checkout.'));?></p></div><div class="sdw-version">v<?php echo esc_html(SDW_VERSION);?></div></div>
            <div class="sdw-grid">
                <div class="sdw-card sdw-stat"><span><?php echo esc_html($this->txt('تقویم ایران','Iran calendar'));?></span><strong><?php echo 'yes'===$o['iran_holidays']?esc_html($this->txt('فعال','Active')):esc_html($this->txt('خاموش','Off'));?></strong></div>
                <div class="sdw-card sdw-stat"><span><?php echo esc_html($this->txt('فاصله ارسال','Delivery delay'));?></span><strong><?php echo intval($o['delay_days']).' '.esc_html($this->txt('روز','day(s)'));?></strong></div>
                <div class="sdw-card sdw-stat"><span><?php echo esc_html($this->txt('نمایش انتخابگر','Selector'));?></span><strong><?php echo esc_html($o['display_style']);?></strong></div>
            </div>
            <form method="post" action="options.php"><?php settings_fields('sdw_group');?>
                <div class="sdw-card sdw-section"><h2><?php echo esc_html($this->txt('زبان و تقویم','Language & calendar'));?></h2><div class="sdw-form-grid">
                    <label><span><?php echo esc_html($this->txt('زبان رابط','Interface language'));?></span><select name="sdw_settings[language]"><option value="fa" <?php selected($o['language'],'fa');?>>فارسی</option><option value="en" <?php selected($o['language'],'en');?>>English</option><option value="auto" <?php selected($o['language'],'auto');?>><?php echo esc_html($this->txt('خودکار','Automatic'));?></option></select></label>
                    <label><span><?php echo esc_html($this->txt('نوع تقویم','Calendar type'));?></span><select name="sdw_settings[calendar]"><option value="jalali" <?php selected($o['calendar'],'jalali');?>><?php echo esc_html($this->txt('شمسی ایران','Persian Jalali'));?></option><option value="gregorian" <?php selected($o['calendar'],'gregorian');?>><?php echo esc_html($this->txt('میلادی','Gregorian'));?></option></select></label>
                </div></div>

                <div class="sdw-card sdw-section"><h2><?php echo esc_html($this->txt('تقویم و تعطیلات ایران','Iran occasions & holidays'));?></h2>
                    <?php $this->toggle('iran_holidays',$this->txt('تعطیلات رسمی ایران در ارسال لحاظ شود','Use Iran official holidays for delivery'),$o['iran_holidays'],$this->txt('داده داخلی سال‌های ۱۴۰۴، ۱۴۰۵ و ۱۴۰۶؛ بدون نیاز به API.','Built-in data for 1404, 1405 and 1406; no API required.'));?>
                    <?php $this->toggle('iran_occasions',$this->txt('مناسبت‌های ایرانی فعال باشد','Enable Iranian occasions'),$o['iran_occasions'],$this->txt('مناسبت‌های مهم ملی، فرهنگی و مذهبی به تاریخ‌ها اضافه می‌شوند.','Adds important national, cultural and religious occasions.'));?>
                    <?php $this->toggle('show_occasion',$this->txt('نام مناسبت روی کارت تاریخ نمایش داده شود','Show occasion title on date cards'),$o['show_occasion']);?>
                    <div class="sdw-days"><?php $days=array(6=>array('شنبه','Saturday'),0=>array('یکشنبه','Sunday'),1=>array('دوشنبه','Monday'),2=>array('سه‌شنبه','Tuesday'),3=>array('چهارشنبه','Wednesday'),4=>array('پنجشنبه','Thursday'),5=>array('جمعه','Friday'));foreach($days as $v=>$names):?><label><input type="checkbox" name="sdw_settings[weekend_days][]" value="<?php echo esc_attr($v);?>" <?php checked(in_array((string)$v,(array)$o['weekend_days'],true));?>><span><?php echo esc_html($rtl?$names[0]:$names[1]);?></span></label><?php endforeach;?></div>
                    <label class="sdw-full"><span><?php echo esc_html($this->txt('تعطیلات و تعطیلی‌های موردی فروشگاه','Store-specific / temporary closures'));?></span><textarea name="sdw_settings[holidays]" rows="5" placeholder="<?php echo 'jalali'===$o['calendar']?'1405/07/10':'2026-10-02';?>"><?php echo esc_textarea($o['holidays']);?></textarea><small><?php echo esc_html($this->txt('برای تعطیلی‌های اضطراری یا اختصاصی فروشگاه؛ هر تاریخ در یک خط.','For emergency or store-specific closures; one date per line.'));?></small></label>
                </div>

                <div class="sdw-card sdw-section"><h2><?php echo esc_html($this->txt('قوانین ارسال','Delivery rules'));?></h2><div class="sdw-form-grid">
                    <?php $this->toggle('enabled',$this->txt('انتخاب تاریخ تحویل فعال باشد','Enable delivery date selection'),$o['enabled']);?>
                    <?php $this->toggle('required',$this->txt('انتخاب تاریخ اجباری باشد','Require a delivery date'),$o['required']);?>
                    <?php $this->toggle('cart_enabled',$this->txt('انتخاب تاریخ داخل سبد خرید نمایش داده شود','Show date selector in cart'),$o['cart_enabled']);?>
                    <label><span><?php echo esc_html($this->txt('روزهای استراحت بعد از سفارش','Blocked days after order'));?></span><select name="sdw_settings[delay_days]"><?php for($i=0;$i<=7;$i++):?><option value="<?php echo $i;?>" <?php selected((int)$o['delay_days'],$i);?>><?php echo esc_html($i.' '.$this->txt('روز','day(s)'));?></option><?php endfor;?></select><small><?php echo esc_html($this->txt('۱ روز یعنی فردا استراحت و ارسال از پس‌فردا؛ تعطیلات نیز رد می‌شوند.','1 day blocks tomorrow; delivery starts the day after, with holidays skipped.'));?></small></label>
                    <label><span><?php echo esc_html($this->txt('تعداد تاریخ قابل انتخاب','Selectable dates'));?></span><input type="number" min="1" max="60" name="sdw_settings[max_days]" value="<?php echo esc_attr($o['max_days']);?>"></label>
                    <label><span><?php echo esc_html($this->txt('ساعت پایان پذیرش روزانه','Daily cutoff'));?></span><input type="time" name="sdw_settings[cutoff_time]" value="<?php echo esc_attr($o['cutoff_time']);?>"></label>
                </div></div>

                <div class="sdw-card sdw-section"><h2><?php echo esc_html($this->txt('ظاهر انتخاب تاریخ','Date selector UI'));?></h2><p class="description"><?php echo esc_html($this->txt('تقویم کوچک، حالت پیشنهادی و پیش‌فرض است؛ کاربر تاریخ را مستقیم از شبکه تقویم انتخاب می‌کند.','Mini calendar is the recommended default; customers pick directly from the calendar grid.'));?></p><div class="sdw-style-grid">
                    <?php $styles=array('calendar'=>array('تقویم کوچک','Mini calendar','▦'), 'cards'=>array('کارت گرافیکی','Graphic cards','▤'), 'pills'=>array('کپسولی افقی','Horizontal pills','●'), 'list'=>array('لیست مینیمال','Minimal list','☷'), 'select'=>array('انتخاب کشویی','Dropdown','⌄'));foreach($styles as $key=>$s):?>
                    <label class="sdw-style-option"><input type="radio" name="sdw_settings[display_style]" value="<?php echo esc_attr($key);?>" <?php checked($o['display_style'],$key);?>><span><i><?php echo esc_html($s[2]);?></i><b><?php echo esc_html($rtl?$s[0]:$s[1]);?></b><small><?php echo esc_html($key);?></small></span></label><?php endforeach;?>
                </div><div class="sdw-form-grid">
                    <label><span><?php echo esc_html($this->txt('اندازه تقویم','Calendar size'));?></span><select name="sdw_settings[calendar_density]"><option value="compact" <?php selected($o['calendar_density'],'compact');?>><?php echo esc_html($this->txt('کوچک و جمع‌وجور','Compact'));?></option><option value="comfortable" <?php selected($o['calendar_density'],'comfortable');?>><?php echo esc_html($this->txt('کمی بزرگ‌تر','Comfortable'));?></option></select></label>
                    <?php $this->toggle('show_calendar_legend',$this->txt('راهنمای رنگ‌های تقویم نمایش داده شود','Show calendar legend'),$o['show_calendar_legend']);?>
                    <?php $this->toggle('show_selected_summary',$this->txt('خلاصه تاریخ انتخاب‌شده زیر تقویم نمایش داده شود','Show selected-date summary'),$o['show_selected_summary']);?>
                    <?php $this->toggle('show_first_available',$this->txt('اولین روز قابل ارسال مشخص شود','Highlight first available date'),$o['show_first_available']);?>
                </div><div class="sdw-form-grid sdw-copy-grid">
                    <label><span>عنوان فارسی</span><input type="text" name="sdw_settings[title_fa]" value="<?php echo esc_attr($o['title_fa']);?>"></label><label><span>توضیح فارسی</span><input type="text" name="sdw_settings[subtitle_fa]" value="<?php echo esc_attr($o['subtitle_fa']);?>"></label>
                    <label><span>English title</span><input type="text" name="sdw_settings[title_en]" value="<?php echo esc_attr($o['title_en']);?>"></label><label><span>English subtitle</span><input type="text" name="sdw_settings[subtitle_en]" value="<?php echo esc_attr($o['subtitle_en']);?>"></label>
                    <label><span><?php echo esc_html($this->txt('رنگ اصلی','Accent color'));?></span><input type="color" name="sdw_settings[accent]" value="<?php echo esc_attr($o['accent']);?>"></label>
                </div></div>

                <div class="sdw-card sdw-section"><h2><?php echo esc_html($this->txt('پیک موتوری','Motorcycle courier'));?></h2><?php $this->toggle('motorcycle_all_days',$this->txt('پیک موتوری حتی در تعطیلات قابل انتخاب باشد','Allow motorcycle courier even on holidays'),$o['motorcycle_all_days']);?><label class="sdw-full"><span><?php echo esc_html($this->txt('کلیدواژه‌های تشخیص روش ارسال','Shipping method keywords'));?></span><input type="text" name="sdw_settings[motorcycle_keywords]" value="<?php echo esc_attr($o['motorcycle_keywords']);?>"></label></div>

                <div class="sdw-card sdw-section"><h2><?php echo esc_html($this->txt('پیش‌نمایش روزهای قابل ارسال','Upcoming delivery preview'));?></h2><div class="sdw-preview-days"><?php foreach(array_slice($preview,0,8) as $date):$occasion=SDW_Delivery::occasion($date);?><div><strong><?php echo esc_html(SDW_Delivery::weekday((int)$date->format('w')));?></strong><span><?php echo esc_html(SDW_Delivery::formatted_date($date));?></span><?php if($occasion):?><small><?php echo esc_html($occasion);?></small><?php endif;?></div><?php endforeach;?></div></div>
                <?php submit_button($this->txt('ذخیره تنظیمات','Save settings'),'primary sdw-save');?>
            </form>
        </div><?php
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class SDW_Core {
    private static $instance=null;
    public static function instance(){ if(null===self::$instance) self::$instance=new self(); return self::$instance; }
    private function __construct(){ $this->maybe_upgrade(); add_action('init',array($this,'load_textdomain')); add_action('admin_notices',array($this,'woocommerce_notice')); if(is_admin()) new SDW_Admin(); new SDW_Checkout(); }
    private function maybe_upgrade(){ $installed=(string)get_option('sdw_plugin_version',''); if(''===$installed||version_compare($installed,'1.3.0','<')){ $settings=get_option('sdw_settings',array()); $settings=wp_parse_args($settings,self::defaults()); $settings['display_style']='calendar'; update_option('sdw_settings',$settings); update_option('sdw_plugin_version',SDW_VERSION); } elseif($installed!==SDW_VERSION){ update_option('sdw_plugin_version',SDW_VERSION); } }
    public static function defaults(){ return array(
        'enabled'=>'yes','required'=>'yes','language'=>'fa','calendar'=>'jalali','delay_days'=>1,'max_days'=>14,'cutoff_time'=>'18:00',
        'weekend_days'=>array('5'),'holidays'=>'','iran_holidays'=>'yes','iran_occasions'=>'yes','show_occasion'=>'yes',
        'motorcycle_keywords'=>'پیک موتوری,موتوری,motorcycle','motorcycle_all_days'=>'yes','display_style'=>'calendar','cart_enabled'=>'yes',
        'calendar_density'=>'compact','show_calendar_legend'=>'yes','show_selected_summary'=>'yes','show_first_available'=>'yes',
        'title_fa'=>'زمان تحویل سفارش','subtitle_fa'=>'یک روز مناسب برای دریافت سفارش انتخاب کنید.','title_en'=>'Delivery date','subtitle_en'=>'Choose a convenient day to receive your order.','accent'=>'#111827'
    ); }
    public static function options(){ $raw=get_option('sdw_settings',array()); if(isset($raw['preparation_days'])&&!isset($raw['delay_days']))$raw['delay_days']=(int)$raw['preparation_days']; if(isset($raw['title'])&&!isset($raw['title_fa']))$raw['title_fa']=$raw['title']; if(isset($raw['subtitle'])&&!isset($raw['subtitle_fa']))$raw['subtitle_fa']=$raw['subtitle']; return wp_parse_args($raw,self::defaults()); }
    public static function language(){ $o=self::options(); if('auto'===$o['language']) return 0===strpos(determine_locale(),'fa')?'fa':'en'; return in_array($o['language'],array('fa','en'),true)?$o['language']:'fa'; }
    public static function text($fa,$en){ return 'fa'===self::language()?$fa:$en; }
    public static function activate(){ $old=get_option('sdw_settings',array()); $settings=wp_parse_args($old,self::defaults()); if(!isset($old['display_style']))$settings['display_style']='calendar'; update_option('sdw_settings',$settings); update_option('sdw_plugin_version',SDW_VERSION); }
    public function load_textdomain(){ load_plugin_textdomain('smart-delivery-for-woocommerce',false,dirname(plugin_basename(SDW_FILE)).'/languages'); }
    public function woocommerce_notice(){ if(current_user_can('activate_plugins')&&!class_exists('WooCommerce')) echo '<div class="notice notice-warning"><p><strong>Smart Delivery:</strong> '.esc_html(self::text('برای استفاده از افزونه، ووکامرس باید فعال باشد.','WooCommerce must be active to use this plugin.')).'</p></div>'; }
}

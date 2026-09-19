<?php
/**
 * Plugin Name: ارسال هوشمند ووکامرس
 * Plugin URI: https://github.com/sahandse/smart-delivery-for-woocommerce
 * Description: انتخاب هوشمند تاریخ ارسال سفارش ووکامرس با پشتیبانی از تقویم شمسی/میلادی، تعطیلات، زمان آماده‌سازی، ساعت برش و چند رابط کاربری.
 * Version: 1.1.1
 * Author: Sahand Rezvan
 * Author URI: https://github.com/sahandse
 * Text Domain: smart-delivery-for-woocommerce
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 10.9
 */

defined('ABSPATH') || exit;

final class SDFW_Plugin {
    const VERSION = '1.1.1';
    const OPTION  = 'sdfw_settings';
    const META    = '_sdfw_delivery_date';

    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action('before_woocommerce_init', [$this, 'declare_hpos']);
        add_action('plugins_loaded', [$this, 'boot']);
    }

    public function declare_hpos() {
        if (class_exists('Automattic\\WooCommerce\\Utilities\\FeaturesUtil')) {
            Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                __FILE__,
                true
            );
        }
    }

    public function boot() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'woocommerce_notice']);
            return;
        }

        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_assets']);

        add_action('woocommerce_after_order_notes', [$this, 'checkout_field']);
        add_action('woocommerce_checkout_process', [$this, 'validate_checkout']);
        add_action('woocommerce_checkout_create_order', [$this, 'save_order_meta'], 10, 2);
        add_action('woocommerce_admin_order_data_after_shipping_address', [$this, 'admin_order_meta']);
        add_action('woocommerce_order_details_after_order_table', [$this, 'customer_order_meta']);

        add_action('wp_ajax_sdfw_dates', [$this, 'ajax_dates']);
        add_action('wp_ajax_nopriv_sdfw_dates', [$this, 'ajax_dates']);
    }

    public function woocommerce_notice() {
        echo '<div class="notice notice-error"><p>ارسال هوشمند ووکامرس برای اجرا به WooCommerce نیاز دارد.</p></div>';
    }

    public function defaults() {
        return [
            'enabled' => 'yes',
            'language' => 'auto',
            'calendar' => 'jalali',
            'prep_days' => 1,
            'cutoff' => '16:00',
            'weekly_off' => ['5'],
            'special_holidays' => '',
            'days_ahead' => 14,
            'ui' => 'cards',
            'accent' => '#111827',
            'courier_keywords' => 'courier,motor,peyk,پیک',
            'courier_ignore_weekly_off' => 'yes',
            'label_fa' => 'تاریخ ارسال',
            'label_en' => 'Delivery date',
        ];
    }

    public function settings() {
        return wp_parse_args((array) get_option(self::OPTION, []), $this->defaults());
    }

    public function register_settings() {
        register_setting('sdfw_group', self::OPTION, [$this, 'sanitize_settings']);
    }

    public function sanitize_settings($in) {
        $d = $this->defaults();
        $out = [];
        $out['enabled'] = !empty($in['enabled']) ? 'yes' : 'no';
        $out['language'] = in_array($in['language'] ?? '', ['auto','fa','en'], true) ? $in['language'] : $d['language'];
        $out['calendar'] = in_array($in['calendar'] ?? '', ['jalali','gregorian'], true) ? $in['calendar'] : $d['calendar'];
        $out['prep_days'] = min(7, max(0, absint($in['prep_days'] ?? 1)));
        $out['days_ahead'] = min(60, max(3, absint($in['days_ahead'] ?? 14)));
        $out['cutoff'] = preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $in['cutoff'] ?? '') ? $in['cutoff'] : $d['cutoff'];
        $weekly = isset($in['weekly_off']) ? (array) $in['weekly_off'] : [];
        $out['weekly_off'] = array_values(array_intersect(array_map('strval', range(0,6)), array_map('strval', $weekly)));
        $out['special_holidays'] = sanitize_textarea_field($in['special_holidays'] ?? '');
        $out['ui'] = in_array($in['ui'] ?? '', ['cards','chips','select','list'], true) ? $in['ui'] : $d['ui'];
        $out['accent'] = sanitize_hex_color($in['accent'] ?? '') ?: $d['accent'];
        $out['courier_keywords'] = sanitize_text_field($in['courier_keywords'] ?? $d['courier_keywords']);
        $out['courier_ignore_weekly_off'] = !empty($in['courier_ignore_weekly_off']) ? 'yes' : 'no';
        $out['label_fa'] = sanitize_text_field($in['label_fa'] ?? $d['label_fa']);
        $out['label_en'] = sanitize_text_field($in['label_en'] ?? $d['label_en']);
        return $out;
    }

    public function admin_menu() {
        if (function_exists('s_store_register_submenu')) {
            s_store_register_submenu('smart-delivery-for-woocommerce', 'ارسال هوشمند ووکامرس', [$this, 'settings_page'], 'manage_woocommerce', 'ارسال هوشمند ووکامرس');
            return;
        }
        add_submenu_page(
            'woocommerce',
            'ارسال هوشمند ووکامرس',
            'ارسال هوشمند',
            'manage_woocommerce',
            'smart-delivery-for-woocommerce',
            [$this, 'settings_page']
        );
    }

    public function admin_assets($hook) {
        if (false === strpos($hook, 'smart-delivery-for-woocommerce')) return;
        wp_enqueue_style('sdfw-admin', plugin_dir_url(__FILE__) . 'assets/admin.css', [], self::VERSION);
    }

    public function frontend_assets() {
        if (!is_checkout()) return;
        wp_enqueue_style('sdfw-front', plugin_dir_url(__FILE__) . 'assets/frontend.css', [], self::VERSION);
        wp_enqueue_script('sdfw-front', plugin_dir_url(__FILE__) . 'assets/frontend.js', ['jquery'], self::VERSION, true);
        wp_localize_script('sdfw-front', 'SDFW', [
            'ajax' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sdfw_dates'),
        ]);
    }

    public function settings_page() {
        if (!current_user_can('manage_woocommerce')) return;
        $s = $this->settings();
        $days = [
            0 => 'یکشنبه', 1 => 'دوشنبه', 2 => 'سه‌شنبه',
            3 => 'چهارشنبه', 4 => 'پنجشنبه', 5 => 'جمعه', 6 => 'شنبه'
        ];
        ?>
        <div class="wrap sdfw-admin">
            <div class="sdfw-hero">
                <div>
                    <h1>ارسال هوشمند ووکامرس</h1>
                    <p>مدیریت حرفه‌ای تاریخ ارسال، تعطیلات و تجربه انتخاب تاریخ در تسویه‌حساب.</p>
                </div>
                <span class="sdfw-version">v<?php echo esc_html(self::VERSION); ?></span>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields('sdfw_group'); ?>
                <div class="sdfw-grid">
                    <section class="sdfw-card">
                        <h2>تنظیمات عمومی</h2>
                        <label class="sdfw-switch-row"><span>فعال بودن افزونه</span><input type="checkbox" name="<?php echo self::OPTION; ?>[enabled]" value="1" <?php checked($s['enabled'],'yes'); ?>></label>

                        <label>زبان
                            <select name="<?php echo self::OPTION; ?>[language]">
                                <option value="auto" <?php selected($s['language'],'auto'); ?>>خودکار</option>
                                <option value="fa" <?php selected($s['language'],'fa'); ?>>فارسی</option>
                                <option value="en" <?php selected($s['language'],'en'); ?>>English</option>
                            </select>
                        </label>

                        <label>تقویم
                            <select name="<?php echo self::OPTION; ?>[calendar]">
                                <option value="jalali" <?php selected($s['calendar'],'jalali'); ?>>شمسی ایران</option>
                                <option value="gregorian" <?php selected($s['calendar'],'gregorian'); ?>>میلادی</option>
                            </select>
                        </label>

                        <label>روز آماده‌سازی
                            <input type="number" min="0" max="7" name="<?php echo self::OPTION; ?>[prep_days]" value="<?php echo esc_attr($s['prep_days']); ?>">
                            <small>مثلاً ۱ یعنی اولین تاریخ قابل انتخاب از پس‌فردا محاسبه می‌شود.</small>
                        </label>

                        <label>تعداد روزهای قابل نمایش
                            <input type="number" min="3" max="60" name="<?php echo self::OPTION; ?>[days_ahead]" value="<?php echo esc_attr($s['days_ahead']); ?>">
                        </label>

                        <label>ساعت برش سفارش
                            <input type="time" name="<?php echo self::OPTION; ?>[cutoff]" value="<?php echo esc_attr($s['cutoff']); ?>">
                        </label>
                    </section>

                    <section class="sdfw-card">
                        <h2>تعطیلات</h2>
                        <p class="description">روزهای تعطیل هفتگی را انتخاب کنید.</p>
                        <div class="sdfw-days">
                            <?php foreach ($days as $num => $label): ?>
                                <label><input type="checkbox" name="<?php echo self::OPTION; ?>[weekly_off][]" value="<?php echo esc_attr($num); ?>" <?php checked(in_array((string)$num,(array)$s['weekly_off'],true)); ?>> <?php echo esc_html($label); ?></label>
                            <?php endforeach; ?>
                        </div>
                        <label>تعطیلات خاص
                            <textarea rows="7" name="<?php echo self::OPTION; ?>[special_holidays]" placeholder="2026-09-20&#10;2026-09-21"><?php echo esc_textarea($s['special_holidays']); ?></textarea>
                            <small>هر تاریخ میلادی را در یک خط وارد کنید. این تاریخ‌ها در محاسبات حذف می‌شوند.</small>
                        </label>
                    </section>

                    <section class="sdfw-card">
                        <h2>ظاهر تسویه‌حساب</h2>
                        <label>مدل نمایش
                            <select name="<?php echo self::OPTION; ?>[ui]">
                                <option value="cards" <?php selected($s['ui'],'cards'); ?>>کارت گرافیکی</option>
                                <option value="chips" <?php selected($s['ui'],'chips'); ?>>چیپ</option>
                                <option value="select" <?php selected($s['ui'],'select'); ?>>منوی کشویی</option>
                                <option value="list" <?php selected($s['ui'],'list'); ?>>لیست مینیمال</option>
                            </select>
                        </label>
                        <label>رنگ اصلی <input type="color" name="<?php echo self::OPTION; ?>[accent]" value="<?php echo esc_attr($s['accent']); ?>"></label>
                        <label>عنوان فارسی <input type="text" name="<?php echo self::OPTION; ?>[label_fa]" value="<?php echo esc_attr($s['label_fa']); ?>"></label>
                        <label>عنوان انگلیسی <input type="text" name="<?php echo self::OPTION; ?>[label_en]" value="<?php echo esc_attr($s['label_en']); ?>"></label>
                    </section>

                    <section class="sdfw-card">
                        <h2>پیک موتوری</h2>
                        <label>کلیدواژه‌های روش ارسال
                            <input type="text" name="<?php echo self::OPTION; ?>[courier_keywords]" value="<?php echo esc_attr($s['courier_keywords']); ?>">
                            <small>با کاما جدا کنید؛ مانند courier,motor,peyk,پیک</small>
                        </label>
                        <label class="sdfw-switch-row"><span>پیک از تعطیلی هفتگی عبور کند</span><input type="checkbox" name="<?php echo self::OPTION; ?>[courier_ignore_weekly_off]" value="1" <?php checked($s['courier_ignore_weekly_off'],'yes'); ?>></label>
                    </section>
                </div>
                <?php submit_button('ذخیره تنظیمات'); ?>
            </form>
        </div>
        <?php
    }

    private function is_fa() {
        $s = $this->settings();
        if ('fa' === $s['language']) return true;
        if ('en' === $s['language']) return false;
        return 0 === strpos(determine_locale(), 'fa');
    }

    private function is_courier() {
        if (!WC()->session) return false;
        $chosen = (array) WC()->session->get('chosen_shipping_methods', []);
        if (!$chosen) return false;
        $keywords = array_filter(array_map('trim', explode(',', $this->settings()['courier_keywords'])));
        foreach ($chosen as $method) {
            foreach ($keywords as $k) {
                if ('' !== $k && false !== mb_stripos($method, $k)) return true;
            }
        }
        return false;
    }

    private function holidays() {
        $raw = preg_split('/\r\n|\r|\n/', $this->settings()['special_holidays']);
        return array_values(array_filter(array_map('trim', (array)$raw)));
    }

    private function is_available_date(DateTimeImmutable $date, $courier = false) {
        $s = $this->settings();
        $ymd = $date->format('Y-m-d');
        if (in_array($ymd, $this->holidays(), true)) return false;
        if (!($courier && 'yes' === $s['courier_ignore_weekly_off'])) {
            if (in_array($date->format('w'), (array)$s['weekly_off'], true)) return false;
        }
        return true;
    }

    private function available_dates() {
        $s = $this->settings();
        if ('yes' !== $s['enabled']) return [];

        $tz = wp_timezone();
        $now = new DateTimeImmutable('now', $tz);
        [$h,$m] = array_map('intval', explode(':', $s['cutoff']));
        $cutoff = $now->setTime($h,$m);
        $extra = $now > $cutoff ? 1 : 0;

        // prep_days=1 means a full rest day after order, therefore selectable dates start after 2 calendar days.
        $startOffset = (int)$s['prep_days'] + 1 + $extra;
        $cursor = $now->setTime(0,0)->modify('+' . $startOffset . ' days');
        $courier = $this->is_courier();

        $dates = [];
        $guard = 0;
        while (count($dates) < (int)$s['days_ahead'] && $guard < 120) {
            if ($this->is_available_date($cursor, $courier)) {
                $dates[] = [
                    'value' => $cursor->format('Y-m-d'),
                    'label' => $this->format_date($cursor),
                ];
            }
            $cursor = $cursor->modify('+1 day');
            $guard++;
        }
        return $dates;
    }

    private function format_date(DateTimeImmutable $date) {
        $s = $this->settings();
        if ('jalali' === $s['calendar']) {
            [$jy,$jm,$jd] = $this->gregorian_to_jalali(
                (int)$date->format('Y'),
                (int)$date->format('n'),
                (int)$date->format('j')
            );
            $months = $this->is_fa()
                ? [1=>'فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند']
                : [1=>'Farvardin','Ordibehesht','Khordad','Tir','Mordad','Shahrivar','Mehr','Aban','Azar','Dey','Bahman','Esfand'];
            return sprintf('%d %s %d', $jd, $months[$jm], $jy);
        }
        return wp_date(get_option('date_format'), $date->getTimestamp(), wp_timezone());
    }

    public function checkout_field($checkout) {
        if ('yes' !== $this->settings()['enabled']) return;
        $dates = $this->available_dates();
        if (!$dates) return;
        $s = $this->settings();
        $label = $this->is_fa() ? $s['label_fa'] : $s['label_en'];

        echo '<div id="sdfw-delivery" class="sdfw-ui sdfw-' . esc_attr($s['ui']) . '" style="--sdfw-accent:' . esc_attr($s['accent']) . '">';
        echo '<h3>' . esc_html($label) . '</h3>';

        if ('select' === $s['ui']) {
            echo '<select name="sdfw_delivery_date" id="sdfw_delivery_date" required>';
            echo '<option value="">' . esc_html($this->is_fa() ? 'یک تاریخ را انتخاب کنید' : 'Select a date') . '</option>';
            foreach ($dates as $d) echo '<option value="' . esc_attr($d['value']) . '">' . esc_html($d['label']) . '</option>';
            echo '</select>';
        } else {
            echo '<div class="sdfw-options">';
            foreach ($dates as $i => $d) {
                echo '<label class="sdfw-option"><input type="radio" name="sdfw_delivery_date" value="' . esc_attr($d['value']) . '" ' . ($i === 0 ? 'required' : '') . '><span>' . esc_html($d['label']) . '</span></label>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    public function validate_checkout() {
        if ('yes' !== $this->settings()['enabled']) return;
        $selected = isset($_POST['sdfw_delivery_date']) ? sanitize_text_field(wp_unslash($_POST['sdfw_delivery_date'])) : '';
        if (!$selected) {
            wc_add_notice($this->is_fa() ? 'لطفاً تاریخ ارسال را انتخاب کنید.' : 'Please select a delivery date.', 'error');
            return;
        }
        $allowed = wp_list_pluck($this->available_dates(), 'value');
        if (!in_array($selected, $allowed, true)) {
            wc_add_notice($this->is_fa() ? 'تاریخ ارسال انتخاب‌شده معتبر نیست.' : 'The selected delivery date is not available.', 'error');
        }
    }

    public function save_order_meta($order, $data) {
        if (!empty($_POST['sdfw_delivery_date'])) {
            $value = sanitize_text_field(wp_unslash($_POST['sdfw_delivery_date']));
            if (in_array($value, wp_list_pluck($this->available_dates(), 'value'), true)) {
                $order->update_meta_data(self::META, $value);
            }
        }
    }

    public function admin_order_meta($order) {
        $date = $order->get_meta(self::META);
        if ($date) echo '<p><strong>تاریخ ارسال:</strong> ' . esc_html($this->humanize_saved($date)) . '</p>';
    }

    public function customer_order_meta($order) {
        $date = $order->get_meta(self::META);
        if ($date) echo '<section class="woocommerce-order-details"><p><strong>' . esc_html($this->is_fa() ? 'تاریخ ارسال' : 'Delivery date') . ':</strong> ' . esc_html($this->humanize_saved($date)) . '</p></section>';
    }

    private function humanize_saved($ymd) {
        try {
            return $this->format_date(new DateTimeImmutable($ymd, wp_timezone()));
        } catch (Exception $e) {
            return $ymd;
        }
    }

    public function ajax_dates() {
        check_ajax_referer('sdfw_dates', 'nonce');
        wp_send_json_success($this->available_dates());
    }

    private function gregorian_to_jalali($gy, $gm, $gd) {
        $g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = 355666 + (365*$gy) + intdiv($gy2+3,4) - intdiv($gy2+99,100) + intdiv($gy2+399,400) + $gd + $g_d_m[$gm-1];
        $jy = -1595 + (33 * intdiv($days,12053));
        $days %= 12053;
        $jy += 4 * intdiv($days,1461);
        $days %= 1461;
        if ($days > 365) {
            $jy += intdiv($days-1,365);
            $days = ($days-1)%365;
        }
        if ($days < 186) {
            $jm = 1 + intdiv($days,31);
            $jd = 1 + ($days%31);
        } else {
            $jm = 7 + intdiv($days-186,30);
            $jd = 1 + (($days-186)%30);
        }
        return [$jy,$jm,$jd];
    }
}

SDFW_Plugin::instance();

<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SDW_Iran_Calendar {
    public static function holidays() {
        return array(
            '1404'=>array(
                '1404-01-01'=>array('نوروز','Nowruz','solar'), '1404-01-02'=>array('عید نوروز','Nowruz holiday','solar'), '1404-01-03'=>array('عید نوروز','Nowruz holiday','solar'), '1404-01-04'=>array('عید نوروز','Nowruz holiday','solar'),
                '1404-01-11'=>array('عید سعید فطر','Eid al-Fitr','lunar'), '1404-01-12'=>array('روز جمهوری اسلامی ایران / تعطیل عید فطر','Islamic Republic Day / Eid al-Fitr holiday','mixed'), '1404-01-13'=>array('روز طبیعت','Nature Day','solar'),
                '1404-02-04'=>array('شهادت امام جعفر صادق (ع)','Martyrdom of Imam Ja\'far al-Sadiq','lunar'),
                '1404-03-14'=>array('رحلت امام خمینی / شهادت امام محمد باقر (ع)','Anniversary of Imam Khomeini / Martyrdom of Imam al-Baqir','mixed'), '1404-03-15'=>array('قیام ۱۵ خرداد','15 Khordad Uprising','solar'), '1404-03-17'=>array('عید قربان','Eid al-Adha','lunar'), '1404-03-25'=>array('عید غدیر','Eid al-Ghadir','lunar'),
                '1404-04-14'=>array('تاسوعا','Tasu\'a','lunar'), '1404-04-15'=>array('عاشورا','Ashura','lunar'),
                '1404-05-24'=>array('اربعین حسینی','Arbaeen','lunar'),
                '1404-06-01'=>array('رحلت پیامبر (ص) و شهادت امام حسن مجتبی (ع)','Demise of Prophet Muhammad / Martyrdom of Imam Hasan','lunar'), '1404-06-02'=>array('شهادت امام رضا (ع)','Martyrdom of Imam Reza','lunar'), '1404-06-10'=>array('شهادت امام حسن عسکری (ع)','Martyrdom of Imam Hasan al-Askari','lunar'), '1404-06-19'=>array('ولادت پیامبر (ص) و امام صادق (ع)','Birth of Prophet Muhammad and Imam al-Sadiq','lunar'),
                '1404-09-03'=>array('شهادت حضرت فاطمه زهرا (س)','Martyrdom of Fatimah al-Zahra','lunar'),
                '1404-10-12'=>array('ولادت امام علی (ع) و روز پدر','Birth of Imam Ali / Father\'s Day','lunar'), '1404-10-26'=>array('مبعث پیامبر (ص)','Prophet\'s Ascension Mission','lunar'),
                '1404-11-14'=>array('نیمه شعبان','Mid-Sha\'ban','lunar'), '1404-11-22'=>array('پیروزی انقلاب اسلامی','Anniversary of the Islamic Revolution','solar'),
                '1404-12-19'=>array('شهادت امام علی (ع)','Martyrdom of Imam Ali','lunar'), '1404-12-29'=>array('ملی شدن صنعت نفت / عید فطر','Nationalization of the Oil Industry / Eid al-Fitr','mixed')
            ),
            '1405'=>array(
                '1405-01-01'=>array('نوروز / تعطیل عید فطر','Nowruz / Eid al-Fitr holiday','mixed'), '1405-01-02'=>array('عید نوروز','Nowruz holiday','solar'), '1405-01-03'=>array('عید نوروز','Nowruz holiday','solar'), '1405-01-04'=>array('عید نوروز','Nowruz holiday','solar'),
                '1405-01-12'=>array('روز جمهوری اسلامی ایران','Islamic Republic Day','solar'), '1405-01-13'=>array('روز طبیعت','Nature Day','solar'), '1405-01-24'=>array('شهادت امام جعفر صادق (ع)','Martyrdom of Imam Ja\'far al-Sadiq','lunar'),
                '1405-03-03'=>array('شهادت امام محمد باقر (ع)','Martyrdom of Imam al-Baqir','lunar'), '1405-03-06'=>array('عید قربان','Eid al-Adha','lunar'), '1405-03-14'=>array('رحلت امام خمینی / عید غدیر','Anniversary of Imam Khomeini / Eid al-Ghadir','mixed'), '1405-03-15'=>array('قیام ۱۵ خرداد','15 Khordad Uprising','solar'),
                '1405-04-03'=>array('تاسوعا','Tasu\'a','lunar'), '1405-04-04'=>array('عاشورا','Ashura','lunar'),
                '1405-05-13'=>array('اربعین حسینی','Arbaeen','lunar'), '1405-05-21'=>array('رحلت پیامبر (ص) و شهادت امام حسن مجتبی (ع)','Demise of Prophet Muhammad / Martyrdom of Imam Hasan','lunar'), '1405-05-22'=>array('شهادت امام رضا (ع)','Martyrdom of Imam Reza','lunar'), '1405-05-30'=>array('شهادت امام حسن عسکری (ع)','Martyrdom of Imam Hasan al-Askari','lunar'),
                '1405-06-08'=>array('ولادت پیامبر (ص) و امام صادق (ع)','Birth of Prophet Muhammad and Imam al-Sadiq','lunar'),
                '1405-08-22'=>array('شهادت حضرت فاطمه زهرا (س)','Martyrdom of Fatimah al-Zahra','lunar'),
                '1405-10-02'=>array('ولادت امام علی (ع) و روز پدر','Birth of Imam Ali / Father\'s Day','lunar'), '1405-10-16'=>array('مبعث پیامبر (ص)','Prophet\'s Ascension Mission','lunar'),
                '1405-11-04'=>array('نیمه شعبان','Mid-Sha\'ban','lunar'), '1405-11-22'=>array('پیروزی انقلاب اسلامی','Anniversary of the Islamic Revolution','solar'),
                '1405-12-09'=>array('شهادت امام علی (ع)','Martyrdom of Imam Ali','lunar'), '1405-12-19'=>array('عید سعید فطر','Eid al-Fitr','lunar'), '1405-12-20'=>array('تعطیل عید فطر','Eid al-Fitr holiday','lunar'), '1405-12-29'=>array('ملی شدن صنعت نفت','Nationalization of the Oil Industry','solar')
            ),
            '1406'=>array(
                '1406-01-01'=>array('نوروز','Nowruz','solar'), '1406-01-02'=>array('عید نوروز','Nowruz holiday','solar'), '1406-01-03'=>array('عید نوروز','Nowruz holiday','solar'), '1406-01-04'=>array('عید نوروز','Nowruz holiday','solar'), '1406-01-12'=>array('روز جمهوری اسلامی ایران','Islamic Republic Day','solar'), '1406-01-13'=>array('روز طبیعت','Nature Day','solar'), '1406-01-14'=>array('شهادت امام جعفر صادق (ع)','Martyrdom of Imam Ja\'far al-Sadiq','lunar'),
                '1406-02-24'=>array('شهادت امام محمد باقر (ع)','Martyrdom of Imam al-Baqir','lunar'), '1406-02-27'=>array('عید قربان','Eid al-Adha','lunar'),
                '1406-03-04'=>array('عید غدیر','Eid al-Ghadir','lunar'), '1406-03-14'=>array('رحلت امام خمینی','Anniversary of Imam Khomeini','solar'), '1406-03-15'=>array('قیام ۱۵ خرداد','15 Khordad Uprising','solar'), '1406-03-24'=>array('تاسوعا','Tasu\'a','lunar'), '1406-03-25'=>array('عاشورا','Ashura','lunar'),
                '1406-05-03'=>array('اربعین حسینی','Arbaeen','lunar'), '1406-05-11'=>array('رحلت پیامبر (ص) و شهادت امام حسن مجتبی (ع)','Demise of Prophet Muhammad / Martyrdom of Imam Hasan','lunar'), '1406-05-12'=>array('شهادت امام رضا (ع)','Martyrdom of Imam Reza','lunar'), '1406-05-20'=>array('شهادت امام حسن عسکری (ع)','Martyrdom of Imam Hasan al-Askari','lunar'), '1406-05-29'=>array('ولادت پیامبر (ص) و امام صادق (ع)','Birth of Prophet Muhammad and Imam al-Sadiq','lunar'),
                '1406-08-12'=>array('شهادت حضرت فاطمه زهرا (س)','Martyrdom of Fatimah al-Zahra','lunar'), '1406-09-21'=>array('ولادت امام علی (ع) و روز پدر','Birth of Imam Ali / Father\'s Day','lunar'), '1406-10-05'=>array('مبعث پیامبر (ص)','Prophet\'s Ascension Mission','lunar'), '1406-10-23'=>array('نیمه شعبان','Mid-Sha\'ban','lunar'), '1406-11-22'=>array('پیروزی انقلاب اسلامی','Anniversary of the Islamic Revolution','solar'), '1406-11-28'=>array('شهادت امام علی (ع)','Martyrdom of Imam Ali','lunar'), '1406-12-08'=>array('عید سعید فطر','Eid al-Fitr','lunar'), '1406-12-09'=>array('تعطیل عید فطر','Eid al-Fitr holiday','lunar'), '1406-12-29'=>array('ملی شدن صنعت نفت','Nationalization of the Oil Industry','solar')
            )
        );
    }

    public static function occasions() {
        return array(
            '01-01'=>array('آغاز نوروز','Nowruz'), '01-06'=>array('زادروز زرتشت / روز امید','Zoroaster\'s Birthday / Day of Hope'), '01-20'=>array('روز ملی فناوری هسته‌ای','National Nuclear Technology Day'), '01-23'=>array('روز دندانپزشک','Dentist Day'), '01-25'=>array('بزرگداشت عطار نیشابوری','Attar of Nishapur Day'), '01-29'=>array('روز ارتش','Army Day'),
            '02-01'=>array('روز سعدی','Saadi Day'), '02-03'=>array('روز معمار و بزرگداشت شیخ بهایی','Architect Day / Sheikh Bahaei Day'), '02-09'=>array('روز شوراها','Councils Day'), '02-10'=>array('روز ملی خلیج فارس','National Persian Gulf Day'), '02-12'=>array('روز معلم','Teacher\'s Day'), '02-15'=>array('روز شیراز','Shiraz Day'), '02-25'=>array('روز فردوسی','Ferdowsi Day'), '02-28'=>array('بزرگداشت حکیم عمر خیام','Omar Khayyam Day'),
            '03-03'=>array('سالروز آزادسازی خرمشهر','Liberation of Khorramshahr'), '03-14'=>array('رحلت امام خمینی','Anniversary of Imam Khomeini'), '03-15'=>array('قیام ۱۵ خرداد','15 Khordad Uprising'), '03-25'=>array('روز ملی گل و گیاه','National Flower and Plant Day'),
            '04-01'=>array('روز اصناف','Guilds Day'), '04-07'=>array('روز قوه قضاییه','Judiciary Day'), '04-10'=>array('روز صنعت و معدن','Industry and Mining Day'), '04-13'=>array('جشن تیرگان','Tirgan Festival'),
            '05-14'=>array('روز حقوق بشر اسلامی','Islamic Human Rights Day'), '05-17'=>array('روز خبرنگار','Journalist Day'), '05-28'=>array('سالروز کودتای ۲۸ مرداد','1953 Coup Anniversary'),
            '06-01'=>array('روز پزشک و بزرگداشت ابن‌سینا','Physician Day / Avicenna Day'), '06-04'=>array('روز کارمند','Employee Day'), '06-05'=>array('روز داروساز','Pharmacist Day'), '06-13'=>array('روز تعاون','Cooperatives Day'), '06-27'=>array('روز شعر و ادب فارسی','Persian Poetry and Literature Day'), '06-31'=>array('آغاز هفته دفاع مقدس','Sacred Defense Week'),
            '07-07'=>array('روز آتش‌نشانی و ایمنی','Firefighting and Safety Day'), '07-08'=>array('بزرگداشت مولوی','Rumi Day'), '07-20'=>array('روز حافظ','Hafez Day'),
            '08-13'=>array('روز دانش‌آموز','Student Day'), '08-24'=>array('روز کتاب و کتابخوانی','Book and Reading Day'),
            '09-16'=>array('روز دانشجو','University Student Day'), '09-25'=>array('روز پژوهش','Research Day'),
            '10-05'=>array('روز ایمنی در برابر زلزله','Earthquake Safety Day'), '10-20'=>array('سالروز شهادت امیرکبیر','Amir Kabir Memorial Day'),
            '11-22'=>array('سالروز پیروزی انقلاب اسلامی','Anniversary of the Islamic Revolution'),
            '12-05'=>array('روز مهندس','Engineer Day'), '12-15'=>array('روز درختکاری','Arbor Day'), '12-29'=>array('روز ملی شدن صنعت نفت','Nationalization of the Oil Industry')
        );
    }

    public static function jalali_key( DateTimeImmutable $date ) {
        list($y,$m,$d)=SDW_Calendar::gregorian_to_jalali((int)$date->format('Y'),(int)$date->format('n'),(int)$date->format('j'));
        return sprintf('%04d-%02d-%02d',$y,$m,$d);
    }
    public static function holiday_info( DateTimeImmutable $date ) {
        $key=self::jalali_key($date); $year=substr($key,0,4); $all=self::holidays();
        return isset($all[$year][$key]) ? $all[$year][$key] : null;
    }
    public static function is_holiday( DateTimeImmutable $date ) { return null !== self::holiday_info($date); }
    public static function occasion_info( DateTimeImmutable $date ) {
        $key=self::jalali_key($date); $md=substr($key,5); $all=self::occasions();
        if(isset($all[$md])) return $all[$md];
        $h=self::holiday_info($date); return $h ? array($h[0],$h[1]) : null;
    }
    public static function label( DateTimeImmutable $date, $lang='fa' ) {
        $info=self::holiday_info($date); if(!$info) $info=self::occasion_info($date); if(!$info) return '';
        return 'fa'===$lang ? $info[0] : $info[1];
    }
    public static function supported_years(){ return array('1404','1405','1406'); }
}

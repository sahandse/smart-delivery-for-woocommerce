<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SDW_Calendar {
    public static function gregorian_to_jalali( $gy, $gm, $gd ) {
        $g_d_m = array(0,31,59,90,120,151,181,212,243,273,304,334);
        if ( $gy > 1600 ) { $jy = 979; $gy -= 1600; } else { $jy = 0; $gy -= 621; }
        $gy2 = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
        $days = 365*$gy + intdiv($gy2+3,4) - intdiv($gy2+99,100) + intdiv($gy2+399,400) - 80 + $gd + $g_d_m[$gm-1];
        $jy += 33 * intdiv($days,12053); $days %= 12053;
        $jy += 4 * intdiv($days,1461); $days %= 1461;
        if ( $days > 365 ) { $jy += intdiv($days-1,365); $days = ($days-1)%365; }
        if ( $days < 186 ) { $jm = 1 + intdiv($days,31); $jd = 1 + ($days%31); }
        else { $jm = 7 + intdiv($days-186,30); $jd = 1 + (($days-186)%30); }
        return array($jy,$jm,$jd);
    }

    public static function jalali_to_gregorian( $jy, $jm, $jd ) {
        if ( $jy > 979 ) { $gy = 1600; $jy -= 979; } else { $gy = 621; }
        $days = 365*$jy + intdiv($jy,33)*8 + intdiv(($jy%33)+3,4) + 78 + $jd + (($jm<7) ? ($jm-1)*31 : (($jm-7)*30+186));
        $gy += 400 * intdiv($days,146097); $days %= 146097;
        if ( $days > 36524 ) {
            $gy += 100 * intdiv(--$days,36524); $days %= 36524;
            if ( $days >= 365 ) $days++;
        }
        $gy += 4 * intdiv($days,1461); $days %= 1461;
        if ( $days > 365 ) { $gy += intdiv($days-1,365); $days = ($days-1)%365; }
        $gd = $days + 1;
        $sal_a = array(0,31,((($gy%4==0)&&($gy%100!=0))||($gy%400==0))?29:28,31,30,31,30,31,31,30,31,30,31);
        for ( $gm=1; $gm<=12 && $gd>$sal_a[$gm]; $gm++ ) $gd -= $sal_a[$gm];
        return array($gy,$gm,$gd);
    }

    public static function fa_digits( $value ) {
        return strtr( (string) $value, array('0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹') );
    }

    public static function en_digits( $value ) {
        return strtr( (string) $value, array('۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9') );
    }

    public static function format( DateTimeImmutable $date, $calendar = 'gregorian', $lang = 'fa' ) {
        if ( 'jalali' === $calendar ) {
            list($y,$m,$d) = self::gregorian_to_jalali((int)$date->format('Y'),(int)$date->format('n'),(int)$date->format('j'));
            $out = sprintf('%04d/%02d/%02d',$y,$m,$d);
            return 'fa' === $lang ? self::fa_digits($out) : $out;
        }
        $out = $date->format('Y/m/d');
        return 'fa' === $lang ? self::fa_digits($out) : $out;
    }

    public static function normalize_holiday( $value, $calendar = 'gregorian' ) {
        $value = trim(self::en_digits($value));
        $value = str_replace('/', '-', $value);
        if ( ! preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $m) ) return '';
        $y=(int)$m[1]; $mo=(int)$m[2]; $d=(int)$m[3];
        if ( 'jalali' === $calendar ) {
            list($y,$mo,$d) = self::jalali_to_gregorian($y,$mo,$d);
        }
        if ( ! checkdate($mo,$d,$y) ) return '';
        return sprintf('%04d-%02d-%02d',$y,$mo,$d);
    }
}

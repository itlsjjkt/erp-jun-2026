<?php

use Carbon\Carbon;

function idDate($datetime=null, $format=null)
{
	$datetime = $datetime ? $datetime:Carbon::now();
    $format   = $format  ? $format : 'd M Y';
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->format($format);
}


function tomorrow($format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	return Carbon::tomorrow()->format($format);
}

function yesterday($format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	return Carbon::yesterday()->format($format);
}

function nextDay($datetime=null, $day = null, $format=null)
{
	$day = strtoupper($day);
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	$days = ['SUNDAY' => Carbon::SUNDAY, 'MONDAY' => Carbon::MONDAY, 'TUESDAY' => Carbon::TUESDAY, 'WEDNESDAY' => Carbon::WEDNESDAY, 'THURSDAY' => Carbon::THURSDAY, 'FRIDAY' => Carbon::FRIDAY, 'SATURDAY' => Carbon::SATURDAY];
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->next($days[$day])->format($format);
}

function dayOfWeek($datetime=null)
{
	$days = ['Sunday','Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
	$datetime = $datetime ? $datetime:Carbon::now();
	return $days[Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->dayOfWeek];
}

function ukDate($datetime=null, $timestamp=false)
{
	$datetime = $datetime ? $datetime:Carbon::now();
	$timestamp = $timestamp ? 'd/m/Y H:ia':'d/m/Y';
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->format($timestamp);
}

function ukDateToDate($datetime=null, $timestamp=false)
{
	$datetime = $datetime ? $datetime:Carbon::now();
	$format = $timestamp ? 'd/m/Y H:i:s':'d/m/Y';
	$timestamp = $timestamp ? 'Y-m-d H:i:s':'Y-m-d';
	return Carbon::createFromFormat($format, $datetime)->format($timestamp);
}

function humanDate($datetime)
{
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->diffForHumans();
}

function age($datetime)
{
	return Carbon::createFromFormat('Y-m-d', $datetime)->age;
}

function weekend($datetime=null)
{
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->isWeekend();
}

function diffInDays($datetime)
{
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->diffInDays();
}

function addYears($datetime=null, $years = null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->addYears($years)->format($format);
}

function addMonths($datetime=null, $months = null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->addMonths($months)->format($format);
}

function addWeeks($datetime=null, $weeks = null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->addWeeks($weeks)->format($format);
}

function addDays($datetime=null, $days = null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->addDays($days)->format($format);
}

function startOfDay($datetime=null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->startOfDay()->format($format);
}

function endOfDay($datetime=null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->endOfDay()->format($format);
}

function startOfWeek($datetime=null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->startOfWeek()->format($format);
}

function endOfWeek($datetime=null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->endOfWeek()->format($format);
}

function startOfMonth($datetime=null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->startOfMonth()->format($format);
}

function endOfMonth($datetime=null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->endOfMonth()->format($format);
}

function startOfYear($datetime=null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->startOfYear()->format($format);
}

function endOfYear($datetime=null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->endOfYear()->format($format);
}

function startOfDecade($datetime=null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->startOfDecade()->format($format);
}

function endOfDecade($datetime=null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->endOfDecade()->format($format);
}

function startOfCentury($datetime=null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->startOfCentury()->format($format);
}

function endOfCentury($datetime=null, $format=null)
{
	$format = $format ? $format:'Y-m-d H:i:s';
	$datetime = $datetime ? $datetime:Carbon::now();
	return Carbon::createFromFormat('Y-m-d H:i:s', $datetime)->endOfCentury()->format($format);
}

function getRomawi($bln){
    switch ($bln){
            case 1: 
                return "I";
                break;
            case 2:
                return "II";
                break;
            case 3:
                return "III";
                break;
            case 4:
                return "IV";
                break;
            case 5:
                return "V";
                break;
            case 6:
                return "VI";
                break;
            case 7:
                return "VII";
                break;
            case 8:
                return "VIII";
                break;
            case 9:
                return "IX";
                break;
            case 10:
                return "X";
                break;
            case 11:
                return "XI";
                break;
            case 12:
                return "XII";
                break;
      }
}


if ( ! function_exists('getTime'))
{
    function getTime($date){
        $time=substr($date,10,9);
        return $time;
    }
}
# untuk menampilkan tanggal dengan Number Indoensia
if ( ! function_exists('dateID'))
{
	function dateID()
	{
		$date=date("d/m/Y");
        return $date;
	}
}

# untuk menampilkan tanggal dengan format MySQL
if ( ! function_exists('dateMySQL'))
{
	function dateMySQL()
	{
		$date=date("Y-m-d");
        return $date;
	}
}

# untuk menampilkan tanggal dan waktu dengan format MySQL
if ( ! function_exists('datetimeMySQL'))
{
	function datetimeMySQL()
	{
		$datetimes=date("Y-m-d H:i:s");
        return $datetimes;
	}
}


# untuk menampilkan tanggal dari format indonesia ke format MySQL
if ( ! function_exists('datetimeID2MySQL'))
{
    function datetimeID2MySQL($date){
        $th=substr($date,0,4);
        $bulan=substr($date,5,2);
        $tgl=substr($date,8,2);
        $dateIDNum="$tgl/$bulan/$th";
        return $dateIDNum;
    }
}

# untuk menampilkan tanggal dari format MySQL ke format Number Indoensia
if ( ! function_exists('dateNumMySQL2ID'))
{
    function dateNumMySQL2ID($date){
        $th=substr($date,0,4);
        $bulan=substr($date,5,2);
        $tgl=substr($date,8,2);
        $dateIDNum="$tgl/$bulan/$th";
        return $dateIDNum;
    }
}

# untuk menampilkan tanggal dari format MySQL ke format Number Indoensia
# bug jquery
if ( ! function_exists('dateNumMySQL2ID_j'))
{
    function dateNumMySQL2ID_j($date){
        $th=substr($date,0,4);
        $bulan=substr($date,5,2);
        $tgl=substr($date,8,2);
        #$dateIDNum="$tgl/$bulan/$th";
        $dateIDNum="$bulan/$tgl/$th";
        return $dateIDNum;
    }
}
# untuk menampilkan tanggal dari format Number Indonesia ke format MySQL
if ( ! function_exists('dateNumID2MySQL'))
{
    function dateNumID2MySQL($date){
        $th=substr($date,6,4);
        $bulan=substr($date,3,2);
        $tgl=substr($date,0,2);
        $dateIDNum="$th-$bulan-$tgl";
        return $dateIDNum;
    }
}

# untuk mengubah format date MySQL ke format Text Indonesia
# cara penggunaan :
# 1. ambil date dari MySQL dan masukkan ke dalam variabl, misal : $deteMySQL=$row[$dateMySQL];
# 2. masukkan ke dalam fungsi dateTextMySQL2ID, dateTextMySQL2ID($deteMySQL);
if ( ! function_exists('dateTextMySQL2ID'))
{
    function dateTextMySQL2ID($date){
        $th=substr($date,0,4);
        $bulan=substr($date,5,2);
        $tgl=substr($date,8,2);

        $tglDepan=substr($tgl,0,1);
        $tgldiambil=substr($tgl,1,1);

        if($tglDepan=="0"){
           $tglID=$tgldiambil;
        }else{
           $tglID=$tgl;
        }

        if($bulan=="01")
        {
         $dateID ="$tglID Januari $th";
         return $dateID;
        }
        elseif($bulan=="02")
        {
         $dateID ="$tglID Februari $th";
         return $dateID;
        }
        elseif($bulan=="03")
        {
         $dateID ="$tglID Maret $th";
         return $dateID;
        }
        elseif($bulan=="04")
        {
         $dateID ="$tglID April $th";
         return $dateID;
        }
        elseif($bulan=="05")
        {
         $dateID ="$tglID Mei $th";
         return $dateID;
        }
        elseif($bulan=="06")
        {
         $dateID ="$tglID Juni $th";
         return $dateID;
        }
        elseif($bulan=="07")
        {
         $dateID ="$tglID Juli $th";
         return $dateID;
        }
        elseif($bulan=="08")
        {
         $dateID ="$tglID Agustus $th";
         return $dateID;
        }
        if($bulan=="09")
        {
         $dateID ="$tglID September $th";
         return $dateID;
        }
        elseif($bulan=="10")
        {
         $dateID ="$tglID Oktober $th";
         return $dateID;
        }
        elseif($bulan=="11")
        {
         $dateID ="$tglID November $th";
         return $dateID;
        }
        elseif($bulan=="12")
        {
         $dateID ="$tglID Desember $th";
         return $dateID;
        }
    }
}//end function
/**
 * Generate select tag html
 */
if ( ! function_exists('listBulan'))
{
    function listBulan($command="",$selected=''){
         $selected = ($selected=='') ? date('m') : $selected;
        ?>
        <select name="Bulan" <?=$command;?>>
            <option value="0">-- pilih --</option>
            <option value="1" <?if($selected==1)echo "selected=''"?>>Januari</option>
            <option value="2" <?if($selected==2)echo "selected=''"?>>Februari</option>
            <option value="3" <?if($selected==3)echo "selected=''"?>>Maret</option>
            <option value="4" <?if($selected==4)echo "selected=''"?>>April</option>
            <option value="5" <?if($selected==5)echo "selected=''"?>>Mei</option>
            <option value="6" <?if($selected==6)echo "selected=''"?>>Juni</option>
            <option value="7" <?if($selected==7)echo "selected=''"?>>Juli</option>
            <option value="8" <?if($selected==8)echo "selected=''"?>>Agustus</option>
            <option value="9" <?if($selected==9)echo "selected=''"?>>September</option>
            <option value="10" <?if($selected==10)echo "selected=''"?>>Oktober</option>
            <option value="11" <?if($selected==11)echo "selected=''"?>>November</option>
            <option value="12" <?if($selected==12)echo "selected=''"?>>Desember</option>
        </select>
        <?php
    }
}//end function

/**
 * Generate select tag html
 */
if ( ! function_exists('listTahun'))
{
    function listTahun($start='',$end = '',$command="",$selected=''){
         if ($start=='') $start = date('Y');
         $selected = ($selected=='') ? date('Y') : $selected;
         $end = ($end=='') ? date('Y') : $end;
        ?>
        <select name="Tahun" <?=$command;?>>
            <option value="0">-- pilih --</option>
            <?php
            for($i=$start;$i<=$end;$i++){
                ?><option value="<?=$i;?>" <?if($selected==$i)echo "selected=''"?>><?=$i;?></option><?php
            }
            ?>
        </select>
        <?php
    }
}//end function

if ( ! function_exists('dates_between'))
{
    function dates_between($start_date, $end_date = false)
    {
         $listDate = array();
         $listDate[] = $start_date;
         if ( $start_date <> $end_date OR !$end_date)
         {
             $start_date = is_int($start_date) ? $start_date : strtotime($start_date);
             $end_date = is_int($end_date) ? $end_date : strtotime($end_date);

             $end_date -= (60 * 60 * 24);

             $test_date = $start_date;
             $day_incrementer = 1;

             do
             {
                 $test_date = $start_date + ($day_incrementer * 60 * 60 * 24);
                 $listDate[] = date("Y-m-d", $test_date);
             } while ( $test_date <= $end_date && ++$day_incrementer );
         }
        return (isset($listDate) ? $listDate : null);
    }
}

if ( ! function_exists('blnText'))
{
    function blnText($bulan) {
        if($bulan == "1") { return "Januari";}
        if($bulan == "2") { return "Februari";}
        if($bulan == "3") { return "Maret";}
        if($bulan == "4") { return "April";}
        if($bulan == "5") { return "Mei";}
        if($bulan == "6") { return "Juni";}
        if($bulan == "7") { return "Juli";}
        if($bulan == "8") { return "Agustus";}
        if($bulan == "9") { return "September";}
        if($bulan == "10") { return "Oktober";}
        if($bulan == "11") { return "November";}
        if($bulan == "12") { return "Desember";}
        if($bulan == "") { return "N/A";}
    }
}

if ( ! function_exists('bln3Char'))
{
    function bln3Char($bulan) {
        if($bulan == "01") { return "Jan";}
        if($bulan == "02") { return "Feb";}
        if($bulan == "03") { return "Mar";}
        if($bulan == "04") { return "Apr";}
        if($bulan == "05") { return "Mei";}
        if($bulan == "06") { return "Jun";}
        if($bulan == "07") { return "Jul";}
        if($bulan == "08") { return "Agu";}
        if($bulan == "09") { return "Sep";}
        if($bulan == "10") { return "Okt";}
        if($bulan == "11") { return "Nov";}
        if($bulan == "12") { return "Des";}
        if($bulan == "") { return "N/A";}
    }
}

if ( ! function_exists('DateDiff'))
{
    function DateDiff($dt1, $dt2) {
    	$date1 = (strtotime($dt1) != -1) ? strtotime($dt1) : $dt1;
    	$date2 = (strtotime($dt2) != -1) ? strtotime($dt2) : $dt2;
    	$dtDiff = $date1 - $date2;
    	$totalDays = intval($dtDiff/(24*60*60));
    	return $totalDays;
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime($datetime, $time = 'yes') {
        $explode = explode(" ", $datetime);
        if (count($explode)) {
            return dateNumMySQL2ID($explode[0]).($time == 'yes' ? " ".substr($explode[1], 0, 5) : '');
        }
    }
}


function get_interval_in_month($from, $to, $type) {

    $toDate = Carbon::parse($to);
    $fromDate = Carbon::parse($from);

    if($type=='day') return $toDate->diffInDays($fromDate);
    elseif($type=='month') return  $toDate->diffInMonths($fromDate);
    else $toDate->diffInYears($fromDate);

}

function idDate2($datetime=null, $format=null)
{
	$datetime = $datetime ? $datetime:Carbon::now();
    $format   = $format  ? $format : 'd M Y';
	return Carbon::createFromFormat('Y-m-d', $datetime)->format($format);
}

<?php 

session_start();

$img_x          = 90;
$img_y          = 33;
$num_n          = 5;
$font_min_size  = 17;
$lines_n_max    = 1;
$nois_percent   = 2;
$angle_max      = 18;


$font_path = "./fonts/";
$font_arr = array('f3.TTF','f4.TTF','f5.TTF');

$im=imagecreate($img_x, $img_y); 

$text_colors = array();
for ($i=0; $i<5; $i++)
	$text_colors[] = imagecolorallocate($im, $c = rand(0, 255), $c - rand(128, 255), $c - rand(128, 255));
$img_color  = imagecolorallocate($im, 255, 255, 255);

imagefill($im, 0, 0, $img_color); 

$number=''; 

for ($n=0; $n<$num_n; $n++){ 
    $num=rand(0,9); 
    $number.=$num; 
    $font_size=rand($font_min_size, $img_y/2); 
    $angle=rand(360-$angle_max,360+$angle_max); 

    $font_cur=rand(0,count($font_arr)-1); 
    $font_cur=$font_arr[$font_cur];
    $font_cur = $font_path.$font_cur;


    $y=rand(($img_y-$font_size)/4+$font_size, ($img_y-$font_size)/2+$font_size); 

    $x=rand(($img_x/$num_n-$font_size)/2, $img_x/$num_n-$font_size)+$n*$img_x/$num_n; 

    imagettftext($im, $font_size, $angle, $x, $y, $text_colors[rand(0, 4)], $font_cur, $num); 
}; 

$_SESSION["wbch:captcha"] = $number;


$nois_n_pix=round($img_x*$img_y*$nois_percent/100); 

for ($n=0; $n<$nois_n_pix; $n++){ 
    $x=rand(0, $img_x); 
    $y=rand(0, $img_y); 
    imagesetpixel($im, $x, $y, $text_colors[ rand(0, 4) ]); 
}; 

for ($n=0; $n<$nois_n_pix; $n++){ 
    $x=rand(0, $img_x); 
    $y=rand(0, $img_y); 
    imagesetpixel($im, $x, $y, $img_color); 
}; 

$lines_n=rand(0,$lines_n_max); 

for ($n=0; $n<$lines_n; $n++){ 
    $x1=rand(0, $img_x); 
    $y1=rand(0, $img_y); 
    $x2=rand(0, $img_x); 
    $y2=rand(0, $img_y); 
    imageline($im, $x1, $y1, $x2, $y2, $text_colors[ rand(0, 4) ]); 
}; 

Header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
Header("Last-Modified: ".gmdate("D, d M Y H:i:s")."GMT"); 
Header("Cache-Control: no-cache, must-revalidate"); 
Header("Pragma: no-cache"); 

header("Content-type:image/png"); 
imagepng($im); 
imagedestroy($im);

?>

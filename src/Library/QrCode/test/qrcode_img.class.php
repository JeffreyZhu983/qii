<?php
/*
#
# QRcode image class library for PHP4  version 0.50beta9 (C)2002-2004,Y.Swetake
#
# This version supports QRcode model2 version 1-40.
#
*/
//require "qrcode.php";
//require "RoundedCorner.php";

class Qrcode_image extends Qrcode{

    var $module_size;
    var $quiet_zone;
    var $qrcode_version;

    public function __construct(){
       parent::__construct();
       //$this->module_size=10;
       $this->module_size=25;
       
       $this->quiet_zone=4;

    }

    public function set_module_size($z){
        if ($z>0 && $z<9){
            $this->module_size=$z;
        }    
    }

    public function set_quietzone($z){
        if ($z>0 && $z<9){
            $this->quiet_zone=$z;
        }
    }
   
    /**
     * 输出二维码
     * @param type $org_data
     * @param type $filename 图片名
     * @param type $filetype 图片后缀
     * @param type $imgwh 图片大小
     * @param type $filelogo 图片logo
     * @param type $ptcolor 定点
     * @param type $inptcolor 内定点
     * @param type $fcolor 前景色
     * @param type $bcolor 背景色
     * @param type $ccolor 内容
     * @param type $style 样式：2液态 1直角 0圆角
     */
    public function qrcode_image_out($org_data,$filename='Uploads/yufu.png',$imgwh=380,$filelogo='',$filebg='',$ptcolor='#000000',$inptcolor='#000000',$fcolor='#000000',$bcolor='#FFFFFF',$ccolor='#000000',$style=1){
        
        
        //生成二维码
        $im=$this->mkimage($this->cal_qrcode($org_data),$ptcolor,$inptcolor,$fcolor,$bcolor,$ccolor,$style);

        //保存原图
        //imagepng($im, "src.png");
        
        //对原图按要求进行缩放
        $im=$this->resizeImage($im,$imgwh,$imgwh);
        $w=  imagesx($im);
        $h=  imagesy($im);
        
        
        //增加logo
        if(!empty($filelogo)){
            $im=$this->imageaddlogo($im,$filelogo);
        }
        

        //添加背景图
        if(!empty($filebg)){
            $im=$this->imageaddbg($im,$filebg);
        }
        
        //保存图片
        
        imagepng($im, $filename);
    }

    public function mkimage($data,$ptcolor,$inptcolor,$fcolor,$bcolor,$ccolor,$style){
        
        $data_array=explode("\n",$data);
        
        $ptcolor= $this->hex2rgb($ptcolor);
        $inptcolor=  $this->hex2rgb($inptcolor);
        $fcolor=  $this->hex2rgb($fcolor);
        $bcolor=  $this->hex2rgb($bcolor);
        $ccolor=  $this->hex2rgb($ccolor);

        
        
        $image_size=$c=count($data_array)-1;
        //$image_size=$c;
        $s=$this->module_size;//每一块的大小
        
        $output_size=$image_size*$this->module_size;

        
        $img=ImageCreate($output_size,$output_size);
        $bcolor = ImageColorAllocate ($img, $bcolor['r'], $bcolor['g'], $bcolor['b']);//背景色
        $ptcolor = ImageColorAllocate ($img, $ptcolor['r'], $ptcolor['g'], $ptcolor['b']);//定点色
        $inptcolor = ImageColorAllocate ($img, $inptcolor['r'], $inptcolor['g'], $inptcolor['b']);//内定点
        $fcolor = ImageColorAllocate ($img, $fcolor['r'], $fcolor['g'], $fcolor['b']);//前景色
        $ccolor = ImageColorAllocate ($img, $ccolor['r'], $ccolor['g'], $ccolor['b']);//内容色
        
        //imagecolorallocate() 返回一个标识符，代表了由给定的 RGB 成分组成的颜色。red，green 和 blue 分别是所需要的颜色的红，绿，蓝成分。这些参数是 0 到 255 的整数或者十六进制的 0x00 到 0xFF。 imagecolorallocate() 必须被调用以创建每一种用在 image 所代表的图像中的颜色。
        //第一次对 imagecolorallocate() 的调用会给基于调色板的图像填充背景色，即用 imagecreate() 建立的图像。
        
        
        $y=0;
        foreach($data_array as $row){
            
            $x=0;
            while ($x<$image_size){
                 if (substr($row,$x,1)=="1"){
                     //返回字符串 string 由 start 和 length 参数指定的子字符串。
                     
                        //左上角定点
                        if($x<7&&$y<7){
                            //左上角定点的四个大角
                            if($x===0||$y===0||$x===6||$y===6){
                                switch ($style) {
                                    case 2:
                                         //液态
                                        if($x===0&&$y===0){
                                            $this->roundedcorner($img,$x,$y,$s,$ptcolor,TRUE,FALSE,FALSE,FALSE);
                                        }else if ($x===0&&$y===6) {
                                            $this->roundedcorner($img,$x,$y,$s,$ptcolor,FALSE,TRUE,FALSE,FALSE);
                                        }else if($x===6&&$y===6){
                                            $this->roundedcorner($img,$x,$y,$s,$ptcolor,FALSE,FALSE,TRUE,FALSE);
                                        }else if ($x===6&&$y===0) {
                                            $this->roundedcorner($img,$x,$y,$s,$ptcolor,FALSE,FALSE,FALSE,TRUE);
                                        }else {
                                            imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $ptcolor);

                                        }
                                        break;
                                    case 1:
                                        //直角
                                        imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $ptcolor);
                                        break;
                                    case 0:
                                        //圆圈
                                        imagefilledellipse ($img, ($x*$this->module_size)+($this->module_size/2), ($y*$this->module_size)+($this->module_size/2), $this->module_size, $this->module_size, $ptcolor );
                                        break;
                                }
                                
                            }else{
                                switch ($style) {
                                    case 2:
                                        //液态
                                        if($x===2&&$y===2){
                                            $this->roundedcorner($img,$x,$y,$s,$inptcolor,TRUE,FALSE,FALSE,FALSE);
                                        }else if ($x===2&&$y===4) {
                                            $this->roundedcorner($img,$x,$y,$s,$inptcolor,FALSE,TRUE,FALSE,FALSE);
                                        }else if($x===4&&$y===4){
                                            $this->roundedcorner($img,$x,$y,$s,$inptcolor,FALSE,FALSE,TRUE,FALSE);
                                        }else if ($x===4&&$y===2) {
                                            $this->roundedcorner($img,$x,$y,$s,$inptcolor,FALSE,FALSE,FALSE,TRUE);
                                        }else {
                                           imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $inptcolor);
                                        }
                                        break;
                                    case 1:
                                        //直角
                                        imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $inptcolor);
                                        break;
                                    case 0:
                                        //圆圈
                                        imagefilledellipse ($img, ($x*$this->module_size)+($this->module_size/2), ($y*$this->module_size)+($this->module_size/2), $this->module_size, $this->module_size, $inptcolor );
                                        break;
                                }
                                 
                            }
                            
                        } elseif ($x>$image_size-8&&$y<7) { //右上角定点
                            
                            if($x===$image_size-7||$y===0||$x===$image_size-1||$y===6){
                                switch ($style) {
                                    case 2:
                                        //液态
                                        if($x===$image_size-7&&$y===0){
                                            $this->roundedcorner($img,$x,$y,$s,$ptcolor,TRUE,FALSE,FALSE,FALSE);
                                        }else if ($x===$image_size-7&&$y===6) {
                                            $this->roundedcorner($img,$x,$y,$s,$ptcolor,FALSE,TRUE,FALSE,FALSE);
                                        }else if($x===$image_size-1&&$y===6){
                                            $this->roundedcorner($img,$x,$y,$s,$ptcolor,FALSE,FALSE,TRUE,FALSE);
                                        }else if ($x===$image_size-1&&$y===0) {
                                            $this->roundedcorner($img,$x,$y,$s,$ptcolor,FALSE,FALSE,FALSE,TRUE);
                                        }else {
                                            imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $ptcolor);
                                        }
                                        break;
                                    case 1:
                                        //直角
                                        imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $ptcolor);
                                        break;
                                    case 0:
                                        //圆圈                                
                                        imagefilledellipse ($img, ($x*$this->module_size)+($this->module_size/2), ($y*$this->module_size)+($this->module_size/2), $this->module_size, $this->module_size, $ptcolor );
                                        break;
                                }
                                 
                               }else{
                                   switch ($style) {
                                       case 2:
                                           //液态
                                            if($x===$image_size-5&&$y===2){
                                                $this->roundedcorner($img,$x,$y,$s,$inptcolor,TRUE,FALSE,FALSE,FALSE);
                                            }else if ($x===$image_size-5&&$y===4) {
                                                $this->roundedcorner($img,$x,$y,$s,$inptcolor,FALSE,TRUE,FALSE,FALSE);
                                            }else if($x===$image_size-3&&$y===4){
                                                $this->roundedcorner($img,$x,$y,$s,$inptcolor,FALSE,FALSE,TRUE,FALSE);
                                            }else if ($x===$image_size-3&&$y===2) {
                                                $this->roundedcorner($img,$x,$y,$s,$inptcolor,FALSE,FALSE,FALSE,TRUE);
                                            }else {
                                                imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $inptcolor);
                                            }
                                           break;
                                       case 1:
                                           //直角
                                            imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $inptcolor);
                                           break;
                                       case 0:
                                            //圆圈                                
                                            imagefilledellipse ($img, ($x*$this->module_size)+($this->module_size/2), ($y*$this->module_size)+($this->module_size/2), $this->module_size, $this->module_size, $inptcolor );
                                           break;
                                   }
                                 
                                
                            }
                            
                        } elseif ($y>count($data_array)-9&&$x<7) { //左下角定点
                            
                            if($x===0||$y===$image_size-7||$x===6||$y===$image_size-1){
                                switch ($style) {
                                    case 2:
                                        //液态
                                        if($x===0&&$y===$image_size-7){
                                            $this->roundedcorner($img,$x,$y,$s,$ptcolor,TRUE,FALSE,FALSE,FALSE);
                                        }else if ($x===0&&$y===$image_size-1) {
                                            $this->roundedcorner($img,$x,$y,$s,$ptcolor,FALSE,TRUE,FALSE,FALSE);
                                        }else if($x===6&&$y===$image_size-1){
                                            $this->roundedcorner($img,$x,$y,$s,$ptcolor,FALSE,FALSE,TRUE,FALSE);
                                        }else if ($x===6&&$y===$image_size-7) {
                                            $this->roundedcorner($img,$x,$y,$s,$ptcolor,FALSE,FALSE,FALSE,TRUE);
                                        }else {
                                            imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $ptcolor);
                                        }
                                        break;
                                    case 1:
                                        //直角
                                        imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $ptcolor);
                                        break;
                                    case 0:
                                        //圆圈                                
                                        imagefilledellipse ($img, ($x*$this->module_size)+($this->module_size/2), ($y*$this->module_size)+($this->module_size/2), $this->module_size, $this->module_size, $ptcolor );
                                        break;
                                }
                                
                                
                                }  else {
                                    switch ($style) {
                                        case 2:
                                            //液态
                                            if($x===2&&$y===$image_size-5){
                                                $this->roundedcorner($img,$x,$y,$s,$inptcolor,TRUE,FALSE,FALSE,FALSE);
                                            }else if ($x===2&&$y===$image_size-3) {
                                                $this->roundedcorner($img,$x,$y,$s,$inptcolor,FALSE,TRUE,FALSE,FALSE);
                                            }else if($x===4&&$y===$image_size-3){
                                                $this->roundedcorner($img,$x,$y,$s,$inptcolor,FALSE,FALSE,TRUE,FALSE);
                                            }else if ($x===4&&$y===$image_size-5) {
                                                $this->roundedcorner($img,$x,$y,$s,$inptcolor,FALSE,FALSE,FALSE,TRUE);
                                            }else {
                                                imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $inptcolor);
                                            }
                                            break;
                                        case 1:
                                            //直角
                                            imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $inptcolor);
                                            break;
                                        case 0:
                                            //圆圈
                                            imagefilledellipse ($img, ($x*$this->module_size)+($this->module_size/2), ($y*$this->module_size)+($this->module_size/2), $this->module_size, $this->module_size, $inptcolor );
                            
                                            break;
                                    }

                                }
                            
                        } else {
                            //液态
                            switch ($style) {
                                case 2:
                                    //上
                                    if($y-1<0){
                                        //靠边的块都属于0
                                        $t=0;
                                    }else{
                                        $t= $data_array[$y-1][$x];
                                    }
                                    //左上
                                    if($x-1<0||$y-1<0){
                                        $lt=0;
                                    }  else {
                                        $lt= $data_array[$y-1][$x-1];
                                    }
                                    //左
                                    if($x-1<0){
                                        $l=0;
                                    }else{
                                        $l= $data_array[$y][$x-1];
                                    }
                                    //左下
                                    if($x-1<0||$y+1>$image_size-1){
                                        $lb=0;
                                    }else{
                                        $lb= $data_array[$y+1][$x-1];
                                    }
                                    //下
                                    if($y+1>$image_size-1){
                                        $b=0;
                                    }  else {
                                        $b= $data_array[$y+1][$x];
                                    }
                                    //右下
                                    if($x+1>$image_size-1||$y+1>$image_size-1){
                                        $rb=0;
                                    }  else {
                                        $rb= $data_array[$y+1][$x+1];
                                    }
                                    //右
                                    if($x+1>$image_size-1){
                                        $r=0;
                                    }else{
                                        $r= $data_array[$y][$x+1];
                                    }
                                    //右上
                                    if($x+1>$image_size-1||$y-1<0){
                                        $rt= 0;
                                    }else{
                                        $rt= $data_array[$y-1][$x+1];
                                    }

                                    //上+左+下+右=0 全圆
                                    if($t==0&&$l==0&&$b==0&&$r==0){
                                        //全圆
                                        imagefilledellipse ($img, ($x*$s)+($s/2), ($y*$s)+($s/2), $s, $s, $fcolor );  
                                    }elseif ($t==0&&$l==0&&$r==0) {
                                        //上半圆
                                        $this->halfrounded($img,$x,$y,$s,$fcolor,TRUE,FALSE,FALSE,FALSE); 
                                    }elseif($t==0&&$l==0&&$b==0){
                                        //左半圆
                                        $this->halfrounded($img,$x,$y,$s,$fcolor,FALSE,TRUE,FALSE,FALSE); 
                                    }elseif ($l==0&&$b==0&&$r==0) {
                                        //下半圆
                                        $this->halfrounded($img,$x,$y,$s,$fcolor,FALSE,FALSE,TRUE,FALSE); 
                                    }elseif ($t==0&&$b==0&&$r==0) {
                                        //右半圆
                                        $this->halfrounded($img,$x,$y,$s,$fcolor,FALSE,FALSE,FALSE,TRUE);                    
                                    }elseif ($t==0&&$l==0) {
                                        //左上角
                                        $this->roundedcorner($img,$x,$y,$s,$fcolor,TRUE,FALSE,FALSE,FALSE);
                                    }elseif ($l==0&&$b==0) {
                                        //左下角
                                        $this->roundedcorner($img,$x,$y,$s,$fcolor,FALSE,TRUE,FALSE,FALSE);
                                    }elseif($b==0&&$r==0){
                                        //右下角
                                        $this->roundedcorner($img,$x,$y,$s,$fcolor,FALSE,FALSE,TRUE,FALSE);
                                    }elseif ($r==0&&$t==0) {
                                        //右上角
                                        $this->roundedcorner($img,$x,$y,$s,$fcolor,FALSE,FALSE,FALSE,TRUE);
                                    }else{
                                        //直角
                                        imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $fcolor);

                                    }
                                    break;

                                case 1:
                                     //直角
                                    imagefilledrectangle ( $img , $x*$this->module_size , $y*$this->module_size , ($x+1)*$this->module_size , ($y+1)*$this->module_size , $fcolor);
                                    break;
                                case 0:
                                    //圆圈
                                    imagefilledellipse ($img, ($x*$this->module_size)+($this->module_size/2), ($y*$this->module_size)+($this->module_size/2), $this->module_size, $this->module_size, $fcolor );
                                    break;
                            }
                        }
                     
                     
                 }else{
                     if($x<7&&$y<7){
                         
                     }elseif ($x>$image_size-8&&$y<7) { //右上角定点
                         
                     }elseif ($y>count($data_array)-9&&$x<7) { //左下角定点
                         
                     }else {
                         if($style===2){
                            //液态
                            //为两个黑块之间的直角填充圆度
                            //上
                            if($y-1<0){
                                //靠边的块都属于0
                                $t=0;
                            }else{
                                $t= $data_array[$y-1][$x];
                            }
                            //左上
                            if($x-1<0||$y-1<0){
                                $lt=0;
                            }  else {
                                $lt= $data_array[$y-1][$x-1];
                            }
                            //左
                            if($x-1<0){
                                $l=0;
                            }else{
                                $l= $data_array[$y][$x-1];
                            }
                            //左下
                            if($x-1<0||$y+1>$image_size-1){
                                $lb=0;
                            }else{
                                $lb= $data_array[$y+1][$x-1];
                            }
                            //下
                            if($y+1>$image_size-1){
                                $b=0;
                            }  else {
                                $b= $data_array[$y+1][$x];
                            }
                            //右下
                            if($x+1>$image_size-1||$y+1>$image_size-1){
                                $rb=0;
                            }  else {
                                $rb= $data_array[$y+1][$x+1];
                            }
                            //右
                            if($x+1>$image_size-1){
                                $r=0;
                            }else{
                                $r= $data_array[$y][$x+1];
                            }
                            //右上
                            if($x+1>$image_size-1||$y-1<0){
                                $rt= 0;
                            }else{
                                $rt= $data_array[$y-1][$x+1];
                            }
                            if ($t==1&&$lt==1&&$l==1) {
                                //左上角
                                $this->halfcorner($img,$x,$y,$s,$bcolor,$fcolor,TRUE,FALSE,FALSE,FALSE);
                            }

                            if ($l==1&&$lb==1&&$b==1) {
                                //左下角
                                $this->halfcorner($img,$x,$y,$s,$bcolor,$fcolor,FALSE,TRUE,FALSE,FALSE);
                            }
                            if($b==1&&$rb==1&&$r==1){
                                //右下角
                                $this->halfcorner($img,$x,$y,$s,$bcolor,$fcolor,FALSE,FALSE,TRUE,FALSE);
                            }
                            if ($r==1&&$rt==1&&$t==1) {
                                //右上角
                                $this->halfcorner($img,$x,$y,$s,$bcolor,$fcolor,FALSE,FALSE,FALSE,TRUE);
                            }
                         }
                         
                     }

                 }
                 $x++;
            }
            $y++;
        }

        return($img);

    }
    //半角
    public function halfcorner($img,$x,$y,$s,$bcolor,$fcolor,$lt=TRUE,$lb=TRUE,$rb=TRUE,$rt=TRUE) {
        //左上半角
        if($lt){
            imagefilledarc($img, $x*$s, $y*$s, $s/2, $s/2, 0, 90, $fcolor, IMG_ARC_PIE);
            imagefilledarc($img, $x*$s+$s/4, $y*$s+$s/4, $s/2, $s/2, 180, 270, $bcolor, IMG_ARC_PIE);
        }
        //左下半角
        if($lb){
            imagefilledarc($img, $x*$s, ($y+1)*$s, $s/2, $s/2, 270, 360, $fcolor, IMG_ARC_PIE);
            imagefilledarc($img, $x*$s+$s/4, ($y+1)*$s-$s/4, $s/2, $s/2, 90, 180, $bcolor, IMG_ARC_PIE);
        }
        //右下半角
        if($rb){
            imagefilledarc($img, ($x+1)*$s, ($y+1)*$s, $s/2, $s/2, 180, 270, $fcolor, IMG_ARC_PIE);
            imagefilledarc($img, ($x+1)*$s-$s/4, ($y+1)*$s-$s/4, $s/2, $s/2, 0, 90, $bcolor, IMG_ARC_PIE);
            
        }
        //右上半角
        if($rt){
            imagefilledarc($img, ($x+1)*$s, $y*$s, ($s/2), ($s/2), 90, 180,$fcolor , IMG_ARC_PIE);
            imagefilledarc($img, ($x+1)*$s-($s/4), $y*$s+($s/4), ($s/2), ($s/2), 270, 360, $bcolor, IMG_ARC_PIE);
        }
        
    }
    //半圆
    public function halfrounded($img,$x,$y,$s,$color,$t=TRUE,$l=TRUE,$b=TRUE,$r=TRUE) {
        //上半圆
        if($t){
            imagefilledarc($img, ($x*$s)+($s/2), ($y*$s)+($s/2), $s, $s, 180, 270, $color, IMG_ARC_PIE);
            imagefilledarc($img, ($x*$s)+($s/2), ($y*$s)+($s/2), $s, $s, 270, 360, $color, IMG_ARC_PIE);
            imagefilledrectangle ($img , $x*$s , ($y*$s)+($s/2) , ($x+1)*$s , ($y+1)*$s , $color);
        }
        //左半圆
        if($l){
            imagefilledarc($img, ($x*$s)+($s/2), ($y*$s)+($s/2), $s, $s, 90, 180, $color, IMG_ARC_PIE);
            imagefilledarc($img, ($x*$s)+($s/2), ($y*$s)+($s/2), $s, $s, 180, 270, $color, IMG_ARC_PIE);
            imagefilledrectangle ($img , ($x*$s)+($s/2) , ($y*$s) , ($x+1)*$s , ($y+1)*$s , $color);
        }
        
        //下半圆
        if($b){
            imagefilledarc($img, ($x*$s)+($s/2), ($y*$s)+($s/2), $s, $s, 0, 90, $color, IMG_ARC_PIE);
            imagefilledarc($img, ($x*$s)+($s/2), ($y*$s)+($s/2), $s, $s, 90, 180, $color, IMG_ARC_PIE);
            imagefilledrectangle ($img , $x*$s , $y*$s , ($x+1)*$s , ($y*$s)+($s/2) , $color);
        }
        //右半圆
        if($r){
            imagefilledarc($img, ($x*$s)+($s/2), ($y*$s)+($s/2), $s, $s, 270, 360, $color, IMG_ARC_PIE);
            imagefilledarc($img, ($x*$s)+($s/2), ($y*$s)+($s/2), $s, $s, 0, 90, $color, IMG_ARC_PIE);
            imagefilledrectangle ($img , $x*$s , $y*$s , ($x*$s)+($s/2) , ($y+1)*$s , $color);
            
        }
    }
    //圆角
    public function roundedcorner($img,$x,$y,$s,$color,$lt=TRUE,$lb=TRUE,$rb=TRUE,$rt=TRUE) {
        if($lt){
            imagefilledarc($img, ($x*$s)+($s/2), ($y*$s)+($s/2), $s, $s, 180, 270, $color, IMG_ARC_PIE);
            $values = array(($x+1)*$s,($y*$s),($x+1)*$s,($y+1)*$s,($x*$s),($y+1)*$s,);
            $values1 = array(($x*$s),($y+1)*$s,($x*$s)+($s/2),($y*$s)+($s/2),($x*$s),($y*$s)+($s/2),);
            $values2 = array(($x*$s)+($s/2),($y*$s)+($s/2),($x*$s)+($s/2),($y*$s),($x+1)*$s,($y*$s),);
            imagefilledpolygon($img, $values, 3, $color);
            imagefilledpolygon($img, $values1, 3, $color);
            imagefilledpolygon($img, $values2, 3, $color);
        }
        if($lb){
            imagefilledarc($img, ($x*$s)+($s/2), ($y*$s)+($s/2), $s, $s, 90, 180, $color, IMG_ARC_PIE);
            $values = array($x*$s,$y*$s,$x*$s+$s,$y*$s,$x*$s+$s,($y+1)*$s,);
            $values1 = array($x*$s,$y*$s,$x*$s,$y*$s+($s/2),$x*$s+$s,$y*$s+($s/2),);
            $values2 = array($x*$s+($s/2),$y*$s+($s/2),$x*$s+($s/2),($y+1)*$s,$x*$s+$s,($y+1)*$s,);
            imagefilledpolygon($img, $values, 3, $color);
            imagefilledpolygon($img, $values1, 3, $color);
            imagefilledpolygon($img, $values2, 3, $color);
        }
        if($rb){
            imagefilledarc($img, ($x*$s)+($s/2), $y*$s+($s/2), $s, $s, 360, 90, $color, IMG_ARC_PIE);
            $values = array($x*$s,($y+1)*$s,$x*$s,$y*$s,($x+1)*$s,$y*$s,);
            $values1 = array($x*$s,($y+1)*$s,$x*$s+($s/2),$y*$s+($s/2),$x*$s+($s/2),($y+1)*$s,);
            $values2 = array($x*$s+($s/2),$y*$s+($s/2),($x+1)*$s,$y*$s,($x+1)*$s,$y*$s+($s/2),);
            imagefilledpolygon($img, $values, 3, $color);
            imagefilledpolygon($img, $values1, 3, $color);
            imagefilledpolygon($img, $values2, 3, $color);
        }
        if($rt){
            imagefilledarc($img, ($x*$s)+($s/2), $y*$s+($s/2), $s, $s, 270, 360, $color, IMG_ARC_PIE);
            $values = array($x*$s,$y*$s,$x*$s,$y*$s+$s,($x+1)*$s,$y*$s+$s,);
            $values1 = array($x*$s,$y*$s,$x*$s+($s/2),$y*$s,$x*$s+($s/2),$y*$s+($s/2),);
            $values2 = array($x*$s+($s/2),$y*$s+($s/2),($x+1)*$s,$y*$s+($s/2),($x+1)*$s,$y*$s+$s,);
            imagefilledpolygon($img, $values, 3, $color);
            imagefilledpolygon($img, $values1, 3, $color);
            imagefilledpolygon($img, $values2, 3, $color);
        }
    }
    
    /**
     * 缩放图片
     * @param type $im
     * @param type $maxwidth
     * @param type $maxheight
     * @return type
     */
    public function resizeImage($im,$maxwidth,$maxheight){
        $pic_width = imagesx($im);
        $pic_height = imagesy($im);

        $newim = imagecreatetruecolor($maxwidth,$maxheight);
        ImageCopyResampled($newim,$im,0,0,0,0,$maxwidth,$maxheight,$pic_width,$pic_height);
        imagedestroy($im);  
        
        return $newim;
        
    }
    
    //增加背景
    public function imageaddbg($im,$bgpath) {
        
        //计算宽和高
        $w = imagesx($im);
        $h = imagesy($im);

        //加载logo
        $ext = substr($bgpath, strrpos($bgpath, '.'));
        if (empty($ext)) {
            return false;	
        }
        switch(strtolower($ext)) {
            case '.jpg':
                    $src_im = @imagecreatefromjpeg($bgpath);
                    break;
            case '.gif':
                    $src_im = @imagecreatefromgif($bgpath);
                    break;
            case '.png':
                    $src_im = @imagecreatefrompng($bgpath);
                    break;

        }
       
        $bgw=  imagesx($src_im);
        $bgh=  imagesy($src_im);
        imagecopymerge($src_im, $im, ($bgw/2)-($w/2), ($bgh/2)-($h/2), 0, 0, $w, $h, 100);
        imagedestroy($im); 
        return $src_im;
    }
    
    //图片增加logo
    public function imageaddlogo($im,$logopath) {
        
        //计算宽和高
        $w = imagesx($im);
        $h = imagesy($im);

        //加载logo
        $ext = substr($logopath, strrpos($logopath, '.'));
        if (empty($ext)) {
            return false;	
        }
        switch(strtolower($ext)) {
            case '.jpg':
                    $src_im = @imagecreatefromjpeg($logopath);
                    break;
            case '.gif':
                    $src_im = @imagecreatefromgif($logopath);
                    break;
            case '.png':
                    $src_im = @imagecreatefrompng($logopath);
                    break;

        }
        $src_im=  $this->resizeImage($src_im,46,46);
        $src_w = imagesx($src_im);
        $src_h = imagesy($src_im);

        
        //logo边框1 小
        $bor1=ImageCreate($src_w+2,$src_h+2);
        ImageColorAllocate ($bor1, 237, 234, 237);//背景色
        $bor1_w = imagesx($bor1);
        $bor1_h = imagesy($bor1);
        
        //logo边框2 中
        $bor2=ImageCreate($bor1_w+8,$bor1_h+8);
        ImageColorAllocate ($bor2, 255, 255, 255);//背景色
        $bor2_w = imagesx($bor2);
        $bor2_h = imagesy($bor2);
        
        //logo边框3 大
        $bor3=ImageCreate($bor2_w+2,$bor2_h+2);
        ImageColorAllocate ($bor3, 215, 215, 215);//背景色
        $bor3_w = imagesx($bor3);
        $bor3_h = imagesy($bor3);

        //圆角处理
        $rounder = new RoundedCorner('', 5);

        //二维码与logo边框3合并
        $bor3=$rounder->round_it($bor3);
        imagecopymerge($im, $bor3, ($w/2)-($bor3_w/2), ($h/2)-($bor3_h/2), 0, 0, $bor3_w, $bor3_h, 100);
        imagedestroy($bor3); 

        //二维码与logo边框2合并
        $bor2=$rounder->round_it($bor2);
        imagecopymerge($im, $bor2, ($w/2)-($bor2_w/2), ($h/2)-($bor2_h/2), 0, 0, $bor2_w, $bor2_h, 100);
        imagedestroy($bor2); 

       //二维码与logo边框1合并
        $bor1=$rounder->round_it($bor1);
        imagecopymerge($im, $bor1, ($w/2)-($bor1_w/2), ($h/2)-($bor1_h/2), 0, 0, $bor1_w, $bor1_h, 100);
        imagedestroy($bor1); 
        
       //二维码与logo合并
        $src_im=$rounder->round_it($src_im);
        imagecopymerge($im, $src_im, ($w/2)-($src_w/2), ($h/2)-($src_h/2), 0, 0, $src_w, $src_h, 100);
        imagedestroy($src_im); 
        return $im;
    }
    /** 
    * 16进制颜色转换为RGB色值 
    * @method hex2rgb 
    */ 
    public function hex2rgb($hexColor) { 
        $color = str_replace('#', '', $hexColor); 
        if (strlen($color) > 3) { 

            $rgb = array( 
                'r' => hexdec(substr($color, 0, 2)), 
                'g' => hexdec(substr($color, 2, 2)), 
                'b' => hexdec(substr($color, 4, 2)) 
            ); 
        } else { 

            $color = str_replace('#', '', $hexColor); 
            $r = substr($color, 0, 1) . substr($color, 0, 1); 
            $g = substr($color, 1, 1) . substr($color, 1, 1); 
            $b = substr($color, 2, 1) . substr($color, 2, 1); 
            $rgb = array( 
                'r' => hexdec($r), 
                'g' => hexdec($g), 
                'b' => hexdec($b) 
            ); 
        } 

        return $rgb; 
    } 
    
    
}

?>
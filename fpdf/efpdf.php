<?php
require_once(dirname( __FILE__ ).'/fpdf.php');

class eFPDF extends FPDF
{	
      
  var $angle = 0;  
  
  var $T128;                                            
  var $ABCset="";                                         
  var $Aset="";                                          
  var $Bset="";                                          
  var $Cset="";                                          
  var $SetFrom;                                          
  var $SetTo;                                            
  var $JStart = array("A"=>103, "B"=>104, "C"=>105);     
  var $JSwap = array("A"=>101, "B"=>100, "C"=>99);       
   	
  /**
   *     
   */
  public function __construct() {
  	parent::__construct();  
  }
		
  //Quader (Characters)
  function Quader($bolSelected = true, $intColHeight = 5, $strBorder = '')
  {
    $cf_name=$this->FontFamily;
    $cf_style=$this->FontStyle;
    $cf_size=$this->FontSizePt;
    $dx=0.1+$this->FontSizePt/10*0.13;
    $dy=0.06+$this->FontSizePt/10*0.18;
    $this->SetFont('ZapfDingbats','',$cf_size);
    $cw=&$this->CurrentFont['cw'];
    $l=ceil($cw[chr(113)]/1000*$this->FontSize);
    if ($bolSelected) {
      $x=$this->GetX();
      $y=$this->GetY();
      $this->Cell($l,$intColHeight,chr(113),$strBorder,0,'L');
      $x2=$this->GetX();
      $y2=$this->GetY();
      $this->SetXY($x+$dx,$y-$dy);
      $this->Cell($l,$intColHeight,chr(55),'',0,'L');
      $this->SetXY($x2,$y2);
    } else {
      $this->Cell($l,$intColHeight,chr(113),$strBorder,0,'L');
    }
    $this->SetFont($cf_name,$cf_style,$cf_size);
  }

  //Quader2  (Lines with x)
  function Quader2($bolSelected = true, $intColHeight = 5, $strBorder = '', $intMargin = '')
  {
    $cMargin=$this->cMargin;
    if ($intMargin==='')  { $intMargin=$cMargin; } else { $this->cMargin=$intMargin; }
    $sl=$this->GetStringWidth('X');
    $l=max($sl,$intColHeight);
    $dx=floor($l-$sl)/2;
    $dy=floor($l-$sl)/2;
    if ($bolSelected) {
      $x=$this->GetX();
      $y=$this->GetY();
      $this->Cell($l,$intColHeight,'x',$strBorder,0,'C');
      $x2=$this->GetX();
      $y2=$this->GetY();
      $this->SetXY($x+$dx,$y+$dy);
      $this->Cell($l-(2*$dx),$intColHeight-(2*$dy),'',1,0,'L');
      $this->SetXY($x2,$y2);
    } else {
      $x=$this->GetX();
      $y=$this->GetY();
      $this->Cell($l,$intColHeight,'',$strBorder,0,'C');
      $x2=$this->GetX();
      $y2=$this->GetY();
      $this->SetXY($x+$dx,$y+$dy);
      $this->Cell($l-(2*$dx),$intColHeight-(2*$dy),'',1,0,'L');
      $this->SetXY($x2,$y2);
    }
    $this->cMargin=$cMargin;
  }

  //Get hight of multicell by txt
  function GetMultiCellHight($w = 0, $h = 0, $txt = '')
  {
    $col=0;
    $cw=&$this->CurrentFont['cw'];
    if($w==0)
      $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 && $s[$nb-1]=="\n")
      $nb--;
    $sep=-1;
    $i=0;
    $j=0;
    $l=0;
    $ns=0;
    $nl=1;
    while($i<$nb)
    {
      //Get next character
      $c=$s[$i];
      if($c=="\n")
      {
        //Explicit line break
        if($this->ws>0)
        {
          $this->ws=0;
        }
        $col++;
        $i++;
        $sep=-1;
        $j=$i;
        $l=0;
        $ns=0;
        $nl++;
        continue;
      }
      if($c==' ')
      {
        $sep=$i;
        $ls=$l;
        $ns++;
      }
      $l+=$cw[$c];
      if($l>$wmax)
      {
        //Automatic line break
        if($sep==-1)
        {
          if($i==$j)
            $i++;
          if($this->ws>0)
          {
            $this->ws=0;
          }
          $col++;
        }
        else
        {
          $col++;
          $i=$sep+1;
        }
        $sep=-1;
        $j=$i;
        $l=0;
        $ns=0;
        $nl++;
      }
      else
        $i++;
    }
    //Last chunk
    if($this->ws>0)
    {
      $this->ws=0;
    }
    $col++;
    return $col;
  }
  
  function MultiCellCol($w = 0, $h = 0, $txt = '', $border = 0, $align = 'L', $fill = 0, $mincol = 2)
  {
    $txt = preg_replace('/(\n|\r)*$/','',$txt);
    $col = $this->GetMultiCellHight($w, $h, $txt);
    if ($col < $mincol) {
      for ($i=$col;$i<=$mincol;$i++) $txt.= PHP_EOL;
    }
    $this->MultiCell($w, $h, $txt, $border, $align, $fill);
  }
  
  //Get hight of multicell2 by txt
  function GetMultiCell2Hight($w, $h, $txt)
  {
    //Output text with automatic or explicit line breaks
    $cw=&$this->CurrentFont['cw'];
    if($w==0)
      $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 && $s[$nb-1]=="\n")
      $nb--;
    $sep=-1;
    $i=0;
    $j=0;
    $l=0;
    $ns=0;
    $nl=1;
    $sc=false;
    while($i<$nb)
    {
      //Get next character
      $c=$s[$i];
      if($c=="\n")
      {
        //Explicit line break
        $i++;
        $sep=-1;
        $j=$i;
        $l=0;
        $ns=0;
        $nl++;
        continue;
      }
      if($c=='{' && $i+3<=$nb && $s[$i+3]=='}')
      {
      // Style change
        if(in_array(strtoupper($s[$i+1].$s[$i+2]),array('\B','\I')))
        {
          // Bold
          if(strtoupper($s[$i+1].$s[$i+2])=='\B')
          {
            if(strpos($this->FontStyle,'B')!==false)
            {
              $style=str_replace('B','',$this->FontStyle);
            }
            else
            {
              $style=$this->FontStyle.'B';
            }
          }
          // Italic
          if(strtoupper($s[$i+1].$s[$i+2])=='\I')
          {
            if(strpos($this->FontStyle,'I')!==false)
            {
              $style=str_replace('I','',$this->FontStyle);
            }
            else
            {
              $style=$this->FontStyle.'I';
            }
          }
          $nw=$l*$this->FontSize/1000;
          if (!$sc)
          {
            $x=$this->x;
            $sc=true;
            $w_org=$w;
            $wmax_org=$wmax;
          }
          $s=substr($s,0,$i).substr($s,$i+4);
          $nb=$nb-4;
          $wmax=$wmax-($nw*1000/$this->FontSize);
          $w=$w-$nw;
          $j=$i;
          $l=0;
          $sep_bak=$sep;
          $sep=-1;
          $ns=0;
          $this->SetFont('',$style);
          $cw=&$this->CurrentFont['cw'];
          continue;
        }
      }
      if($c==' ')
      {
        $sep=$i;
        $ls=$l;
        $ns++;
      }
      $l+=$cw[$c];
      if($l>$wmax)
      {
        //Automatic line break
        if($sep==-1)
        {
          if($i==$j)
            $i++;
          if($sc && $sep_bak>0)
          {
            $i=$j;
            $j=$sep_bak+1;
          }
        }
        else
        {
          $i=$sep+1;
        }
        if($sc)
        {
          $this->x=$x;
          $w=$w_org;
          $wmax=$wmax_org;
          $sc=false;
        }
        $sep=-1;
        $j=$i;
        $l=0;
        $ns=0;
        $nl++;
      }
      else
        $i++;
    }
    return $nl;
  }

  function MultiCell2($w, $h, $txt, $border=0, $align='J', $fill=false)
  {
    //Output text with automatic or explicit line breaks
    $cf_name=$this->FontFamily;
    $cf_style=$this->FontStyle;
    $cf_size=$this->FontSizePt;
    $cw=&$this->CurrentFont['cw'];
    $nl_max=$this->GetMultiCell2Hight($w,$h,$txt);
    if($w==0)
      $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 && $s[$nb-1]=="\n")
      $nb--;
    $b=0;
    if($border)
    {
      if($border==1)
      {
        $border='LTRB';
        $b='LRT';
        $b2='LR';
      }
      else
      {
        $b2='';
        if(strpos($border,'L')!==false)
          $b2.='L';
        if(strpos($border,'R')!==false)
          $b2.='R';
        $b=(strpos($border,'T')!==false) ? $b2.'T' : $b2;
      }
    }
    $sep=-1;
    $i=0;
    $j=0;
    $l=0;
    $ns=0;
    $nl=1;
    $sc=false;
    while($i<$nb)
    {
      //Get next character
      //echo ($sc) ? 'T'.$i.$s.$sep.'<br>' : 'F'.$i.$s.$sep.'<br>';
      $c=$s[$i];
      if($c=="\n")
      {
        //Explicit line break
        if($this->ws>0)
        {
          $this->ws=0;
          $this->_out('0 Tw');
        }
        $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
        $i++;
        $sep=-1;
        $j=$i;
        $l=0;
        $ns=0;
        $nl++;
        if($border && $nl==2)
          $b=$b2;
        continue;
      }
      if($c=='{' && $i+3<=$nb && $s[$i+3]=='}')
      {
      // Style change
        if(in_array(strtoupper($s[$i+1].$s[$i+2]),array('\B','\I')))
        {
          // Bold
          if(strtoupper($s[$i+1].$s[$i+2])=='\B')
          {
            if(strpos($this->FontStyle,'B')!==false)
            {
              $style=str_replace('B','',$this->FontStyle);
            }
            else
            {
              $style=$this->FontStyle.'B';
            }
          }
          // Italic
          if(strtoupper($s[$i+1].$s[$i+2])=='\I')
          {
            if(strpos($this->FontStyle,'I')!==false)
            {
              $style=str_replace('I','',$this->FontStyle);
            }
            else
            {
              $style=$this->FontStyle.'I';
            }
          }
          $nw=$l*$this->FontSize/1000;
          if ($sc)
          {
            $b3=str_replace('L','',str_replace('R','',$b));
          }
          else
          {
            $b3=str_replace('R','',$b);
            $x=$this->x;
          }
          if ($nl==$nl_max && strpos($border,'B')!==false && strpos($b3,'B')==false)
            $b3.='B';
          $this->Cell($nw,$h,substr($s,$j,$i-$j),$b3,0,$align,$fill);
          if (!$sc)
          {
            $sc=true;
            $w_org=$w;
            $wmax_org=$wmax;
          }
          $s=substr($s,0,$i).substr($s,$i+4);
          $nb=$nb-4;
          $wmax=$wmax-($nw*1000/$this->FontSize);
          $w=$w-$nw;
          $j=$i;
          $l=0;
          $sep_bak=$sep;
          $sep=-1;
          $ns=0;
          $this->SetFont('',$style);
          $cw=&$this->CurrentFont['cw'];
          continue;
        }
      }
      if($c==' ')
      {
        $sep=$i;
        $ls=$l;
        $ns++;
      }
      $l+=$cw[$c];
      if($l>$wmax)
      {
        //Automatic line break
        if($sep==-1)
        {
          if($i==$j)
            $i++;
          if($this->ws>0)
          {
            $this->ws=0;
            $this->_out('0 Tw');
          }
          $b3=($sc) ? str_replace('L','',$b) : $b;
          if($sc && $sep_bak>0)
          {
            $i=$j;
            $j=$sep_bak+1;
          }
          $this->Cell($w,$h,substr($s,$j,$i-$j),$b3,2,$align,$fill);
        }
        else
        {
          if($align=='J')
          {
            $this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
            $this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
          }
          $b3=($sc) ? str_replace('L','',$b) : $b;
          $this->Cell($w,$h,substr($s,$j,$sep-$j),$b3,2,$align,$fill);
          $i=$sep+1;
        }
        if($sc)
        {
          $this->x=$x;
          $w=$w_org;
          $wmax=$wmax_org;
          $sc=false;
        }
        $sep=-1;
        $j=$i;
        $l=0;
        $ns=0;
        $nl++;
        if($border && $nl==2)
          $b=$b2;
      }
      else
        $i++;
    }
    //Last chunk
    if($this->ws>0)
    {
      $this->ws=0;
      $this->_out('0 Tw');
    }
    if($border && strpos($border,'B')!==false)
      $b.='B';
    $b3=($sc) ? str_replace('L','',$b) : $b;
    $this->Cell($w,$h,substr($s,$j,$i-$j),$b3,2,$align,$fill);
    $this->x=$this->lMargin;
    $this->SetFont($cf_name,$cf_style,$cf_size);
  }

  function MultiCell2Col($w = 0, $h = 0, $txt = '', $border = 0, $align = 'L', $fill = 0, $mincol = 2)
  {
    $txt = preg_replace('/(\n|\r)*$/','',$txt);
    $col = $this->GetMultiCell2Hight($w, $h, $txt);
    if ($col < $mincol) {
      for ($i=$col;$i<=$mincol;$i++) $txt.= PHP_EOL;
    }
    $this->MultiCell2($w, $h, $txt, $border, $align, $fill);
  }

  //RedCross => Kennzeichnung von Aussteuerungen
  function RedCross($bolSelected = true, $intColHeight = 4.5, $rahmen = '')
  {
    if ($bolSelected == true) {
      $this->SetFont('ZapfDingbats','',10);
      $this->SetTextColor(242,29,43);
      $this->cell(3,$intColHeight,'8',$rahmen,0,0,0);
      $this->SetTextColor();
      $this->SetFont('Arial','',10);
    } else {
      $this->cell(3,$intColHeight,'',$rahmen,0,0,0);
    }
  }

  //Zelle mit genauer Länge Text
  function Cell2($h = 0,$txt = '', $ceil = false, $rpad = 0, $border = 0, $ln = 0, $align = 'L', $fill = false, $link = '') {
    $w = $this->GetStringWidth($txt);
    if ($ceil) $w = ceil($w);
    if (!empty($rpad)) $w = $w + $rpad;
    $this->Cell($w,$h,$txt,$border,$ln,$align,$fill,$link);
  }
  
  
  // Computes the number of lines a MultiCell of width w will take
  public function nbLines($w, $txt) {
  	$cw = &$this->CurrentFont['cw'];
  	if ($w == 0) {
  		$w = $this->w - $this->rMargin - $this->x;
  	}
  	$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
  	$s = str_replace("\r", '', $txt);
  	$nb = strlen($s);
  	if ($nb > 0 && $s[$nb-1] == "\n") {
  		$nb--;
  	}
  	$sep = -1;
  	$i	= 0;
  	$j	= 0;
  	$l	= 0;
  	$nl	= 1;
  	while ($i < $nb) {
  		$c = $s[$i];
  		if($c == "\n") {
  			$i++;
  			$sep = -1;
  			$j = $i;
  			$l = 0;
  			$nl++;
  			continue;
  		}
  		if($c == ' ') {
  			$sep = $i;
  		}
  		$l += $cw[$c];
  		if ($l > $wmax) {
  			if ($sep == -1) {
  				if ($i == $j) {
  					$i++;
  				}
  			} else {
  				$i = $sep+1;
  			}
  			$sep = -1;
  			$j = $i;
  			$l = 0;
  			$nl++;
  		} else {
  			$i++;
  		}
  	}
  	return $nl;
  }
  
  // If the height h would cause an overflow, add a new page immediately
  public function checkPageBreak($h) {
  	if ($this->getY() + $h > $this->PageBreakTrigger) {
  		$this->addPage($this->CurOrientation);
  		$this->setX(30);
  	}
  }  
 
  public function writeTable($tcolums, $startX = 30, $bottomSpace=0) {
  	//Durhc alle Spalten gehen
  	for ($i = 0; $i < sizeof($tcolums); $i++) {
  		$current_col = $tcolums[$i];
  		$height = 0;
  			
  		//maximale Höhe der aktuellen Zeile bestimmen
  		$nb = 0;
  		for($b = 0; $b < sizeof($current_col); $b++) {
  			//Style setzen
  			$this->setFont($current_col[$b]['font_name'], $current_col[$b]['font_style'], $current_col[$b]['font_size']);
  			$color = explode(",", $current_col[$b]['fillcolor']);
  			$this->setFillColor($color[0], $color[1], $color[2]);
  			$color = explode(",", $current_col[$b]['textcolor']);
  			$this->setTextColor($color[0], $color[1], $color[2]);
  			$color = explode(",", $current_col[$b]['drawcolor']);
  			$this->setDrawColor($color[0], $color[1], $color[2]);
  			$this->setLineWidth($current_col[$b]['linewidth']);
  
  			$nb = max($nb, $this->nbLines($current_col[$b]['width'], $current_col[$b]['text']));
  			$height = $current_col[$b]['height'];
  		}
  		
  		$h = $height * $nb + $bottomSpace;
  			
  		//Ggf. Einen Seitenumbruch durchführen
  		$this->checkPageBreak($h);
  			
  		$this->setX($startX);
  			
  		//Zellen der Zeile
  		for($b = 0; $b < sizeof($current_col); $b++) {
  			$w = $current_col[$b]['width'];
  			$a = $current_col[$b]['align'];
  
  			//aktuelle Position speichern
  			$x = $this->GetX();
  			$y = $this->GetY();
  
  			//Style setzen
  			$this->setFont($current_col[$b]['font_name'], $current_col[$b]['font_style'], $current_col[$b]['font_size']);
  			$color = explode(",", $current_col[$b]['fillcolor']);
  			$this->setFillColor($color[0], $color[1], $color[2]);
  			$color = explode(",", $current_col[$b]['textcolor']);
  			$this->setTextColor($color[0], $color[1], $color[2]);
  			$color = explode(",", $current_col[$b]['drawcolor']);
  			$this->setDrawColor($color[0], $color[1], $color[2]);
  			$this->setLineWidth($current_col[$b]['linewidth']);
  
  			$color = explode(",", $current_col[$b]['fillcolor']);
  			$this->setDrawColor($color[0], $color[1], $color[2]);
  
  			//Zellenhintergrund zeichen
  			$this->rect($x, $y, $w, $h, 'D');
  
  			$color = explode(",", $current_col[$b]['drawcolor']);
  			$this->setDrawColor($color[0], $color[1], $color[2]);
  
  			//Zellenrand zeichnen
  			if (substr_count($current_col[$b]['linearea'], "T") > 0) {
  				$this->line($x, $y, $x+$w, $y);
  			}
  			if (substr_count($current_col[$b]['linearea'], "B") > 0) {
  				$this->line($x, $y+$h, $x+$w, $y+$h);
  			}
  			if (substr_count($current_col[$b]['linearea'], "L") > 0) {
  				$this->line($x, $y, $x, $y+$h);
  			}
  			if (substr_count($current_col[$b]['linearea'], "R") > 0) {
  				$this->line($x+$w, $y, $x+$w, $y+$h);
  			}
  			
  			//Text schreiben
  			$this->multicell($w, $current_col[$b]['height']-1, $current_col[$b]['text'], 0, $a, 0);
  
  			
  			//Position ans rehcte Ende der Zelle setzen
  			$this->setXY($x + $w, $y);
  		}
  			
  		////Zur nächsten Zeile gehen und wieder links anfangen
  		$this->ln($h);
  		$this->setX($startX);
  	}
  }
  
  
  ##############################################################################################################################################
  ########################################				  	ROTATION			################################################################
  ##############################################################################################################################################
  
  function Rotate($angle,$x=-1,$y=-1)
  {
  	if($x==-1)
  		$x=$this->x;
  	if($y==-1)
  		$y=$this->y;
  	if($this->angle!=0)
  		$this->_out('Q');
  	$this->angle=$angle;
  	if($angle!=0)
  	{
  		$angle*=M_PI/180;
  		$c=cos($angle);
  		$s=sin($angle);
  		$cx=$x*$this->k;
  		$cy=($this->h-$y)*$this->k;
  		$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
  	}
  }
  
  function _endpage()
  {
  	if($this->angle!=0)
  	{
  		$this->angle=0;
  		$this->_out('Q');
  	}
  	parent::_endpage();
  }
  
  
  
  ###############################################################################################################################################
  ############################################			CODE 128		#########################################################################
  ###############################################################################################################################################
  
  
  function PDF_Code128($orientation='P', $unit='mm', $format='A4') {
  
  	//parent::FPDF($orientation,$unit,$format);
  
  	$this->T128[] = array(2, 1, 2, 2, 2, 2);           //0 : [ ]               // composition des caractères
  	$this->T128[] = array(2, 2, 2, 1, 2, 2);           //1 : [!]
  	$this->T128[] = array(2, 2, 2, 2, 2, 1);           //2 : ["]
  	$this->T128[] = array(1, 2, 1, 2, 2, 3);           //3 : [#]
  	$this->T128[] = array(1, 2, 1, 3, 2, 2);           //4 : [$]
  	$this->T128[] = array(1, 3, 1, 2, 2, 2);           //5 : [%]
  	$this->T128[] = array(1, 2, 2, 2, 1, 3);           //6 : [&]
  	$this->T128[] = array(1, 2, 2, 3, 1, 2);           //7 : [']
  	$this->T128[] = array(1, 3, 2, 2, 1, 2);           //8 : [(]
  	$this->T128[] = array(2, 2, 1, 2, 1, 3);           //9 : [)]
  	$this->T128[] = array(2, 2, 1, 3, 1, 2);           //10 : [*]
  	$this->T128[] = array(2, 3, 1, 2, 1, 2);           //11 : [+]
  	$this->T128[] = array(1, 1, 2, 2, 3, 2);           //12 : [,]
  	$this->T128[] = array(1, 2, 2, 1, 3, 2);           //13 : [-]
  	$this->T128[] = array(1, 2, 2, 2, 3, 1);           //14 : [.]
  	$this->T128[] = array(1, 1, 3, 2, 2, 2);           //15 : [/]
  	$this->T128[] = array(1, 2, 3, 1, 2, 2);           //16 : [0]
  	$this->T128[] = array(1, 2, 3, 2, 2, 1);           //17 : [1]
  	$this->T128[] = array(2, 2, 3, 2, 1, 1);           //18 : [2]
  	$this->T128[] = array(2, 2, 1, 1, 3, 2);           //19 : [3]
  	$this->T128[] = array(2, 2, 1, 2, 3, 1);           //20 : [4]
  	$this->T128[] = array(2, 1, 3, 2, 1, 2);           //21 : [5]
  	$this->T128[] = array(2, 2, 3, 1, 1, 2);           //22 : [6]
  	$this->T128[] = array(3, 1, 2, 1, 3, 1);           //23 : [7]
  	$this->T128[] = array(3, 1, 1, 2, 2, 2);           //24 : [8]
  	$this->T128[] = array(3, 2, 1, 1, 2, 2);           //25 : [9]
  	$this->T128[] = array(3, 2, 1, 2, 2, 1);           //26 : [:]
  	$this->T128[] = array(3, 1, 2, 2, 1, 2);           //27 : [;]
  	$this->T128[] = array(3, 2, 2, 1, 1, 2);           //28 : [<]
  	$this->T128[] = array(3, 2, 2, 2, 1, 1);           //29 : [=]
  	$this->T128[] = array(2, 1, 2, 1, 2, 3);           //30 : [>]
  	$this->T128[] = array(2, 1, 2, 3, 2, 1);           //31 : [?]
  	$this->T128[] = array(2, 3, 2, 1, 2, 1);           //32 : [@]
  	$this->T128[] = array(1, 1, 1, 3, 2, 3);           //33 : [A]
  	$this->T128[] = array(1, 3, 1, 1, 2, 3);           //34 : [B]
  	$this->T128[] = array(1, 3, 1, 3, 2, 1);           //35 : [C]
  	$this->T128[] = array(1, 1, 2, 3, 1, 3);           //36 : [D]
  	$this->T128[] = array(1, 3, 2, 1, 1, 3);           //37 : [E]
  	$this->T128[] = array(1, 3, 2, 3, 1, 1);           //38 : [F]
  	$this->T128[] = array(2, 1, 1, 3, 1, 3);           //39 : [G]
  	$this->T128[] = array(2, 3, 1, 1, 1, 3);           //40 : [H]
  	$this->T128[] = array(2, 3, 1, 3, 1, 1);           //41 : [I]
  	$this->T128[] = array(1, 1, 2, 1, 3, 3);           //42 : [J]
  	$this->T128[] = array(1, 1, 2, 3, 3, 1);           //43 : [K]
  	$this->T128[] = array(1, 3, 2, 1, 3, 1);           //44 : [L]
  	$this->T128[] = array(1, 1, 3, 1, 2, 3);           //45 : [M]
  	$this->T128[] = array(1, 1, 3, 3, 2, 1);           //46 : [N]
  	$this->T128[] = array(1, 3, 3, 1, 2, 1);           //47 : [O]
  	$this->T128[] = array(3, 1, 3, 1, 2, 1);           //48 : [P]
  	$this->T128[] = array(2, 1, 1, 3, 3, 1);           //49 : [Q]
  	$this->T128[] = array(2, 3, 1, 1, 3, 1);           //50 : [R]
  	$this->T128[] = array(2, 1, 3, 1, 1, 3);           //51 : [S]
  	$this->T128[] = array(2, 1, 3, 3, 1, 1);           //52 : [T]
  	$this->T128[] = array(2, 1, 3, 1, 3, 1);           //53 : [U]
  	$this->T128[] = array(3, 1, 1, 1, 2, 3);           //54 : [V]
  	$this->T128[] = array(3, 1, 1, 3, 2, 1);           //55 : [W]
  	$this->T128[] = array(3, 3, 1, 1, 2, 1);           //56 : [X]
  	$this->T128[] = array(3, 1, 2, 1, 1, 3);           //57 : [Y]
  	$this->T128[] = array(3, 1, 2, 3, 1, 1);           //58 : [Z]
  	$this->T128[] = array(3, 3, 2, 1, 1, 1);           //59 : [[]
  	$this->T128[] = array(3, 1, 4, 1, 1, 1);           //60 : [\]
  	$this->T128[] = array(2, 2, 1, 4, 1, 1);           //61 : []]
  	$this->T128[] = array(4, 3, 1, 1, 1, 1);           //62 : [^]
  	$this->T128[] = array(1, 1, 1, 2, 2, 4);           //63 : [_]
  	$this->T128[] = array(1, 1, 1, 4, 2, 2);           //64 : [`]
  	$this->T128[] = array(1, 2, 1, 1, 2, 4);           //65 : [a]
  	$this->T128[] = array(1, 2, 1, 4, 2, 1);           //66 : [b]
  	$this->T128[] = array(1, 4, 1, 1, 2, 2);           //67 : [c]
  	$this->T128[] = array(1, 4, 1, 2, 2, 1);           //68 : [d]
  	$this->T128[] = array(1, 1, 2, 2, 1, 4);           //69 : [e]
  	$this->T128[] = array(1, 1, 2, 4, 1, 2);           //70 : [f]
  	$this->T128[] = array(1, 2, 2, 1, 1, 4);           //71 : [g]
  	$this->T128[] = array(1, 2, 2, 4, 1, 1);           //72 : [h]
  	$this->T128[] = array(1, 4, 2, 1, 1, 2);           //73 : [i]
  	$this->T128[] = array(1, 4, 2, 2, 1, 1);           //74 : [j]
  	$this->T128[] = array(2, 4, 1, 2, 1, 1);           //75 : [k]
  	$this->T128[] = array(2, 2, 1, 1, 1, 4);           //76 : [l]
  	$this->T128[] = array(4, 1, 3, 1, 1, 1);           //77 : [m]
  	$this->T128[] = array(2, 4, 1, 1, 1, 2);           //78 : [n]
  	$this->T128[] = array(1, 3, 4, 1, 1, 1);           //79 : [o]
  	$this->T128[] = array(1, 1, 1, 2, 4, 2);           //80 : [p]
  	$this->T128[] = array(1, 2, 1, 1, 4, 2);           //81 : [q]
  	$this->T128[] = array(1, 2, 1, 2, 4, 1);           //82 : [r]
  	$this->T128[] = array(1, 1, 4, 2, 1, 2);           //83 : [s]
  	$this->T128[] = array(1, 2, 4, 1, 1, 2);           //84 : [t]
  	$this->T128[] = array(1, 2, 4, 2, 1, 1);           //85 : [u]
  	$this->T128[] = array(4, 1, 1, 2, 1, 2);           //86 : [v]
  	$this->T128[] = array(4, 2, 1, 1, 1, 2);           //87 : [w]
  	$this->T128[] = array(4, 2, 1, 2, 1, 1);           //88 : [x]
  	$this->T128[] = array(2, 1, 2, 1, 4, 1);           //89 : [y]
  	$this->T128[] = array(2, 1, 4, 1, 2, 1);           //90 : [z]
  	$this->T128[] = array(4, 1, 2, 1, 2, 1);           //91 : [{]
  	$this->T128[] = array(1, 1, 1, 1, 4, 3);           //92 : [|]
  	$this->T128[] = array(1, 1, 1, 3, 4, 1);           //93 : [}]
  	$this->T128[] = array(1, 3, 1, 1, 4, 1);           //94 : [~]
  	$this->T128[] = array(1, 1, 4, 1, 1, 3);           //95 : [DEL]
  	$this->T128[] = array(1, 1, 4, 3, 1, 1);           //96 : [FNC3]
  	$this->T128[] = array(4, 1, 1, 1, 1, 3);           //97 : [FNC2]
  	$this->T128[] = array(4, 1, 1, 3, 1, 1);           //98 : [SHIFT]
  	$this->T128[] = array(1, 1, 3, 1, 4, 1);           //99 : [Cswap]
  	$this->T128[] = array(1, 1, 4, 1, 3, 1);           //100 : [Bswap]
  	$this->T128[] = array(3, 1, 1, 1, 4, 1);           //101 : [Aswap]
  	$this->T128[] = array(4, 1, 1, 1, 3, 1);           //102 : [FNC1]
  	$this->T128[] = array(2, 1, 1, 4, 1, 2);           //103 : [Astart]
  	$this->T128[] = array(2, 1, 1, 2, 1, 4);           //104 : [Bstart]
  	$this->T128[] = array(2, 1, 1, 2, 3, 2);           //105 : [Cstart]
  	$this->T128[] = array(2, 3, 3, 1, 1, 1);           //106 : [STOP]
  	$this->T128[] = array(2, 1);                       //107 : [END BAR]
  
  	for ($i = 32; $i <= 95; $i++) {                                            // jeux de caractères
  		$this->ABCset .= chr($i);
  	}
  	$this->Aset = $this->ABCset;
  	$this->Bset = $this->ABCset;
  	for ($i = 0; $i <= 31; $i++) {
  		$this->ABCset .= chr($i);
  		$this->Aset .= chr($i);
  	}
  	for ($i = 96; $i <= 126; $i++) {
  		$this->ABCset .= chr($i);
  		$this->Bset .= chr($i);
  	}
  	$this->Cset="0123456789";
  
  	for ($i=0; $i<96; $i++) {                                                  // convertisseurs des jeux A & B
  		@$this->SetFrom["A"] .= chr($i);
  		@$this->SetFrom["B"] .= chr($i + 32);
  		@$this->SetTo["A"] .= chr(($i < 32) ? $i+64 : $i-32);
  		@$this->SetTo["B"] .= chr($i);
  	}
  }
  
  //________________ Fonction encodage et dessin du code 128 _____________________
  function Code128($x, $y, $code, $w, $h) {
  	
  	$this->PDF_Code128();  	
  	
  	$Aguid = "";                                                                      // Création des guides de choix ABC
  	$Bguid = "";
  	$Cguid = "";
  	for ($i=0; $i < strlen($code); $i++) {
  		$needle = substr($code,$i,1);
  		$Aguid .= ((strpos($this->Aset,$needle)===false) ? "N" : "O");
  		$Bguid .= ((strpos($this->Bset,$needle)===false) ? "N" : "O");
  		$Cguid .= ((strpos($this->Cset,$needle)===false) ? "N" : "O");
  	}
  
  	$SminiC = "OOOO";
  	$IminiC = 4;
  
  	$crypt = "";
  	while ($code > "") {
  		// BOUCLE PRINCIPALE DE CODAGE
  		$i = strpos($Cguid,$SminiC);                                                // forçage du jeu C, si possible
  		if ($i!==false) {
  			$Aguid [$i] = "N";
  			$Bguid [$i] = "N";
  		}
  
  		if (substr($Cguid,0,$IminiC) == $SminiC) {                                  // jeu C
  			$crypt .= chr(($crypt > "") ? $this->JSwap["C"] : $this->JStart["C"]);  // début Cstart, sinon Cswap
  			$made = strpos($Cguid,"N");                                             // étendu du set C
  			if ($made === false) {
  				$made = strlen($Cguid);
  			}
  			if (fmod($made,2)==1) {
  				$made--;                                                            // seulement un nombre pair
  			}
  			for ($i=0; $i < $made; $i += 2) {
  				$crypt .= chr(strval(substr($code,$i,2)));                          // conversion 2 par 2
  			}
  			$jeu = "C";
  		} else {
  			$madeA = strpos($Aguid,"N");                                            // étendu du set A
  			if ($madeA === false) {
  				$madeA = strlen($Aguid);
  			}
  			$madeB = strpos($Bguid,"N");                                            // étendu du set B
  			if ($madeB === false) {
  				$madeB = strlen($Bguid);
  			}
  			$made = (($madeA < $madeB) ? $madeB : $madeA );                         // étendu traitée
  			$jeu = (($madeA < $madeB) ? "B" : "A" );                                // Jeu en cours
  
  			$crypt .= chr(($crypt > "") ? $this->JSwap[$jeu] : $this->JStart[$jeu]); // début start, sinon swap
  
  			$crypt .= strtr(substr($code, 0,$made), $this->SetFrom[$jeu], $this->SetTo[$jeu]); // conversion selon jeu
  
  		}
  		$code = substr($code,$made);                                           // raccourcir légende et guides de la zone traitée
  		$Aguid = substr($Aguid,$made);
  		$Bguid = substr($Bguid,$made);
  		$Cguid = substr($Cguid,$made);
  	}                                                                          // FIN BOUCLE PRINCIPALE
  
  	$check = ord($crypt[0]);                                                   // calcul de la somme de contrôle
  	for ($i=0; $i<strlen($crypt); $i++) {
  		$check += (ord($crypt[$i]) * $i);
  	}
  	$check %= 103;
  
  	$crypt .= chr($check) . chr(106) . chr(107);                               // Chaine Cryptée complète
  
  	$i = (strlen($crypt) * 11) - 8;                                            // calcul de la largeur du module
  	$modul = $w/$i;
  
  	for ($i=0; $i<strlen($crypt); $i++) {                                      // BOUCLE D'IMPRESSION
  		$c = $this->T128[ord($crypt[$i])];
  		for ($j=0; $j<count($c); $j++) {
  			$this->Rect($x,$y,$c[$j]*$modul,$h,"F");
  			$x += ($c[$j++]+$c[$j])*$modul;
  		}
  	}
  }
   
  
}
  
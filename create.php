<?php
require('fpdf/fpdf.php');

class PDF_TextBox extends FPDF
{

function drawTextBox($strText, $w, $h, $align='L', $valign='T', $border=true)
{
    $xi=$this->GetX();
    $yi=$this->GetY();
    
    $hrow=$this->FontSize;
    $textrows=$this->drawRows($w,$hrow,$strText,0,$align,0,0,0);
    $maxrows=floor($h/$this->FontSize);
    $rows=min($textrows,$maxrows);

    $dy=0;
    if (strtoupper($valign)=='M')
        $dy=($h-$rows*$this->FontSize)/2;
    if (strtoupper($valign)=='B')
        $dy=$h-$rows*$this->FontSize;

    $this->SetY($yi+$dy);
    $this->SetX($xi);

    $this->drawRows($w,$hrow,$strText,0,$align,false,$rows,1);

    if ($border)
        $this->Rect($xi,$yi,$w,$h);
}

function drawRows($w, $h, $txt, $border=0, $align='J', $fill=false, $maxline=0, $prn=0)
{
    $cw=&$this->CurrentFont['cw'];
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
            if(is_int(strpos($border,'L')))
                $b2.='L';
            if(is_int(strpos($border,'R')))
                $b2.='R';
            $b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
        }
    }
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
                if ($prn==1) $this->_out('0 Tw');
            }
            if ($prn==1) {
                $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
            }
            $i++;
            $sep=-1;
            $j=$i;
            $l=0;
            $ns=0;
            $nl++;
            if($border && $nl==2)
                $b=$b2;
            if ( $maxline && $nl > $maxline )
                return substr($s,$i);
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
                    if ($prn==1) $this->_out('0 Tw');
                }
                if ($prn==1) {
                    $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                }
            }
            else
            {
                if($align=='J')
                {
                    $this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
                    if ($prn==1) $this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
                }
                if ($prn==1){
                    $this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
                }
                $i=$sep+1;
            }
            $sep=-1;
            $j=$i;
            $l=0;
            $ns=0;
            $nl++;
            if($border && $nl==2)
                $b=$b2;
            if ( $maxline && $nl > $maxline )
                return substr($s,$i);
        }
        else
            $i++;
    }
    //Last chunk
    if($this->ws>0)
    {
        $this->ws=0;
        if ($prn==1) $this->_out('0 Tw');
    }
    if($border && is_int(strpos($border,'B')))
        $b.='B';
    if ($prn==1) {
        $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
    }
    $this->x=$this->lMargin;
    return $nl;
}

function Footer()
{
    // Go to 1.5 cm from bottom
    $this->SetY(-15);
    // Select Arial italic 8
	$this->SetFont('Arial','B',8);
	
	$date = date('m-d-Y');
    // Print centered page number
    $this->Cell(0,10,''.$date,0,0,'C');
}

}
?>
<?php
$pdf=new PDF_TextBox();
$pdf->AddPage();
$pdf->SetFont('Arial','',15);
$pdf->SetTopMargin(180);
$pdf->SetXY(80,265);
$pdf->drawTextBox('', 50, 10, 'C', 'M');
$pdf->Output();
?>
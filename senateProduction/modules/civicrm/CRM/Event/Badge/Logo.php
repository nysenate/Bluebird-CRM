<?
require_once 'CRM/Event/Badge.php';
require_once 'CRM/Utils/Date.php';

class CRM_Event_Badge_Logo extends CRM_Event_Badge {

     function __construct() {
      parent::__construct();
      $pw=210; $ph=297;// A4
      $h=50; $w=75;
      $this->format = array('name' => 'Sigel 3C', 'paper-size' => 'A4', 'metric' => 'mm', 'lMargin' => ($pw-$w*2)/2,
                              'tMargin' => ($ph-$h*5)/2, 'NX' => 2, 'NY' => 5, 'SpaceX' => 0, 'SpaceY' => 0,
                              'width' => $w, 'height' => $h, 'font-size' => 12);
      $this->lMarginLogo = 20;
      $this->tMarginName = 20;
//      $this->setDebug ();
   }

   public function generateLabel($participant) {
     $x = $this->pdf->GetAbsX();
     $y = $this->pdf->GetY();
     $this->printBackground (true);
     $this->pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '2,2', 'color' => array(0, 0, 200)));

     $this->pdf->SetFontSize(8);
     $this->pdf->MultiCell ($this->pdf->width-$this->lMarginLogo, 0, $this->event->title ,$this->border,"L",0,1,$x+$this->lMarginLogo ,$y);

     $this->pdf->SetXY($x,$y+$this->pdf->height-5);
     $date = CRM_Utils_Date::customFormat($this->event->start_date, "%e %b");
     $this->pdf->Cell ($this->pdf->width, 0, $date ,$this->border,2,"R");

     $this->pdf->SetFontSize(15);
     $this->pdf->MultiCell ($this->pdf->width,10, $participant['first_name']. " ".$participant['last_name'] ,$this->border,"C",0,1,$x ,$y+$this->tMarginName);
     $this->pdf->SetFontSize(10);
     $this->pdf->MultiCell ($this->pdf->width, 0, $participant['current_employer'] ,$this->border,"C",0,1,$x,$this->pdf->getY());
   }

}

?>

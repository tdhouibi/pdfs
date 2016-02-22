<?php
require_once('/fpdf/efpdf.php');
#require_once('/inc/__dbconn.php');
#$imagepfad = './mandanten/';
$bID = 276;
$ml  = 20;   // Linker Rand
$mr  = 15;   // Rechter Rand
$mt  = 15;   // Oberer Rand
$mb  = 10;   // Unterer Rand
$fzt = 14;   // Schriftgröße Titel
$fzu = 7;    // Schriftgröße Unterschriften
$fzs = 9;    // Schriftgröße SPE
$fz  = 9;    // Schriftgröße Text
$zha = 5;    // Zeilenhöhe Anschrift
$zhu = 4;    // Zeilenhöhe Unterschriften
$zhk = 5.5;  // Zeilenhöhe Kopf
$zhs = 4;    // Zeilenhöhe SPE
$zhz = 2;    // Zeilenhöhe Zwischenraum
$zh  = 4;    // Zeilenhöhe
$lwn = 6.5;  // Breite der Nummerierung
$lwb = 43.5; // Breite der Beschreibung
$lwt = 210-$ml-$mr-$lwn-$lwb; // Breite der ausfüllbaren Spalte
$pe  = 285;  // max. Seitenhöhe
$ae_info_label = 'Zusatzinformationen:';  // Beschriftung der AE Kommentare
$vorgangsnummer_show = false; // Anzeige der Vorgangsnummer


#$i[10]='Ärztlicher Zusatzbericht mit HWS';
class PDF extends eFPDF
{
  //Page header
  function Header()
  {
  }

  //Page footer
  function Footer()
  {$this->SetTextColor(0, 0, 0);

  //Position at 1.2 cm from bottom

  $this->SetY(-11);

  $this->SetX(179);

  //Arial italic 8

  $this->SetFont('Arial','B',8);

  //Page number

  $this->Cell(0,10,'Seite '.$this->PageNo().'/{nb}',0,0,'C');
  }
}


//Instanciation of inherited class
$pdf=new PDF();
$pdf->SetAutoPageBreak(true,$mb);
$pdf->SetMargins($ml,$mt,$mr);
$pdf->AliasNbPages();

// -------------------------------- Bericht --------------------------------
$pdf->AddPage();

$URL="./actineo.jpg";
$pdf->image($URL,186,12,20,55);

$pdf->SetXY(20,42.5);
$pdf->SetFont('Arial','B',$fzt-1);
$pdf->Cell(60,$zhu,utf8_decode('WWW.ARZTATTESTE.DE/DEVK'),'',2,'L',0);
$pdf->ln(14);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(60,$zhu,utf8_decode('Ihre Unterstützung. In wenigen Schritten.'),'',2,'L',0);

$pdf->ln(6);
$pdf->SetFont('Arial','',$fz);
$pdf->MultiCellCol($lwt+$lwn+$lwb-35,$zh,utf8_decode('Die digitale Übermittlung der Berichtinformationen ist der schnellste und bequemste Weg für Sie. '
        . 'Und so einfach geth´s:'));

#$pdf->SetXY(20,42.5);
#$pdf->SetFont('Arial','B',$fzt);
#$pdf->Cell(60,$zhu,utf8_decode('WWW.ARTATTESTE.DE/DEVK'),'',2,'L',0);

$URL="./Download-Icon_actineo.jpg";
$pdf->image($URL,17,86,28,27);
$URL="./actineo_punkt.jpg";
$pdf->image($URL,10,107,4,4);
$pdf->SetXY(46,89);
$pdf->SetFont('Arial','B',$fz);
$pdf->Cell(60,$zhu,utf8_decode('1.Bericht herunterladen'),'',2,'L',0);
$pdf->SetXY(46,94);
$pdf->SetFont('Arial','',$fz);
$pdf->MultiCellCol($lwt+$lwn+$lwb-40,$zh,utf8_decode('Wählen Sie den ').'XXX'.utf8_decode($i[10]).' aus der angezeigten Liste aus und laden ihn lokal auf ihren Computer herunter.');
#$pdf->ln(10);
$URL="./Ausfuellen-Icon_actineo.jpg";
$pdf->image($URL,17,116,28,27);
$URL="./Actineopunkt.jpg";
$pdf->image($URL,10,151,1.7,1.7);
$pdf->SetXY(46,119);
$pdf->SetFont('Arial','B',$fz);
$pdf->Cell(60,$zhu,utf8_decode('2.Bericht ausfüllen'),'',2,'L',0);
$pdf->SetXY(46,124);
$pdf->SetFont('Arial','',$fz);
$pdf->MultiCellCol($lwt+$lwn+$lwb-70,$zh,utf8_decode('Füllen Sie bitte das ausgewählte Berichts-Dokument am Rechner aus und unterschreiben ihn ggf. digital. Anschließend besteht die Möglichkeit, den ausgefüllten Bericht für Ihre eigene Dokumentation abzuspeichern.'));
$URL="./Upload-Icon_actineo.jpg";
$pdf->image($URL,17,146,28,27);
$URL="./Actineopunkt.jpg";
$pdf->image($URL,10,207,1.7,1.7);
$pdf->SetXY(46,149);
$pdf->SetFont('Arial','B',$fz);
$pdf->Cell(60,$zhu,utf8_decode('3.Dokumente hochladen'),'',2,'L',0);
$pdf->SetXY(46,154);
$pdf->SetFont('Arial','',$fz);
$pdf->MultiCellCol($lwt+$lwn+$lwb-50,$zh,utf8_decode('Senden Sie uns Ihren ausgefüllten digitalen Arztbericht und weitere unfallbedingte Befundberichte über die verschlüsselte Upload-Funktion zu. Dazu wählen Sie die entsprechenden Dokumente auf Ihrem Computer aus und hängen sie an.'));

#$pdf->SetXY(20,170.5);
$pdf->ln(15);
$pdf->SetFont('Arial','',$fz);
$pdf->MultiCellCol($lwt+$lwn+$lwb-15,$zh,utf8_decode('Zur Möglichkeit der digitalen Unterschrift stellen wir Ihnen die Aktuelleste Version der Adobe-acrobat_readers auf der internetseite zur Verfügung.'));
$pdf->ln(6);
$pdf->SetFont('Arial','',$fz);
$pdf->MultiCellCol($lwt+$lwn+$lwb-20,$zh,utf8_decode('Für den Fall, dass die unfallbedingten Befundberichte nicht in digitaler Form vorliegen, können Sie diese und etwaige andere Dokumente auch per Fax an: 02236 48003 586 oder per Post an oben genannte Adresse zusenden. Bitte fügen Sie dazu das online bereitgestellte Dokument: Rückantwort bei.'));
//---------------------------------Ausgabe--------------------------------
$pdf->Output();

?>

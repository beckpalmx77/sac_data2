<?php
use setasign\Fpdi;

require_once('../vendor/tcpdf/etc/tcpdf_include.php');
require_once('../vendor/setasign/fpdi/src/autoload.php');

class Pdf extends Fpdi\Tcpdf\Fpdi
{
    /**
     * "Remembers" the template id of the imported page
     */
    protected $tplId;

    /**
     * Draw an imported PDF logo on every page
     */
    function Header()
    {
        if ($this->tplId === null) {
            $this->setSourceFile('logo.pdf');
            $this->tplId = $this->importPage(1);
        }
        $size = $this->useImportedPage($this->tplId, 130, 5, 60);

        $this->SetFont('freesans', 'B', 20);
        $this->SetTextColor(0);
        $this->SetXY(PDF_MARGIN_LEFT, 5);
        $this->Cell(0, $size['height'], 'TCPDF and FPDI');
    }

    function Footer()
    {
        // emtpy method body
    }
}

// initiate PDF
$pdf = new Pdf();
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(true, 40);

// add a page
$pdf->AddPage();

// get external file content
$utf8text = file_get_contents('tcpdf/examples/data/utf8test.txt', true);

$pdf->SetFont('freeserif', '', 12);
// now write some text above the imported page
$pdf->Write(5, $utf8text);

$pdf->Output('generated.pdf', 'I');

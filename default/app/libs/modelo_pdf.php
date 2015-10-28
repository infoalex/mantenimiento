<?php
Load::lib('fpdf/fpdf');

//define('FPDF_FONTPATH','/fpdf/font');
	//require("fpdf/fpdf.php");
	/**
	* 
	*/
	class MyFpdf extends FPDF
	{
		
		function header()
		{
			//$this->Image('img/HEADER_REPORTE.png',0,0,215.5,25);
			$this->ln(25);
		}

		function footer(){
			$this->SetY(-27);
			$this->SetFont('Times','I',10);
			$this->cell(0,5,"Carretera Nacional Via Turen, Sector E, Planta Arroz del Alba, S.A. Piritu. Estado Portuguesa.",0,2,'C');
			$this->SetFont('Times','IB',10);
			$this->cell(0,5,"Telefono 0256-3361377/3361455/3361333/3362000/361255",0,2,'C');
			$this->SetFont('Times','',10);
			$this->ln(3);
			$this->Cell(0,5,'Pag '.$this->PageNo().'/{nb}',0,0,'C');
			$this->AliasNbPages();
		}
	}
	?>
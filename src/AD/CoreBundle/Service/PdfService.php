<?php

namespace AD\CoreBundle\Service;

use AD\CoreBundle\Entity\Flat;
use AD\CoreBundle\Entity\Pension;
use setasign\Fpdi\Fpdi;

class PdfService {
	public $myLinks = array ();

	/*
	 *
	 * ############################################################
	 * # GENERATION FOCNTION PDF
	 * ############################################################
	 *
	 */
	public function generateFlatRecapPdf($flats) {
		$pdf = new Fpdi ();
		
		$this->generateCover($pdf,"Departamentos");
		$this->generateSummary ( $pdf, $flats );

		foreach ( $flats as $flat ) {
			$this->addFlat ( $pdf, $this->getOptions (), $flat );
		}

		return $pdf;
	}
	public function generatePensionRecapPdf($pensions) {
		$pdf = new Fpdi ();

		$this->generateCover($pdf,"Pensiones");
		$this->generateSummary ( $pdf, $pensions );

		foreach ( $pensions as $pension ) {
			$this->addPension ( $pdf, $this->getOptions (), $pension );
		}

		return $pdf;
	}
	
	
	public function addFlat(Fpdi $pdf, $options, Flat $flat) {
		$this->addPage ( $pdf, $options, null, 'A4' );
		$this->addLogo ( $pdf, $options, true );

		$this->addTitle ( $pdf, $options, $flat->getName () );
		$this->addSubtitleTitle ( $pdf, $options, "Descripción" );
		$this->addText ( $pdf, $options, "Título: " . $flat->getTitle () );
		$this->addText ( $pdf, $options, "Precio: $" . $this->printNumber ( $flat->getPrice () ) );
		$this->addText ( $pdf, $options, "Ciudad: " . $flat->getCity () );

		$this->addText ( $pdf, $options, $this->WriteHTML ( $pdf, $options, $flat->getDescriptionSpanish () ) );

		$this->addSubtitleTitle ( $pdf, $options, "Información privada" );
		$this->addText ( $pdf, $options, $this->WriteHTML ( $pdf, $options, $flat->getPrivateInformation () ) );
	}
	public function addPension(Fpdi $pdf, $options, Pension $pension) {
		$this->addPage ( $pdf, $options, null, 'A4' );
		$this->addLogo ( $pdf, $options, true );

		$this->addTitle ( $pdf, $options, $pension->getName () );
		$this->addSubtitleTitle ( $pdf, $options, "Descripción" );
		$this->addText ( $pdf, $options, "Título: " . $pension->getTitle () );
		$this->addText ( $pdf, $options, "Precio: $" . $this->printNumber ( $pension->getMinimumPrice () ) . " - $" . $this->printNumber ( $pension->getMaximumPrice () ) );
		$this->addText ( $pdf, $options, "Ciudad: " . $pension->getCity () );

		$this->addText ( $pdf, $options, $this->WriteHTML ( $pdf, $options, $pension->getDescriptionSpanish () ) );

		$this->addSubtitleTitle ( $pdf, $options, "Información privada" );
		$this->addText ( $pdf, $options, $this->WriteHTML ( $pdf, $options, $pension->getPrivateInformation () ) );
	}

	/*
	 *
	 * ############################################################
	 * # CALCULATED FOCNTION
	 * ############################################################
	 *
	 */
	private function generateSummary(Fpdi $pdf, $buildings) {
		$this->addPage ( $pdf, $this->getOptions (), null, 'A4' );
		$this->addLogo ( $pdf, $this->getOptions (), true );
		$this->addTitle ( $pdf, $this->getOptions(), "Indice" );
		foreach ( $buildings as $key => $building ) {
			$numPage = $key+2+$pdf->PageNo()-1;

			// on créé un lien
			$this->myLinks [$numPage] = $pdf->AddLink();

			// on écrit le nom de la page et on y associe le lien
			$text = iconv ( 'UTF-8', 'windows-1252', $building->getName() );
			$pdf->cell ( 0, 5, ($key+1).') '.$text, 0, 0, 'L', false, $this->myLinks [$numPage] );

			// on écrit le numéro de la page
			$pdf->cell ( 0, 5, $numPage, 0, 1, 'R');
		}
		
	}
	
	
	private function generateCover(Fpdi $pdf, $type){
		$this->addPage($pdf, $this->getOptions(), null, "A4");
		$this->addLogo ( $pdf, $this->getOptions (), true );
		$pdf->ln(50);
		$this->addTitle ( $pdf, $this->getOptions(), "Inventario" );
		$this->addTitle ( $pdf, $this->getOptions(), $type );
	}
	
	private function addLogo(Fpdi $pdf, $options, $skipLines) {
		$pageWidth = $pdf->GetPageWidth ();
		$pdf->Image ( 'bundles/core/img/logo_small_rounded_200x200.png', $pageWidth / 2 - $options ['logoWidth'] / 2, 10, $options ['logoWidth'], $options ['logoHeight'] );
		if ($skipLines) {
			$pdf->ln ( 50 );
			$pdf->Line ( $pdf->GetPageWidth () / 4, $pdf->getY (), $pdf->GetPageWidth () - ($pdf->GetPageWidth () / 4), $pdf->GetY () );
			$pdf->ln ( 10 );
		}
	}

	/**
	 * Add a page with specific size and orientation + page footer
	 *
	 * @param Fpdi $pdf
	 * @param
	 *        	$options
	 * @param
	 *        	$orientation
	 * @param
	 *        	$size
	 */
	private function addPage(Fpdi $pdf, $options, $orientation, $size) {
		$pdf->AddPage ( $orientation, $size );
		
		$page = $pdf->PageNo();
		if (isset($this->myLinks[$page])) {
			$pdf->setLink($this->myLinks[$page]);
		}
		
		$this->addFooter ( $pdf, $options );
	}

	/**
	 * Add Page footer
	 *
	 * @param Fpdi $pdf
	 * @param
	 *        	$options
	 */
	private function addFooter(Fpdi $pdf, $options) {
		// Go to 1.5 cm from bottom
		$pdf->SetY ( $pdf->GetPageHeight () - 35 );
		// Select Arial italic 8
		$pdf->SetTextColor ( 0, 0, 0 );
		$pdf->SetFont ( 'Arial', 'I', 12 );
		// Print centered page number
		$pdf->Cell ( 0, 10, 'Pagina ' . $pdf->PageNo (), 0, 0, 'R' );
		$pdf->SetY ( 0 );
	}

	/**
	 * Format numbers
	 *
	 * @param
	 *        	$number
	 * @return string
	 */
	private function printNumber($number) {
		return number_format ( $number, 0, ',', '.' );
	}

	/**
	 * Add A title to a section
	 *
	 * @param Fpdi $pdf
	 * @param
	 *        	$options
	 * @param
	 *        	$text
	 */
	private function addTitle(Fpdi $pdf, $options, $text) {
		$text = iconv ( 'UTF-8', 'windows-1252', $text );
		$pdf->SetFillColor ( 255, 255, 255 );
		$pdf->SetTextColor ( 0, 0, 0 );
		$pdf->SetFont ( $options ['font'] ['style'], $options ['font'] ['bold'], $options ['font'] ['title-1'] );
		$pdf->Cell ( 0, $options ['titleHeight'], $text, 0, 1, 'C', true );
		$pdf->SetTextColor ( 0, 0, 0 );
		$pdf->SetFillColor ( 255, 255, 255 );
		$pdf->SetFont ( $options ['font'] ['style'], null, $options ['font'] ['text'] );
		$pdf->ln ( 4 );
	}

	/**
	 * Add A subtitle to a section
	 *
	 * @param Fpdi $pdf
	 * @param
	 *        	$options
	 * @param
	 *        	$text
	 */
	private function addSubtitleTitle(Fpdi $pdf, $options, $text) {
		$text = iconv ( 'UTF-8', 'windows-1252', $text );
		$pdf->SetFont ( $options ['font'] ['style'], $options ['font'] ['bold'], $options ['font'] ['title-2'] );
		$pdf->Cell ( 0, $options ['titleHeight'], $text, 0, 1, 'L', true );
		$pdf->SetFont ( $options ['font'] ['style'], null, $options ['font'] ['text'] );
		$pdf->ln ( 2 );
	}
	private function addBoldText(Fpdi $pdf, $options, $text, $ln) {
		$text = iconv ( 'UTF-8', 'windows-1252', $text );
		$pdf->SetFont ( $options ['font'] ['style'], $options ['font'] ['bold'], $options ['textHeight'] );
		$pdf->Cell ( 0, $options ['textHeight'], $text, 0, $ln, 'L', true );
		$pdf->SetFont ( $options ['font'] ['style'], null, $options ['font'] ['text'] );
	}

	/**
	 * Add text to a section
	 *
	 * @param Fpdi $pdf
	 * @param
	 *        	$options
	 * @param
	 *        	$text
	 */
	private function addText(Fpdi $pdf, $options, $text) {
		$text = iconv ( 'UTF-8', 'windows-1252', $text );
		$pdf->MultiCell ( 0, $options ['textHeight'], $text );
	}

	/**
	 * GET options for pdf style
	 *
	 * @param
	 *        	$pension
	 * @return number[]|string[][]|number[][]|[]
	 */
	public function getOptions() {
		$options = array (
				'font' => array (
						'style' => 'Arial',
						'title-1' => 20,
						'title-2' => 16,
						'title-3' => 12,
						'text' => 11,
						'bold' => 'B',
						'italic' => 'I'
				),
				'logoWidth' => 30,
				'logoHeight' => 30,
				'leftMargin' => 10,
				'rightMargin' => 10,
				'textHeight' => 5,
				'titleHeight' => 8,
				'title-1' => 16,
				'title-2' => 14,
				'text' => 11
		);

		return $options;
	}
	public function writeHtml($pdf, $options, $html) {
		$html = strip_tags ( $html, "<p><br><li>" ); // supprime tous les tags sauf ceux reconnus
		$html = html_entity_decode ( $html );
		$html = str_replace ( "\n", ' ', $html ); // remplace retour à la ligne par un espace
		$a = preg_split ( '/<(.*)>/U', $html, - 1, PREG_SPLIT_DELIM_CAPTURE ); // éclate la chaîne avec les balises

		foreach ( $a as $i => $e ) {
			if ($i % 2 == 0) {
				$this->addText ( $pdf, $options, $e );
			} else {
				// Tag
				if ($e [0] == '/')
					$this->CloseTag ( $pdf, $options, strtoupper ( substr ( $e, 1 ) ) );
				else {
					// Extract attributes
					$a2 = explode ( ' ', $e );
					$tag = strtoupper ( array_shift ( $a2 ) );
					$this->OpenTag ( $pdf, $options, $tag );
				}
			}
		}
	}
	function OpenTag($pdf, $options, $tag) {
		// Opening tag
		switch ($tag) {
			case 'LI' :
				break;
		}
	}
	function CloseTag($pdf, $options, $tag) {
		// Opening tag
		switch ($tag) {
			case 'BR' :
				$pdf->Ln ( 1 );
				break;
			case 'P' :
				$pdf->Ln ( 1 );
				break;
			case 'LI' :
				break;
		}
	}
}
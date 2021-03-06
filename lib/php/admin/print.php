<?php
	session_start();
    include_once '../key.php';
    require_once '../fpdf/fpdf.php';
    
    function get_pdf($poi_data) {
        $pdf = new FPDF();
        $pdf->AddPage("");
        $pdf->SetTitle('Cyclofiche ' . $poi_data['ref_poi']);
        $pdf->SetMargins(10, 10);

        $pdf->Image('../../../resources/images/logo-main.png', NULL, NULL, 0, 30);
        $pdf->SetY(10);
        $pdf->SetX(30);

        $pdf->SetFont('Arial', 'B', 50);
        $pdf->Cell(170, 15, utf8_decode('Cyclofiche'), '', 2, 'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->Text(170, 10, utf8_decode('Date: ' . strftime("%d/%m/%Y", strtotime($poi_data['datecreation_poi']))));

        $pdf->SetFont('Arial', 'B', 35);
        $pdf->Cell(170, 15, utf8_decode($poi_data['ref_poi']), '', 0, 'C');

        $pdf->SetFont('Arial', '', 12);

        $pdf->Text(8, 48, utf8_decode('16 rue Ausone'));
        $pdf->Text(8, 53, utf8_decode('33000 Bordeaux'));
        $pdf->Text(8, 58, utf8_decode('Tél : 05 56 81 63 89'));
        $pdf->Text(8, 63, utf8_decode('Courriel : contact@velo-cite.org'));

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Text(78, 48, utf8_decode('Localisation de la demande'));
        
        $pdf->SetY(53);
        $pdf->SetX(79);
        $pdf->SetFont('Arial','B', 12);
        $pdf->Write(0.4, 'Commune: ');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(0.4, utf8_decode($poi_data['lib_commune']));

        $pdf->SetY(59);
        $pdf->SetX(79);
        $pdf->SetFont('Arial','B', 12);
        $pdf->Write(0.4, utf8_decode('Localisation précise : '));
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetY(63);
        $pdf->SetX(81);
        $pdf->MultiCell(0, 5, utf8_decode($poi_data['rue_poi']));

        $pdf->SetY(85);
        $pdf->SetX(8);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, utf8_decode($poi_data['lib_subcategory']), '', 2, 'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 5, utf8_decode("Description: " . $poi_data['desc_poi'] . "\n\n"));

        $pdf->MultiCell(0, 5, utf8_decode("Proposition: " . $poi_data['prop_poi'] . "\n\n"));

        $pdf->MultiCell(0, 5, utf8_decode("Commentaire Vélo Cité: " . $poi_data['commentfinal_poi'] . "\n\n"));

        $pdf->MultiCell(0, 5, utf8_decode("Commentaire Métropole: " . $poi_data['reponsegrandtoulouse_poi'] . "\n\n"));

        if (isset($poi_data['photo_poi'])) {
            $photo_filename = '../../../resources/pictures/' . $poi_data['photo_poi'];
            if (file_exists($photo_filename) && is_file($photo_filename))
                $exifimagetype = exif_imagetype ( $photo_filename );
                $imagefiletype = [
                    1 => 'GIF',
                    2 => 'JPG',
                    3 => 'PNG'
                ][$exifimagetype];
                if (!is_null($imagefiletype))
                    $pdf->Image('../../../resources/pictures/' . $poi_data['photo_poi'], NULL, NULL, 180, 0, $imagefiletype);
        }

        return $pdf;
    }

	if (isset($_SESSION['user']) && isset($_GET['id_poi'])) {
        switch (SGBD) {
			case 'mysql':
				$link = mysql_connect(HOST,DB_USER,DB_PASS);
				mysql_select_db(DB_NAME);	
				mysql_query("SET NAMES 'utf8'");
                
                $id_poi = mysql_real_escape_string($_GET['id_poi']);
            
                $sql = "SELECT poi.*, commune.lib_commune, subcategory.lib_subcategory FROM poi 
                INNER JOIN commune ON commune.id_commune = poi.commune_id_commune
                INNER JOIN subcategory ON subcategory.id_subcategory = poi.subcategory_id_subcategory
                WHERE id_poi = ". $id_poi;
                $result = mysql_query($sql);
                $poi = mysql_fetch_array($result);
                
                $pdf_obj = get_pdf($poi);
                
                mysql_free_result($result);
                mysql_close($link);
                
                $pdf_obj->Output("I", "cyclofiche-" . $poi['ref_poi'] . ".pdf");

				break;
			case 'postgresql':
				// TODO
				break;
		}
    }
?>
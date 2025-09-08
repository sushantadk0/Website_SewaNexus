<?php
require 'fpdf.php';
require 'db.php';

if (!isset($_GET['service_id']) || empty($_GET['service_id'])) {
    die("Error: Service ID not provided.");
}

$service_id = $_GET['service_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM mytbl WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        die("Service not found.");
    }

    $required_documents = json_decode($service['field'] ?? '[]', true) ?: [];
    $steps = json_decode($service['Steps'] ?? '[]', true) ?: [];
    $fee_info = html_entity_decode($service['fee'] ?? '', ENT_QUOTES, 'UTF-8');
    $service_name = html_entity_decode($service['services'] ?? '', ENT_QUOTES, 'UTF-8');

    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    $pdf->SetFillColor(41, 128, 185); // Professional blue
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 20, "", 0, 1, 'C', true); // Background bar
    
    $pdf->SetY($pdf->GetY() - 20);
    $pdf->Cell(0, 20, "Service Guide", 0, 1, 'C');

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(52, 73, 94); // Dark gray
    $pdf->Cell(0, 10, $service_name, 0, 1, 'C');

    // Subtitle with line
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(127, 140, 141); // Light gray
    $pdf->Cell(0, 8, "Your comprehensive step-by-step guide", 0, 1, 'C');
    
    // Decorative line
    $pdf->SetDrawColor(189, 195, 199);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(50, $pdf->GetY() + 3, 160, $pdf->GetY() + 3);
    $pdf->Ln(15);

    // Helper function to create section headers
    function createSectionHeader($pdf, $title, $color_r, $color_g, $color_b, $icon = '') {
        $pdf->SetDrawColor($color_r, $color_g, $color_b);
        $pdf->SetFillColor($color_r, $color_g, $color_b);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 12);
        
        // Create colored header bar
        $pdf->Cell(0, 10, "", 0, 1, 'L', true);
        $pdf->SetY($pdf->GetY() - 10);
        $pdf->Cell(0, 10, "  $icon $title", 0, 1, 'L');
        $pdf->Ln(3);
    }

    // --- Steps to Complete Section ---
    createSectionHeader($pdf, "Steps to Complete", 46, 204, 113, chr(149));
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(44, 62, 80);
    
    foreach ($steps as $i => $step) {
        $step_number = $i + 1;
        $clean_step = html_entity_decode($step, ENT_QUOTES, 'UTF-8');
        
        // Step number circle
        $pdf->SetFillColor(52, 152, 219);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(8, 8, $step_number, 1, 0, 'C', true);
        
        // Step content
        $pdf->SetTextColor(44, 62, 80);
        $pdf->SetFont('Arial', '', 10);
        
        // Calculate remaining width for text
        $remaining_width = 180 - 8 - 5; // Total width - circle width - margin
        
        // Split long text if necessary
        $lines = explode("\n", wordwrap($clean_step, 80, "\n"));
        $first_line = true;
        
        foreach ($lines as $line) {
            if ($first_line) {
                $pdf->Cell(5, 8, "", 0, 0); // Spacing
                $pdf->Cell($remaining_width, 8, $line, 0, 1, 'L');
                $first_line = false;
            } else {
                $pdf->Cell(13, 8, "", 0, 0); // Align with text above
                $pdf->Cell($remaining_width, 8, $line, 0, 1, 'L');
            }
        }
        $pdf->Ln(3);
    }

    // Check if we need a new page
    if ($pdf->GetY() > 240) {
        $pdf->AddPage();
    }

    // --- Required Documents Section ---
    $pdf->Ln(5);
    createSectionHeader($pdf, "Required Documents", 231, 76, 60, chr(164));
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(44, 62, 80);
    
    if (!empty($required_documents)) {
        foreach ($required_documents as $doc) {
            $clean_doc = html_entity_decode($doc, ENT_QUOTES, 'UTF-8');
            
            // Bullet point
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(8, 7, chr(149), 0, 0, 'C');
            
            // Document text
            $pdf->SetFont('Arial', '', 10);
            $pdf->MultiCell(0, 7, $clean_doc, 0, 'L');
            $pdf->Ln(2);
        }
    } else {
        $pdf->SetTextColor(127, 140, 141);
        $pdf->Cell(0, 8, "No specific documents required.", 0, 1, 'L');
    }

    // --- Fee Information Section ---
    $pdf->Ln(10);
    createSectionHeader($pdf, "Fee Information", 243, 156, 18, chr(165));
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(44, 62, 80);
    
    if (!empty($fee_info)) {
        // Clean the fee information from HTML entities and format nicely
        $clean_fee = str_replace(['&amp;', '&lt;', '&gt;', '&quot;', '&#039;'], ['&', '<', '>', '"', "'"], $fee_info);
        $clean_fee = html_entity_decode($clean_fee, ENT_QUOTES, 'UTF-8');
        
        $pdf->MultiCell(0, 7, $clean_fee, 0, 'L');
    } else {
        $pdf->SetTextColor(127, 140, 141);
        $pdf->Cell(0, 8, "Fee information not available.", 0, 1, 'L');
    }

    // Footer section
    $pdf->Ln(15);
    $pdf->SetDrawColor(189, 195, 199);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(5);
    
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetTextColor(127, 140, 141);
    $pdf->Cell(0, 8, "Generated on " . date('F j, Y \a\t g:i A'), 0, 1, 'C');
    $pdf->Cell(0, 6, "Nexus Sewa - Your Service Guide Partner", 0, 1, 'C');

    // Set proper headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $service_name) . '_Guide.pdf"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    // Output the PDF
    $pdf->Output('D', preg_replace('/[^a-zA-Z0-9_-]/', '_', $service_name) . '_Guide.pdf');

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("An error occurred while generating the PDF. Please try again later.");
} catch (Exception $e) {
    error_log("PDF generation error: " . $e->getMessage());
    die("An error occurred while generating the PDF. Please try again later.");
}
?>
<?php
class TCPDF_Simple {
    private $content = '';
    private $title = '';
    
    public function __construct($title = 'Document') {
        $this->title = $title;
    }
    
    public function addContent($content) {
        $this->content .= $content . "\n";
    }
    
    public function generatePDF($filename) {
        // Simple PDF structure
        $pdf_content = "%PDF-1.4\n";
        $pdf_content .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf_content .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdf_content .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $pdf_content .= "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        
        // Content stream
        $stream = "BT /F1 12 Tf 50 750 Td ({$this->title}) Tj 0 -20 Td";
        $lines = explode("\n", $this->content);
        foreach ($lines as $line) {
            $stream .= " (" . addslashes($line) . ") Tj 0 -15 Td";
        }
        $stream .= " ET";
        
        $pdf_content .= "5 0 obj\n<< /Length " . strlen($stream) . " >>\nstream\n{$stream}\nendstream\nendobj\n";
        
        $xref_offset = strlen($pdf_content);
        $pdf_content .= "xref\n0 6\n0000000000 65535 f \n";
        
        // Calculate offsets
        $offsets = [];
        $lines = explode("\n", $pdf_content);
        $current_offset = 0;
        for ($i = 0; $i < count($lines); $i++) {
            if (strpos($lines[$i], ' 0 obj') !== false) {
                $offsets[] = sprintf("%010d 00000 n ", $current_offset);
            }
            $current_offset += strlen($lines[$i]) + 1;
        }
        
        foreach ($offsets as $offset) {
            $pdf_content .= $offset . "\n";
        }
        
        $pdf_content .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xref_offset}\n%%EOF";
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf_content));
        
        echo $pdf_content;
    }
}
?>

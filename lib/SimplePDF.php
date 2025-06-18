<?php
class SimplePDF {
    private $content = '';
    private $title = '';
    
    public function __construct($title = 'Document') {
        $this->title = $title;
    }
    
    public function addHeader($text) {
        $this->content .= "<h1 style='color: #2563eb; text-align: center; margin-bottom: 20px;'>{$text}</h1>\n";
    }
    
    public function addSection($title, $content) {
        $this->content .= "<h2 style='color: #1e293b; border-bottom: 2px solid #2563eb; padding-bottom: 5px;'>{$title}</h2>\n";
        $this->content .= "<div style='margin: 15px 0;'>{$content}</div>\n";
    }
    
    public function addTable($headers, $rows) {
        $table = "<table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>\n";
        
        // Headers
        $table .= "<tr style='background: #f8fafc;'>";
        foreach ($headers as $header) {
            $table .= "<th style='border: 1px solid #e2e8f0; padding: 10px; text-align: left;'>{$header}</th>";
        }
        $table .= "</tr>\n";
        
        // Rows
        foreach ($rows as $row) {
            $table .= "<tr>";
            foreach ($row as $cell) {
                $table .= "<td style='border: 1px solid #e2e8f0; padding: 10px;'>{$cell}</td>";
            }
            $table .= "</tr>\n";
        }
        
        $table .= "</table>\n";
        $this->content .= $table;
    }
    
    public function output($filename) {
        $html = "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>{$this->title}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
                .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #eee; padding-top: 20px; }
            </style>
        </head>
        <body>
            {$this->content}
            <div class='footer'>
                <p>Generated on " . date('F j, Y \a\t g:i A') . " by SmartEnergy Dashboard</p>
            </div>
        </body>
        </html>";
        
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.html"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        
        echo $html;
    }
}
?>

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../libs/pdfparser/autoload.php'); // Include PDFParser
use Smalot\PdfParser\Parser;

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

try {
    // Check for uploaded file
    if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error.');
    }

    $uploaded_file = $_FILES['pdf_file']['tmp_name'];

    // Initialize PDFParser and get text
    $parser = new Parser();
    $pdf = $parser->parseFile($uploaded_file);
    $pdf_text = $pdf->getText();

    // Debugging: Ensure text is available
    if (empty($pdf_text)) {
        throw new Exception('No text was extracted from the PDF.');
    }

    // Debug raw PDF text (optional)
    // file_put_contents('debug_pdf_text.txt', $pdf_text);

    // Parse Questions and Answers
    $keywords = [];
    $answers = [];

    // Enhanced Regex to match Question and Answer with multi-line support
    preg_match_all('/Question\s*\d+:\s*(.*?)\nAnswer:\s*((?:(?!\nQuestion\s*\d+:).)*?)(?=\nQuestion\s*\d+:|\z)/is', $pdf_text, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $question = trim($match[1]);
        $answer = trim($match[2]);

        // Sanitize and clean text
        $question = preg_replace('/\s+/', ' ', $question); // Normalize spaces
        $answer = preg_replace('/\s+/', ' ', $answer);     // Normalize spaces

        $keywords[] = $question; // Extracted Question
        $answers[] = $answer;    // Extracted Answer
    }

    // Handle cases where no questions/answers were found
    if (empty($keywords) || empty($answers)) {
        throw new Exception('No valid questions and answers found in the PDF.');
    }

    // Build Response
    $response = [
        'status' => 'success',
        'keywords' => $keywords,
        'response' => implode("\n\n", $answers) // Combine all answers into a single string
    ];

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);

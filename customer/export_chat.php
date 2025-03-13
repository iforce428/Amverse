<?php
require_once('../config.php');
require_once('../libs/tcpdf/tcpdf.php');

if (isset($_GET['session_id'])) {
    $session_id = $_GET['session_id'];

    // Fetch messages for the session
    $sql = "SELECT * FROM chat_messages WHERE session_id = ? ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $messages = $stmt->get_result();

    // Initialize PDF
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    $pdf->Write(0, "Chat Transcript\n\n", '', 0, 'C', true, 0, false, false, 0); // Centered title

    // Add messages to PDF
    while ($msg = $messages->fetch_assoc()) {
        $sender = $msg['sender_type'] === 'user' ? 'You: ' : 'Bot: ';
        $message = htmlspecialchars_decode($msg['message']);
        $pdf->Write(0, $sender . $message . "\n\n", '', 0, '', false, 0, false, false, 0); // Add line spacing
    }

    // Output PDF
    $pdf->Output("chat_transcript_$session_id.pdf", 'D');
}
?>

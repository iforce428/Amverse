<?php
// Debugging: Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../classes/DBConnection.php'); // Include the DBConnection class

// Initialize database connection
$db = new DBConnection();
$conn = $db->conn;

// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

// Validate and process the incoming request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_SESSION['customer_id'];
    $conversation_data = $_POST['conversation'] ?? null;

    if (empty($conversation_data)) {
        echo json_encode(['status' => 'error', 'message' => 'No conversation data provided.']);
        exit;
    }

    $conversation = json_decode($conversation_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON format.']);
        exit;
    }

    // Start a new chat session
    $stmt = $conn->prepare("INSERT INTO chat_sessions (customer_id, created_at) VALUES (?, NOW())");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare chat session statement.']);
        exit;
    }
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();

    // Get the inserted chat session ID
    $session_id = $stmt->insert_id;
    $stmt->close();

    // Insert messages into the chat_messages table
    $stmt = $conn->prepare("INSERT INTO chat_messages (session_id, sender_type, message, created_at) VALUES (?, ?, ?, NOW())");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare chat message statement.']);
        exit;
    }

    foreach ($conversation as $message) {
        $sender_type = $message['type'] === 'user' ? 'user' : 'bot';
        $text = $message['message'];

        $stmt->bind_param("iss", $session_id, $sender_type, $text);
        $stmt->execute();
    }
    $stmt->close();

    echo json_encode(['status' => 'success', 'message' => 'Conversation saved successfully.']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
exit;

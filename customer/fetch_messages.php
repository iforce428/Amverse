<?php
require_once('../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_id = $_POST['session_id'] ?? 0;

    // Fetch messages for the given session
    $sql = "SELECT * FROM chat_messages WHERE session_id = ? ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $messages = $stmt->get_result();

    // Display messages as list items
    while ($msg = $messages->fetch_assoc()) {
        echo '<li class="list-group-item">';
        if ($msg['sender_type'] === 'user') {
            echo '<b>You: </b>';
        } else {
            echo '<b>Bot: </b>';
        }
        echo htmlspecialchars($msg['message']);
        echo '</li>';
    }
}
?>

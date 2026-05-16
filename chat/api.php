<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Must be logged in
if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle POST request (Send Message)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if (empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Message is empty']);
        exit;
    }
    
    // Optional receiver_id for direct messages, NULL for global team chat
    $receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : NULL;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $receiver_id, htmlspecialchars($message)]);
        
        echo json_encode(['status' => 'success', 'message_id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error', 'debug' => $e->getMessage()]);
    }
    exit;
}

// Handle GET request (Fetch Messages)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
    
    try {
        // Fetch global messages or messages where the user is sender or receiver
        // For simplicity of a "Team Chat", we fetch all messages where receiver_id IS NULL
        $stmt = $pdo->prepare("
            SELECT c.*, u.full_name as sender_name, u.profile_picture 
            FROM chat_messages c 
            JOIN users u ON c.sender_id = u.id 
            WHERE c.id > ? AND c.receiver_id IS NULL 
            ORDER BY c.id ASC
            LIMIT 50
        ");
        $stmt->execute([$last_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'messages' => $messages]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error', 'debug' => $e->getMessage()]);
    }
    exit;
}

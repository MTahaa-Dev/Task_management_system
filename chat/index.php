<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$current_page = 'chat';
$currentUser = get_current_user_data($pdo);

// Fetch all active users to show online status (simplified, just all users for UI)
$stmt = $pdo->prepare("SELECT id, full_name, profile_picture, role FROM users WHERE status = 'active' ORDER BY full_name ASC");
$stmt->execute();
$allUsers = $stmt->fetchAll();

?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<style>
/* Chat Specific Styles to match premium look */
.chat-container {
    height: calc(100vh - 110px);
    overflow: hidden;
}
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    scroll-behavior: smooth;
}
.chat-messages::-webkit-scrollbar {
    width: 6px;
}
.chat-messages::-webkit-scrollbar-thumb {
    background-color: var(--border-color);
    border-radius: 10px;
}
.chat-message {
    display: flex;
    max-width: 80%;
    animation: fadeIn 0.3s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.chat-message.me {
    align-self: flex-end;
    flex-direction: row-reverse;
}
.chat-message.other {
    align-self: flex-start;
}
.chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 10px;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}
.chat-bubble {
    background: var(--card-bg);
    padding: 12px 18px;
    border-radius: 18px;
    box-shadow: var(--card-shadow);
    position: relative;
    border: 1px solid var(--border-color);
    word-break: break-word;
}
.chat-message.me .chat-bubble {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    border-bottom-right-radius: 4px;
}
.chat-message.other .chat-bubble {
    border-bottom-left-radius: 4px;
}
.chat-info {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 5px;
    display: flex;
    justify-content: space-between;
    gap: 15px;
}
.chat-message.me .chat-info {
    flex-direction: row-reverse;
}
.chat-message.me .chat-info span.sender-name {
    color: var(--text-color);
}
.chat-input-area {
    padding: 1rem 1.5rem;
    background: var(--card-bg);
    border-top: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 10px;
}
.chat-input {
    flex: 1;
    border-radius: 30px;
    padding: 12px 20px;
    border: 1px solid var(--border-color);
    background: var(--bg-color);
    color: var(--text-color);
    outline: none;
    transition: all 0.3s;
}
.chat-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}
.btn-send {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}
.btn-send:hover {
    transform: scale(1.05);
    background: var(--primary-dark);
}
.btn-send i {
    font-size: 1.2rem;
    margin-left: 3px;
}

/* Sidebar for online users */
.users-sidebar {
    border-left: 1px solid var(--border-color);
    background: var(--card-bg);
    overflow-y: auto;
    height: 100%;
}
.user-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    border-bottom: 1px solid var(--border-color);
    transition: all 0.2s;
}
.user-item:hover {
    background: var(--bg-color);
}
.user-item .avatar-wrapper {
    position: relative;
    margin-right: 12px;
}
.user-item .status-dot {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #10B981;
    border: 2px solid var(--card-bg);
}
.user-item .status-dot.offline {
    background: #9CA3AF;
}
.user-item .user-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-color);
}
.user-item .user-role {
    font-size: 0.75rem;
    color: var(--text-muted);
}
</style>

<div class="main-content">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid p-4">
        
        <div class="card-premium border-0 overflow-hidden" data-aos="fade-up">
            <div class="row g-0 chat-container">
                
                <!-- Main Chat Area -->
                <div class="col-lg-9 col-md-8 d-flex flex-column h-100">
                    <div class="card-header-premium border-bottom d-flex align-items-center">
                        <div>
                            <h5 class="mb-0 fw-bold"><i class='bx bx-hash text-primary me-2'></i>Team General Chat</h5>
                            <small class="text-muted">Real-time communication for everyone</small>
                        </div>
                    </div>
                    
                    <div class="chat-messages" id="chatMessages">
                        <!-- Messages will be loaded here via AJAX -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chat-input-area">
                        <form id="chatForm" class="d-flex w-100 gap-2 m-0" onsubmit="sendMessage(event)">
                            <input type="text" id="messageInput" class="chat-input" placeholder="Type a message..." required autocomplete="off">
                            <button type="submit" class="btn-send" id="sendBtn">
                                <i class='bx bx-send'></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Active Users Sidebar -->
                <div class="col-lg-3 col-md-4 d-none d-md-block h-100">
                    <div class="users-sidebar">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0 fw-bold">Team Members</h6>
                        </div>
                        <?php foreach($allUsers as $u): ?>
                        <div class="user-item">
                            <div class="avatar-wrapper">
                                <img src="/demo/<?php echo htmlspecialchars(get_profile_picture($u['profile_picture'])); ?>" class="chat-avatar object-fit-cover" style="margin:0; width:35px; height:35px;">
                                <div class="status-dot <?php echo (rand(0,10) > 2) ? '' : 'offline'; ?>"></div>
                            </div>
                            <div>
                                <div class="user-name"><?php echo htmlspecialchars($u['full_name']); ?> <?php if($u['id'] == $_SESSION['user_id']) echo '<small class="text-muted">(You)</small>'; ?></div>
                                <div class="user-role text-capitalize"><?php echo htmlspecialchars($u['role']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
let lastMessageId = 0;
const currentUserId = <?php echo $_SESSION['user_id']; ?>;
const chatMessages = document.getElementById('chatMessages');

function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function fetchMessages(initialLoad = false) {
    fetch(`/demo/chat/api.php?last_id=${lastMessageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.messages.length > 0) {
                if (initialLoad) {
                    chatMessages.innerHTML = ''; // clear loading spinner
                }
                
                data.messages.forEach(msg => {
                    lastMessageId = Math.max(lastMessageId, msg.id);
                    appendMessage(msg);
                });
                
                scrollToBottom();
            } else if (initialLoad && chatMessages.innerHTML.includes('spinner-border')) {
                chatMessages.innerHTML = '<div class="text-center py-5 text-muted">No messages yet. Say hello!</div>';
            }
        })
        .catch(error => console.error('Error fetching messages:', error));
}

function appendMessage(msg) {
    const isMe = parseInt(msg.sender_id) === currentUserId;
    const msgClass = isMe ? 'me' : 'other';
    const avatarPath = msg.profile_picture ? msg.profile_picture : 'assets/images/default-avatar.png';
    const avatarContent = `<img src="/demo/${avatarPath}" class="chat-avatar object-fit-cover">`;
        
    const time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    // Check if the placeholder "No messages yet" exists
    if (chatMessages.innerHTML.includes('No messages yet')) {
        chatMessages.innerHTML = '';
    }

    const messageHtml = `
        <div class="chat-message ${msgClass}">
            ${avatarContent}
            <div class="message-content">
                <div class="chat-info">
                    <span class="sender-name fw-bold">${msg.sender_name}</span>
                    <span>${time}</span>
                </div>
                <div class="chat-bubble">
                    ${msg.message}
                </div>
            </div>
        </div>
    `;
    
    chatMessages.insertAdjacentHTML('beforeend', messageHtml);
}

function sendMessage(e) {
    e.preventDefault();
    const input = document.getElementById('messageInput');
    const btn = document.getElementById('sendBtn');
    const message = input.value.trim();
    
    if (!message) return;
    
    input.disabled = true;
    btn.disabled = true;
    
    const formData = new FormData();
    formData.append('message', message);
    
    fetch('/demo/chat/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            input.value = '';
            fetchMessages(); // Immediately fetch to show the new message
        } else {
            alert('Failed to send message.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending the message.');
    })
    .finally(() => {
        input.disabled = false;
        btn.disabled = false;
        input.focus();
    });
}

// Initial load
document.addEventListener('DOMContentLoaded', () => {
    fetchMessages(true);
    
    // Poll for new messages every 3 seconds
    setInterval(() => {
        fetchMessages();
    }, 3000);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

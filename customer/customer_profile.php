<?php
session_start();
require_once('../config.php');

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_signin.php");
    exit;
}

// Get logged-in customer ID
$customer_id = $_SESSION['customer_id'];

// Fetch chat sessions for the customer
$sql = "SELECT * FROM chat_sessions WHERE customer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$sessions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile - Amverse</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar-brand img {
            margin-right: 0.5em;
        }

        .card {
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background-color: #004aad;
            color: #fff;
        }

        .btn-primary {
            background-color: #004aad;
            border: none;
        }

        .btn-primary:hover {
            background-color: #00357a;
        }

        table th,
        table td {
            text-align: center;
            vertical-align: middle;
        }

        .bg-gradient-navy {
            background: #001f3f linear-gradient(180deg, #26415c, #001f3f) repeat-x !important;
            color: #fff;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-navy">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <img src="<?php echo validate_image($_settings->info('logo')) ?>" alt="Logo" width="30" height="30">
                <?php echo $_settings->info('short_name'); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Profile
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="../index.php">Go to Chat</a></li>
                            <li><a class="dropdown-item" href="customer_logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container my-5">
        <h2 class="text-center mb-4">Your Chat History</h2>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Session ID</th>
                                <th>Start Timestamp</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $sessions->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm view-messages" data-session-id="<?= $row['id'] ?>">View Messages</button>
                                        <a href="export_chat.php?session_id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Download PDF</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Viewing Messages -->
    <div class="modal fade" id="viewMessagesModal" tabindex="-1" aria-labelledby="viewMessagesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewMessagesModalLabel">Chat Messages</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul id="messages-list" class="list-group"></ul>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        $(document).on('click', '.view-messages', function() {
            const sessionId = $(this).data('session-id');
            $('#messages-list').html('<li class="list-group-item">Loading...</li>'); // Placeholder

            $.ajax({
                url: "fetch_messages.php", // Fetch messages dynamically
                method: "POST",
                data: {
                    session_id: sessionId
                },
                success: function(data) {
                    $('#messages-list').html(data); // Populate the modal with messages
                    $('#viewMessagesModal').modal('show'); // Show the modal
                },
                error: function() {
                    alert('Failed to fetch messages.');
                }
            });
        });
    </script>
</body>

</html>
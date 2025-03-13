<?php
require_once('../config.php'); 
require_once '../classes/DBConnection.php';
session_start();

$db = new DBConnection();
$conn = $db->conn;

$responseMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $responseMessage = 'Email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $responseMessage = 'Invalid email format.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM customers WHERE email = ?");
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $customer = $result->fetch_assoc();

            if (password_verify($password, $customer['password'])) {
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_name'] = $customer['name'];
                header("Location: ../index.php");
                exit;
            } else {
                $responseMessage = 'Invalid password.';
            }
        } else {
            $responseMessage = 'No account found with this email.';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<?php require_once('../inc/header.php') ?>
<body class="hold-transition login-page">
    <script>
        start_loader();
    </script>
    <style>
        body {
            background-image: url("<?php echo validate_image($_settings->info('cover')) ?>");
            background-size: cover;
            background-repeat: no-repeat;
            backdrop-filter: contrast(1);
        }

        #page-title {
            text-shadow: 6px 4px 7px black;
            font-size: 3.5em;
            color: #fff4f4 !important;
            background: #8080801c;
        }
    </style>
    <h1 class="text-center text-white px-4 py-5" id="page-title"><b><?php echo $_settings->info('name') ?></b></h1>
    <div class="login-box">
        <div class="card card-navy my-2">
            <div class="card-body">
                <p class="login-box-msg">Please enter your credentials</p>
                <?php if (!empty($responseMessage)): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($responseMessage); ?></div>
                <?php endif; ?>
                <form action="" method="post">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" name="email" placeholder="Email" autofocus required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8">
                            <a href="<?php echo base_url ?>">Go to Website</a>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <a href="customer_signup.php" class="btn btn-secondary btn-sm">Don't have an account? Sign Up</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            end_loader();
        })
    </script>
</body>
</html>

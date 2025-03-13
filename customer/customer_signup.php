<?php
require_once('../config.php'); // Ensure this file sets $_settings properly
require_once('../classes/DBConnection.php');

// Initialize database connection
$db = new DBConnection();
$conn = $db->conn;

$responseMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $responseMessage = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $responseMessage = 'Invalid email format.';
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $responseMessage = 'Email is already registered.';
        } else {
            $stmt->close();

            // Insert new customer
            $stmt = $conn->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $responseMessage = 'Sign-up successful!';
            } else {
                $responseMessage = 'Failed to register. Please try again.';
            }
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
    start_loader()
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
      <div class="card card-outline card-primary">
          <div class="card-header text-center">
              <h2>Sign Up</h2>
          </div>
          <div class="card-body">
              <p class="login-box-msg">Create your account below</p>
              <?php if (!empty($responseMessage)): ?>
                  <div class="alert alert-info"><?php echo htmlspecialchars($responseMessage); ?></div>
              <?php endif; ?>
              <form action="" method="POST">
                  <div class="input-group mb-3">
                      <input type="text" name="name" id="name" class="form-control" placeholder="Full Name" required>
                      <div class="input-group-append">
                          <div class="input-group-text">
                              <span class="fas fa-user"></span>
                          </div>
                      </div>
                  </div>
                  <div class="input-group mb-3">
                      <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                      <div class="input-group-append">
                          <div class="input-group-text">
                              <span class="fas fa-envelope"></span>
                          </div>
                      </div>
                  </div>
                  <div class="input-group mb-3">
                      <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                      <div class="input-group-append">
                          <div class="input-group-text">
                              <span class="fas fa-lock"></span>
                          </div>
                      </div>
                  </div>
                  <div class="row">
                      <div class="col-8">
                          <a href="../index.php">Go to Website</a>
                      </div>
                      <div class="col-4">
                          <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
                      </div>
                  </div>
              </form>
              <p class="mt-3 mb-1">
                  Already have an account? <a href="customer_signin.php">Sign In</a>
              </p>
          </div>
      </div>
  </div>

  <script src="../plugins/jquery/jquery.min.js"></script>
  <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
      $(document).ready(function(){
          end_loader();
      });
  </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>

<body class="backgrndimg">
<div><?php
session_start();
require_once "database.php";

if (isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Query to find user
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        // Verifying password
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            header("Location: user.html");
            exit();
        } else {
            echo "<div class='alert alert-danger'>Password does not match</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>User does not exist</div>";
    }

    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
?>
    <form action="login_action.php" method="post">
        <div class="signup-container">
            <div class="register-logo">
                <img src="./Assets/SIA_LOGO_wobg1.png" alt="">
            </div>
            <h1>Login</h1>
            <p>Please fill in this form to log in.</p>
            <hr>
      
            <label for="email"><b>Email</b></label>
            <input type="text" placeholder="Enter Email" name="email" id="email" required>
      
            <label for="password"><b>Password</b></label>
            <input type="password" placeholder="Enter Password" name="password" id="password" required>
          
            <button type="submit" name="login" class="registerbtn">Login</button>
            <p style="text-align: center;">Forgot <a href="#">Password?</a>.</p>
        </div>
      
        <div class="signup-container signin">
            <p>Don't have an account? <a href="./register.html">Register here</a>.</p>
        </div>
    </form>
    </div>
</body>

</html>

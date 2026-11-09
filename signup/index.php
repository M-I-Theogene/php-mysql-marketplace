<?php
session_start();

include '../config.php';
$query = new Database();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin/");
        exit;
    } else if ($_SESSION['role'] == 'seller') {
        header("Location: ../seller/");
    } else {
        header("Location: ../");
        exit;
    }
}

if (isset($_COOKIE['username']) && isset($_COOKIE['session_token'])) {

    if (session_id() !== $_COOKIE['session_token']) {
        session_write_close();
        session_id($_COOKIE['session_token']);
        session_start();
    }

    $username = $_COOKIE['username'];

    $result = $query->select('accounts', 'id, role', "WHERE username = '$username'");

    if (!empty($result)) {
        $user = $result[0];

        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $_COOKIE['username'];
        $_SESSION['id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: ../admin/");
            exit;
        } else if ($user['role'] == 'seller') {
            header("Location: ../seller/");
        } else {
            header("Location: ../");
            exit;
        }
    }
}

$msg = [];

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $number = $_POST['number'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $existingUser = $query->executeQuery("SELECT * FROM accounts WHERE username='$username' OR email='$email' OR number='$number'");

    if (!in_array($role, ['user', 'seller'], true)) {
        $msg = [
            "title" => "Error!",
            "text" => "Please choose a valid account type."
        ];
        $existingUser = null;
    }

    if (isset($existingUser) && $existingUser->num_rows > 0) {
        $msg = [
            "title" => "Error!",
            "text" => "Username, email, or phone number already exists."
        ];
    } elseif (isset($existingUser)) {
        // Sellers must supply business details so Cypadi can review them
        // before any product is listed.
        $profile = [];
        if ($role === 'seller') {
            $profile['business_name'] = trim($_POST['business_name'] ?? '');
            $profile['country'] = trim($_POST['country'] ?? '');
            $profile['id_number'] = trim($_POST['id_number'] ?? '');
            if ($profile['business_name'] === '' || $profile['country'] === '' || $profile['id_number'] === '') {
                $msg = [
                    "title" => "Error!",
                    "text" => "Business name, country and national ID number are required for seller accounts."
                ];
                $existingUser = null;
            }
        }
    }

    if (isset($existingUser) && $existingUser !== null && empty($msg)) {
        $result = $query->registerUser($name, $number, $email, $username, $password, $role, $profile ?? []);
        $userData = $query->executeQuery("SELECT * FROM accounts WHERE username='$username'")->fetch_assoc();

        if (!empty($result) && !empty($userData) && isset($userData['id'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $userData['id'];
            $_SESSION['name'] = $name;
            $_SESSION['number'] = $number;
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            setcookie('username', $username, time() + (86400 * 30), "/", "", true, true);
            setcookie('session_token',  session_id(), time() + (86400 * 30), "/", "", true, true);

            $msg = [
                "title" => "Success!",
                "text" => "Registration completed!",
                "icon" => "success"
            ];

            if ($role === 'admin') {
                header("Location: ../admin/");
                exit;
            }

            if ($role === 'seller') {
                // New sellers land on the panel, which explains the pending review.
                header("Location: ../seller/");
                exit;
            }

            header("Location: ../");
            exit;
        } else {
            $msg = [
                "title" => "Error!",
                "text" => "An error occurred while saving the data."
            ];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="../src/css/login.css">
    <link rel="icon" href="../favicon.ico">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .error-message {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }

        body {
            padding: 40px 0px !important;
        }
    </style>
</head>

<body>
    <?php if (!empty($msg)): ?>
        <script>
            Swal.fire({
                title: "<?php echo $msg['title']; ?>",
                text: "<?php echo $msg['text']; ?>",
                icon: "<?php echo $msg['icon'] ?? 'error'; ?>",
                confirmButtonText: "OK"
            });
        </script>
    <?php endif; ?>

    <div class="form-container">

        <h2>Sign Up</h2>

        <form method="post" action="" enctype="multipart/form-data" id="signup-form">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" placeholder="Full Name" required maxlength="30">
            </div>

            <div class="form-group">
                <label for="number">Number</label>
                <input type="phone" name="number" placeholder="Tell: +998991234567" required maxlength="20">
                <p class="error-message" id="number-error"></p>
            </div>

            <div class="form-group">
                <label for="role" class="font-weight-bold">Role</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="user">Customer</option>
                    <option value="seller">Seller</option>
                </select>
            </div>

            <div id="seller-fields" style="display:none;">
                <div class="form-group">
                    <label for="business_name">Business Name *</label>
                    <input type="text" name="business_name" id="business_name" placeholder="Business or trading name" maxlength="150">
                </div>

                <div class="form-group">
                    <label for="country">Country of Operation *</label>
                    <input type="text" name="country" id="country" placeholder="Rwanda" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="id_number">National ID / Passport Number *</label>
                    <input type="text" name="id_number" id="id_number" placeholder="Identity document number" maxlength="100">
                </div>

                <p class="error-message" style="color:#666;">
                    Seller accounts are reviewed by Cypadi before products can be listed.
                </p>
            </div>

            <div class="form-group">
                <label for="name">Email</label>
                <input type="email" name="email" placeholder="Email" required maxlength="255">
                <p class="error-message" id="email-error"></p>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" placeholder="Username" required maxlength="255">
                <p class="error-message" id="username-error"></p>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required maxlength="255">
                    <button type="button" id="toggle-password" class="password-toggle"><i
                            class="fas fa-eye"></i></button>
                </div>
            </div>

            <div class="form-group">
                <input type="submit" name="submit" id="submit" value="Submit">
            </div>

            <div class="text-center">
                <p>Already have an account? <a href="../login/">Log in</a></p>
            </div>
        </form>

    </div>

    <script>
        function toggleSellerFields() {
            var roleField = document.getElementById('role');
            var box = document.getElementById('seller-fields');
            var isSeller = roleField.value === 'seller';
            box.style.display = isSeller ? 'block' : 'none';
            ['business_name', 'country', 'id_number'].forEach(function(id) {
                var field = document.getElementById(id);
                if (field) { field.required = isSeller; }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('role').addEventListener('change', toggleSellerFields);
            toggleSellerFields();
        });

        $('#file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });

        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const toggleIcon = this.querySelector('i');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });

        $(document).ready(function() {
            $('input[name="number"]').on('input', function() {
                var number = $(this).val();
                if (number.length > 0 && !/^\d+$/.test(number)) {
                    $('#number-error').text('Number must contain only digits');
                } else {
                    $('#number-error').text('');
                }
            });

            $('input[name="email"]').on('input', function() {
                var email = $(this).val();
                if (email.length > 0 && !/\S+@\S+\.\S+/.test(email)) {
                    $('#email-error').text('Invalid email.');
                } else {
                    $('#email-error').text('');
                }
            });
        });


        $(document).ready(function() {
            var button_active = true;

            function isOne(value, callback) {
                $.ajax({
                    url: 'check_username.php',
                    type: 'POST',
                    data: {
                        username: value
                    },
                    success: function(response) {
                        if (response === 'exists') {
                            callback(true);
                        } else {
                            callback(false);
                        }
                    }
                });
            }

            function toggleSubmitButton() {
                if (button_active) {
                    $('#submit').prop('disabled', false);
                } else {
                    $('#submit').prop('disabled', true);
                }
            }

            $('input[name="username"]').on('input', function() {
                var username = $(this).val();
                if (username.length > 0 && !/^[a-zA-Z0-9_]+$/.test(username)) {
                    $('#username-error').text('Username can only contain letters, numbers, and underscores!');
                    button_active = false;
                } else {
                    $('#username-error').text('');
                    button_active = true;

                    if (username.length > 0) {
                        isOne(username, function(result) {
                            if (result) {
                                $('#username-error').text('This username already exists.');
                                button_active = false;
                            } else {
                                $('#username-error').text('');
                                toggleSubmitButton();
                            }
                        });
                    } else {
                        toggleSubmitButton();
                    }
                }

                toggleSubmitButton();
            });
        });

        $(document).ready(function() {
            function isEmailExists(email, callback) {
                $.ajax({
                    url: 'check_email.php',
                    type: 'POST',
                    data: {
                        email: email
                    },
                    success: function(response) {
                        if (response === 'exists') {
                            callback(true);
                        } else {
                            callback(false);
                        }
                    }
                });
            }

            $('input[name="email"]').on('input', function() {
                var email = $(this).val();
                if (email.length > 0 && !isValidEmail(email)) {
                    $('#email-error').text('Invalid email.');
                } else {
                    $('#email-error').text('');
                }
                if (email.length > 0) {
                    isEmailExists(email, function(result) {
                        if (result) {
                            $('#email-error').text('This email already exists.');
                        } else {
                            $('#email-error').text('');
                        }
                    });
                }
            });

            function isValidEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }
        });

        $(document).ready(function() {
            function isNumberExists(number, callback) {
                $.ajax({
                    url: 'check_number.php',
                    type: 'POST',
                    data: {
                        number: number
                    },
                    success: function(response) {
                        if (response === 'exists') {
                            callback(true);
                        } else {
                            callback(false);
                        }
                    }
                });
            }

            $('input[name="number"]').on('input', function() {
                var number = $(this).val();
                if (number.length > 0 && !/^\d+$/.test(number)) {
                    $('#number-error').text('Number must contain only digits');
                } else {
                    $('#number-error').text('');
                }
                if (number.length > 0) {
                    isNumberExists(number, function(result) {
                        if (result) {
                            $('#number-error').text('This number already exists.');
                        } else {
                            $('#number-error').text('');
                        }
                    });
                }
            });
        });

        function hideErrorMessage() {
            $('.error').hide();
        }
    </script>

    <?php if (isset($msg)): ?>
        <script>
            $(document).ready(function() {
                setTimeout(function() {
                    hideErrorMessage();
                }, 4000);
            });
        </script>
    <?php endif ?>

</body>

</html>

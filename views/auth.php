<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #111;
            color: #fff;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-form {
            background-color: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .form-control {
            background-color: #222;
            border: 1px solid #444;
            color: #ccc;
            border-radius: 4px;
            transition: border-color 0.3s, box-shadow 0.3s;
            height: auto;
            padding: 1.5rem 0.75rem 0.5rem;
        }

        .form-control:focus {
            background-color: #333;
            border-color: #666;
            color: #fff;
            box-shadow: none;
        }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-group label {
            position: absolute;
            top: 0.5rem;
            left: 0.75rem;
            font-size: 1rem;
            color: #999;
            pointer-events: none;
            transition: all 0.2s ease-out;
        }

        .form-control:focus ~ label,
        .form-control:not(:placeholder-shown) ~ label {
            top: 0.25rem;
            font-size: 0.75rem;
            color: #999;
        }

        .form-control:focus ~ label {
            color: #fff;
        }

        .btn-primary {
            background-color: #fff;
            color: #000;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: background-color 0.3s, transform 0.2s;
            padding: 0.75rem 1rem;
        }

        .btn-primary:hover {
            background-color: #f0f0f0;
            color: #000;
            transform: translateY(-1px);
        }

        .tab-container {
            margin-bottom: 2rem;
        }

        .tab-headers {
            display: flex;
            border-bottom: 1px solid #333;
            margin-bottom: 1.5rem;
        }

        .tab-header {
            padding: 0.75rem 1rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: #999;
            transition: color 0.3s, border-color 0.3s;
            border-bottom: 2px solid transparent;
        }

        .tab-header.active {
            color: #fff;
            border-bottom-color: #fff;
        }

        .tab-contents {
            position: relative;
            min-height: 300px;
        }

        .tab-content {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease-out, visibility 0.4s ease-out;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }

        .tab-content.active {
            opacity: 1;
            visibility: visible;
            position: relative;
        }

        select.form-control {
            padding: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-form">
            <div class="tab-container">
                <div class="tab-headers">
                    <span class="tab-header active" data-target="#login">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </span>
                    <span class="tab-header" data-target="#register">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </span>
                </div>
                <div class="tab-contents">
                    <div class="tab-content active" id="login">
                        <form action="/controllers/auth/logincontroller.php" method="POST">
                            <div class="form-group">
                                <input type="text" class="form-control" name="loginEmail" id="loginEmail" placeholder=" " required>
                                <label for="loginEmail">Username or Email</label>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" name="loginPassword" id="loginPassword" placeholder=" " required>
                                <label for="loginPassword">Password</label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </form>
                    </div>
                    <div class="tab-content" id="register">
                        <form action="/controllers/auth/registercontroller.php" method="POST">
                            <div class="form-group">
                                <input type="text" class="form-control" name="registerUsername" id="registerUsername" placeholder=" " required>
                                <label for="registerUsername">Username</label>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control" name="registerEmail" id="registerEmail" placeholder=" " required>
                                <label for="registerEmail">Email</label>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" name="registerPassword" id="registerPassword" placeholder=" " required>
                                <label for="registerPassword">Password</label>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="registerRole" id="registerRole">
                                    <option value="author">Author</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        const tabHeaders = document.querySelectorAll('.tab-header');
        const tabContents = document.querySelectorAll('.tab-content');

        tabHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const targetId = header.dataset.target;
                const targetContent = document.querySelector(targetId);

                tabHeaders.forEach(header => header.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                header.classList.add('active');
                targetContent.classList.add('active');
            });
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heisenberg Terminal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/header.css">
    <link href="https://fonts.googleapis.com/css2?family=Haas+Grot+Text:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand animate__animated animate__fadeInLeft" href="index.php">
                <span class="brand-text">Heisenberg Terminal</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <form class="form-inline my-2 my-lg-0 ml-auto animate__animated animate__fadeInRight">
                    <input class="form-control mr-sm-2" type="search" placeholder="Search articles" aria-label="Search">
                </form>
                <ul class="navbar-nav align-items-center animate__animated animate__fadeInRight">
                    <?php if (isset($_SESSION['username'])) : ?>
                        <li class="nav-item dropdown user-dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="<?php echo $_SESSION['role'] === 'admin' ? '/views/admin/dashboard.php' : ($_SESSION['role'] === 'author' ? '/views/author/dashboard.php' : '/views/user/dashboard.php'); ?>">DASHBOARD</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="/controllers/auth/logoutcontroller.php">LOGOUT</a>
                            </div>
                        </li>
                    <?php else : ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light btn-sm" href="/views/auth.php">LOGIN / SIGN UP</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <script src="/assets/js/fetchPrices.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/animatecss@4.0.0/dist/animate.min.js"></script>
</body>

</html>

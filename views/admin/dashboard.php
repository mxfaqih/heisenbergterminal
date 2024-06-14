<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Handle CRUD operations for categories
if (isset($_POST['category_action'])) {
    $category_name = $_POST['category_name'] ?? null;
    $category_id = $_POST['category_id'] ?? null;

    if ($_POST['category_action'] == 'add') {
        $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (:category_name)");
        $stmt->bindParam(':category_name', $category_name);
    } elseif ($_POST['category_action'] == 'edit') {
        $stmt = $pdo->prepare("UPDATE categories SET category_name = :category_name WHERE category_id = :category_id");
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':category_name', $category_name);
    } elseif ($_POST['category_action'] == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = :category_id");
        $stmt->bindParam(':category_id', $category_id);
    } elseif ($_POST['category_action'] == 'multi_delete') {
        if (isset($_POST['category_ids']) && is_array($_POST['category_ids'])) {
            $ids = implode(',', array_map('intval', $_POST['category_ids']));
            $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id IN ($ids)");
        }
    }

    $stmt->execute();
    header("Location: dashboard.php");
    exit();
}

// Handle CRUD operations for tags
if (isset($_POST['tag_action'])) {
    if ($_POST['tag_action'] == 'multi_edit') {
        if (isset($_POST['tags']) && is_array($_POST['tags'])) {
            foreach ($_POST['tags'] as $tag_id => $tag_name) {
                $stmt = $pdo->prepare("UPDATE tags SET tag_name = :tag_name WHERE tag_id = :tag_id");
                $stmt->bindParam(':tag_id', $tag_id);
                $stmt->bindParam(':tag_name', $tag_name);
                $stmt->execute();
            }
        }
    } else {
        $tag_name = $_POST['tag_name'];
        $tag_id = $_POST['tag_id'] ?? null;

        if ($_POST['tag_action'] == 'add') {
            $stmt = $pdo->prepare("INSERT INTO tags (tag_name) VALUES (:tag_name)");
            $stmt->bindParam(':tag_name', $tag_name);
        } elseif ($_POST['tag_action'] == 'edit') {
            $stmt = $pdo->prepare("UPDATE tags SET tag_name = :tag_name WHERE tag_id = :tag_id");
            $stmt->bindParam(':tag_id', $tag_id);
            $stmt->bindParam(':tag_name', $tag_name);
        } elseif ($_POST['tag_action'] == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM tags WHERE tag_id = :tag_id");
            $stmt->bindParam(':tag_id', $tag_id);
        }

        $stmt->execute();
    }

    header("Location: dashboard.php");
    exit();
}

// Handle CRUD operations for users
if (isset($_POST['user_action'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $user_id = $_POST['user_id'] ?? null;

    if ($_POST['user_action'] == 'add') {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (:username, :password, :email)");
    } elseif ($_POST['user_action'] == 'edit') {
        $stmt = $pdo->prepare("UPDATE users SET username = :username, password = :password, email = :email WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
    } elseif ($_POST['user_action'] == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
    }

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    header("Location: dashboard.php");
    exit();
}

// Fetch categories, tags, and users
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$tags = $pdo->query("SELECT * FROM tags")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            background-color: #111;
            color: #f0f0f0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: #000;
            border-bottom: 1px solid #333;
            padding: 10px 0;
            justify-content: center;
        }

        .navbar-brand {
            font-size: 1.3rem;
        }

        .nav-link {
            color: #aaa;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #fff;
        }

        .nav-link i {
            margin-right: 10px;
        }

        .main-content {
            padding: 30px;
            background-color: #1a1a1a;
        }

        .card {
            background-color: #222;
            border: none;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4);
        }

        .card-header {
            background-color: #333;
            color: #fff;
            font-weight: bold;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            border-bottom: 1px solid #444;
        }

        .form-control {
            background-color: #333;
            color: #f0f0f0;
            border: 1px solid #444;
            border-radius: 5px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .form-control:focus {
            background-color: #444;
            color: #fff;
            border-color: #666;
            box-shadow: none;
        }

        .btn {
            border-radius: 5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #fff;
            color: #000;
        }

        .btn-warning {
            background-color: #ccc;
            color: #000;
        }

        .btn-danger {
            background-color: #999;
            color: #000;
        }

        .btn-info {
            background-color: #888;
            color: #000;
        }

        .btn-success {
            background-color: #aaa;
            color: #000;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(255, 255, 255, 0.2);
        }

        .list-group-item {
            background-color: #333;
            color: #f0f0f0;
            border: 1px solid #444;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }

        .list-group-item:hover {
            background-color: #444;
        }

        .badge {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 20px;
            background-color: #fff;
            color: #000;
        }

        .custom-control-input:checked~.custom-control-label::before {
            background-color: #000;
            border-color: #fff;
        }

        .custom-control-input:checked~.custom-control-label::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%23000' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26l2.974 2.99L8 2.193z'/%3e%3c/svg%3e");
        }

        .card-body ul.list-group {
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg">
                <a class="navbar-brand" href="#"><i class="fas fa-cogs"></i> Admin Panel</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item px-2">
                            <a class="nav-link active" href="#categories"><i class="fas fa-tags"></i> Categories</a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="#tags"><i class="fas fa-hashtag"></i> Tags</a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="#users"><i class="fas fa-users"></i> Users</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main role="main" class="col-md-10 ml-sm-auto main-content" style="max-width: 100%; display:flex; gap: 30px;">
                <!-- Categories CRUD -->
                <div class="card" id="categories" style="width: 600px;">
                    <div class="card-header">
                        <i class="fas fa-tags"></i> Categories
                    </div>
                    <div class="card-body">
                        <form method="post" id="category-form" class="mb-4">
                            <input type="hidden" name="category_id" id="category_id">
                            <div class="form-group">
                                <label for="category_name">Category Name</label>
                                <input type="text" class="form-control" name="category_name" id="category_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary" name="category_action" value="add"><i class="fas fa-plus"></i> Add</button>
                            <button type="submit" class="btn btn-warning" name="category_action" value="edit"><i class="fas fa-edit"></i> Edit</button>
                        </form>

                        <form method="post" id="multi-delete-form">
                            <input type="hidden" name="category_action" value="multi_delete">
                            <button type="submit" class="btn btn-danger mb-3" name="category_action" value="multi_delete"><i class="fas fa-trash-alt"></i> Delete Selected</button>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="select_all">
                                        <label class="custom-control-label" for="select_all">Select All</label>
                                    </div>
                                </li>
                                <?php foreach ($categories as $category) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="category_ids[]" value="<?php echo $category['category_id']; ?>" id="category_<?php echo $category['category_id']; ?>">
                                            <label class="custom-control-label" for="category_<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></label>
                                        </div>
                                        <button type="button" class="btn btn-info btn-sm" onclick="editCategory('<?php echo $category['category_id']; ?>', '<?php echo $category['category_name']; ?>')"><i class="fas fa-edit"></i></button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </form>
                    </div>
                </div>

                <!-- Tags CRUD -->
                <div class="card" id="tags" style="width: 600px;">
                    <div class="card-header">
                        <i class="fas fa-hashtag"></i> Tags
                    </div>
                    <div class="card-body">
                        <form method="post" id="tags-form" class="mb-4">
                            <input type="hidden" name="tag_action" id="tag_action">
                            <input type="hidden" name="tag_id" id="tag_id">
                            <div class="form-group">
                                <label for="tag_name">Tag Name</label>
                                <input type="text" class="form-control" name="tag_name" id="tag_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary" name="tag_action" value="add"><i class="fas fa-plus"></i> Add</button>
                            <button type="submit" class="btn btn-warning" name="tag_action" value="edit"><i class="fas fa-edit"></i> Edit</button>
                            <button type="submit" class="btn btn-danger" name="tag_action" value="delete"><i class="fas fa-trash-alt"></i> Delete</button>
                        </form>
                        <form method="post" id="multi-edit-form">
                            <input type="hidden" name="tag_action" value="multi_edit">
                            <ul class="list-group">
                                <?php foreach ($tags as $tag) : ?>
                                    <li class="list-group-item">
                                        <span class="badge badge-primary tag-name" onclick="editTag('<?php echo $tag['tag_id']; ?>', this)"><?php echo $tag['tag_name']; ?></span>
                                        <input type="hidden" name="tags[<?php echo $tag['tag_id']; ?>]" value="<?php echo $tag['tag_name']; ?>" class="form-control mt-2 tag-input" style="display: none;">
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="submit" class="btn btn-success mt-3"><i class="fas fa-save"></i> Save Changes</button>
                        </form>
                    </div>
                </div>

                <!-- Users CRUD -->
                <div class="card" id="users" style="width: 600px;">
                    <div class="card-header">
                        <i class="fas fa-users"></i> Users
                    </div>
                    <div class="card-body">
                        <form method="post" class="mb-4">
                            <input type="hidden" name="user_id" id="user_id">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" name="username" id="username" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" name="email" id="email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" name="password" id="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary" name="user_action" value="add"><i class="fas fa-user-plus"></i> Add</button>
                            <button type="submit" class="btn btn-warning" name="user_action" value="edit"><i class="fas fa-user-edit"></i> Edit</button>
                            <button type="submit" class="btn btn-danger" name="user_action" value="delete"><i class="fas fa-user-times"></i> Delete</button>
                        </form>
                        <ul class="list-group">
                            <?php foreach ($users as $user) : ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-user"></i> <?php echo $user['username']; ?></span>
                                    <button class="btn btn-info btn-sm" onclick="editUser('<?php echo $user['user_id']; ?>', '<?php echo $user['username']; ?>', '<?php echo $user['email']; ?>')"><i class="fas fa-edit"></i></button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function editCategory(id, name) {
            document.getElementById('category_id').value = id;
            document.getElementById('category_name').value = name;
        }

        function editTag(id, element) {
            const input = document.querySelector('input[name="tags[' + id + ']"]');
            if (input) {
                input.type = 'text';
                input.style.display = 'block';
                element.style.display = 'none';
            }
        }

        function editUser(id, username, email) {
            document.getElementById('user_id').value = id;
            document.getElementById('username').value = username;
            document.getElementById('email').value = email;
        }

        document.getElementById('select_all').addEventListener('click', function(event) {
            var checkboxes = document.querySelectorAll('input[name="category_ids[]"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = event.target.checked;
            });
        });

        document.querySelectorAll('.tag-name').forEach(element => {
            element.addEventListener('click', () => {
                const input = element.nextElementSibling;
                if (input) {
                    input.type = 'text';
                    input.style.display = 'block';
                    element.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>

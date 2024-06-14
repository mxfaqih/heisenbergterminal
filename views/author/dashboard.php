<?php
session_start();
include '../../config/database.php';

// Check if the user is an author
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'author') {
    header("Location: ../../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch author data
$stmt = $pdo->prepare("SELECT * FROM authors WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$author = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle bio update
if (isset($_POST['update_bio'])) {
    $bio = $_POST['bio'];
    $stmt = $pdo->prepare("UPDATE authors SET bio = :bio WHERE user_id = :user_id");
    $stmt->bindParam(':bio', $bio);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    header("Location: dashboard.php");
    exit();
}

// Fetch articles written by the author
$stmt = $pdo->prepare("SELECT * FROM news WHERE author_id = :author_id");
$stmt->bindParam(':author_id', $author['author_id']);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle article creation
if (isset($_POST['create_article'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = $_POST['category_id'];
    $status = $_POST['status'];
    $tags = isset($_POST['tags']) ? $_POST['tags'] : [];

    // Handle file upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['cover_image']['tmp_name'];
        $file_name = $_FILES['cover_image']['name'];
        $destination = '../../uploads/' . $file_name;

        if (move_uploaded_file($file_tmp_path, $destination)) {
            // Insert file record
            $stmt = $pdo->prepare("INSERT INTO files (user_id, file_path) VALUES (:user_id, :file_path)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':file_path', $file_name);
            $stmt->execute();
            $cover_image_id = $pdo->lastInsertId();
        }
    }

    // Insert news record
    $stmt = $pdo->prepare("INSERT INTO news (author_id, category_id, title, content, status, cover_image_id) VALUES (:author_id, :category_id, :title, :content, :status, :cover_image_id)");
    $stmt->bindParam(':author_id', $author['author_id']);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':cover_image_id', $cover_image_id);
    $stmt->execute();
    $news_id = $pdo->lastInsertId();

    // Associate tags with news
    foreach ($tags as $tag_id) {
        $stmt = $pdo->prepare("INSERT INTO news_tags (news_id, tag_id) VALUES (:news_id, :tag_id)");
        $stmt->bindParam(':news_id', $news_id);
        $stmt->bindParam(':tag_id', $tag_id);
        $stmt->execute();
    }

    header("Location: dashboard.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Author Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>

<body>
    <?php
    include '../../includes/header.php';
    ?>
    <div class="container mt-5">
        <h1>Author Dashboard</h1>

        <!-- Profile Section -->
        <div class="card mt-4">
            <div class="card-body">
                <h2>Profile</h2>
                <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
                <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                <p>
                    <strong>Bio:</strong> <?php echo $author['bio']; ?>
                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editBioModal">Edit</button>
                </p>
            </div>
        </div>

        <!-- Create New Article Button -->
        <div class="mt-4">
            <button class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#createArticleModal">CREATE NEW ARTICLE</button>
        </div>

        <!-- Articles List -->
        <div class="mt-4">
            <h2>Your Articles</h2>
            <ul class="list-group">
                <?php foreach ($articles as $index => $article) : ?>
                    <li class="list-group-item">
                        <?php echo $article['title']; ?>
                        <br>
                        <small>
                            Tags:
                            <?php
                            $stmt = $pdo->prepare("SELECT t.tag_name FROM tags t JOIN news_tags nt ON t.tag_id = nt.tag_id WHERE nt.news_id = :news_id");
                            $stmt->bindParam(':news_id', $article['news_id']);
                            $stmt->execute();
                            $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            echo implode(', ', array_column($tags, 'tag_name'));
                            ?>
                        </small>
                        <input type="hidden" name="id" value="<?php echo $article['news_id'] ?>">
                        <button onclick="handleEditBtn(this)" class="btn btn-sm btn-warning float-right ml-2" data-toggle="modal" data-target="#editArticleModal" article-id="<?php echo $article['news_id'] ?>" article-category="<?php echo $article['category_id'] ?>" article-title="<?php echo $article['title'] ?>" article-content="<?php echo $article['content'] ?>" article-status="<?php echo $article['status'] ?>">Edit</button>
                        <a href="/controllers/author/deletearticle.php?id=<?php echo $article['news_id']; ?>" class="btn btn-sm btn-danger float-right">Delete</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Edit Bio Modal -->
        <div class="modal fade" id="editBioModal" tabindex="-1" role="dialog" aria-labelledby="editBioModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editBioModalLabel">Edit Bio</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="bio">Bio</label>
                                <textarea class="form-control" name="bio" id="bio" required><?php echo $author['bio']; ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="update_bio">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Create Article Modal -->
        <div class="modal fade" id="createArticleModal" tabindex="-1" role="dialog" aria-labelledby="createArticleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <form method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createArticleModalLabel">Create New Article</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select class="form-control" id="category" name="category_id" required>
                                    <?php
                                    $stmt = $pdo->query("SELECT * FROM categories");
                                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($categories as $category) {
                                        echo '<option value="' . $category['category_id'] . '">' . $category['category_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="content">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                                <script>
                                    CKEDITOR.replace('content');
                                </script>
                            </div>
                            <div class="form-group">
                                <label for="tags">Tags</label>
                                <select class="form-control" id="tags" name="tags[]" multiple required>
                                    <?php
                                    $stmt = $pdo->query("SELECT * FROM tags");
                                    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($tags as $tag) {
                                        echo '<option value="' . $tag['tag_id'] . '">' . $tag['tag_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="cover_image">Cover Image</label>
                                <input type="file" class="form-control" id="cover_image" name="cover_image" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="create_article">Create Article</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Article Modal -->
        <div class="modal fade" id="editArticleModal" tabindex="-1" role="dialog" aria-labelledby="createArticleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <form action="/controllers/author/updatearticle.php" id="form-edit" method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createArticleModalLabel">Edit Article</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="article_id" id="edit_article_id">
                            <div class="form-group">
                                <label for="edit_title">Title</label>
                                <input type="text" class="form-control" name="edit_title" id="edit_title" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_category_id">Category</label>
                                <select class="form-control" name="edit_category_id" id="edit_category_id" required>
                                    <!-- Fetch categories from the database -->
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM categories");
                                    $stmt->execute();
                                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($categories as $category) {
                                        echo '<option value="' . $category['category_id'] . '">' . $category['category_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <!-- Dalam modal Create Article dan Edit Article -->
                            <div class="form-group">
                                <label for="edit_tags">Tags</label>
                                <select class="form-control" name="tags[]" id="edit_tags" multiple required>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM tags");
                                    $stmt->execute();
                                    $all_tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($all_tags as $tag) {
                                        echo '<option value="' . $tag['tag_id'] . '">' . $tag['tag_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                                <small class="form-text text-muted">Hold Ctrl (Windows) or Command (Mac) to select multiple tags.</small>
                            </div>
                            <div class="form-group">
                                <label for="edit_content">Content</label>
                                <textarea class="form-control" name="edit_content" id="edit_content" required></textarea>
                                <script>
                                    CKEDITOR.replace('edit_content');
                                </script>
                            </div>
                            <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select class="form-control" name="edit_status" id="edit_status" required>
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_cover_image">Cover Image</label>
                                <input type="file" class="form-control" name="edit_cover_image" id="edit_cover_image">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="update_article">Save Article</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script>
            function handleEditBtn(el) {
                var id = el.getAttribute("article-id");
                var category = el.getAttribute("article-category");
                var title = el.getAttribute("article-title");
                var content = el.getAttribute("article-content");
                var status = el.getAttribute("article-status");

                var modal = document.getElementById("editArticleModal");
                var idField = modal.querySelector('#edit_article_id');
                var titleField = modal.querySelector('#edit_title');
                var categoryField = modal.querySelector('#edit_category_id');
                var contentField = modal.querySelector('#edit_content');
                var statusField = modal.querySelector('#edit_status');

                // Fill form with article data
                idField.value = id;
                titleField.value = title;
                categoryField.value = category;
                statusField.value = status;

                // Replace the existing CKEditor instance
                if (CKEDITOR.instances.edit_content) {
                    CKEDITOR.instances.edit_content.destroy();
                }
                CKEDITOR.replace('edit_content');
                CKEDITOR.instances.edit_content.setData(content);

                // Fetch tags for this article
                fetch('/controllers/author/get_article_tags.php?id=' + id)
                    .then(response => response.json())
                    .then(tags => {
                        var tagSelect = document.getElementById('edit_tags');
                        tags.forEach(tag => {
                            var option = tagSelect.querySelector(`option[value="${tag.tag_id}"]`);
                            if (option) {
                                option.selected = true;
                            }
                        });
                    });
            }
        </script>

</body>

</html>

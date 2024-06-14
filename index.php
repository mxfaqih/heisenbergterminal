<?php
include 'config/database.php';
session_start();

// Fetch categories
$categoriesQuery = "SELECT * FROM categories";
$categoriesStmt = $pdo->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch tags
$tagsQuery = "SELECT * FROM tags";
$tagsStmt = $pdo->prepare($tagsQuery);
$tagsStmt->execute();
$tags = $tagsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch news
$newsQuery = "SELECT news.*, files.file_path FROM news 
              LEFT JOIN files ON news.cover_image_id = files.file_id
              ORDER BY news.news_id DESC";
$newsStmt = $pdo->prepare($newsQuery);
$newsStmt->execute();
$news = $newsStmt->fetchAll(PDO::FETCH_ASSOC);

$topAssets = [
    ['name' => 'Bitcoin', 'price' => 28000, 'change' => 1.5, 'type' => 'Crypto'],
    ['name' => 'Ethereum', 'price' => 1800, 'change' => -0.8, 'type' => 'Crypto'],
    ['name' => 'Apple Inc.', 'price' => 175.24, 'change' => 0.3, 'type' => 'Stock'],
    ['name' => 'Gold', 'price' => 1942.50, 'change' => -0.2, 'type' => 'Commodity'],
    ['name' => 'Crude Oil', 'price' => 68.75, 'change' => 1.8, 'type' => 'Commodity'],
    ['name' => 'Amazon.com', 'price' => 3415.00, 'change' => -1.1, 'type' => 'Stock'],
    ['name' => 'Dogecoin', 'price' => 0.08, 'change' => 3.5, 'type' => 'Crypto'],
    ['name' => 'Wheat', 'price' => 6.85, 'change' => 2.2, 'type' => 'Commodity'],
    ['name' => 'Tesla Inc.', 'price' => 685.47, 'change' => 1.5, 'type' => 'Stock'],
    ['name' => 'Silver', 'price' => 23.15, 'change' => -0.7, 'type' => 'Commodity'],
];

include 'includes/header.php';
?>

<!-- RUNNING TEXT ASSETS -->
<div style="overflow: hidden; background-color: #fff; color: #000; padding: 5px 0; height: 50px; border: 1px solid #000; border-radius: 10px;">
    <div class="running-text" style="display: flex; animation: runningText 30s linear infinite; white-space: nowrap;">
        <?php foreach ($topAssets as $asset) : ?>
            <div class="asset-box" style="background-color: #000; color: #fff; padding: 5px 10px; margin-right: 10px; display: inline-block; border-radius: 10px;">
                <span style="font-weight: bold;"><?php echo $asset['name']; ?></span>
                <span style="color: <?php echo ($asset['change'] >= 0) ? 'green' : 'red'; ?>;">
                    $<?php echo number_format($asset['price'], 2); ?>
                    <span style="font-size: 0.8em;">
                        <?php echo ($asset['change'] >= 0) ? '▲' : '▼'; ?>
                    </span>
                </span>
            </div>
        <?php endforeach; ?>
        <?php foreach ($topAssets as $asset) : ?>
            <div class="asset-box" style="background-color: #000; color: #fff; padding: 5px 10px; margin-right: 10px; display: inline-block; border-radius: 10px;">
                <span style="font-weight: bold;"><?php echo $asset['name']; ?></span>
                <span style="color: <?php echo ($asset['change'] >= 0) ? 'green' : 'red'; ?>;">
                    $<?php echo number_format($asset['price'], 2); ?>
                    <span style="font-size: 0.8em;">
                        <?php echo ($asset['change'] >= 0) ? '▲' : '▼'; ?>
                    </span>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    @keyframes runningText {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-50%);
        }
    }
</style>

<div class="container-fluid mt-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Hero Section for the Latest News -->
            <?php if (!empty($news)) : ?>
                <?php $latestNews = array_shift($news); ?>
                <div class="hero-section mb-5" style="background-image: url('/uploads/<?php echo $latestNews['file_path']; ?>'); background-size: cover; height: 500px; position: relative;">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.7);"></div>
                    <div class="hero-content text-white" style="position: absolute; bottom: 20px; left: 20px; right: 20px;">
                        <span class="badge bg-light text-dark mb-2">Category</span>
                        <h1 class="display-4"><?php echo $latestNews['title']; ?></h1>
                        <p class="lead"><?php echo substr($latestNews['content'], 0, 150); ?>...</p>
                        <a href="views/article_detail.php?id=<?php echo $latestNews['news_id']; ?>" class="btn btn-outline-light">Read More</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Smaller news items -->
            <div class="row">
                <?php foreach ($news as $article) : ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100" style="background-color: #222; color: #eee;">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <div class="card-img" style="background-image: url('/uploads/<?php echo $article['file_path']; ?>'); background-size: cover; height: 100%;"></div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $article['title']; ?></h5>
                                        <p class="card-text small"><?php echo substr($article['content'], 0, 80); ?>...</p>
                                        <a href="views/article_detail.php?id=<?php echo $article['news_id']; ?>" class="btn btn-sm btn-outline-light">Read More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Categories Widget -->
            <div class="card mb-4" style="background-color: #222; color: #eee;">
                <div class="card-header" style="background-color: #333;">Categories</div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($categories as $category) : ?>
                        <li class="list-group-item" style="background-color: #222; color: #eee; transition: background-color 0.3s;">
                            <a href="#" class="text-decoration-none text-light" style="display: block;"><?php echo $category['category_name']; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Tags Widget -->
            <div class="card mb-4" style="background-color: #222; color: #eee;">
                <div class="card-header" style="background-color: #333;">Tags</div>
                <div class="card-body">
                    <?php foreach ($tags as $tag) : ?>
                        <a href="#" class="badge bg-dark text-light me-1 mb-1" style="text-decoration: none; transition: background-color 0.3s;"><?php echo $tag['tag_name']; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- About This Website Widget -->
            <div class="card mb-4" style="background-color: #222; color: #eee;">
                <div class="card-header" style="background-color: #333;">About Heisenberg Terminal</div>
                <div class="card-body">
                    <p class="small">Your trusted source for the latest finance news and analysis. We cover economy, forex, crypto, stocks, and more.</p>
                    <p class="small">Founded by finance and journalism experts, we provide accurate, objective, and timely information to help you make better financial decisions.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php
session_start();
require 'language_loader.php';
require 'db_connect.php';

// Check if logged in
if (!isset($_SESSION['is_logged_in'])) {
    header('Location: /');
    exit();
}
$is_vip = isset($_SESSION['is_vip']) && $_SESSION['is_vip'] === true;

// CSRF token generation for forms
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /home');
    exit();
}
$anime_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];
$current_episode_number = (int)($_GET['ep'] ?? 1);

if ($is_vip) {
    if ($user_id && $anime_id && $current_episode_number) {
        $sql = "INSERT INTO watch_history (user_id, anime_id, episode_number) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE watched_at = CURRENT_TIMESTAMP";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $anime_id, $current_episode_number]);
    }
    $history_stmt = $pdo->prepare("SELECT episode_number FROM watch_history WHERE user_id = ? AND anime_id = ?");
    $history_stmt->execute([$user_id, $anime_id]);
    $watched_episodes = $history_stmt->fetchAll(PDO::FETCH_COLUMN, 0);
} else {
    $watched_episodes = [];
}

$stmt_anime = $pdo->prepare("SELECT * FROM animes WHERE id = ?");
$stmt_anime->execute([$anime_id]);
$anime = $stmt_anime->fetch(PDO::FETCH_ASSOC);
if (!$anime) {
    header('Location: /home');
    exit();
}

$stmt_episodes = $pdo->prepare("SELECT * FROM episodes WHERE anime_id = ? ORDER BY episode_number ASC");
$stmt_episodes->execute([$anime_id]);
$episodes = $stmt_episodes->fetchAll(PDO::FETCH_ASSOC);

$current_video_url = '';
$current_title = '';
$found_ep = false;
if (!empty($episodes)) {
    foreach ($episodes as $ep) {
        if ($ep['episode_number'] == $current_episode_number) {
            $current_video_url = $ep['video_url'];
            $current_title = "EP {$ep['episode_number']} - {$ep['title']}";
            $found_ep = true;
            break;
        }
    }
    if (!$found_ep) {
        $current_video_url = $episodes[0]['video_url'];
        $current_episode_number = $episodes[0]['episode_number'];
        $current_title = "EP {$episodes[0]['episode_number']} - {$episodes[0]['title']}";
    }
} else {
    $current_episode_number = 1;
}

$stmt_check_fav = $pdo->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND anime_id = ?");
$stmt_check_fav->execute([$user_id, $anime_id]);
$is_in_list = $stmt_check_fav->fetch() ? true : false;

// Fetch reviews for this anime
$review_stmt = $pdo->prepare(
    "SELECT c.review_title, c.review_body, c.created_at, u.username 
     FROM comments c 
     JOIN users u ON c.user_id = u.id 
     WHERE c.anime_id = ? 
     ORDER BY c.created_at DESC"
);
$review_stmt->execute([$anime_id]);
$reviews = $review_stmt->fetchAll(PDO::FETCH_ASSOC);
$current_page_nav = ''; 
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($anime['title']); ?> - <?php echo $lang['aether_stream']; ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="/style.css?v=1.9">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .rating-display { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; font-size: 16px; }
        .rating-display .stars { color: #f39c12; }
        .rating-display .rating-value { font-weight: bold; color: #fff; }
        .rating-display .rating-count { color: #888; }
        .rate-this { margin-top: 20px; background-color: #222; padding: 15px; border-radius: 8px; }
        .rate-this h4 { margin:0 0 10px 0; }
        .star-rating-form { display: flex; flex-direction: row-reverse; justify-content: flex-end; }
        .star-rating-form input[type="radio"] { display: none; }
        .star-rating-form label { font-size: 24px; color: #444; cursor: pointer; transition: color 0.2s; }
        .star-rating-form input[type="radio"]:checked ~ label,
        .star-rating-form label:hover,
        .star-rating-form label:hover ~ label { color: #f39c12; }

        /* Review Section Styles */
        .review-title { color: #fff; font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .review-author { font-size: 14px; color: #007AFF; margin-bottom: 5px; }
        .review-date { font-size: 12px; color: #888; margin-bottom: 10px; }
        .review-body p { color: #ccc; line-height: 1.6; margin: 0; }
    </style>
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <div class="sidebar-logo"><?php echo $lang['aether_stream']; ?></div>
            <nav class="sidebar-nav">
                <a href="/home" class="nav-item">Home</a>
                <a href="/browse" class="nav-item">Browse</a>
                <a href="/manhwas" class="nav-item">Manhwa</a>
                <a href="/my_list" class="nav-item">My List</a>
                <a href="/history" class="nav-item">History</a>
                <a href="/settings" class="nav-item">Settings</a>
            </nav>
            <div class="sidebar-footer">
                <a href="/logout" class="logout-btn">Logout</a>
            </div>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="search-bar">
                    <a href="/home" class="back-link"><?php echo $lang['back_to_home']; ?></a>
                </div>
                <div class="user-profile">
                    <div class="language-switcher">
                        <a href="/change_language?lang=en" class="<?php echo ($_SESSION['lang'] === 'en') ? 'active' : ''; ?>">EN</a> |
                        <a href="/change_language?lang=mm" class="<?php echo ($_SESSION['lang'] === 'mm') ? 'active' : ''; ?>">MM</a>
                    </div>
                    <?php if ($is_vip): ?>
                        <span><?php echo $lang['vip_member']; ?></span>
                    <?php else: ?>
                        <a href="/purchase_vip" class="btn btn-primary">Get VIP</a>
                    <?php endif; ?>
                </div>
            </header>

            <?php if ($is_vip): ?>
                <section class="watch-area">
                    <div class="video-player-container">
                        <?php if ($current_video_url !== '' && $current_video_url !== 'your_video_link_here.mp4'): ?>
                            <video id="anime-player" controls width="100%" autoplay>
                                <source id="video-source" src="<?php echo htmlspecialchars($current_video_url); ?>" type="video/mp4">
                            </video>
                        <?php else: ?>
                            <div class="video-placeholder">
                                <h3><?php echo $lang['video_unavailable']; ?></h3>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <section class="series-info-and-episodes">
                    <div class="series-info">
                        <h1 class="series-title"><?php echo htmlspecialchars($anime['title']); ?></h1>
                        <div class="rating-display">
                            <span class="stars">
                                <?php $rating = round($anime['average_rating']); for ($i = 1; $i <= 5; $i++): ?><i class="<?php echo ($i <= $rating) ? 'fas' : 'far'; ?> fa-star"></i><?php endfor; ?>
                            </span>
                            <span class="rating-value"><?php echo number_format($anime['average_rating'], 1); ?></span>
                            <span class="rating-count">(<?php echo $anime['rating_count']; ?> votes)</span>
                        </div>
                        <h2 id="episode-title-display" style="color: #ccc; font-size: 1.2em; margin-bottom: 10px;"><?php echo htmlspecialchars($current_title); ?></h2>
                        <p class="series-description"><?php echo htmlspecialchars($anime['description']); ?></p>
                        <div class="series-meta">
                            <strong><?php echo $lang['genre']; ?>:</strong>
                            <span class="genre-tags">
                                <?php $genres = explode(',', $anime['genre']); foreach ($genres as $genre_item): $genre_item = trim($genre_item); if (!empty($genre_item)): echo '<a href="/genre?name=' . urlencode($genre_item) . '" class="genre-tag">' . htmlspecialchars($genre_item) . '</a>'; endif; endforeach; ?>
                            </span>
                        </div>
                        <form action="/toggle_list" method="POST" style="margin-top: 20px; display: inline-block;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="anime_id" value="<?php echo $anime_id; ?>">
                            <button type="submit" class="btn <?php echo $is_in_list ? 'btn-secondary' : 'btn-primary'; ?>">
                                <?php echo $is_in_list ? $lang['remove_from_my_list'] : $lang['add_to_my_list']; ?>
                            </button>
                        </form>
                        <div class="rate-this">
                            <h4>Rate this series:</h4>
                            <form action="/submit_rating.php" method="POST">
                                <input type="hidden" name="anime_id" value="<?php echo $anime_id; ?>">
                                <div class="star-rating-form">
                                    <input type="radio" id="star5" name="rating" value="5" onchange="this.form.submit()"><label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star4" name="rating" value="4" onchange="this.form.submit()"><label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star3" name="rating" value="3" onchange="this.form.submit()"><label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star2" name="rating" value="2" onchange="this.form.submit()"><label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star1" name="rating" value="1" onchange="this.form.submit()"><label for="star1" title="1 star"><i class="fas fa-star"></i></label>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="episode-list-area">
                        <h2 class="episode-list-title"><?php echo $lang['episodes']; ?></h2>
                        <div class="episode-list">
                            <?php if (!empty($episodes)): foreach($episodes as $episode): ?>
                                <a href="/watch?id=<?php echo $anime_id; ?>&ep=<?php echo $episode['episode_number']; ?>" class="episode-item <?php echo ($episode['episode_number'] == $current_episode_number) ? 'active' : ''; ?>">
                                    <span class="episode-number">EP <?php echo htmlspecialchars($episode['episode_number']); ?></span>
                                    <span class="episode-title"><?php echo htmlspecialchars($episode['title']); ?></span>
                                    <?php if (in_array($episode['episode_number'], $watched_episodes)): ?><span class="watched-indicator">✔️</span><?php endif; ?>
                                </a>
                            <?php endforeach; else: ?><p><?php echo $lang['no_episodes_found']; ?></p><?php endif; ?>
                        </div>
                    </div>
                </section>
                
                <section class="comment-section">
                    <h2 class="comment-section-title">Reviews & Comments</h2>
                    <div class="comment-form-container">
                        <form action="/post_comment" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="anime_id" value="<?php echo $anime_id; ?>">
                            <div class="form-group">
                                <input type="text" name="review_title" class="input-field" placeholder="Review Title (e.g., My Favorite Anime!)" style="margin-bottom:10px;" required>
                            </div>
                            <div class="form-group">
                                <textarea name="review_body" placeholder="Write your review here..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Post Review</button>
                        </form>
                    </div>
                    <div class="comment-list">
                        <?php if (empty($reviews)): ?>
                            <p>Be the first to write a review!</p>
                        <?php else: foreach ($reviews as $review): ?>
                            <div class="comment-item">
                                <h3 class="review-title"><?php echo htmlspecialchars($review['review_title']); ?></h3>
                                <p class="review-author">by <?php echo htmlspecialchars($review['username']); ?></p>
                                <p class="review-date"><?php echo (new DateTime($review['created_at']))->format('M j, Y'); ?></p>
                                <div class="review-body">
                                    <p><?php echo nl2br(htmlspecialchars($review['review_body'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </section>
            
            <?php else: ?>
                <div class="series-info"><h1 class="series-title"><?php echo htmlspecialchars($anime['title']); ?></h1></div>
                <div class="video-player-container" style="background-color: #1a1a1a;">
                    <div class="message error" style="text-align: center; padding: 40px;">
                        <h3>This content is for VIP members only.</h3>
                        <p style="margin: 10px 0 20px 0;"><?php echo $lang['vip_needed_message']; ?></p>
                        <a href="/purchase_vip" class="btn btn-primary">Upgrade to VIP Now</a>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
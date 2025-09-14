<?php
session_start();
require 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$postId = $_GET['id'];

// Получаем пост
$stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = :id");
$stmt->execute(['id' => $postId]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: index.php');
    exit;
}

// Обработка формы добавления комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author = trim($_POST['author']);
    $comment = trim($_POST['comment']);

    if (!empty($author) && !empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, author, comment) VALUES (:post_id, :author, :comment)");
        $stmt->execute(['post_id' => $postId, 'author' => $author, 'comment' => $comment]);
        header('Location: post.php?id=' . $postId);
        exit;
    } else {
        $error = "Заполните все поля!";
    }
}

// Получаем комментарии
$stmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = :post_id ORDER BY created_at DESC");
$stmt->execute(['post_id' => $postId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4"><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="card mb-4">
            <div class="card-body">
                <p class="card-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                <p class="card-text"><small class="text-muted">Автор: <?php echo htmlspecialchars($post['username']); ?></small></p>
                <p class="card-text"><small class="text-muted">Создано: <?php echo $post['created_at']; ?></small></p>
            </div>
        </div>

        <!-- Форма добавления комментария -->
        <h2 class="mb-3">Комментарии</h2>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Оставить комментарий</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Имя</label>
                        <input type="text" class="form-control" name="author">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Комментарий</label>
                        <textarea class="form-control" name="comment" rows="4"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Отправить</button>
                </form>
                <?php if (isset($error)) echo "<div class='alert alert-danger mt-3'>$error</div>"; ?>
            </div>
        </div>

        <!-- Список комментариев -->
        <?php if ($comments): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="card mb-2">
                    <div class="card-body">
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                        <p class="card-text"><small class="text-muted">Автор: <?php echo htmlspecialchars($comment['author']); ?></small></p>
                        <p class="card-text"><small class="text-muted">Создано: <?php echo $comment['created_at']; ?></small></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Комментариев пока нет.</p>
        <?php endif; ?>
        <p class="mt-3"><a href="index.php" class="btn btn-outline-secondary">Назад к постам</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
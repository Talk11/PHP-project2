<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Получаем имя пользователя
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$username = $user['username'] ?? 'Гость';

// Обработка формы добавления поста
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (!empty($title) && !empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id) VALUES (:title, :content, :user_id)");
        $stmt->execute(['title' => $title, 'content' => $content, 'user_id' => $_SESSION['user_id']]);
        header('Location: index.php');
        exit;
    } else {
        $error = "Заполните все поля!";
    }
}

// Получаем список постов
$stmt = $pdo->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мини-блог</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Мини-блог</h1>
        <p class="text-muted">Привет, <?php echo htmlspecialchars($username); ?>!</p>

        <!-- Форма добавления поста -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Добавить пост</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Заголовок</label>
                        <input type="text" class="form-control" name="title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Содержание</label>
                        <textarea class="form-control" name="content" rows="5"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Опубликовать</button>
                </form>
                <?php if (isset($error)) echo "<div class='alert alert-danger mt-3'>$error</div>"; ?>
            </div>
        </div>

        <!-- Список постов -->
        <h2 class="mb-3">Посты</h2>
        <?php if ($posts): ?>
            <div class="row">
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                                <p class="card-text"><?php echo substr(htmlspecialchars($post['content']), 0, 100) . '...'; ?></p>
                                <p class="card-text"><small class="text-muted">Автор: <?php echo htmlspecialchars($post['username']); ?></small></p>
                                <p class="card-text"><small class="text-muted">Создано: <?php echo $post['created_at']; ?></small></p>
                                <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary">Читать далее</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">Постов пока нет.</p>
        <?php endif; ?>
        <p class="mt-3"><a href="logout.php" class="btn btn-outline-secondary">Выйти</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
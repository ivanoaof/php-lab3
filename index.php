<?php
$usersFile = __DIR__ . '/users.json';
$message = "";
$currentUser = null;

// Функция для получения всех пользователей
function getUsers($file) {
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?: [];
}

// Функция для сохранения всех пользователей
function saveUsers($file, $users) {
    file_put_contents($file, json_encode($users));
}

// Получение текущего пользователя из cookie
if (isset($_COOKIE['username'])) {
    $settings = isset($_COOKIE['settings']) ? json_decode($_COOKIE['settings'], true) : ['bg_color'=>'#ffffff','font_color'=>'#000000'];
    $currentUser = ['username'=>$_COOKIE['username'], 'settings'=>$settings];
}

// Обработка POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $users = getUsers($usersFile);

    // Регистрация
    if (isset($_POST['register'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $settings = [
            'bg_color' => $_POST['bg_color'] ?? '#ffffff',
            'font_color' => $_POST['font_color'] ?? '#000000'
        ];

        if (isset($users[$username])) {
            $message = "Пользователь уже существует!";
        } else {
            $users[$username] = ['password'=>password_hash($password, PASSWORD_DEFAULT), 'settings'=>$settings];
            saveUsers($usersFile, $users);
            $message = "Регистрация успешна!";
        }
    }

    // Вход
    if (isset($_POST['login'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (isset($users[$username]) && password_verify($password, $users[$username]['password'])) {
            setcookie('username', $username, time()+3600, '/');
            setcookie('settings', json_encode($users[$username]['settings']), time()+3600, '/');
            $currentUser = ['username'=>$username,'settings'=>$users[$username]['settings']];
            $message = "Вход успешен!";
        } else {
            $message = "Неверный логин или пароль!";
        }
    }

    // Выход
    if (isset($_POST['logout'])) {
        setcookie('username', '', time()-3600, '/');
        setcookie('settings', '', time()-3600, '/');
        $currentUser = null;
        $message = "Вы вышли";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lab3 PHP</title>
</head>
<body <?php if($currentUser) echo "style='background: {$currentUser['settings']['bg_color']}; color: {$currentUser['settings']['font_color']}'"; ?>>

<h1>Lab3 PHP с Cookies</h1>

<?php if($message) echo "<p><strong>$message</strong></p>"; ?>

<?php if(!$currentUser): ?>
<h2>Регистрация</h2>
<form method="post">
    <input name="username" placeholder="Имя пользователя" required>
    <input name="password" type="password" placeholder="Пароль" required>
    <label>Фон:
        <input type="color" name="bg_color" value="#ffffff">
    </label>
    <label>Цвет шрифта:
        <input type="color" name="font_color" value="#000000">
    </label>
    <button type="submit" name="register">Зарегистрироваться</button>
</form>

<h2>Вход</h2>
<form method="post">
    <input name="username" placeholder="Имя пользователя" required>
    <input name="password" type="password" placeholder="Пароль" required>
    <button type="submit" name="login">Войти</button>
</form>
<?php else: ?>
<h2>Привет, <?= htmlspecialchars($currentUser['username']) ?>!</h2>
<p>Фон: <?= $currentUser['settings']['bg_color'] ?>, Цвет шрифта: <?= $currentUser['settings']['font_color'] ?></p>
<form method="post">
    <button type="submit" name="logout">Выйти</button>
</form>
<?php endif; ?>

</body>
</html>

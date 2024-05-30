<?php
header('Content-Type: application/json');

$host = 'localhost';
$db = 'event_organizer';
$user = 'root';
$pass = 'truman94';  // No password for root
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['user_id'])) {
            $user_id = intval($_GET['user_id']);
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            echo json_encode($user);
        } else {
            $stmt = $pdo->query("SELECT * FROM users");
            $users = $stmt->fetchAll();
            echo json_encode($users);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['username']) && isset($data['password']) && isset($data['email'])) {
            $username = $data['username'];
            $pass = password_hash($data['password'], PASSWORD_BCRYPT);  // Hash the password before storing
            $email = $data['email'];
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $pass, $email])) {
                echo json_encode(["user_id" => $pdo->lastInsertId()]);
            } else {
                echo json_encode(["error" => $stmt->errorInfo()]);
            }
        } else {
            echo json_encode(["error" => "Invalid input"]);
        }
        break;
    default:
        echo json_encode(["error" => "Unsupported request method"]);
        break;
}
?>

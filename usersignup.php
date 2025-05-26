<?php
require_once 'db.php'; // include the database connection

header('Content-Type: application/json'); // return JSON responses

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data and trim spaces
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $address = trim($_POST['address'] ?? '');

    // Basic validation
    if (empty($username) || empty($password) || empty($password2) || empty($address)) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required.']);
        exit;
    }

    if ($password !== $password2) {
        http_response_code(400);
        echo json_encode(['error' => 'Passwords do not match.']);
        exit;
    }

    // Optional: check username length or format here

    // Hash password securely
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM user WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            http_response_code(409); // Conflict
            echo json_encode(['error' => 'Username already taken.']);
            exit;
        }

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO user (username, password, address) VALUES (?, ?, ?)");
        $stmt->execute([$username, $passwordHash, $address]);

        echo json_encode(['success' => 'User registered successfully!']);
        exit;

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        exit;
    }

} else {
    http_response_code(405); // Method not allowed
    echo json_encode(['error' => 'Only POST requests are allowed.']);
    exit;
}

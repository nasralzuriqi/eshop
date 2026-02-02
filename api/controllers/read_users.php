<?php
header("Content-Type: application/json; charset=UTF-8");
include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../models/User.php';

$database = Database::getInstance();
$db = $database->getConnection();
$user = new User($db);

$stmt = $user->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $users_arr = [];
    $users_arr['status'] = 'success';
    $users_arr['data'] = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $user_item = [
            'id' => $id,
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'created_at' => $created_at
        ];
        array_push($users_arr['data'], $user_item);
    }
    http_response_code(200);
    echo json_encode($users_arr);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'No users found.']);
}

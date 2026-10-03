<?php

require '../config/connect_db.php'; // เชื่อมต่อฐานข้อมูล

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? '';
$line_api_token = isset($_POST['line_api_token']) ? trim($_POST['line_api_token']) : '';
$doc_type = isset($_POST['doc_type']) ? trim($_POST['doc_type']) : '';
$detail = $_POST['detail'] ?? '';

if ($action == 'insert') {
    $stmt = $conn->prepare("INSERT INTO aline_api (line_api_token, doc_type, detail) VALUES (:token, :doc_type, :detail)");
    $stmt->bindParam(':token', $line_api_token);
    $stmt->bindParam(':doc_type', $doc_type);
    $stmt->bindParam(':detail', $detail);
    $stmt->execute();
} elseif ($action == 'update') {
    $stmt = $conn->prepare("UPDATE aline_api SET line_api_token = :token, doc_type = :doc_type, detail = :detail WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':token', $line_api_token);
    $stmt->bindParam(':doc_type', $doc_type);
    $stmt->bindParam(':detail', $detail);
    $stmt->execute();
} elseif ($action == 'delete') {
    $stmt = $conn->prepare("DELETE FROM aline_api WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
} else {
    $stmt = $conn->query("SELECT * FROM aline_api ORDER BY id DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td class='id'>{$row['id']}</td>
                <td class='token'>{$row['line_api_token']}</td>
                <td class='doc_type'>{$row['doc_type']}</td>
                <td class='detail'>{$row['detail']}</td>
                <td>
                    <button class='btn btn-warning btn-sm edit'>Edit</button>
                    <button class='btn btn-danger btn-sm delete' data-id='{$row['id']}'>Delete</button>
                </td>
              </tr>";
    }
}



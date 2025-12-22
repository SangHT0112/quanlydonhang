<?php
include '../config.php';
checkLogin();

$role = $_SESSION['role'];

$stmt = $conn->prepare("
  SELECT * FROM system_messages
  WHERE receiver_role = ?
  ORDER BY created_at ASC
  LIMIT 20
");
$stmt->bind_param("s", $role);
$stmt->execute();

echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));

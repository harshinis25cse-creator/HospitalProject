
<?php
include "db.php";

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "POST method required."
    ]);
    exit;
}

$id = filter_input(INPUT_POST, "patientId", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid patient ID."
    ]);
    exit;
}

$stmt = $conn->prepare(
    "DELETE FROM patient WHERE id = ? AND status = 'Waiting'"
);

$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows === 1) {
    echo json_encode([
        "success" => true,
        "message" => "Waiting patient registration cancelled."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Patient not found or cannot be cancelled. Only Waiting patients can be deleted."
    ]);
}

$stmt->close();
$conn->close();
?>

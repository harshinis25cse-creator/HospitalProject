
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
    "UPDATE patient
     SET status = 'Discharged',
         discharge_date = CURDATE(),
         bed_number = NULL
     WHERE id = ?
       AND (
           status IN ('Allocated', 'Admitted')
           OR status = '0'
           OR status = '1'
       )
       AND bed_number IS NOT NULL"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database query failed: " . $conn->error
    ]);
    $conn->close();
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows === 1) {
        echo json_encode([
            "success" => true,
            "message" => "Patient discharged successfully. Bed released."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Patient not found, already discharged, or no bed assigned."
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Discharge failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>

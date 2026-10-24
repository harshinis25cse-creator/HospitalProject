
<?php
include "db.php";

header("Content-Type: application/json; charset=UTF-8");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}

$sql = "SELECT
            id,
            name,
            age,
            health_issue,
            priority,
            bed_number,
            status,
            admission_date,
            discharge_date
        FROM patient
        ORDER BY id DESC";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to load patients."
    ]);
    exit;
}

$patients = [];

while ($row = $result->fetch_assoc()) {
    $status = strtolower(trim((string)($row["status"] ?? "")));

    if ($status === "0" && $row["bed_number"] !== null) {
        $row["status"] = "Allocated";
    } elseif ($status === "allocated") {
        $row["status"] = "Allocated";
    } elseif ($status === "waiting") {
        $row["status"] = "Waiting";
    } elseif ($status === "discharged") {
        $row["status"] = "Discharged";
    }

    $patients[] = $row;
}

echo json_encode([
    "success" => true,
    "patients" => $patients
]);

$conn->close();
?>

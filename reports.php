
<?php
include "db.php";

header("Content-Type: application/json");

if (!isset($conn) || $conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

$sql = "SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'Admitted' THEN 1 ELSE 0 END) AS admitted,
    SUM(CASE WHEN status = 'Waiting' THEN 1 ELSE 0 END) AS waiting,
    SUM(CASE WHEN status = 'Discharged' THEN 1 ELSE 0 END) AS discharged
    FROM patient";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => $conn->error
    ]);
    exit;
}

$counts = $result->fetch_assoc();

$healthResult = $conn->query(
    "SELECT health_issue, COUNT(*) AS total
     FROM patient
     GROUP BY health_issue"
);

$healthIssues = [];

if ($healthResult) {
    while ($row = $healthResult->fetch_assoc()) {
        $healthIssues[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "total" => (int)$counts["total"],
    "admitted" => (int)$counts["admitted"],
    "waiting" => (int)$counts["waiting"],
    "discharged" => (int)$counts["discharged"],
    "health_issues" => $healthIssues
]);

$conn->close();
?>

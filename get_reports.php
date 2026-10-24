
<?php
include "db.php";

header("Content-Type: application/json; charset=UTF-8");

try {
    $date = $_GET["date"] ?? "";

    $counts = [
        "total" => 0,
        "admitted" => 0,
        "waiting" => 0,
        "discharged" => 0
    ];

    $sql = "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(status = 'Allocated'), 0) AS admitted,
        COALESCE(SUM(status = 'Waiting'), 0) AS waiting,
        COALESCE(SUM(status = 'Discharged'), 0) AS discharged
        FROM patient";

    $result = $conn->query($sql);
    $counts = $result->fetch_assoc();

    $healthIssues = [];
    $sql = "SELECT priority, COUNT(*) AS total
            FROM patient
            GROUP BY priority
            ORDER BY priority DESC";

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $healthIssues[] = [
            "priority" => $row["priority"],
            "total" => (int)$row["total"]
        ];
    }

    $daily = null;

    if ($date !== "") {
        $parsed = DateTime::createFromFormat("!Y-m-d", $date);

        if (!$parsed || $parsed->format("Y-m-d") !== $date) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Invalid date"
            ]);
            exit;
        }

        $stmt = $conn->prepare(
            "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(status = 'Allocated'), 0) AS admitted,
                COALESCE(SUM(status = 'Waiting'), 0) AS waiting,
                COALESCE(SUM(status = 'Discharged'), 0) AS discharged
             FROM patient
             WHERE DATE(admission_date) = ?"
        );

        $stmt->bind_param("s", $date);
        $stmt->execute();
        $daily = $stmt->get_result()->fetch_assoc();
    }

    echo json_encode([
        "success" => true,
        "counts" => $counts,
        "healthIssues" => $healthIssues,
        "daily" => $daily
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to load reports. Check database columns."
    ]);
}
?>


<?php
include "db.php";

header("Content-Type: text/plain; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Invalid request method.");
}

// Get form values
$patient_id = filter_input(INPUT_POST, "patientId", FILTER_VALIDATE_INT);
$name = trim($_POST["patientName"] ?? "");
$age = filter_input(INPUT_POST, "age", FILTER_VALIDATE_INT);
$health_issue = trim($_POST["healthIssue"] ?? "");

// Validate form values
if (
    !$patient_id || $patient_id < 1 ||
    $name === "" ||
    !$age || $age < 1 || $age > 120 ||
    !in_array(
        $health_issue,
        ["Critical", "High", "Medium", "Low"],
        true
    )
) {
    http_response_code(400);
    exit("Please enter a valid Patient ID, name, age and health issue.");
}

// Automatic priority
$priorities = [
    "Critical" => 5,
    "High"     => 4,
    "Medium"   => 3,
    "Low"      => 1
];

$priority = $priorities[$health_issue];

// Prevent simultaneous allocation requests
$lockResult = $conn->query(
    "SELECT GET_LOCK('hospital_bed_allocation', 10) AS lock_status"
);

if (!$lockResult) {
    http_response_code(500);
    exit("Unable to start allocation: " . $conn->error);
}

$lockRow = $lockResult->fetch_assoc();

if ((int)$lockRow["lock_status"] !== 1) {
    http_response_code(503);
    $conn->close();
    exit("Bed allocation is busy. Please try again.");
}

try {
    // Check duplicate patient ID
    $check = $conn->prepare(
        "SELECT id FROM patient WHERE id = ?"
    );

    if (!$check) {
        throw new Exception(
            "Patient ID check failed: " . $conn->error
        );
    }

    $check->bind_param("i", $patient_id);
    $check->execute();
    $check->store_result();

    $duplicate = $check->num_rows > 0;
    $check->close();

    if ($duplicate) {
        http_response_code(409);
        throw new Exception(
            "Patient ID already exists. Enter a different ID."
        );
    }

    $bed_number = null;
    $status = "Waiting";

    /*
     * These statuses represent occupied beds in the current
     * database. A bed is occupied only when discharge_date
     * is NULL and the patient still has that bed assigned.
     */
    $activeStatuses = "'Allocated', 'Admitted', '0', '1'";

    // Find first free normal bed: 1 to 10
    $normalQuery = "
        SELECT b.bed_no
        FROM (
            SELECT 1 AS bed_no
            UNION ALL SELECT 2
            UNION ALL SELECT 3
            UNION ALL SELECT 4
            UNION ALL SELECT 5
            UNION ALL SELECT 6
            UNION ALL SELECT 7
            UNION ALL SELECT 8
            UNION ALL SELECT 9
            UNION ALL SELECT 10
        ) AS b
        WHERE NOT EXISTS (
            SELECT 1
            FROM patient p
            WHERE p.bed_number = b.bed_no
              AND p.status IN ($activeStatuses)
              AND p.discharge_date IS NULL
        )
        ORDER BY b.bed_no ASC
        LIMIT 1
    ";

    $normalResult = $conn->query($normalQuery);

    if (!$normalResult) {
        throw new Exception(
            "Normal bed check failed: " . $conn->error
        );
    }

    if ($normalResult->num_rows > 0) {
        $row = $normalResult->fetch_assoc();
        $bed_number = (int)$row["bed_no"];
        $status = "Allocated";
    }

    // Buffer beds 101-103: Critical patients only
    if ($bed_number === null && $health_issue === "Critical") {

        $bufferQuery = "
            SELECT b.bed_no
            FROM (
                SELECT 101 AS bed_no
                UNION ALL SELECT 102
                UNION ALL SELECT 103
            ) AS b
            WHERE NOT EXISTS (
                SELECT 1
                FROM patient p
                WHERE p.bed_number = b.bed_no
                  AND p.status IN ($activeStatuses)
                  AND p.discharge_date IS NULL
            )
            ORDER BY b.bed_no ASC
            LIMIT 1
        ";

        $bufferResult = $conn->query($bufferQuery);

        if (!$bufferResult) {
            throw new Exception(
                "Buffer bed check failed: " . $conn->error
            );
        }

        if ($bufferResult->num_rows > 0) {
            $row = $bufferResult->fetch_assoc();
            $bed_number = (int)$row["bed_no"];
            $status = "Allocated";
        }
    }

    // Set admission date only when a bed is allocated
    $admission_date = ($status === "Allocated")
        ? date("Y-m-d")
        : null;

    // Save patient in database
    $sql = "
        INSERT INTO patient
        (
            id,
            name,
            age,
            health_issue,
            priority,
            bed_number,
            status,
            admission_date
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            "Save query preparation failed: " . $conn->error
        );
    }

    $stmt->bind_param(
        "isisiiis",
        $patient_id,
        $name,
        $age,
        $health_issue,
        $priority,
        $bed_number,
        $status,
        $admission_date
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        throw new Exception("Patient save failed: " . $error);
    }

    $stmt->close();

    echo "Patient registered successfully!"
        . " | ID: " . $patient_id
        . " | Status: " . $status
        . " | Bed: " . ($bed_number ?? "Not allocated");

} catch (Exception $e) {

    if (http_response_code() < 400) {
        http_response_code(500);
    }

    echo $e->getMessage();

} finally {

    $conn->query(
        "SELECT RELEASE_LOCK('hospital_bed_allocation')"
    );

    $conn->close();
}
?>

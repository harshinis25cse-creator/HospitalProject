
<?php
include "db.php";

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "POST request required"
    ]);
    exit;
}

$patient_id = filter_input(
    INPUT_POST,
    "patient_id",
    FILTER_VALIDATE_INT
);

if (!$patient_id || $patient_id < 1) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Enter a valid patient ID"
    ]);
    exit;
}

$diagnosis = trim($_POST["final_diagnosis"] ?? "");
$summary = trim($_POST["treatment_summary"] ?? "");
$medicines = trim($_POST["discharge_medicines"] ?? "");
$instructions = trim($_POST["home_care_instructions"] ?? "");
$follow_up = trim($_POST["follow_up_date"] ?? "");
$doctor = trim($_POST["doctor_name"] ?? "");

if ($diagnosis === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Final diagnosis is required"
    ]);
    exit;
}

if ($follow_up !== "") {
    $date = DateTime::createFromFormat("!Y-m-d", $follow_up);

    if (!$date || $date->format("Y-m-d") !== $follow_up) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid follow-up date"
        ]);
        exit;
    }
}

$conn->begin_transaction();

try {
    // Check patient exists and is not already discharged
    $check = $conn->prepare(
        "SELECT status FROM patient WHERE id = ? FOR UPDATE"
    );
    $check->bind_param("i", $patient_id);
    $check->execute();

    $patient = $check->get_result()->fetch_assoc();

    if (!$patient) {
        throw new Exception("Patient ID not found");
    }

    if (strtolower($patient["status"]) === "discharged") {
        throw new Exception("Patient is already discharged");
    }

    // Save discharge details
    $sql = "INSERT INTO discharge_details
    (patient_id, final_diagnosis, treatment_summary,
     discharge_medicines, home_care_instructions,
     follow_up_date, doctor_name)
    VALUES (?, ?, ?, ?, ?, NULLIF(?, ''), ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssss",
        $patient_id,
        $diagnosis,
        $summary,
        $medicines,
        $instructions,
        $follow_up,
        $doctor
    );
    $stmt->execute();

    // Update patient status
    $update = $conn->prepare(
        "UPDATE patient
         SET status = 'Discharged'
         WHERE id = ?"
    );
    $update->bind_param("i", $patient_id);
    $update->execute();

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Patient discharged successfully"
    ]);

} catch (Throwable $e) {
    $conn->rollback();

    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>

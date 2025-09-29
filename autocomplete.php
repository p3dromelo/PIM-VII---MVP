<?php
include("includes/db.php");

$q = isset($_GET['q']) ? trim($_GET['q']) : "";

if ($q === "") {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id, titulo, jogo FROM videos WHERE titulo LIKE ? OR jogo LIKE ? ORDER BY data_upload DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$searchTerm = "%$q%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = [
        "id" => $row["id"],
        "titulo" => $row["titulo"],
        "jogo" => $row["jogo"]
    ];
}

echo json_encode($suggestions);
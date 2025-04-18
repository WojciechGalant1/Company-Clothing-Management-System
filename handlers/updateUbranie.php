<?php
include_once __DIR__ . '/../app/controllers/StanMagazynuC.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nazwa = $_POST['nazwa'];
    $rozmiar = $_POST['rozmiar'];
    $ilosc = $_POST['ilosc'];
    $iloscMin = $_POST['iloscMin'];
    $uwagi = $_POST['uwagi'];

    $stanMagazynuC = new StanMagazynuC();

    $result = $stanMagazynuC->updateStanMagazynu($id, $nazwa, $rozmiar, $ilosc, $iloscMin, $uwagi);

    if ($result['status'] === 'success') {
        http_response_code(200);
    } elseif ($result['status'] === 'not_found') {
        http_response_code(404);
    } else {
        http_response_code(500);
    }
    echo json_encode($result);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'method_not_allowed', 'message' => 'Metoda niedozwolona.']);
}
?>

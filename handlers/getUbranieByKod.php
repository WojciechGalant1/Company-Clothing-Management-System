<?php
include_once __DIR__ . '/../app/controllers/KodC.php';

if (isset($_GET['kod'])) {
    $kodC = new KodC();
    $kodData = $kodC->findByNazwa($_GET['kod']);

    if ($kodData) {
        $response = [
            'id_ubrania' => $kodData['id_ubrania'],
            'nazwa_ubrania' => $kodData['nazwa_ubrania'],
            'id_rozmiar' => $kodData['id_rozmiar'],
            'nazwa_rozmiaru' => $kodData['nazwa_rozmiaru'],
        ];
    } else {
        $response = ['error' => 'Nie znaleziono kodu'];
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>



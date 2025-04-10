<?php
include_once __DIR__ . '/../app/controllers/WydaneUbraniaC.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($data['id']) ? $data['id'] : null;

    if ($id) {
        $wydaneUbraniaC = new WydaneUbraniaC();
        $success = $wydaneUbraniaC->destroyStatus($id);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Nie udało się zaktualizować statusu.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Nie podano ID ubrania.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Metoda niedozwolona.']);
    exit;
}


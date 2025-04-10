<?php
include_once __DIR__ . '/../database/Database.php';
include_once __DIR__ . '/../models/WydaneUbrania.php';

class WydaneUbraniaC extends Database {

    private $pdo;
    private $currentDate;
    private $twoMonthsAhead; 
    private $sixMonthsAgo;

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->getPdo();
        $this->currentDate = new DateTime();
        $this->twoMonthsAhead = (new DateTime())->modify('+2 months');
        $this->sixMonthsAgo = (new DateTime())->modify('-6 months');
    }

    public function create(WydaneUbrania $wydaneUbrania) {
        $stmt = $this->pdo->prepare("INSERT INTO wydane_ubrania (id_wydania, id_ubrania, id_rozmiaru, ilosc, data_waznosci, status) VALUES (:id_wydania, :id_ubrania, :id_rozmiaru, :ilosc, :data_waznosci, :status)");
        $stmt->bindValue(':id_wydania', $wydaneUbrania->getIdWydania());
        $stmt->bindValue(':id_ubrania', $wydaneUbrania->getIdUbrania());
        $stmt->bindValue(':id_rozmiaru', $wydaneUbrania->getIdRozmiaru());
        $stmt->bindValue(':ilosc', $wydaneUbrania->getIlosc());
        $stmt->bindValue(':data_waznosci', $wydaneUbrania->getDataWaznosci());
        $stmt->bindValue(':status', $wydaneUbrania->getStatus());
        return $stmt->execute();
    }
     
    public function getUbraniaByWydanieId($id_wydania) {
        $stmt = $this->pdo->prepare("SELECT wu.id, wu.ilosc, wu.data_waznosci, wu.status, wu.id_ubrania, u.nazwa_ubrania, r.nazwa_rozmiaru,
            CASE 
                WHEN wu.data_waznosci <= :currentDate THEN 1
                WHEN wu.data_waznosci <= :twoMonthsAhead THEN 1
                ELSE 0
            END AS canBeReported
        FROM wydane_ubrania wu
        LEFT JOIN ubranie u ON wu.id_ubrania = u.id_ubranie
        LEFT JOIN rozmiar r ON wu.id_rozmiaru = r.id_rozmiar
        WHERE wu.id_wydania = :id_wydania");
    
        $stmt->bindValue(':id_wydania', $id_wydania);
        $stmt->bindValue(':currentDate', $this->currentDate->format('Y-m-d'));
        $stmt->bindValue(':twoMonthsAhead', $this->twoMonthsAhead->format('Y-m-d'));
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUbraniaByWydanieIdTermin($id_wydania) {
        $stmt = $this->pdo->prepare("SELECT wu.id, wu.ilosc, wu.data_waznosci, wu.id_ubrania, u.nazwa_ubrania, r.nazwa_rozmiaru,
           CASE 
               WHEN wu.data_waznosci <= :currentDate THEN 'Przeterminowane'
               WHEN wu.data_waznosci <= :twoMonthsAheadDup THEN 'Koniec ważności'
               ELSE 'Brak danych'
           END AS statusText
        FROM wydane_ubrania wu
        LEFT JOIN ubranie u ON wu.id_ubrania = u.id_ubranie
        LEFT JOIN rozmiar r ON wu.id_rozmiaru = r.id_rozmiar
        WHERE wu.id_wydania = :id_wydania
        AND wu.status = 1 AND (wu.data_waznosci <= :currentDateDup OR wu.data_waznosci <= :twoMonthsAhead)");

        $stmt->bindValue(':id_wydania', $id_wydania);
        $stmt->bindValue(':currentDate', $this->currentDate->format('Y-m-d'));
        $stmt->bindValue(':currentDateDup', $this->currentDate->format('Y-m-d'));
        $stmt->bindValue(':twoMonthsAhead', $this->twoMonthsAhead->format('Y-m-d'));
        $stmt->bindValue(':twoMonthsAheadDup', $this->twoMonthsAhead->format('Y-m-d'));
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $newStatus) {
        $stmt = $this->pdo->prepare("UPDATE wydane_ubrania SET status = :newStatus WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':newStatus', $newStatus, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function destroyStatus($id) {
        $stmt = $this->pdo->prepare("UPDATE wydane_ubrania SET status = 2, data_waznosci = :current_date WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':current_date', date('Y-m-d'), PDO::PARAM_STR);
    
        return $stmt->execute();
    }

    public function deleteWydaneUbranieStatus($id) {
        $stmt = $this->pdo->prepare("UPDATE wydane_ubrania SET status = 3 WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getUbraniaPoTerminie() {
        $stmt = $this->pdo->prepare("SELECT ubranie.nazwa_ubrania, rozmiar.nazwa_rozmiaru, SUM(wydane_ubrania.ilosc) AS ilosc,
                   stan_magazynu.ilosc AS ilosc_magazyn, stan_magazynu.iloscMin AS ilosc_min
            FROM wydane_ubrania
            JOIN ubranie ON wydane_ubrania.id_ubrania = ubranie.id_ubranie
            JOIN rozmiar ON wydane_ubrania.id_rozmiaru = rozmiar.id_rozmiar
            JOIN stan_magazynu ON wydane_ubrania.id_ubrania = stan_magazynu.id_ubrania
               AND wydane_ubrania.id_rozmiaru = stan_magazynu.id_rozmiaru
            WHERE (wydane_ubrania.data_waznosci <= :currentDate
                   OR (wydane_ubrania.data_waznosci > :currentDateDup AND wydane_ubrania.data_waznosci <= :twoMonthsAhead))
              AND wydane_ubrania.status = 1
            GROUP BY ubranie.nazwa_ubrania, rozmiar.nazwa_rozmiaru");

        $stmt->bindValue(':currentDate', $this->currentDate->format('Y-m-d'));
        $stmt->bindValue(':currentDateDup', $this->currentDate->format('Y-m-d'));
        $stmt->bindValue(':twoMonthsAhead', $this->twoMonthsAhead->format('Y-m-d'));
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWydaneUbraniaWithDetails() {    
        $stmt = $this->pdo->prepare("SELECT wu.id, DATE_FORMAT(wu.data_waznosci, '%Y-%m-%d %H:%i') AS data, 
                   u.nazwa_ubrania AS nazwa_ubrania, r.nazwa_rozmiaru AS rozmiar, wu.ilosc, uz.nazwa AS wydane_przez, 
                   CONCAT(p.imie, ' ', p.nazwisko) AS wydane_dla 
            FROM wydane_ubrania wu LEFT JOIN ubranie u ON wu.id_ubrania = u.id_ubranie 
            LEFT JOIN rozmiar r ON wu.id_rozmiaru = r.id_rozmiar LEFT JOIN wydania w ON wu.id_wydania = w.id_wydania 
            LEFT JOIN pracownicy p ON w.pracownik_id = p.id_pracownik LEFT JOIN uzytkownicy uz ON w.user_id = uz.id 
            WHERE wu.data_waznosci >= :sixMonthsAgo ORDER BY nazwa_ubrania");
    
        $stmt->bindValue(':sixMonthsAgo', $this->sixMonthsAgo->format('Y-m-d'));
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUbraniaById($id) {
        $stmt = $this->pdo->prepare("SELECT id_wydania, id_ubrania, id_rozmiaru, ilosc FROM wydane_ubrania WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    
    
}

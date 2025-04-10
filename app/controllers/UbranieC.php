<?php
include_once __DIR__ . '/../database/Database.php';
include_once __DIR__ . '/../models/Ubranie.php';

class UbranieC extends Database {

    private $pdo; 

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getPdo();
    }

    public function create(Ubranie $ubranie) {
        $stmt = $this->pdo->prepare("INSERT INTO ubranie (nazwa_ubrania) VALUES (:nazwa_ubrania)");
        
        $nazwa_ubrania = $ubranie->getNazwaUbrania();
        $stmt->bindParam(':nazwa_ubrania', $nazwa_ubrania);
        
        $stmt->execute();
        return $this->pdo->lastInsertId(); 
    }

    public function firstOrCreate(Ubranie $ubranie) {
        $existing = $this->findByName($ubranie->getNazwaUbrania());
        if ($existing) {
            return $existing->getIdUbranie();
        }
        return $this->create($ubranie);
    }

    public function findByName($nazwa) {
        $stmt = $this->pdo->prepare("SELECT * FROM ubranie WHERE nazwa_ubrania = :nazwa_ubrania");
        $stmt->bindParam(':nazwa_ubrania', $nazwa);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $ubranie = new Ubranie($result['nazwa_ubrania']);
            $ubranie->setIdUbranie($result['id_ubranie']);
            return $ubranie;
        }
        return null;
    }

    public function searchByName($query) {
        $stmt = $this->pdo->prepare('SELECT nazwa_ubrania AS nazwa FROM ubranie WHERE nazwa_ubrania LIKE :query LIMIT 10');
        $query = "%$query%";
        $stmt->bindParam(':query', $query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllWithRozmiary() {
        $stmt = $this->pdo->query("SELECT u.id_ubranie AS id, u.nazwa_ubrania AS nazwa, r.id_rozmiar AS rozmiar_id, r.nazwa_rozmiaru AS rozmiar FROM stan_magazynu sm JOIN ubranie u ON sm.id_ubrania = u.id_ubranie JOIN rozmiar r ON sm.id_rozmiaru = r.id_rozmiar");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllUnique() {
        $stmt = $this->pdo->query("SELECT DISTINCT u.id_ubranie AS id, u.nazwa_ubrania AS nazwa FROM ubranie u JOIN stan_magazynu sm ON u.id_ubranie = sm.id_ubrania");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

  /*   public function getRozmiaryByUbranieId($ubranieId) {
        $stmt = $this->pdo->prepare("SELECT r.id_rozmiar AS id, r.nazwa_rozmiaru AS rozmiar FROM rozmiar r 
                               INNER JOIN stan_magazynu sm ON r.id_rozmiar = sm.id_rozmiaru WHERE sm.id_ubrania = :ubranieId");
        $stmt->bindParam(':ubranieId', $ubranieId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 */

    public function getRozmiaryByUbranieId($ubranieId) {
        $stmt = $this->pdo->prepare("SELECT r.id_rozmiar AS id, r.nazwa_rozmiaru AS rozmiar, sm.ilosc AS ilosc 
            FROM rozmiar r INNER JOIN stan_magazynu sm ON r.id_rozmiar = sm.id_rozmiaru WHERE sm.id_ubrania = :ubranieId");
        $stmt->bindParam(':ubranieId', $ubranieId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        
}
?>

<?php
    require_once '../db/db.php';

    class Font {
        public $db;

        public function __construct() {
            $this->db = new Database(); 
        }

        public function save($fontName, $filePath, $preview) {
            $stmt = $this->db->prepare("INSERT INTO fonts (font_name, file_path, preview) VALUES (?, ?, ?)");
            
            $stmt->execute([$fontName, $filePath, $preview]);

            $stmt = null;
        }

        public function getAll() {
            $query = "SELECT * FROM fonts"; 
            $stmt = $this->db->prepare($query); 
            $stmt->execute(); 
            return $stmt->fetchAll(PDO::FETCH_ASSOC); 
        }

        // getById method to fetch a font by its ID
        public function getById($fontId) {
            $stmt = $this->db->prepare("SELECT * FROM fonts WHERE id = ?");
            $stmt->execute([$fontId]);
            return $stmt->fetch(PDO::FETCH_ASSOC); 
        }

        // Add a delete method to delete a font
        public function delete($fontId) {
            $stmt = $this->db->prepare("DELETE FROM fonts WHERE id = ?");
            return $stmt->execute([$fontId]);
        }

    }
?>

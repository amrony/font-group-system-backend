<?php
    require_once '../db/db.php';

    class GroupFont {
        public $db;

        public function __construct() {
            $this->db = new Database(); 
        }

        public function createGroup($group_title) {
            $stmt = $this->db->conn->prepare("INSERT INTO font_groups (group_title) VALUES (?)");
            $stmt->execute([$group_title]);

            $lastId = $this->db->conn->lastInsertId(); 
            $stmt = null;
            return $lastId;
        }

        public function addFontToGroup($font_group_id, $font_id, $name) {
            $stmt = $this->db->conn->prepare("INSERT INTO group_fonts (font_group_id, font_id, name) VALUES (?, ?, ?)");
            $success = $stmt->execute([$font_group_id, $font_id, $name]);

            $stmt = null;
            return $success;
        }


        public function getAllFontGroups() {
            $stmt = $this->db->conn->prepare("
                SELECT 
                    fg.id AS group_id, 
                    fg.group_title,
                    gf.font_id,
                    gf.name AS font_name,
                    f.font_name
                FROM font_groups fg
                LEFT JOIN group_fonts gf ON fg.id = gf.font_group_id
                LEFT JOIN fonts f ON gf.font_id = f.id
                ORDER BY fg.id DESC
            ");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = null;
    
            // Format data into grouped structure
            $groups = [];
    
            foreach ($rows as $row) {
                $group_id = $row['group_id'];
    
                if (!isset($groups[$group_id])) {
                    $groups[$group_id] = [
                        'group_id' => $group_id,
                        'group_title' => $row['group_title'],
                        'fonts' => []
                    ];
                }
    
                // Add font if exists
                if ($row['font_id']) {
                    $groups[$group_id]['fonts'][] = [
                        'font_id' => $row['font_id'],
                        'name' => $row['font_name'],
                        'font_name' => $row['font_name']
                    ];
                }
            }
    
            return json_encode(array_values($groups));
        }


        public function deleteFontGroup($font_group_id) {
            $this->db->conn->beginTransaction();
            
            try {
                $stmt = $this->db->conn->prepare("DELETE FROM group_fonts WHERE font_group_id = ?");
                $stmt->execute([$font_group_id]);
        
                $stmt = $this->db->conn->prepare("DELETE FROM font_groups WHERE id = ?");
                $stmt->execute([$font_group_id]);
                $this->db->conn->commit();
        
                return json_encode(['status' => 'success', 'message' => 'Font group deleted successfully']);
            } catch (Exception $e) {
                $this->db->conn->rollBack();
                return json_encode(['status' => 'error', 'message' => 'Failed to delete font group: ' . $e->getMessage()]);
            }
        }

        public function getFontGroupById($font_group_id) {
            $stmt = $this->db->conn->prepare("
                SELECT 
                    fg.id AS group_id, 
                    fg.group_title,
                    gf.font_id,
                    gf.id as group_font_id,
                    gf.name AS group_font_name,
                    f.font_name
                FROM font_groups fg
                LEFT JOIN group_fonts gf ON fg.id = gf.font_group_id
                LEFT JOIN fonts f ON gf.font_id = f.id
                WHERE fg.id = ?
            ");
            $stmt->execute([$font_group_id]);
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = null;

        
            if ($row) {
                // Format data into grouped structure
                $group = [
                    'group_id' => $row[0]['group_id'],
                    'group_title' => $row[0]['group_title'],
                    'fonts' => []
                ];
        
                foreach ($row as $font) {
                    $group['fonts'][] = [
                        'id' => $font['group_font_id'],
                        'name' => $font['group_font_name'],
                        'font_id' => $font['font_id']
                    ];
                }
        
                return json_encode($group);
            } else {
                return json_encode(['status' => 'error', 'message' => 'Font group not found']);
            }
        }

            
        public function updateGroupTitle($groupId, $title) {
            $stmt = $this->db->prepare("UPDATE font_groups SET group_title = ? WHERE id = ?");
            return $stmt->execute([$title, $groupId]);
        }

        public function getGroupFonts($groupId) {
            $stmt = $this->db->prepare("SELECT * FROM group_fonts WHERE font_group_id = ?");
            $stmt->execute([$groupId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        
        public function detachFont($fontId) {
            $stmt = $this->db->prepare("DELETE FROM group_fonts WHERE id = ?");
            return $stmt->execute([$fontId]);
        }

        
        public function attachFont($groupId, $name, $selectedFont) {
            $stmt = $this->db->prepare("INSERT INTO group_fonts (font_group_id, name, font_id) VALUES (?, ?, ?)");
            return $stmt->execute([$groupId, $name, $selectedFont]);
        }
        
        public function updateFont($id, $name, $fontId) {
            $stmt = $this->db->prepare("UPDATE group_fonts SET name = ?, font_id = ? WHERE id = ?");
            return $stmt->execute([$name, $fontId, $id]);
        }
    }
?>

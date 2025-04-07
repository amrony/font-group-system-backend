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

            $lastId = $this->db->conn->lastInsertId(); // ✅ return new group ID
            $stmt = null;
            return $lastId;
        }

        public function addFontToGroup($font_group_id, $font_id, $name, $size) {
            $stmt = $this->db->conn->prepare("INSERT INTO group_fonts (font_group_id, font_id, name, size) VALUES (?, ?, ?, ?)");
            $success = $stmt->execute([$font_group_id, $font_id, $name, $size]);

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
                    gf.size,
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
                        'size' => $row['size'],
                        'font_name' => $row['font_name']
                    ];
                }
            }
    
            return json_encode(array_values($groups));
        }


        public function deleteFontGroup($font_group_id) {
            // Start transaction to ensure data consistency
            $this->db->conn->beginTransaction();
            
            try {
                // Delete fonts associated with the font group
                $stmt = $this->db->conn->prepare("DELETE FROM group_fonts WHERE font_group_id = ?");
                $stmt->execute([$font_group_id]);
        
                // Then delete the font group itself
                $stmt = $this->db->conn->prepare("DELETE FROM font_groups WHERE id = ?");
                $stmt->execute([$font_group_id]);
        
                // Commit transaction if both deletes succeed
                $this->db->conn->commit();
        
                return json_encode(['status' => 'success', 'message' => 'Font group deleted successfully']);
            } catch (Exception $e) {
                // Rollback the transaction if an error occurs
                $this->db->conn->rollBack();
                return json_encode(['status' => 'error', 'message' => 'Failed to delete font group: ' . $e->getMessage()]);
            }
        }
    }
?>

<?php
    require_once '../model/GroupFont.php';

    class GroupController {
        private $groupFont;

        public function __construct() {
            $this->groupFont = new GroupFont();
        }

        public function createFontGroup($data) {
            $group_title = $data['group_title'];
            $fonts = $data['fonts'];

            // 1. Create the font group
            $group_id = $this->groupFont->createGroup($group_title);

            if (!$group_id) {
                return json_encode(['status' => 'error', 'message' => 'Failed to create font group']);
            }

            // 2. Insert fonts into the group
            foreach ($fonts as $font) {
                $font_id = $font['font_id'];
                $name = $font['name'];
                $size = $font['size'];

                $success = $this->groupFont->addFontToGroup($group_id, $font_id, $name, $size);
                if (!$success) {
                    return json_encode(['status' => 'error', 'message' => 'Failed to add font to group']);
                }
            }

            return json_encode(['status' => 'success']);
        }


        public function getFontGroups() {
            $groups = $this->groupFont->getAllFontGroups();

            return json_encode(['status' => 'success', 'data' => $groups]);
        }

        public function deleteFontGroup($font_group_id) {
            $group =  $this->groupFont->deleteFontGroup($font_group_id);

            return json_encode(['status' => 'success', 'message' => 'Font group deleted successfully']);
        }
    }
?>

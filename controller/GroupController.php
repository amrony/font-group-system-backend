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

                $success = $this->groupFont->addFontToGroup($group_id, $font_id, $name);
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

        public function getFontGroupById($font_group_id) {
            $groupFont = $this->groupFont->getFontGroupById($font_group_id);

            return json_encode(['status' => 'success', 'data' => $groupFont]);
        }


        public function updateFontGroup($data) {
            $groupId = $data['group_id'];
            $groupTitle = $data['group_title'];
            $newFonts = $data['fonts'];
        
            try {
                // Update group title
                $this->groupFont->updateGroupTitle($groupId, $groupTitle);

                // Get current fonts for this group
                $currentFonts = $this->groupFont->getGroupFonts($groupId);

                // Convert to array of font IDs (or unique identifiers)
                $currentFontIds = array_column($currentFonts, 'id');

                $newFontIds = array_column($newFonts, 'id');

                // Determine fonts to add and remove
                $fontsToAdd = array_diff($newFontIds, $currentFontIds);
                $fontsToRemove = array_diff($currentFontIds, $newFontIds);

        
                // attach/detach operations
                foreach ($fontsToRemove as $fontId) {
                    $this->groupFont->detachFont($fontId);
                }
        
                foreach ($newFonts as $font) {
                    if (in_array($font['id'], $fontsToAdd)) {
                        $this->groupFont->attachFont(
                            $groupId,
                            $font['name'],
                            $font['font_id']
                        );
                    } 
                    else {
                        // Update existing fonts that weren't removed
                        $this->groupFont->updateFont(
                            $font['id'],
                            $font['name'],
                            $font['font_id']
                        );
                    }
                }
        
                return json_encode(['status' => 'success', 'message' => 'Font group updated successfully']);
        
            } catch (Exception $e) {
                return json_encode(['status' => 'error', 'message' => 'Failed to update font group: ' . $e->getMessage()]);
            }
        }

    }
?>

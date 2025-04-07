<?php
require_once '../model/Font.php';

class FontController {

    private $font;

    public function __construct() {
        $this->font = new Font();
    }

    public function uploadFont($file) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'ttf') {
            return json_encode(['status' => 'error', 'message' => 'Only TTF files are allowed.']);
        }

        $uploadDir = '../uploads/';
        $fileName = uniqid() . '.ttf';
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return json_encode(['status' => 'error', 'message' => 'Failed to upload font.']);
        }

        $font = new Font();
        $fontName = pathinfo($file['name'], PATHINFO_FILENAME);
        $preview = "<p style='font-family: \"$fontName\";'>Preview of $fontName</p>";
        $font->save($fontName, $fileName, $preview);

        return json_encode(['status' => 'success', 'message' => 'Font uploaded successfully.']);
    }


    // Handle getting all fonts from the database
    public function getAllFonts() {
        $fonts = $this->font->getAll();

        if (empty($fonts)) {
            return json_encode(['status' => 'error', 'message' => 'No fonts found.']);
        }

        return json_encode(['status' => 'success', 'data' => $fonts]);
    }

    public function deleteFont($fontId) {
        $font = new Font();
        $fontDetails = $font->getById($fontId); // Fetch the font details by ID
        if ($fontDetails) {
            if (file_exists('../uploads/' . $fontDetails['file_path'])) {
                unlink('../uploads/' . $fontDetails['file_path']);
            }
            $font->delete($fontId);

            return json_encode(['status' => 'success', 'message' => 'Font deleted successfully.']);
        } else {
            return json_encode(['status' => 'error', 'message' => 'Font not found']);
        }
    }
}

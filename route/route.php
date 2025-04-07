<?php
// Allow Cross-Origin Requests (CORS) for API routes
header("Access-Control-Allow-Origin: *");  // Allows all origins, or specify the domain like http://localhost:3000
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");  // Allowed HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization");  // Allowed headers
header('Content-Type: application/json');

// Preflight request (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../controller/FontController.php';
require_once '../controller/GroupController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$controller = new FontController();
$groupController = new GroupController();

// Handle font upload
if ($uri === '/font-group-system-backend/upload-font' && $method === 'POST') {
    echo $controller->uploadFont($_FILES['font']);
}

// Handle get all fonts
if ($uri === '/font-group-system-backend/get-fonts' && $method === 'GET') {
    echo $controller->getAllFonts();
}

// Handle delete font
if ($uri === '/font-group-system-backend/delete-font' && $method === 'POST') {
    // Read the JSON payload from the request body
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Check if font_id exists in the payload
    if (isset($data['font_id'])) {
        echo $controller->deleteFont($data['font_id']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'font_id is required']);
    }
}

// Handle font Groupt create
if ($uri === '/font-group-system-backend/create-font-group' && $method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['group_title']) && isset($data['fonts'])) {
        echo $groupController->createFontGroup($data);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    }
}

// get-font-groups
if ($uri === '/font-group-system-backend/get-font-groups' && $method === 'GET') {
    echo $groupController->getFontGroups();
}

if ($uri === '/font-group-system-backend/delete-font-group' && $method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['font_group_id'])) {
        echo $groupController->deleteFontGroup($data['font_group_id']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'font_group_id is required']);
    }
}



if (strpos($uri, '/font-group-system-backend/uploads/') === 0) {
    header("Access-Control-Allow-Origin: *"); 
    header("Content-Type: font/ttf");  
    header("Access-Control-Allow-Methods: GET, OPTIONS");  

    $fontPath = __DIR__ . '/../uploads/' . basename($uri);  
    if (file_exists($fontPath)) {
        header('Content-Length: ' . filesize($fontPath));
        readfile($fontPath);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Font not found']);
    }
}

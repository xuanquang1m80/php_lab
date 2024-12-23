<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Config\Database;
use App\Middleware\AuthMiddleware;
use Exception;

class ProductApiController
{

    private $db;
    private $productModel;
    private $authMiddleware;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->authMiddleware = new AuthMiddleware();
    }
    // Lấy danh sách sản phẩm
    public function index()
    {
        header('Content-Type: application/json');
        $products = $this->productModel->getProducts();
        echo json_encode($products);
    }
    // Lấy thông tin sản phẩm theo ID
    public function show($id)
    {
        header('Content-Type: application/json');
        $product = $this->productModel->getProductById($id);
        if ($product) {
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Product not found']);
        }
    }
    // Thêm sản phẩm mới
    public function store()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);


        // Lấy token từ Header
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        // Xác thực token
        $user = AuthMiddleware::verifyToken($token);

        if (!$user) {
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Invalid or missing token.']);
            exit;
        }


        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? '';
        $category_id = $data['category_id'] ?? null;

        $result = $this->productModel->addProduct(
            $name,
            $description,
            $price,
            $category_id
        );
        if (is_array($result)) {
            http_response_code(400);
            echo json_encode(['errors' => $result]);
        } else {
            http_response_code(201);
            echo json_encode(['message' => 'Product created successfully']);
        }
    }
    // Cập nhật sản phẩm theo ID
    public function update($id)
    {
        
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);

        // Lấy token từ Header
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        // Xác thực token
        $user = AuthMiddleware::verifyToken($token);

        if (!$user) {
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Invalid or missing token.']);
            exit;
        }
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? '';
        $category_id = $data['category_id'] ?? null;
        $result = $this->productModel->updateProduct(
            $id,
            $name,
            $description,
            $price,
            $category_id,
            null
        );
        if ($result) {
            echo json_encode(['message' => 'Product updated successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Product update failed']);
        }
    }
    // Xóa sản phẩm theo ID
    public function destroy($id)
    {
       
        header('Content-Type: application/json');


        // Lấy token từ Header
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        // Xác thực token
        $user = AuthMiddleware::verifyToken($token);

        if (!$user) {
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Invalid or missing token.']);
            exit;
        }
        $result = $this->productModel->deleteProduct($id);
        if ($result) {
            echo json_encode(['message' => 'Product deleted successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Product deletion failed']);
        }
    }
}

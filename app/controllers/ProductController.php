<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Config\Database;
use App\Helpers\SessionHelper;
use App\Helpers\RoleHelper;
use App\Middleware\RoleMiddleware;
use Exception;


class ProductController
{
    private $db;
    private $productModel;
    private $middleware;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->middleware = new RoleMiddleware($this->db);
    }

    public function forbiden()
    {
        include 'app/views/forbiden.php';
    }

//Session
    // public function index()
    // {
    //     // Khởi tạo RoleHelper
    //     $roleHelper = new RoleHelper($this->db);
    
    //     // Lấy danh sách vai trò của người dùng hiện tại
    //     $userId =$_COOKIE['user_data'];
    //     $isAdmin = $roleHelper->userHasRole($userId, 'Admin');
    //     $isUser = $roleHelper->userHasRole($userId, 'Viewer'); // Giả định bạn có vai trò 'User'
    
    //     // Thông báo dựa trên vai trò
    //     if ($isAdmin) {
    //         $welcomeMessage = "Chào mừng Admin!";
    //     } elseif ($isUser) {
    //         $welcomeMessage = "Chào mừng User!";
    //     } else {
    //         $welcomeMessage = "Chào mừng Khách!";
    //     }
    
    //     // Lấy danh sách sản phẩm
    //     $products = $this->productModel->getProducts();
    
    //     // Truyền thông điệp chào mừng đến view
    //     include 'app/views/product/list.php';
    // }
    

    public function index()
{
    // Kiểm tra nếu cookie 'user_data' tồn tại
    if (!isset($_COOKIE['user_data'])) {
        // Nếu không có cookie, điều hướng về trang đăng nhập
        header('Location: /lab_1/Account/login');
        exit;
    }

    // Giải mã cookie và lấy thông tin người dùng
    $userData = json_decode($_COOKIE['user_data'], true);

    // Kiểm tra xem có dữ liệu hợp lệ không
    if (!isset($userData['user_id'])) {
        // Nếu cookie không hợp lệ, điều hướng về trang đăng nhập
        header('Location: /lab_1/Account/login');
        exit;
    }

    // Lấy user_id từ cookie
    $userId = $userData['user_id'];

    // Khởi tạo RoleHelper
    $roleHelper = new RoleHelper($this->db);

    // Lấy danh sách vai trò của người dùng hiện tại
    $isAdmin = $roleHelper->userHasRole($userId, 'Admin');
    $isUser = $roleHelper->userHasRole($userId, 'Viewer'); 

    // Thông báo dựa trên vai trò
    if ($isAdmin) {
        $welcomeMessage = "Chào mừng Admin!";
    } elseif ($isUser) {
        $welcomeMessage = "Chào mừng User!";
    } else {
        $welcomeMessage = "Chào mừng Khách!"; 
    }

    // Lấy danh sách sản phẩm
    $products = $this->productModel->getProducts();

    // Truyền thông điệp chào mừng đến view
    include 'app/views/product/list.php';
}


    public function show($id)
    {
        $product = $this->productModel->getProductById($id);
        if ($product) {
            include 'app/views/product/edit.php';
        } else {
            echo "Không thấy sản phẩm.";
        }
    }

    public function add()
    {
        $this->middleware->handle();
            $categories = (new CategoryModel($this->db))->getCategories();
            include_once 'app/views/product/add.php';
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? '';
            $category_id = $_POST['category_id'] ?? null;

            // var_dump($category_id);
            // die;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            } else {
                $image = "";
            }
            $result = $this->productModel->addProduct(
                $name,
                $description,
                $price,
                $category_id,
                $image
            );
            if (is_array($result)) {
                $errors = $result;
                $categories = (new CategoryModel($this->db))->getCategories();
                include 'app/views/product/add.php';
            } else {
                header('Location: /lab_1/Product');
            }
        }
    }

    public function edit($id)
    {
        $product = $this->productModel->getProductById($id);
        $categories = (new CategoryModel($this->db))->getCategories();
        if ($product) {
            include 'app/views/product/edit.php';
        } else {
            echo "Không thấy sản phẩm.";
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $category_id = $_POST['category_id'];

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            } else {
                $image = $_POST['existing_image'];
            }

            $edit = $this->productModel->updateProduct(
                $id,
                $name,
                $description,
                $price,
                $category_id,
                $image
            );

            if ($edit) {
                header('Location: /lab_1/Product');
            } else {
                echo "Đã xảy ra lỗi khi lưu sản phẩm.";
            }
        }
    }

    public function delete($id)
    {
        if ($this->productModel->deleteProduct($id)) {
            header('Location: /lab_1/Product');
        } else {
            echo "Đã xảy ra lỗi khi xóa sản phẩm.";
        }
    }

    private function uploadImage($file)
    {
        $target_dir = "public/images/";
        // Kiểm tra và tạo thư mục nếu chưa tồn tại
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        // Kiểm tra xem file có phải là hình ảnh không
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            throw new Exception("File không phải là hình ảnh.");
        }
        // Kiểm tra kích thước file (10 MB = 10 * 1024 * 1024 bytes)
        if ($file["size"] > 10 * 1024 * 1024) {
            throw new Exception("Hình ảnh có kích thước quá lớn.");
        }
        // Chỉ cho phép một số định dạng hình ảnh nhất định
        if (
            $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType !=
            "jpeg" && $imageFileType != "gif"
        ) {
            throw new Exception("Chỉ cho phép các định dạng JPG, JPEG, PNG và GIF.");
        }
        // Lưu file
        if (!move_uploaded_file($file["tmp_name"], $target_file)) {
            throw new Exception("Có lỗi xảy ra khi tải lên hình ảnh.");
        }
        return $target_file;
    }

    // public function addToCart($id)
    // {
    //     $product = $this->productModel->getProductById($id);
    //     if (!$product) {
    //         echo "Không tìm thấy sản phẩm.";
    //         return;
    //     }
    //     if (!isset($_SESSION['cart'])) {
    //         $_SESSION['cart'] = [];
    //     }
    //     if (isset($_SESSION['cart'][$id])) {
    //         $_SESSION['cart'][$id]['quantity']++;
    //     } else {
    //         $_SESSION['cart'][$id] = [
    //             'name' => $product->name,
    //             'price' => $product->price,
    //             'quantity' => 1,
    //             'image' => $product->image
    //         ];
    //     }
    //     header('Location: /lab_1/Product/cart');
    // }

    // public function cart()
    // {
    //     $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    //     include 'app/views/product/cart.php';
    // }

    public function checkout()
    {
        include 'app/views/product/checkout.php';
    }


    public function processCheckout()
    {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {


            $name = $_POST['name'];
            $phone = $_POST['phone'];
            $address = $_POST['address'];
            // Kiểm tra giỏ hàng
            if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
                echo "Giỏ hàng trống.";
                return;
            }
            $this->db->beginTransaction();
            try {
                // Lưu thông tin đơn hàng vào bảng orders
                $query = "INSERT INTO orders (name, phone, address) VALUES (:name,:phone, :address)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':address', $address);
                $stmt->execute();
                $order_id = $this->db->lastInsertId();
                // Lưu chi tiết đơn hàng vào bảng order_details
                $cart = $_SESSION['cart'];
                foreach ($cart as $product_id => $item) {
                    $query = "INSERT INTO order_details (order_id, product_id,quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':order_id', $order_id);
                    $stmt->bindParam(':product_id', $product_id);
                    $stmt->bindParam(':quantity', $item['quantity']);
                    $stmt->bindParam(':price', $item['price']);
                    $stmt->execute();
                }
                // Xóa giỏ hàng sau khi đặt hàng thành công
                unset($_SESSION['cart']);
                // Commit giao dịch
                $this->db->commit();
                // Chuyển hướng đến trang xác nhận đơn hàng
                header('Location: /lab_1/Product/orderConfirmation');
            } catch (Exception $e) {
                // Rollback giao dịch nếu có lỗi
                $this->db->rollBack();
                echo "Đã xảy ra lỗi khi xử lý đơn hàng: " . $e->getMessage();
            }
        }
    }

    public function orderConfirmation()
    {
        include 'app/views/product/orderConfirmation.php';
    }

    public function getOrCreateCart($userId)
    {
        $query = "SELECT id FROM carts WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $cart = $stmt->fetch();

        if (!$cart) {
            $query = "INSERT INTO carts (user_id) VALUES (:user_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $this->db->lastInsertId();
        }

        return $cart['id'];
    }

    public function addToCart( $productId)
    {
        $quantity =1;
        $userId = SessionHelper::getUserId(); 

        $cartId = $this->getOrCreateCart($userId);

        $query = "SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        $cartItem = $stmt->fetch();

        if ($cartItem) {
            // Cập nhật số lượng
            $newQuantity = $cartItem['quantity'] + $quantity;
            $query = "UPDATE cart_items SET quantity = :quantity WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':quantity', $newQuantity);
            $stmt->bindParam(':id', $cartItem['id']);
            $stmt->execute();
        } else {
            // Thêm sản phẩm mới
            $query = "INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (:cart_id, :product_id, :quantity, (SELECT price FROM product WHERE id = :product_id))";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':cart_id', $cartId);
            $stmt->bindParam(':product_id', $productId);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->execute();
        }

       
        header('Location: /lab_1/Product/cart/'.$userId);

    }
    
    public function cart($userId)
    {
        $cartId = $this->getOrCreateCart($userId);

        $query = "SELECT ci.*, p.name, p.image,p.price 
              FROM cart_items ci 
              JOIN product p ON ci.product_id = p.id 
              WHERE ci.cart_id = :cart_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->execute();

         $cartItems= $stmt->fetchAll();

        include 'app/views/product/cart.php';
    }

    public function getCart()
    {
        $userId = SessionHelper::getUserId();
        $cartId = $this->getOrCreateCart($userId);

        $query = "SELECT ci.*, p.name, p.image,p.price 
              FROM cart_items ci 
              JOIN product p ON ci.product_id = p.id 
              WHERE ci.cart_id = :cart_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->execute();

         $cartItems= $stmt->fetchAll();

        include 'app/views/product/cart.php';
    }

}

<?php 

namespace App\Controllers;

use App\Models\AccountModel;
use App\Config\Database;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AccountApiController{

    private $accountModel;
    private $db;


    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
    }

    public function Login()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';

            // Tìm tài khoản trong database
            $account = $this->accountModel->getAccountByUserName($username);

            if ($account) {
                $pwd_hashed = $account->password;

                // Kiểm tra mật khẩu
                if (password_verify($password, $pwd_hashed)) {
                    // Tạo payload cho token
                    $config = include 'app/config/jwt.php';
                    $issuedAt = time();
                    $expirationTime = $issuedAt + $config['expiration_time']; // Token hết hạn sau 1 giờ

                    $payload = [
                        'iss' => $config['issuer'],       // Issuer
                        'aud' => $config['audience'],    // Audience
                        'iat' => $issuedAt,              // Issued at
                        'exp' => $expirationTime,        // Expiration time
                        'data' => [
                            'user_id' => $account->id,
                            'username' => $account->username,
                        ],
                    ];

                    // Tạo token
                    $jwt = JWT::encode($payload, $config['secret_key'], 'HS256');

                    // Trả về token
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Login successful',
                        'token' => $jwt
                    ]);
                    exit;
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Password incorrect'
                    ]);
                    exit;
                }
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Account not found'
                ]);
                exit;
            }
        } else {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method'
            ]);
            exit;
        }
    }


}


?>
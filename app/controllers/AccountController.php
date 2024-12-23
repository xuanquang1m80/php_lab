<?php

namespace App\Controllers;

use App\Models\AccountModel;
use App\Config\Database;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AccountController
{

    private $accountModel;
    private $db;


    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
    }
    function register()
    {
        include_once 'app/views/account/register.php';
    }
    public function login()
    {
        $this->checkAutoLogin(); // Kiểm tra cookie trước
        include_once 'app/views/account/login.php';
    }

    function save()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $username = $_POST['username'] ?? '';
            $fullName = $_POST['fullname'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmpassword'] ?? '';
            $errors = [];
            if (empty($username)) {
                $errors['username'] = "Vui long nhap userName!";
            }
            if (empty($fullName)) {
                $errors['fullname'] = "Vui long nhap fullName!";
            }
            if (empty($password)) {
                $errors['password'] = "Vui long nhap password!";
            }
            if ($password != $confirmPassword) {
                $errors['confirmPass'] = "Mat khau va xac nhan chua dung";
            }
            //kiểm tra username đã được đăng ký chưa?
            $account = $this->accountModel->getAccountByUsername($username);
            if ($account) {
                $errors['account'] = "Tai khoan nay da co nguoi dang ky!";
            }

            if (count($errors) > 0) {
                include_once 'app/views/account/register.php';
            } else {
                $password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $result = $this->accountModel->save($username, $fullName, $password);
                if ($result) {
                    header('Location: /lab_1/Account/login');
                }
            }
        }
    }
    // function logout()
    // {
    //     unset($_SESSION['username']);
    //     unset($_SESSION['user_id']);
    //     unset($_SESSION['role']);
    //     header('Location: /lab_1/Product');
    // }
    // public function checkLogin()
    // {
    //     // Kiểm tra xem liệu form đã được submit
    //     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //         $username = $_POST['username'] ?? '';
    //         $password = $_POST['password'] ?? '';
    //         $account = $this->accountModel->getAccountByUserName($username);
    //         if ($account) {
    //             $pwd_hashed = $account->password;
    //             //check mat khau
    //             if (password_verify($password, $pwd_hashed)) {
    //                 session_start();
    //                  $_SESSION['user_id'] = $account->id;
    //                 // $_SESSION['user_role'] = $account->role;
    //                 $_SESSION['username'] = $account->username;
    //                 header('Location: /lab_1/product');
    //                 exit;
    //             } else {
    //                 echo "Password incorrect.";
    //             }
    //         } else {
    //             echo "Bao loi khong tim thay tai khoan";
    //         }
    //     }
    // }

    public function checkLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $account = $this->accountModel->getAccountByUserName($username);

            if ($account) {
                $pwd_hashed = $account->password;

                // Kiểm tra mật khẩu
                if (password_verify($password, $pwd_hashed)) {
                    // Lưu thông tin vào cookie
                    $userData = [
                        'user_id' => $account->id,
                        'username' => $account->username,
                    ];

                    $cookieData = json_encode($userData);
                    setcookie('user_data', $cookieData, time() + (10 * 24 * 60 * 60), "/"); // Lưu cookie trong 10 ngày

                    header('Location: /lab_1/product');
                    exit;
                } else {
                    echo "Password incorrect.";
                }
            } else {
                echo "Không tìm thấy tài khoản.";
            }
        }
    }

    public function checkAutoLogin()
    {
        if (isset($_COOKIE['user_data'])) {
            $userData = json_decode($_COOKIE['user_data'], true);

            if (isset($userData['user_id'], $userData['username'])) {

                header('Location: /lab_1/product');
                exit;
            }
        }
    }

    public function logout()
    {
        // Xóa cookie
        setcookie('user_data', '', time() - 3600, "/");

        header('Location: /lab_1/Product');
        exit;
    }

  

}

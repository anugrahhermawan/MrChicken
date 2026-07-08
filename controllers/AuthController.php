<?php
// controllers/AuthController.php
require_once 'core/Controller.php';
require_once 'models/User.php';

class AuthController extends Controller {
    private User $userModel; // Menambahkan tipe data class User

    // Parameter wajib bertipe PDO
    public function __construct(PDO $db) {
        $this->userModel = new User($db);
    }

    public function login(): void {
        // $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
        // $password = $_POST['password'];

        // echo "<pre>";
        // echo "Username POST: ";
        // var_dump($_POST['username']);
        // echo "Username setelah filter: ";
        // var_dump($username);
        // die();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
            $password = $_POST['password'];

            $user = $this->userModel->findByUsername($username);
            // echo "<pre>";
            // var_dump($user);
            // die();

            if ($user && password_verify($password, $user->password)) {
                $_SESSION['user_id'] = $user->id_user;
                $_SESSION['username'] = $user->username;
                $_SESSION['role'] = $user->role;
                $_SESSION['nama'] = $user->nama_pengguna;
                $_SESSION['last_activity'] = time();

                if ($user->role == 'Owner') {
                    header('Location: index.php?page=dashboard');
                } else {
                    header('Location: index.php?page=kasir');
                }
                exit();
            } else {
                $data['error'] = "Username atau Password salah/tidak aktif!";
                $this->view('auth/login', $data);
            }
        } else {
            $this->view('auth/login');
        }
    }

    public function logout(): void {
        session_unset();
        session_destroy();
        header('Location: index.php?page=login');
        exit();
    }
}
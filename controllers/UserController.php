<?php
// controllers/UserController.php
require_once 'core/Controller.php';
require_once 'models/User.php';

class UserController extends Controller {
    private User $userModel;

    public function __construct(PDO $db) {
        $this->userModel = new User($db);
    }

    // Memastikan hanya Owner yang bisa mengakses
    private function checkOwner(): void {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Owner') {
            $_SESSION['error'] = "Akses ditolak! Menu ini hanya untuk Owner.";
            header('Location: index.php?page=kasir');
            exit();
        }
    }

    // Halaman kelola user
    public function index(): void {
        $this->checkOwner();
        $data['users'] = $this->userModel->getAll();
        $this->view('owner/management_users', $data);
    }

    // Menyimpan user baru
    public function simpan(): void {
        $this->checkOwner();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
            $password = $_POST['password'] ?? '';
            $nama_pengguna = trim(filter_input(INPUT_POST, 'nama_pengguna', FILTER_SANITIZE_SPECIAL_CHARS));
            $role = $_POST['role'] ?? 'Karyawan';

            if (empty($username) || empty($password) || empty($nama_pengguna) || empty($role)) {
                $_SESSION['error'] = "Semua input data wajib diisi!";
                header('Location: index.php?page=users#tabel-karyawan');
                exit();
            }

            // Validasi apakah username sudah terpakai
            $existing = $this->userModel->findByUsername($username);
            if ($existing) {
                $_SESSION['error'] = "Username '{$username}' sudah terdaftar! Gunakan username lain.";
                header('Location: index.php?page=users#tabel-karyawan');
                exit();
            }

            // Hash password dengan BCRYPT
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $save = $this->userModel->create($username, $hashedPassword, $nama_pengguna, $role);

            if ($save) {
                $_SESSION['success'] = "Akun pengguna baru berhasil ditambahkan.";
            } else {
                $_SESSION['error'] = "Gagal menyimpan data pengguna.";
            }

            header('Location: index.php?page=users#tabel-karyawan');
            exit();
        }
    }

    // Mengedit data user
    public function edit(): void {
        $this->checkOwner();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_user = filter_input(INPUT_POST, 'id_user', FILTER_VALIDATE_INT);
            $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
            $nama_pengguna = trim(filter_input(INPUT_POST, 'nama_pengguna', FILTER_SANITIZE_SPECIAL_CHARS));
            $role = $_POST['role'] ?? 'Karyawan';
            $password = $_POST['password'] ?? '';

            if (!$id_user || empty($username) || empty($nama_pengguna) || empty($role)) {
                $_SESSION['error'] = "Semua kolom edit wajib diisi!";
                header('Location: index.php?page=users#tabel-karyawan');
                exit();
            }

            $hashedPassword = null;
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            }

            $update = $this->userModel->update($id_user, $username, $nama_pengguna, $role, $hashedPassword);

            if ($update) {
                $_SESSION['success'] = "Data pengguna berhasil diperbarui.";
            } else {
                $_SESSION['error'] = "Gagal memperbarui data pengguna.";
            }

            header('Location: index.php?page=users#tabel-karyawan');
            exit();
        }
    }

    // Toggle status keaktifan user
    public function toggleStatus(): void {
        $this->checkOwner();

        $id_user = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id_user) {
            $_SESSION['error'] = "ID Pengguna tidak valid!";
            header('Location: index.php?page=users#tabel-karyawan');
            exit();
        }

        // Mencegah owner menonaktifkan akunnya sendiri secara tidak sengaja
        if ($id_user == $_SESSION['user_id']) {
            $_SESSION['error'] = "Aksi ditolak! Anda tidak dapat menonaktifkan akun Anda sendiri.";
            header('Location: index.php?page=users#tabel-karyawan');
            exit();
        }

        $toggle = $this->userModel->toggleStatus($id_user);

        if ($toggle) {
            $_SESSION['success'] = "Status keaktifan pengguna berhasil diubah.";
        } else {
            $_SESSION['error'] = "Gagal mengubah status pengguna.";
        }

        header('Location: index.php?page=users#tabel-karyawan');
        exit();
    }

    // Menghapus data user
    public function hapus(): void {
        $this->checkOwner();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_user = filter_input(INPUT_POST, 'id_user', FILTER_VALIDATE_INT);

            if (!$id_user) {
                $_SESSION['error'] = "ID Pengguna tidak valid!";
                header('Location: index.php?page=users#tabel-karyawan');
                exit();
            }

            if ($id_user == $_SESSION['user_id']) {
                $_SESSION['error'] = "Aksi ditolak! Anda tidak dapat menghapus akun Anda sendiri.";
                header('Location: index.php?page=users#tabel-karyawan');
                exit();
            }

            $delete = $this->userModel->delete($id_user);

            if ($delete) {
                $_SESSION['success'] = "Data pengguna berhasil dihapus dari sistem.";
            } else {
                $_SESSION['error'] = "Gagal menghapus data pengguna.";
            }

            header('Location: index.php?page=users#tabel-karyawan');
            exit();
        }
    }
}

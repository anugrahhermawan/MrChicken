<?php
/** @var string|null $error */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MR. CHICKEN POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin-top: 10%;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="card login-container shadow-sm p-4 w-100">
        <div class="card-body">
            <h3 class="card-title text-center mb-4 font-weight-bold text-primary">MR. CHICKEN POS</h3>
            <p class="text-muted text-center small mb-4">Sistem POS & Distribusi Internal</p>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger small p-2 text-center" role="alert">
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <form action="index.php?page=login" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label small">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required autocomplete="off">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label small">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Masuk Sistem</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
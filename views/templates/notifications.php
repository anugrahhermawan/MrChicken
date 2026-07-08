<?php
// views/templates/notifications.php
?>

<!-- Notification Handler -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show premium-card p-3 mb-4 d-flex align-items-center gap-2" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-circle-check text-success fa-xl"></i>
        <div>
            <strong>Sukses!</strong> <?= $_SESSION['success']; ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['warning'])): ?>
    <div class="alert alert-warning alert-dismissible fade show premium-card p-3 mb-4 d-flex align-items-center gap-2" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-triangle-exclamation text-warning fa-xl"></i>
        <div>
            <strong>Peringatan!</strong> <?= $_SESSION['warning']; ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['warning']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show premium-card p-3 mb-4 d-flex align-items-center gap-2" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-circle-exclamation text-danger fa-xl"></i>
        <div>
            <strong>Error!</strong> <?= $_SESSION['error']; ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

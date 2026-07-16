<?php
// views/owner/partials/_tab_aging_ar.php
/** @var array $aging */
/** @var string $userRole */
?>
<?php if ($userRole === 'Owner'): ?>
    <div class="row mb-4">
        <!-- Card 1: Belum Jatuh Tempo -->
        <div class="col-12 col-md-4 mb-3">
            <div class="card premium-card p-3 border-0 bg-white shadow-sm">
                <div class="card-body p-2 d-flex align-items-center">
                    <div class="p-3 rounded me-3 metric-icon-container">
                        <i class="fa-solid fa-calendar-check fa-2x"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase font-weight-bold text-uppercase-bold-compact">Hari Ini</span>
                        <h4 class="font-weight-bold m-0 mt-1 text-dark-semibold">Rp <?= number_format($aging['current'], 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 2: Sedang Berjalan -->
        <div class="col-12 col-md-4 mb-3">
            <div class="card premium-card p-3 border-0 bg-white shadow-sm">
                <div class="card-body p-2 d-flex align-items-center">
                    <div class="p-3 rounded me-3 metric-icon-container">
                        <i class="fa-solid fa-hourglass-half fa-2x"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase font-weight-bold text-uppercase-bold-compact">Sedang Berjalan (< 3 Bln)</span>
                        <h4 class="font-weight-bold m-0 mt-1 text-dark-semibold">Rp <?= number_format($aging['under_90'], 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 3: Jatuh Tempo -->
        <div class="col-12 col-md-4 mb-3">
            <div class="card premium-card p-3 border-0 bg-white shadow-sm">
                <div class="card-body p-2 d-flex align-items-center">
                    <div class="p-3 rounded me-3 metric-icon-container">
                        <i class="fa-solid fa-triangle-exclamation fa-2x"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase font-weight-bold text-uppercase-bold-compact">Jatuh Tempo (>= 3 Bln)</span>
                        <h4 class="font-weight-bold m-0 mt-1 text-dark-semibold">Rp <?= number_format($aging['over_90'], 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

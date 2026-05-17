<?php
require_once '../core/Auth.php';
Auth::requireRole('admin');
/**
 * Users Management Page
 * TEFA Bakery and Coffee Users Management
 */
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Flash.php';

$userModel   = new UserModel();
$users       = $userModel->getAll();
$csrfToken   = Auth::generateCsrfToken();
$currentUser = ['id_user' => $_SESSION['id_user'] ?? 0];

$pageTitle = 'Users';
$dashboardPage = true;
$pageHeading  = 'Manajemen User';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

    <!-- Flash Messages -->
    <?= Flash::render() ?>

    <!-- Page Content -->
    <div class="page-content">

        <!-- Tombol Tambah User -->
        <div class="content-header" style="margin-bottom: 20px;">
            <button class="btn-primary" onclick="toggleModal('modalUser')">
                <i class="fas fa-plus"></i> Tambah User Baru
            </button>
        </div>

        <!-- Tabel User -->
        <section class="table-card">
            <div class="card-header">
                <h4>DAFTAR USER</h4>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th><span class="th-content">No</span></th>
                            <th><span class="th-content">Username</span></th>
                            <th><span class="th-content">Nama Lengkap</span></th>
                            <th><span class="th-content">Role</span></th>
                            <th><span class="th-content">Status</span></th>
                            <th><span class="th-content">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; color:#999; padding:30px;">
                                Belum ada user terdaftar.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($users as $i => $u): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                            <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                            <td>
                                <span class="role-badge role-<?= htmlspecialchars($u['role']) ?>">
                                    <?= ucfirst(htmlspecialchars($u['role'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $u['status'] === 'aktif' ? 'active' : 'inactive' ?>">
                                    <?= $u['status'] === 'aktif' ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-secondary" onclick="editUser(
                                    <?= $u['id_user'] ?>, 
                                    '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>', 
                                    '<?= htmlspecialchars($u['nama_lengkap'], ENT_QUOTES) ?>', 
                                    '<?= htmlspecialchars($u['role'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($u['status'], ENT_QUOTES) ?>'
                                )" style="padding: 6px 10px; font-size: 13px; margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <?php if ((int)$u['id_user'] !== (int)($currentUser['id_user'] ?? 0)): ?>
                                <!-- Form Delete dengan CSRF -->
                                <form method="POST" action="../controllers/AuthController.php"
                                      onsubmit="return confirm('Hapus user <?= htmlspecialchars($u['username'], ENT_QUOTES) ?>?')"
                                      style="display:inline;">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="id_user" value="<?= (int)$u['id_user'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <button type="submit" class="btn-danger-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="text-muted" title="Tidak bisa hapus diri sendiri">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>    <!-- ═══ MODAL: Tambah/Edit User ═══ -->
    <div id="modalUser" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modalTitle" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modalTitle"><i class="fas fa-user-plus"></i> Tambah User Baru</h3>
                <button class="modal-close" onclick="toggleModal('modalUser')" aria-label="Tutup modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="modalAlerts"></div>

            <form method="POST" action="../controllers/AuthController.php" class="register-form" id="formUser" novalidate>
                <!-- ─── SECURITY: CSRF Token ─── -->
                <input type="hidden" name="action" id="formAction" value="register">
                <input type="hidden" name="id_user" id="id_user" value="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <!-- Username -->
                <div class="form-group">
                    <label for="reg_username">Username <span class="required">*</span></label>
                    <input type="text" id="reg_username" name="username"
                           required minlength="3" maxlength="100"
                           placeholder="Masukkan username" autocomplete="off">
                    <small class="form-hint">Min. 3 karakter, tanpa spasi (tidak dapat diubah setelah dibuat)</small>
                </div>

                <!-- Nama Lengkap -->
                <div class="form-group">
                    <label for="reg_nama">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" id="reg_nama" name="nama_lengkap"
                           required maxlength="100"
                           placeholder="Masukkan nama lengkap">
                </div>

                <div style="display:flex; gap:16px; margin-bottom:16px;">
                    <!-- Role -->
                    <div class="form-group" style="flex:1; margin-bottom:0;">
                        <label for="reg_role">Role <span class="required">*</span></label>
                        <select id="reg_role" name="role" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin">Admin</option>
                            <option value="kasir">Kasir</option>
                            <option value="gudang">Gudang</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="form-group" style="flex:1; margin-bottom:0;">
                        <label for="reg_status">Status <span class="required">*</span></label>
                        <select id="reg_status" name="status" required>
                            <option value="aktif">Aktif</option>
                            <option value="non-aktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label id="label_password" for="reg_password">Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="reg_password" name="password"
                               minlength="6" maxlength="255"
                               placeholder="Min. 6 karakter">
                        <button type="button" class="toggle-pass" aria-label="Tampilkan password"
                                onclick="togglePass('reg_password', this)">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <small id="hint_password" class="form-hint" style="display:none;">Kosongkan jika tidak ingin mengubah password.</small>
                </div>

                <!-- Konfirmasi Password -->
                <div class="form-group" id="group_confirm">
                    <label for="reg_confirm">Konfirmasi Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="reg_confirm" name="confirm_password"
                               minlength="6" maxlength="255"
                               placeholder="Ulangi password">
                        <button type="button" class="toggle-pass" aria-label="Tampilkan konfirmasi"
                                onclick="togglePass('reg_confirm', this)">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="toggleModal('modalUser')">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Simpan User
                    </button>
                </div>
            </form>
        </div>    </form>
        </div>
    </div>

<style>
/* ── Role & Status Badges ── */
.role-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.role-admin  { background: #fce8d5; color: #d4832c; }
.role-kasir  { background: #d5e8fc; color: #2c7ad4; }
.role-gudang { background: #d5fce8; color: #2cd480; }

.status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.status-active   { background: #d4edda; color: #155724; }
.status-inactive { background: #f8d7da; color: #721c24; }

/* ── Btn Danger Small ── */
.btn-danger-sm {
    background: #e74c3c;
    color: #fff;
    border: none;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    transition: 0.2s;
}
.btn-danger-sm:hover { background: #c0392b; }

.content-header { margin-bottom: 20px; }

/* ── Modal ── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.modal-box {
    background: #fff;
    border-radius: 16px;
    padding: 32px;
    width: 100%;
    max-width: 480px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: modalIn 0.3s ease-out;
}
@keyframes modalIn {
    from { opacity: 0; transform: translateY(-20px) scale(0.95); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}
.modal-header h3 { font-size: 18px; color: #2b1b17; }
.modal-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #999;
    padding: 4px 8px;
    border-radius: 6px;
    transition: 0.2s;
}
.modal-close:hover { background: #f0f0f0; color: #333; }

/* ── Form ── */
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #3e2723; margin-bottom: 6px; }
.form-group input,
.form-group select {
    width: 100%;
    padding: 11px 14px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: 0.2s;
    font-family: inherit;
}
.form-group input:focus,
.form-group select:focus { border-color: #3e2723; box-shadow: 0 0 0 3px rgba(62,39,35,0.1); }
.form-hint { font-size: 11px; color: #999; margin-top: 4px; display: block; }
.required { color: #e74c3c; }

.password-wrapper { position: relative; display: flex; align-items: center; }
.password-wrapper input { padding-right: 44px; }
.toggle-pass {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    cursor: pointer;
    color: #999;
    font-size: 15px;
    padding: 0;
}
.toggle-pass:hover { color: #3e2723; }

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #f0f0f0;
}
.btn-primary {
    background: #3e2723;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-primary:hover { background: #4e342e; }
.btn-secondary {
    background: #f5f5f5;
    color: #555;
    border: 1px solid #ddd;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: 0.2s;
}
.btn-secondary:hover { background: #e8e8e8; }

/* ── Flash Messages ── */
.flash-messages { margin: 0 0 20px 0; }
.alert {
    padding: 12px 18px;
    border-radius: 8px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.alert-error   { background: #fdf0f0; color: #c0392b; border: 1px solid #f5c6cb; }
.alert-success { background: #f0fdf4; color: #155724; border: 1px solid #c3e6cb; }
.alert-warning { background: #fff8e1; color: #856404; border: 1px solid #ffc107; }
.alert-info    { background: #e8f4fd; color: #0c5460; border: 1px solid #bee5eb; }
</style>

<script>
/**
 * Toggle tampilan modal
 */
function toggleModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    
    if (modal.style.display === 'none' || modal.style.display === '') {
        // Clear alerts
        const modalAlerts = document.getElementById('modalAlerts');
        if (modalAlerts) modalAlerts.innerHTML = '';
        
        // Reset form for Add User
        document.getElementById('formUser').reset();
        document.getElementById('formAction').value = 'register';
        document.getElementById('id_user').value = '';
        
        document.getElementById('reg_username').readOnly = false;
        document.getElementById('reg_password').required = true;
        document.getElementById('reg_password').oninput = null; // Reset custom listener
        document.getElementById('reg_confirm').required = true;
        document.getElementById('label_password').innerHTML = 'Password <span class="required">*</span>';
        document.getElementById('hint_password').style.display = 'none';
        document.getElementById('group_confirm').style.display = 'block';
        
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Tambah User Baru';
        
        modal.style.display = 'flex';
    } else {
        modal.style.display = 'none';
    }
}

/**
 * Persiapkan form untuk Edit User
 */
function editUser(id, username, nama, role, status) {
    const modal = document.getElementById('modalUser');
    
    document.getElementById('formAction').value = 'update_user';
    document.getElementById('id_user').value = id;
    
    document.getElementById('reg_username').value = username;
    document.getElementById('reg_username').readOnly = true; // Prevent changing username
    
    document.getElementById('reg_nama').value = nama;
    document.getElementById('reg_role').value = role;
    document.getElementById('reg_status').value = status;
    
    // Clear password fields first
    const passInput = document.getElementById('reg_password');
    const confirmInput = document.getElementById('reg_confirm');
    passInput.value = '';
    confirmInput.value = '';
    
    // Optional password fields for update
    passInput.required = false;
    confirmInput.required = false;
    document.getElementById('label_password').innerHTML = 'Password Baru (Opsional)';
    document.getElementById('hint_password').style.display = 'block';
    
    // Hide confirm group initially for edit
    const groupConfirm = document.getElementById('group_confirm');
    groupConfirm.style.display = 'none';
    
    // Show confirm group only if password is typed
    passInput.oninput = function() {
        if (this.value.trim().length > 0) {
            groupConfirm.style.display = 'block';
        } else {
            groupConfirm.style.display = 'none';
            confirmInput.value = '';
        }
    };
    
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Edit User';
    
    // Clear alerts
    const modalAlerts = document.getElementById('modalAlerts');
    if (modalAlerts) modalAlerts.innerHTML = '';
    
    modal.style.display = 'flex';
}

/**
 * Toggle tampilan password
 */
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'far fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'far fa-eye';
    }
}

// Tutup modal jika klik di luar box
document.addEventListener('click', function(e) {
    const modal = document.getElementById('modalUser');
    if (modal && e.target === modal) {
        modal.style.display = 'none';
    }
});

// Auto-hide flash setelah 5 detik
setTimeout(function() {
    const flashes = document.querySelectorAll('.flash-messages .alert');
    flashes.forEach(function(el) {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 5000);

// AJAX Form Submission
document.getElementById('formUser').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    btn.disabled = true;

    const formData = new FormData(this);
    formData.append('ajax', '1');
    const alertsDiv = document.getElementById('modalAlerts');
    alertsDiv.innerHTML = '';

    fetch('../controllers/AuthController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        if (data.status === 'error') {
            alertsDiv.innerHTML = `<div class="alert alert-error" style="margin-bottom:16px;"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
        } else {
            window.location.reload();
        }
    })
    .catch(err => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alertsDiv.innerHTML = `<div class="alert alert-error" style="margin-bottom:16px;"><i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan jaringan.</div>`;
    });
});
</script>

<?php include 'includes/footer.php'; ?>

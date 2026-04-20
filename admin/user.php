<?php
/**
 * Users Management Page
 * TEFA Bakery and Coffee Users Management
 */
$pageTitle = 'Users';
$dashboardPage = true;
$pageHeading = 'Users Management';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

        <!-- Users Management Content -->
        <section class="table-card">
          <div class="card-header">
            <h4>DAFTAR PENGGUNA</h4>
            <button class="view-all-btn">
              <span>Tambah Pengguna</span>
              <i class="fas fa-plus"></i>
            </button>
          </div>
          <div class="table-responsive">
            <table class="custom-table" id="userTable">
              <thead>
                <tr>
                  <th><span class="th-content">Nama</span></th>
                  <th><span class="th-content">Email</span></th>
                  <th><span class="th-content">Role</span></th>
                  <th><span class="th-content">Status</span></th>
                  <th><span class="th-content">Aksi</span></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <div class="prod-info">
                      <div class="prod-img-wrapper">
                        <img
                          src="https://ui-avatars.com/api/?name=Admin+Tefa&background=d4832c&color=fff&size=44"
                          alt="Admin Avatar"
                          loading="lazy"
                        />
                      </div>
                      <span>Admin TEFA</span>
                    </div>
                  </td>
                  <td>admin@tefa.com</td>
                  <td><span class="sku-code">Admin</span></td>
                  <td><span class="text-success">Aktif</span></td>
                  <td>
                    <button class="view-all-btn">Edit</button>
                    <button class="view-all-btn delete">Hapus</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div class="prod-info">
                      <div class="prod-img-wrapper">
                        <img
                          src="https://ui-avatars.com/api/?name=Kasir+Tefa&background=d97706&color=fff&size=44"
                          alt="Kasir Avatar"
                          loading="lazy"
                        />
                      </div>
                      <span>Kasir TEFA</span>
                    </div>
                  </td>
                  <td>kasir@tefa.com</td>
                  <td><span class="sku-code">Kasir</span></td>
                  <td><span class="text-success">Aktif</span></td>
                  <td>
                    <button class="view-all-btn">Edit</button>
                    <button class="view-all-btn delete">Hapus</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>

<?php include 'includes/footer.php'; ?>

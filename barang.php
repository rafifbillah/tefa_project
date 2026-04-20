<?php
/**
 * Users Management Page
 * TEFA Bakery and Coffee Users Management
 */
$pageTitle = 'Barang';
$dashboardPage = true;
$pageHeading = 'Barang Management';
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
                  <th><span class="th-content">Item Name</span></th>
                  <th><span class="th-content">Category</span></th>
                  <th><span class="th-content">Price</span></th>
                  <th><span class="th-content">Actions</span></th>
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
                      <span>Choco Cupcake</span>
                    </div>
                  </td>
                  <td><span class="sku-code">Cake</span></td>
                  <td>21.000</td>
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
                          src="https://ui-avatars.com/api/?name=Admin+Tefa&background=d4832c&color=fff&size=44"
                          alt="Admin Avatar"
                          loading="lazy"
                        />
                      </div>        
                      <span>Sourdough Loaf</span>
                    </div>
                  </td>
                  <td><span class="sku-code">Bread</span></td>
                  <td>12.000</td>
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

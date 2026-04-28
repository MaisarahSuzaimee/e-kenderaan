<?php
require 'config.php';


$currentPage = basename($_SERVER['PHP_SELF']); // e.g., "STK.php", "userList.php"

function getUser($conn)
{
  if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT * FROM penggunajkn WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
  }
  return null;
}

$userData = getUser($conn);
$nama = $userData['nama'] ?? "Pengguna Tidak Dikenali";
$userEmail = $userData['email'] ?? "";
$role = $userData['role'] ?? "";


?>

<div class="header">
    <div class="logo-container">
        <img src="IMG/logo jkn.png" alt="JKN Logo" class="logo" />
    </div>
    <div class="header-text">
        <h1>SISTEM PENGURUSAN & TEMPAHAN KENDERAAN</h1>
        <p>Jabatan Kesihatan Negeri Kedah</p>
    </div>
</div>

<nav class="nav-container">

    <?php if ($role == 'staff') { ?>

    <a href="user_page.php" id="senaraiTempahanLink"
        class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'user_page.php') ? 'active' : '' ?>">
        <i class="fas fa-calendar-alt"></i>
        Senarai Tempahan
    </a>

    <a href="user_page.php?open=tempah" id="TempahKenderaanLink" class="nav-link">
        <i class="fas fa-plus"></i>
        Tempah Kenderaan
    </a>

    <?php } ?>

    <?php
  if ($role == 'admin') {
  ?>
    <!-- <div class="dropdown">
      <a href="#tempahan" class="nav-link dropdown-btn <?= ($currentPage == 'STK.php') ? 'active' : '' ?>">
        <i class="fas fa-calendar-alt"></i>
        Tempahan
      </a>
      <div class="dropdown-content">
        <a href="STK.php" class="nav-link">
          <i class="fas fa-list"></i> Senarai Tempah Kenderaan
        </a>
      </div>
    </div> -->
    <a href="STK.php" id="senaraiTempahanLink"
        class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'STK.php') ? 'active' : '' ?>">
        <i class="fas fa-calendar-alt"></i>
        Senarai Tempahan
    </a>

    <a href="STK.php?open=tempah" id="TempahKenderaanLink" class="nav-link">
        <i class="fas fa-plus"></i>
        Tempah Kenderaan
    </a>

    <a href="Kalendar_Tempahan.php" class="nav-link <?= ($currentPage == 'Kalendar_Tempahan.php') ? 'active' : '' ?>">
        <i class="fas fa-calendar-week"></i>
        Kalendar Tempahan
    </a>

    <a href="Pemandu.php" class="nav-link <?= ($currentPage == 'Pemandu.php') ? 'active' : '' ?>">
        <i class="fas fa-user-tie"></i>
        Pemandu
    </a>

    <a href="Kenderaan_Jabatan.php" class="nav-link <?= ($currentPage == 'Kenderaan_Jabatan.php') ? 'active' : '' ?>">
        <i class="fas fa-car-alt"></i>
        Kenderaan Jabatan
    </a>

    <a href="Kenderaan_Rasmi_Jawatan.php"
        class="nav-link <?= ($currentPage == 'Kenderaan_Rasmi_Jawatan.php') ? 'active' : '' ?>">
        <i class="fas fa-car"></i>
        Kenderaan Rasmi Jawatan
    </a>

    <a href="Penyelenggaraan.php" class="nav-link <?= ($currentPage == 'Penyelenggaraan.php') ? 'active' : '' ?>">
        <i class="fas fa-tools"></i>
        Penyelenggaraan
    </a>

    <a href="Pemeriksaan_Berkala.php"
        class="nav-link <?= ($currentPage == 'Pemeriksaan_Berkala.php') ? 'active' : '' ?>">
        <i class="fas fa-clipboard-check"></i>
        Pemeriksaan Berkala
    </a>

    <a href="Pekeliling_Kenderaan.php"
        class="nav-link <?= ($currentPage == 'Pekeliling_Kenderaan.php') ? 'active' : '' ?>">
        <i class="fas fa-file-alt"></i>
        Pekeliling Kenderaan
    </a>

    <a href="pengguna.php" class="nav-link <?= ($currentPage == 'pengguna.php') ? 'active' : '' ?>">
        <i class="fas fa-user"></i>
        Pengguna
    </a>

    <?php } ?>
    <?php
  if ($role == 'superadmin') { ?>
    <a href="superadmin.php"
        class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'superadmin.php') ? 'active' : '' ?>">
        <i class="fas fa-calendar-week"></i>
        Dashboard
    </a>
    <a href="STK_superadmin.php" id="senaraiTempahanLink"
        class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'STK_superadmin.php') ? 'active' : '' ?>">
        <i class="fas fa-calendar-alt"></i>
        Senarai Tempahan
    </a>

    <div class="dropdown">
        <a href="#kawalan" class="nav-link dropdown-btn 
        <?= ($currentPage == 'Kemaskini_JTK.php' || $currentPage == 'Kemaskini_TP.php' || $currentPage == 'Jenis_Kenderaan.php' || $currentPage == 'Pengeluar.php' || 
        $currentPage == 'Penempatan_pemandu.php' || $currentPage == 'PTJ.php' || $currentPage == 'Bahagian.php' || $currentPage == 'Unit.php') ? 'active' : '' ?>">
            <i class="fas fa-cogs"></i>
            Kawalan
        </a>
        <div class="dropdown-content">
            <a href="Kemaskini_TP.php"  class="nav-link <?= ($currentPage == 'Kemaskini_TP.php') ? 'active' : '' ?>"><i class="fas fa-route"></i> Kemaskini Tujuan Perjalanan</a>
            <a href="Jenis_Kenderaan.php"  class="nav-link <?= ($currentPage == 'Jenis_Kenderaan.php') ? 'active' : '' ?>"><i class="fas fa-car"></i> Jenis Kenderaan</a>
            <a href="Pengeluar.php"  class="nav-link <?= ($currentPage == 'Pengeluar.php') ? 'active' : '' ?>"><i class="fas fa-industry"></i> Pengeluar/Model</a>
            <a href="Penempatan_pemandu.php"  class="nav-link <?= ($currentPage == 'Penempatan_pemandu.php') ? 'active' : '' ?>"><i class="fas fa-user-friends"></i> Penempatan Pemandu</a>
            <a href="PTJ.php" class="nav-link <?= ($currentPage == 'PTJ.php') ? 'active' : '' ?>"><i class="fas fa-building"></i> PTJ</a>
            <a href="Bahagian.php"  class="nav-link <?= ($currentPage == 'Bahagian.php') ? 'active' : '' ?>"><i class="fas fa-puzzle-piece"></i> Bahagian</a>
            <a href="Unit.php"  class="nav-link <?= ($currentPage == 'Unit.php') ? 'active' : '' ?>"><i class="fas fa-layer-group"></i> Unit</a>
        </div>
    </div>
    <div class="dropdown">
        <a href="#system"
            class="nav-link dropdown-btn <?= ($currentPage == 'backup_restore.php' || $currentPage == 'system_logs.php') ? 'active' : '' ?>">
            <i class="fas fa-server"></i>
            Sistem
        </a>
        <div class="dropdown-content">
            <a href="backup_restore.php"
                class="nav-link <?= ($currentPage == 'backup_restore.php') ? 'active' : '' ?>"><i
                    class="fas fa-database"></i> Backup</a>
            <a href="system_logs.php" class="nav-link <?= ($currentPage == 'system_logs.php') ? 'active' : '' ?>"><i
                    class="fas fa-history"></i> Log Sistem</a>
        </div>
    </div>
    <?php }
  ?>

    <div class="dropdown">
        <?php if ($role == 'admin') { ?>
        <a href="#profile"
            class="nav-link dropdown-btn <?= ($currentPage == 'tukar_pass_admin.php') ? 'active' : '' ?>">
            <?php } else if ($role == 'staff') { ?>
            <a href="#profile"
                class="nav-link dropdown-btn <?= ($currentPage == 'tukar_pass_user.php' || $currentPage == 'profile.php') ? 'active' : '' ?>">
                <?php } else if ($role == 'superadmin') { ?>
                <a href="#profile"
                    class="nav-link dropdown-btn <?= ($currentPage == 'tukar_pass_superadmin.php' || $currentPage == 'profile.php' || $currentPage == 'pengguna_superadmin.php') ? 'active' : '' ?>">
                    <?php } ?>
                    <i class="fas fa-user-circle"></i>
                    Profile
                </a>
                <div class="dropdown-content">
                    <?php if ($role == 'admin') { ?>
                    <a href="profile.php" class="nav-link <?= ($currentPage == 'profile.php') ? 'active' : '' ?>"><i
                            class="fas fa-address-card"></i> Profile</a>

                    <a href="tukar_pass_admin.php"
                        class="nav-link <?= ($currentPage == 'tukar_pass_admin.php') ? 'active' : '' ?>">
                        <?php } else if ($role == 'staff') { ?>
                        <a href="profile.php" class="nav-link <?= ($currentPage == 'profile.php') ? 'active' : '' ?>"><i
                                class="fas fa-address-card"></i> Profile</a>

                        <a href="tukar_pass_user.php"
                            class="nav-link <?= ($currentPage == 'tukar_pass_user.php') ? 'active' : '' ?>">
                            <?php } else if ($role == 'superadmin') { ?>
                            <a href="pengguna_superadmin.php"
                                class="nav-link <?= ($currentPage == 'pengguna_superadmin.php') ? 'active' : '' ?>"><i
                                    class="fas fa-user-shield"></i> Senarai Pengguna</a>
                            <a href="profile.php"
                                class="nav-link <?= ($currentPage == 'profile.php') ? 'active' : '' ?>"><i
                                    class="fas fa-address-card"></i> Profile</a>

                            <a href="tukar_pass_superadmin.php"
                                class="nav-link <?= ($currentPage == 'tukar_pass_superadmin.php') ? 'active' : '' ?>">
                                <?php } ?>
                                <i class="fas fa-key"></i> Tukar Kata Laluan
                            </a>
                            <a href="#" onclick="handleLogout()">
                                <i class="fas fa-sign-out-alt"></i> Log Keluar
                            </a>
                </div>
</nav>
<?php include 'tempahanKenderaan.php' ?>
<script src="logout.js"></script>
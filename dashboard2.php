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
        <h2>SISTEM PENGURUSAN & TEMPAHAN KENDERAAN</h2>
        <p style="font-size: 18px;">Jabatan Kesihatan Negeri Kedah</p>
        <!-- <p>Jabatan Kesihatan Negeri Kedah</p> -->
    </div>
</div>
<div class="sidebar open">
    <!-- <div class="logo-details">
            <div class="logo_name">constGenius</div>
            <i class='bx bx-menu' id="btn"></i>
        </div> -->
    <ul class="nav-list">

        <?php
        if ($role == 'staff') { ?>

            <li>
                <a href="user_page.php" class="<?= ($currentPage == 'user_page.php') ? 'active' : '' ?>">
                    <i class='fas fa-car'></i>
                    <span class="links_name">Tempahan Kenderaan</span>
                </a>
                <span class="tooltip">Tempahan Kenderaan</span>
            </li>
            <li>
                <a href="prosedur_tempahan.php" class="<?= ($currentPage == 'prosedur_tempahan.php') ? 'active' : '' ?>">
                    <i class='fas fa-note-sticky'></i>
                    <span class="links_name">Prosedur Tempahan</span>
                </a>
            </li>
            <li>
                <a href="profile.php"
                    class="<?= ($currentPage == 'profile.php' || $currentPage == 'tukar_pass_user.php') ? 'active' : '' ?>">
                    <i class='fas fa-user-circle'></i>
                    <span class="links_name">Profile</span>
                </a>
                <span class="tooltip">Profile</span>
            </li>
            <li>
                <a href="#" onclick="handleLogout()">
                    <i class='fas fa-sign-out-alt'></i>
                    <span class="links_name">Logout</span>
                </a>
                <span class="tooltip">Logout</span>
            </li>

        <?php } else if ($role == 'admin') { ?>
                <li>
                    <a href="dashboard_admin.php" class="<?= ($currentPage == 'dashboard_admin.php') ? 'active' : '' ?>">
                        <i class='fas fa-square-poll-vertical'></i>
                        <span class="links_name">Dashboard</span>
                    </a>
                    <span class="tooltip">Dashboard</span>
                </li>
                <li>
                    <a href="STK.php" class="<?= ($currentPage == 'STK.php') ? 'active' : '' ?>">
                        <i class='fas fa-clipboard-list'></i>
                        <span class="links_name">Tempahan Kenderaan</span>
                    </a>
                    <span class="tooltip">Tempahan Kenderaan</span>
                </li>
                <li>
                    <a href="kalendar_tempahan.php" class="<?= ($currentPage == 'kalendar_tempahan.php') ? 'active' : '' ?>">
                        <i class='fas fa-calendar'></i>
                        <span class="links_name">Kalendar Tempahan</span>
                    </a>
                    <span class="tooltip">Kalendar Tempahan</span>
                </li>
                <li>
                    <a href="pemandu.php" class="<?= ($currentPage == 'pemandu.php') ? 'active' : '' ?>">
                        <i class='fas fa-user-tie'></i>
                        <span class="links_name">Pemandu</span>
                    </a>
                    <span class="tooltip">Pemandu</span>
                </li>
                <li>
                    <a href="Jadual_pemandu.php">
                        <i class='fas fa-calendar-days'></i>
                        <span class="links_name">Jadual Pemandu</span>
                    </a>
                    <span class="tooltip">Jadual Pemandu</span>
                </li>
                <li>
                    <a href="Kenderaan_Jabatan.php">
                        <i class='fas fa-car-alt'></i>
                        <span class="links_name">Kenderaan Jabatan</span>
                    </a>
                    <span class="tooltip">Kenderaan Jabatan</span>
                </li>
                <li>
                    <a href="Pekeliling_Kenderaan.php"
                        class="<?= ($currentPage == 'Pekeliling_Kenderaan.php') ? 'active' : '' ?>">
                        <i class='fas fa-file-alt'></i>
                        <span class="links_name">Pekeliling Kenderaan</span>
                    </a>
                    <span class="tooltip">Pekeliling Kenderaan</span>
                </li>
                <!-- <li>
                    <a href="pengguna.php" class="<?= ($currentPage == 'pengguna.php') ? 'active' : '' ?>">
                        <i class='fas fa-user'></i>
                        <span class="links_name">Pengguna</span>
                    </a>
                    <span class="tooltip">Pengguna</span>
                </li> -->
                <li>
                    <a href="Report.php" class="<?= ($currentPage == 'Report.php') ? 'active' : '' ?>">
                        <i class='fas fa-chart-pie'></i>
                        <span class="links_name">Report</span>
                    </a>
                    <span class="tooltip">Report</span>
                </li>
                <li>
                    <a href="profile.php"
                        class="<?= ($currentPage == 'profile.php' || $currentPage == 'tukar_pass_admin.php') ? 'active' : '' ?>">
                        <i class='fas fa-user-circle'></i>
                        <span class="links_name">Profile</span>
                    </a>
                    <span class="tooltip">Profile</span>
                </li>
                <li>
                    <a href="#" onclick="handleLogout()">
                        <i class='fas fa-sign-out-alt'></i>
                        <span class="links_name">Logout</span>
                    </a>
                    <span class="tooltip">Logout</span>
                </li>
        <?php } else if ($role == 'superadmin') { ?>

                    <li>
                        <a href="superadmin.php" class="<?= ($currentPage == 'superadmin.php') ? 'active' : '' ?>">
                            <i class='fas fa-square-poll-vertical'></i>
                            <span class="links_name">Dashboard</span>
                        </a>
                        <span class="tooltip">Dashboard</span>
                    </li>
                    <li>
                        <a href="STK.php" class="<?= ($currentPage == 'STK.php') ? 'active' : '' ?>">
                            <i class='fas fa-clipboard-list'></i>
                            <span class="links_name">Senarai Tempahan</span>
                        </a>
                        <span class="tooltip">Senarai Tempahan</span>
                    </li>
                    <li>
                        <a href="kalendar_tempahan.php" class="<?= ($currentPage == 'kalendar_tempahan.php') ? 'active' : '' ?>">
                            <i class='fas fa-calendar'></i>
                            <span class="links_name">Kalendar Tempahan</span>
                        </a>
                        <span class="tooltip">Kalendar Tempahan</span>
                    </li>
                    <li>
                        <a href="pemandu.php" class="<?= ($currentPage == 'pemandu.php') ? 'active' : '' ?>">
                            <i class='fas fa-user-tie'></i>
                            <span class="links_name">Pemandu</span>
                        </a>
                        <span class="tooltip">Pemandu</span>
                    </li>
                    <li>
                        <a href="Jadual_pemandu.php">
                            <i class='fas fa-calendar-days'></i>
                            <span class="links_name">Jadual Pemandu</span>
                        </a>
                        <span class="tooltip">Jadual Pemandu</span>
                    </li>
                    <li>
                        <a href="Kenderaan_Jabatan.php">
                            <i class='fas fa-car-alt'></i>
                            <span class="links_name">Kenderaan Jabatan</span>
                        </a>
                        <span class="tooltip">Kenderaan Jabatan</span>
                    </li>
                    <li>
                        <a href="Pekeliling_Kenderaan.php"
                            class="<?= ($currentPage == 'Pekeliling_Kenderaan.php') ? 'active' : '' ?>">
                            <i class='fas fa-file-alt'></i>
                            <span class="links_name">Pekeliling Kenderaan</span>
                        </a>
                        <span class="tooltip">Pekeliling Kenderaan</span>
                    </li>
                    <li>
                        <a href="Report.php" class="<?= ($currentPage == 'Report.php') ? 'active' : '' ?>">
                            <i class='fas fa-chart-pie'></i>
                            <span class="links_name">Report</span>
                        </a>
                        <span class="tooltip">Report</span>
                    </li>
                    <li class="nav-section">KAWALAN</li>
                    <li>
                        <a href="Kemaskini_TP.php" class="<?= ($currentPage == 'Kemaskini_TP.php') ? 'active' : '' ?>">
                            <i class='fas fa-route'></i>
                            <span class="links_name">Tujuan Perjalanan</span>
                        </a>
                        <span class="tooltip">Tujuan Perjalanan</span>
                    </li>
                    <li>
                        <a href="Jenis_Kenderaan.php" class="<?= ($currentPage == 'Jenis_Kenderaan.php') ? 'active' : '' ?>">
                            <i class='fas fa-car'></i>
                            <span class="links_name">Jenis Kenderaan</span>
                        </a>
                        <span class="tooltip">Jenis Kenderaan</span>
                    </li>
                    <!-- <li>
                        <a href="Pengeluar.php" class="<?= ($currentPage == 'Pengeluar.php') ? 'active' : '' ?>">
                            <i class='fas fa-industry'></i>
                            <span class="links_name">Pengeluar</span>
                        </a>
                        <span class="tooltip">Pengeluar</span>
                    </li> -->
                    <li>
                        <a href="Penempatan_pemandu.php" class="<?= ($currentPage == 'Penempatan_pemandu.php') ? 'active' : '' ?>">
                            <i class='fas fa-user-friends'></i>
                            <span class="links_name">Penempatan Pemandu</span>
                        </a>
                        <span class="tooltip">Penempatan Pemandu</span>
                    </li>
                    <li>
                        <a href="PTJ.php" class="<?= ($currentPage == 'PTJ.php') ? 'active' : '' ?>">
                            <i class='fas fa-building'></i>
                            <span class="links_name">PTJ</span>
                        </a>
                        <span class="tooltip">PTJ</span>
                    </li>
                    <li>
                        <a href="Bahagian.php" class="<?= ($currentPage == 'Bahagian.php') ? 'active' : '' ?>">
                            <i class='fas fa-puzzle-piece'></i>
                            <span class="links_name">Bahagian</span>
                        </a>
                        <span class="tooltip">Bahagian</span>
                    </li>
                    <li>
                        <a href="Unit.php" class="<?= ($currentPage == 'Unit.php') ? 'active' : '' ?>">
                            <i class='fas fa-layer-group'></i>
                            <span class="links_name">Unit</span>
                        </a>
                        <span class="tooltip">Unit</span>
                    </li>
                    <li>
                        <a href="pengguna_superadmin.php"
                            class="<?= ($currentPage == 'pengguna_superadmin.php') ? 'active' : '' ?>">
                            <i class='fas fa-user'></i>
                            <span class="links_name">Pengguna</span>
                        </a>
                        <span class="tooltip">Pengguna</span>
                    </li>
                    <li>
                        <a href="profile.php"
                            class="<?= ($currentPage == 'profile.php' || $currentPage == 'tukar_pass_admin.php') ? 'active' : '' ?>">
                            <i class='fas fa-user-circle'></i>
                            <span class="links_name">Profile</span>
                        </a>
                        <span class="tooltip">Profile</span>
                    </li>
                    <li>
                        <a href="#" onclick="handleLogout()">
                            <i class='fas fa-sign-out-alt'></i>
                            <span class="links_name">Logout</span>
                        </a>
                        <span class="tooltip">Logout</span>
                    </li>
        <?php } else if ($role == 'penyelaras_bahagian') { ?>

                        <li>
                            <a href="STK_Bahagian.php" class="<?= ($currentPage == 'STK_Bahagian.php') ? 'active' : '' ?>">
                                <i class='fas fa-clipboard-list'></i>
                                <span class="links_name">Tempahan Kenderaan</span>
                            </a>
                            <span class="tooltip">Tempahan Kenderaan</span>
                        </li>
                        <li>
                            <a href="prosedur_tempahan.php" class="<?= ($currentPage == 'prosedur_tempahan.php') ? 'active' : '' ?>">
                                <i class='fas fa-note-sticky'></i>
                                <span class="links_name">Prosedur Tempahan</span>
                            </a>
                        </li>
                        <li>
                        <a href="profile.php"
                            class="<?= ($currentPage == 'profile.php' || $currentPage == 'tukar_pass_admin.php') ? 'active' : '' ?>">
                            <i class='fas fa-user-circle'></i>
                            <span class="links_name">Profile</span>
                        </a>
                        <span class="tooltip">Profile</span>
                    </li>
                    <li>
                        <a href="#" onclick="handleLogout()">
                            <i class='fas fa-sign-out-alt'></i>
                            <span class="links_name">Logout</span>
                        </a>
                        <span class="tooltip">Logout</span>
                    </li>
        <?php } ?>


    </ul>
</div>


<script src="logout.js"></script>
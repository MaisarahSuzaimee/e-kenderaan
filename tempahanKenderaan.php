<?php
require 'config.php';


$role = $_SESSION['role'] ?? 'guest';



?>
<div id="addModal" class="modal2">
    <div class="modal-content2">
        <div class="container">
            <div class="modal-header">
                <span class="close" onclick="closeTempahModal()">&times;</span>

                <h2 class="form-title">Borang Tempahan Kenderaan</h2>
            </div>

            <form id="addForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="action" value="insert">
                <!-- First column -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tarikh Memohon*</label>
                        <input type="text" id="tarikh_mohon" name="tarikh_mohon" class="form-control2" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pemohon</label>
                        <input type="text" id="pemohon" name="pemohon" class="form-control2"
                            value="<?= htmlspecialchars($_SESSION['username']) ?>" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Bertolak*</label>
                        <textarea id="bertolak" name="bertolak" class="form-control" rows="3"
                            style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;"
                            required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Destinasi*</label>
                        <textarea id="destinasi" name="destinasi" class="form-control" rows="3"
                            style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;"
                            required></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Jenis Perjalanan*</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="jenis_perjalanan" value="2 hala" required>
                            2 hala
                        </label>
                        <label>
                            <input type="radio" name="jenis_perjalanan" value="1 hala" required>
                            1 hala
                        </label>
                    </div>
                </div>

                <!-- Empty space beside Jenis Perjalanan -->
                <!-- <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <div class="form-control" style="border:none;"></div>
                    </div> -->

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tarikh Pergi*</label>
                        <input type="date" id="tarikh_pergi" name="tarikh_pergi" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Masa Pergi*</label>
                        <input type="time" id="Masa_Pergi" name="Masa_Pergi" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tarikh Balik*</label>
                        <input type="date" id="tarikh_balik" name="tarikh_balik" class="form-control" required>
                    </div>


                    <div class="form-group">
                        <label class="form-label">Masa Balik*</label>
                        <input type="time" id="Masa_Balik" name="Masa_Balik" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tujuan Perjalanan*</label>
                        <select id="tujuan_perjalanan2" name="tujuan_perjalanan" class="form-control" required>
                            <option value="">Pilih Tujuan</option>
                            <?php
                            // Query to fetch travel purposes from ttempah_tujuan table
                            $tujuanQuery = "SELECT * FROM ttempah_tujuan ORDER BY tujuan_perjalanan ASC";
                            $tujuanResult = $conn->query($tujuanQuery);

                            if ($tujuanResult && $tujuanResult->num_rows > 0) {
                                while ($tujuan = $tujuanResult->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($tujuan['tujuan_perjalanan']) . '">' .
                                        htmlspecialchars($tujuan['tujuan_perjalanan']) . '</option>';
                                }
                            } else {
                                // Fallback to hardcoded options if query fails
                                $defaultTujuan = ["Mesyuarat", "Lawatan Kerja", "Bank", "Kursus/Seminar", "Lain-lain"];
                                foreach ($defaultTujuan as $tujuan) {
                                    echo '<option>' . $tujuan . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lain-lain Tujuan</label>
                        <input type="text" id="lain_tujuan" name="lain_tujuan" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Negeri*</label>
                        <select id="negeri" name="negeri" class="form-control" required>
                            <option value="">Pilih Negeri</option>
                            <?php
                            // Query to fetch states from tnegeri table
                            $negeriQuery = "SELECT * FROM tnegeri ORDER BY negeri ASC";
                            $negeriResult = $conn->query($negeriQuery);

                            if ($negeriResult && $negeriResult->num_rows > 0) {
                                while ($negeri = $negeriResult->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($negeri['negeri']) . '">' .
                                        htmlspecialchars($negeri['negeri']) . '</option>';
                                }
                            } else {
                                // Fallback to hardcoded options if query fails
                                $defaultNegeri = [
                                    "Johor",
                                    "Kedah",
                                    "Kelantan",
                                    "Kuala Lumpur",
                                    "Labuan",
                                    "Melaka",
                                    "Negeri Sembilan",
                                    "Pahang",
                                    "Perak",
                                    "Perlis",
                                    "Pulau Pinang",
                                    "Putrajaya",
                                    "Sabah",
                                    "Sarawak",
                                    "Selangor",
                                    "Terengganu"
                                ];
                                foreach ($defaultNegeri as $negeri) {
                                    echo '<option>' . $negeri . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jenis Kenderaan*</label>
                        <select id="jenis_kenderaan" name="jenis_kenderaan" class="form-control" required>
                            <option value="">Pilih Kenderaan</option>
                            <?php
                            // Query to fetch vehicle types from ttempah_jenis table
                            $kenderaanQuery = "SELECT * FROM ttempah_jenis ORDER BY jenis_kenderaan ASC";
                            $kenderaanResult = $conn->query($kenderaanQuery);

                            if ($kenderaanResult && $kenderaanResult->num_rows > 0) {
                                while ($kenderaan = $kenderaanResult->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($kenderaan['jenis_kenderaan']) . '">' .
                                        htmlspecialchars($kenderaan['jenis_kenderaan']) . '</option>';
                                }
                            } else {
                                // Fallback to hardcoded options if query fails
                                $defaultKenderaan = ["Sedan", "MPV 6 Seater", "Lori", "Hino", "SUV"];
                                foreach ($defaultKenderaan as $kenderaan) {
                                    echo '<option>' . $kenderaan . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>


                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Bilangan Penumpang*</label>
                        <input type="number" id="bil_penumpang" name="bil_penumpang" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label2">Senarai Penumpang*</label>
                        <textarea id="senarai_penumpang" name="senarai_penumpang" class="form-control" rows="2"
                            style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;"
                            required></textarea>
                    </div>
                </div>



                <div class="button-group">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-cancel" onclick="handleCancel2()"><i class="fas fa-times"></i>
                        Batal</button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
const userRole = "<?= $role ?>";
console.log(userRole);
document.addEventListener("DOMContentLoaded", function() {
    const params = new URLSearchParams(window.location.search);

    if (params.get("open") === "tempah") {
        openTempahModal();

        // make navbar active
        document.getElementById('senaraiTempahanLink').classList.remove('active');
        document.getElementById('TempahKenderaanLink').classList.add('active');
    }
});

function openTempahModal() {
    document.getElementById('addModal').style.display = "block";

    // remove active
    document.getElementById('senaraiTempahanLink').classList.remove('active');
    document.getElementById('TempahKenderaanLink').classList.add('active');
}

function closeTempahModal() {
    document.getElementById('addModal').style.display = "none";

    if (userRole === 'admin') {
        window.location.href = "STK.php";
    } else if (userRole === 'staff') {
        window.location.href = "user_page.php";
    }
    // add active again
    // document.getElementById('senaraiTempahanLink').classList.add('active');
    // document.getElementById('TempahKenderaanLink').classList.remove('active');

}

function handleCancel2() {
    const modal = document.getElementById('addModal');
    const form = document.getElementById('addForm');

    let hasValue = false;

    Array.from(form.elements).forEach(el => {
        if (['pemohon', 'tarikh_mohon'].includes(el.name)) return;
        if (['button', 'submit', 'hidden'].includes(el.type)) return;

        if (el.type === 'radio' || el.type === 'checkbox') {
            if (el.checked) hasValue = true;
        } else if (el.value && el.value.trim() !== '') {
            hasValue = true;
        }
    });

    if (!hasValue) {
        modal.style.display = 'none';

        if (userRole === 'admin') {
            window.location.href = "STK.php";
        } else if (userRole === 'staff') {
            window.location.href = "user_page.php";
        }
        // const senarai = document.getElementById('senaraiTempahanLink');
        // const tempah = document.getElementById('TempahKenderaanLink');

        // if (senarai) senarai.classList.add('active');
        // if (tempah) tempah.classList.remove('active');

        return;
    }

    Swal.fire({
        title: 'Anda pasti?',
        text: "Semua maklumat yang diisi akan dibatalkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, batalkan!',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (result.isConfirmed) {

            // save values first
            const pemohonValue = document.getElementById('pemohon').value;
            const tarikhValue = document.getElementById('tarikh_mohon').value;

            form.reset();

            // restore them
            document.getElementById('pemohon').value = pemohonValue;
            document.getElementById('tarikh_mohon').value = tarikhValue;

            modal.style.display = 'none';

            if (userRole === 'admin') {
                window.location.href = "STK.php";
            } else if (userRole === 'staff') {
                window.location.href = "user_page.php";
            }
            // const senarai = document.getElementById('senaraiTempahanLink');
            // const tempah = document.getElementById('TempahKenderaanLink');

            // if (senarai) senarai.classList.add('active');
            // if (tempah) tempah.classList.remove('active');

            Swal.fire(
                'Dibatalkan!',
                'Borang telah dikosongkan.',
                'success'
            );
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const now = new Date();

    const date = now.toISOString().split('T')[0];
    const time = now.toLocaleTimeString('en-GB');

    document.getElementById("tarikh_mohon").value = date + " " + time;
});

document.addEventListener('DOMContentLoaded', function() {
    const radioButtons = document.querySelectorAll('input[name="jenis_perjalanan"]');
    const tarikhBalik = document.getElementById('tarikh_balik');
    const masaBalik = document.getElementById('Masa_Balik');

    function toggleReturnFields() {
        const selectedValue = document.querySelector('input[name="jenis_perjalanan"]:checked').value;

        if (selectedValue === '1 hala') {
            tarikhBalik.disabled = true;
            masaBalik.disabled = true;
            tarikhBalik.required = false;
            masaBalik.required = false;

            // Clear values
            tarikhBalik.value = '';
            masaBalik.value = '';
        } else {
            tarikhBalik.disabled = false;
            masaBalik.disabled = false;
            tarikhBalik.required = true;
            masaBalik.required = true;
        }
    }

    // Add event listeners to radio buttons
    radioButtons.forEach(function(radio) {
        radio.addEventListener('change', toggleReturnFields);
    });

    // Initialize on page load
    if (document.querySelector('input[name="jenis_perjalanan"]:checked')) {
        toggleReturnFields();
    }
});
document.getElementById('tarikh_balik').addEventListener('change', function() {
    const tarikhPergiDate = new Date(document.getElementById('tarikh_pergi').value);
    const tarikhBalikDate = new Date(this.value);

    if (tarikhBalikDate < tarikhPergiDate) {
        Swal.fire({
            icon: 'error',
            title: 'Ralat',
            text: 'Tarikh balik tidak boleh lebih awal dari tarikh pergi!',
            confirmButtonText: 'OK'
        }).then(() => {
            document.getElementById('tarikh_balik').value = '';
        });
    }
});
</script>
<?php

session_start();
require 'config.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}


if (!in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: error.html");
    exit();
}

$month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('m');
$year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');

if ($month > 12) {
    $month = 1;
    $year++;
} elseif ($month < 1) {
    $month = 12;
    $year--;
}

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

function getMalayMonthName($month)
{
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
        5 => 'Mei', 6 => 'Jun', 7 => 'Julai', 8 => 'Ogos',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember',
    ];
    return $months[$month] ?? '';
}

function jadualPemanduNameUpper(?string $name): string
{
    if ($name === null || $name === '') {
        return '';
    }
    $t = trim($name);
    if ($t === '') {
        return '';
    }
    if (function_exists('mb_strtoupper')) {
        return mb_strtoupper($t, 'UTF-8');
    }
    return strtoupper($t);
}

/** Normalised key for palette map (empty code → single bucket). */
function jadualLeaveTypeCodeKey(?string $code): string
{
    $c = strtoupper(trim((string) $code));

    return $c === '' ? '—' : $c;
}

/**
 * Assign stable palette index 0..11 per distinct code in this month (sorted by code).
 *
 * @param  array<int, array<string, mixed>>  $leaveRows
 * @return array<string, int> code key => palette index
 */
function jadualBuildLeaveCodePalette(array $leaveRows): array
{
    $seen = [];
    foreach ($leaveRows as $r) {
        $seen[jadualLeaveTypeCodeKey($r['leave_type_code'] ?? '')] = true;
    }
    $keys = array_keys($seen);
    sort($keys, SORT_STRING);
    $map = [];
    $i = 0;
    foreach ($keys as $k) {
        $map[$k] = $i % 12;
        $i++;
    }

    return $map;
}

/** Plain-text hover description (Malay) for tooltip — not HTML. */
function jadualLeaveHoverDesc(
    string $nama,
    string $typeName,
    string $typeCode,
    string $startYmd,
    string $endYmd,
    string $reason
): string {
    $sd = '';
    $ed = '';
    if ($startYmd !== '') {
        $ts = strtotime($startYmd);
        $sd = $ts !== false ? date('d/m/Y', $ts) : $startYmd;
    }
    if ($endYmd !== '') {
        $te = strtotime($endYmd);
        $ed = $te !== false ? date('d/m/Y', $te) : $endYmd;
    }
    $period = '';
    if ($sd !== '' && $ed !== '') {
        $period = $sd === $ed ? $sd : $sd . ' hingga ' . $ed;
    } elseif ($sd !== '') {
        $period = $sd;
    } elseif ($ed !== '') {
        $period = $ed;
    }

    $jenis = '';
    if ($typeName !== '') {
        $jenis = $typeName;
        if ($typeCode !== '') {
            $jenis .= ' (kod ' . $typeCode . ')';
        }
    } elseif ($typeCode !== '') {
        $jenis = 'kod jenis ' . $typeCode;
    }

    $parts = [];
    $parts[] = 'Pemandu ' . $nama . ' tidak tersedia kerana cuti.';
    if ($jenis !== '') {
        $parts[] = 'Jenis: ' . $jenis . '.';
    }
    if ($period !== '') {
        $parts[] = 'Tempoh: ' . $period . '.';
    }
    $r = trim($reason);
    if ($r !== '') {
        if (function_exists('mb_strlen') && mb_strlen($r, 'UTF-8') > 120) {
            $r = mb_substr($r, 0, 118, 'UTF-8') . '…';
        } elseif (strlen($r) > 120) {
            $r = substr($r, 0, 118) . '…';
        }
        $parts[] = 'Catatan: ' . $r . '.';
    }
    $parts[] = 'Klik untuk butiran lengkap.';

    return implode(' ', $parts);
}

function jadualAssertAdminLeaveAction(): void
{
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Sila log masuk semula.');
    }
    $r = $_SESSION['role'] ?? '';
    if (!in_array($r, ['admin', 'superadmin'], true)) {
        throw new Exception('Akses ditolak.');
    }
}

$firstDayOfMonth = sprintf('%04d-%02d-01', $year, $month);
$lastDayOfMonth = date('Y-m-t', strtotime($firstDayOfMonth));

$swalMessage = null;
$swalType = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_leave') {
    $redirectMonth = isset($_POST['cal_month']) ? (int) $_POST['cal_month'] : $month;
    $redirectYear = isset($_POST['cal_year']) ? (int) $_POST['cal_year'] : $year;
    try {
        jadualAssertAdminLeaveAction();
        $pemanduId = isset($_POST['pemandu_id']) ? (int) $_POST['pemandu_id'] : 0;
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');
        $typeLeaveId = isset($_POST['type_leave_id']) ? (int) $_POST['type_leave_id'] : 0;
        $reason = trim($_POST['reason'] ?? '');

        if ($pemanduId <= 0) {
            throw new Exception('Sila pilih pemandu.');
        }
        if ($startDate === '' || $endDate === '') {
            throw new Exception('Sila lengkapkan tarikh mula dan tarikh akhir.');
        }
        if ($startDate > $endDate) {
            throw new Exception('Tarikh mula tidak boleh lewat dari tarikh akhir.');
        }
        if ($typeLeaveId <= 0) {
            throw new Exception('Sila pilih jenis cuti.');
        }

        $chk = $pdo->prepare('SELECT id FROM leaves_types WHERE id = ?');
        $chk->execute([$typeLeaveId]);
        if (!$chk->fetch()) {
            throw new Exception('Jenis cuti tidak sah.');
        }

        $ins = $pdo->prepare(
            'INSERT INTO pemandu_leave (pemandu_id, start_date, end_date, type_leave_id, reason) VALUES (?, ?, ?, ?, ?)'
        );
        $reasonVal = $reason === '' ? null : $reason;
        $ins->execute([$pemanduId, $startDate, $endDate, $typeLeaveId, $reasonVal]);

        header('Location: Jadual_pemandu.php?month=' . $redirectMonth . '&year=' . $redirectYear . '&saved=1');
        exit;
    } catch (Exception $e) {
        $swalMessage = $e->getMessage();
        $swalType = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'shorten_leave') {
    $redirectMonth = isset($_POST['cal_month']) ? (int) $_POST['cal_month'] : $month;
    $redirectYear = isset($_POST['cal_year']) ? (int) $_POST['cal_year'] : $year;
    try {
        jadualAssertAdminLeaveAction();
        $leaveId = isset($_POST['leave_id']) ? (int) $_POST['leave_id'] : 0;
        $newEnd = trim($_POST['new_end_date'] ?? '');
        if ($leaveId <= 0 || $newEnd === '') {
            throw new Exception('Data tidak lengkap.');
        }
        $stmt = $pdo->prepare('SELECT start_date, end_date FROM pemandu_leave WHERE id = ?');
        $stmt->execute([$leaveId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new Exception('Rekod cuti tidak dijumpai.');
        }
        $sd = (string) $row['start_date'];
        $ed = (string) $row['end_date'];
        if ($newEnd < $sd) {
            throw new Exception('Tarikh akhir baru tidak boleh awal daripada tarikh mula.');
        }
        if ($newEnd >= $ed) {
            throw new Exception('Pilih tarikh akhir yang lebih awal daripada tarikh akhir semasa untuk memendekkan cuti.');
        }
        $upd = $pdo->prepare('UPDATE pemandu_leave SET end_date = ? WHERE id = ?');
        $upd->execute([$newEnd, $leaveId]);
        header('Location: Jadual_pemandu.php?month=' . $redirectMonth . '&year=' . $redirectYear . '&adjusted=1');
        exit;
    } catch (Exception $e) {
        $swalMessage = $e->getMessage();
        $swalType = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_leave') {
    $redirectMonth = isset($_POST['cal_month']) ? (int) $_POST['cal_month'] : $month;
    $redirectYear = isset($_POST['cal_year']) ? (int) $_POST['cal_year'] : $year;
    try {
        jadualAssertAdminLeaveAction();
        $leaveId = isset($_POST['leave_id']) ? (int) $_POST['leave_id'] : 0;
        if ($leaveId <= 0) {
            throw new Exception('Data tidak lengkap.');
        }
        $del = $pdo->prepare('DELETE FROM pemandu_leave WHERE id = ?');
        $del->execute([$leaveId]);
        if ($del->rowCount() === 0) {
            throw new Exception('Rekod cuti tidak dijumpai.');
        }
        header('Location: Jadual_pemandu.php?month=' . $redirectMonth . '&year=' . $redirectYear . '&deleted=1');
        exit;
    } catch (Exception $e) {
        $swalMessage = $e->getMessage();
        $swalType = 'error';
    }
}

// Ensure at least one leave type exists (FK on pemandu_leave.type_leave_id)
try {
    $cnt = (int) $pdo->query('SELECT COUNT(*) FROM leaves_types')->fetchColumn();
    if ($cnt === 0) {
        $pdo->exec("INSERT INTO leaves_types (code, name) VALUES ('UMUM', 'Cuti Umum')");
    }
} catch (Exception $e) {
    error_log('Jadual_pemandu leaves_types seed: ' . $e->getMessage());
}

try {
    $leaveTypes = $pdo->query('SELECT id, code, name FROM leaves_types ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $leaveTypes = [];
    error_log('Jadual_pemandu: ' . $e->getMessage());
}

try {
    $driversStmt = $pdo->query(
        "SELECT id, namapemandu FROM tpemandu WHERE UPPER(TRIM(status)) = 'AKTIF' ORDER BY namapemandu ASC"
    );
    $drivers = $driversStmt ? $driversStmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Exception $e) {
    $drivers = [];
    error_log('Jadual_pemandu drivers: ' . $e->getMessage());
}

$leaveByDate = [];
try {
    $sql = 'SELECT pl.id, pl.pemandu_id, pl.start_date, pl.end_date, pl.reason, pl.type_leave_id,
                   tp.namapemandu, COALESCE(lt.name, \'\') AS leave_type_name,
                   COALESCE(lt.code, \'\') AS leave_type_code
            FROM pemandu_leave pl
            INNER JOIN tpemandu tp ON tp.id = pl.pemandu_id
            LEFT JOIN leaves_types lt ON lt.id = pl.type_leave_id
            WHERE pl.start_date <= ? AND pl.end_date >= ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$lastDayOfMonth, $firstDayOfMonth]);
    $leaveRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($leaveRows as $row) {
        $segStart = max($row['start_date'], $firstDayOfMonth);
        $segEnd = min($row['end_date'], $lastDayOfMonth);
        if ($segStart > $segEnd) {
            continue;
        }
        $d = new DateTime($segStart);
        $end = new DateTime($segEnd);
        while ($d <= $end) {
            if ((int) $d->format('n') === $month && (int) $d->format('Y') === $year) {
                $dayNum = (int) $d->format('j');
                if (!isset($leaveByDate[$dayNum])) {
                    $leaveByDate[$dayNum] = [];
                }
                $leaveByDate[$dayNum][] = $row;
            }
            $d->modify('+1 day');
        }
    }
} catch (Exception $e) {
    error_log('Jadual_pemandu leave fetch: ' . $e->getMessage());
    $leaveRows = [];
}

$leaveCodePalette = jadualBuildLeaveCodePalette($leaveRows);

if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
    $swalMessage = 'Rekod cuti telah dibatalkan.';
    $swalType = 'success';
} elseif (isset($_GET['adjusted']) && $_GET['adjusted'] === '1') {
    $swalMessage = 'Tempoh cuti telah dikemas kini (dipendekkan).';
    $swalType = 'success';
} elseif (isset($_GET['saved']) && $_GET['saved'] === '1') {
    $swalMessage = 'Rekod cuti pemandu berjaya disimpan.';
    $swalType = 'success';
}

?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadual Pemandu (Cuti) | JKN Kedah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="CSS/STK2.css" />
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="CSS/kalendar_tempahan.css">
    <link rel="stylesheet" href="CSS/jadual_pemandu.css">
    <link rel="stylesheet" href="CSS/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
</head>

<body>
    <?php include 'dashboard2.php'; ?>
    <div class="dashboard jadual-pemandu-page">
        <div class="main-content">
            <!-- <div class="jadual-hero">
                <div class="jadual-hero-text">
                    <h1>Jadual Pemandu</h1>
                    <p class="jadual-hero-sub">Lihat pemandu yang tidak tersedia pada bulan ini. Warna petak mengikut <strong>kod jenis cuti</strong>; klik untuk butiran lengkap.</p>
                </div>
                <div class="jadual-hero-actions">
                    <button type="button" class="btn btn-primary" onclick="openModal('addLeaveModal')">
                        <i class="fas fa-user-clock"></i> Tambah cuti pemandu
                    </button>
                    <a href="#" onclick="handleLogout(); return false;" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div> -->
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Kalendar Tempahan</h1>
                <div class="left-actions">
                    <a href="#" onclick="openModal('addLeaveModal')" class="btn btn-primary">
                        <i class="fas fa-user-clock"></i> Tambah Cuti Pemandu
                    </a>
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>

            <div class="calendar-container jadual-card">
                <div class="calendar-legend">
                    <?php foreach ($leaveCodePalette as $codeKey => $palIdx): ?>
                        <span class="legend-chip">
                            <span class="legend-swatch legend-swatch--pal-<?= (int) $palIdx ?>" aria-hidden="true"></span>
                            <?= $codeKey === '—' ? 'Tiada kod jenis' : htmlspecialchars($codeKey) ?>
                        </span>
                    <?php endforeach; ?>
                    <?php if (empty($leaveCodePalette)): ?>
                        <span class="legend-chip" style="color:#6b7280;">Tiada rekod cuti pada bulan ini</span>
                    <?php endif; ?>
                    <span class="legend-chip">
                        <span class="legend-swatch today-swatch" aria-hidden="true"></span>
                        Hari ini
                    </span>
                    <span class="legend-chip" style="color:#6b7280;font-weight:400;">
                        <i class="fas fa-mouse-pointer" style="opacity:.7;"></i> Tuding untuk ringkasan cepat
                    </span>
                </div>

                <div class="calendar-navigation-gcal">
                    <div class="nav-group">
                        <a href="?month=<?= (int) $prevMonth ?>&year=<?= (int) $prevYear ?>" class="btn-nav-icon" title="Bulan sebelum" aria-label="Bulan sebelum">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="?month=<?= (int) $nextMonth ?>&year=<?= (int) $nextYear ?>" class="btn-nav-icon" title="Bulan seterusnya" aria-label="Bulan seterusnya">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <span class="calendar-month-pill"><?= htmlspecialchars(getMalayMonthName($month) . ' ' . $year) ?></span>
                    </div>
                    <a href="?month=<?= (int) date('n') ?>&year=<?= (int) date('Y') ?>" class="btn-today">
                        <i class="fas fa-calendar-day" aria-hidden="true"></i> Hari ini
                    </a>
                </div>

                <table class="calendar">
                    <thead>
                        <tr>
                            <th class="week-number-header" scope="col">Minggu</th>
                            <th scope="col" style="width: 14.28%;">Ahad</th>
                            <th scope="col" style="width: 14.28%;">Isnin</th>
                            <th scope="col" style="width: 14.28%;">Selasa</th>
                            <th scope="col" style="width: 14.28%;">Rabu</th>
                            <th scope="col" style="width: 14.28%;">Khamis</th>
                            <th scope="col" style="width: 14.28%;">Jumaat</th>
                            <th scope="col" style="width: 14.28%;">Sabtu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $firstDay = mktime(0, 0, 0, $month, 1, $year);
                        $daysInMonth = (int) date('t', $firstDay);
                        $startDay = (int) date('w', $firstDay);
                        $currentDay = 1;
                        $today = (int) date('j');
                        $currentMonth = (int) date('n');
                        $currentYear = (int) date('Y');

                        for ($i = 0; $i < 6; $i++) {
                            $weekLabel = '';
                            if ($currentDay <= $daysInMonth) {
                                $weekLabel = (string) date('W', mktime(0, 0, 0, $month, $currentDay, $year));
                            }
                            echo '<tr>';
                            echo '<td class="week-number-cell">' . htmlspecialchars($weekLabel) . '</td>';
                            for ($j = 0; $j < 7; $j++) {
                                if (($i === 0 && $j < $startDay) || ($currentDay > $daysInMonth)) {
                                    $emptyCls = 'empty' . ($j === 0 || $j === 6 ? ' weekend-empty' : '');
                                    echo "<td class='" . htmlspecialchars($emptyCls) . "'></td>";
                                } elseif ($currentDay <= $daysInMonth) {
                                    $isToday = ($currentDay === $today && $month === $currentMonth && $year === $currentYear);
                                    $isWeekend = ($j === 0 || $j === 6);
                                    $classes = [];
                                    if ($isToday) {
                                        $classes[] = 'today';
                                    }
                                    if ($isWeekend) {
                                        $classes[] = 'weekend';
                                    }
                                    $dateClass = implode(' ', $classes);
                                    echo "<td class='" . htmlspecialchars($dateClass) . "'>";
                                    echo '<span class="date-number' . ($isToday ? ' date-number-today' : '') . '">' . (int) $currentDay . '</span>';

                                    if (!empty($leaveByDate[$currentDay])) {
                                        foreach ($leaveByDate[$currentDay] as $lev) {
                                            $nama = jadualPemanduNameUpper($lev['namapemandu'] ?? '');
                                            $typeCodeRaw = trim((string) ($lev['leave_type_code'] ?? ''));
                                            $typeCodeDisplay = strtoupper($typeCodeRaw);
                                            $paletteKey = jadualLeaveTypeCodeKey($typeCodeRaw);
                                            $palIdx = $leaveCodePalette[$paletteKey] ?? 0;
                                            $line = $typeCodeDisplay !== ''
                                                ? $nama . ' - ' . $typeCodeDisplay
                                                : $nama;
                                            $short = mb_strlen($line) > 44
                                                ? mb_substr($line, 0, 42, 'UTF-8') . '…'
                                                : $line;
                                            $reason = (string) ($lev['reason'] ?? '');
                                            $typeName = (string) ($lev['leave_type_name'] ?? '');
                                            $hoverDesc = jadualLeaveHoverDesc(
                                                $nama,
                                                $typeName,
                                                $typeCodeDisplay,
                                                (string) ($lev['start_date'] ?? ''),
                                                (string) ($lev['end_date'] ?? ''),
                                                $reason
                                            );
                                            $cellYmd = sprintf('%04d-%02d-%02d', $year, $month, $currentDay);
                                            $levEnd = (string) ($lev['end_date'] ?? '');
                                            $isEndDay = ($cellYmd === $levEnd);
                                            echo '<div class="leave-event leave-pal-' . (int) $palIdx . '" role="button" tabindex="0"';
                                            echo ' data-leave-id="' . (int) ($lev['id'] ?? 0) . '"';
                                            echo ' data-cell-date="' . htmlspecialchars($cellYmd, ENT_QUOTES, 'UTF-8') . '"';
                                            echo ' data-is-end-day="' . ($isEndDay ? '1' : '0') . '"';
                                            echo ' data-nama="' . htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') . '"';
                                            echo ' data-type-code="' . htmlspecialchars($typeCodeDisplay, ENT_QUOTES, 'UTF-8') . '"';
                                            echo ' data-start="' . htmlspecialchars($lev['start_date'], ENT_QUOTES, 'UTF-8') . '"';
                                            echo ' data-end="' . htmlspecialchars($lev['end_date'], ENT_QUOTES, 'UTF-8') . '"';
                                            echo ' data-type="' . htmlspecialchars($typeName, ENT_QUOTES, 'UTF-8') . '"';
                                            echo ' data-reason="' . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . '"';
                                            echo ' data-leave-desc="' . htmlspecialchars($hoverDesc, ENT_QUOTES, 'UTF-8') . '"';
                                            echo ' onclick="showLeaveDetailModal(this)"';
                                            echo '>';
                                            echo '<span class="leave-name">';
                                            echo htmlspecialchars($short);
                                            echo '</span>';
                                            echo '</div>';
                                        }
                                    }

                                    echo '</td>';
                                    $currentDay++;
                                }
                            }
                            echo '</tr>';
                            if ($currentDay > $daysInMonth) {
                                break;
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="leaveDetailModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeLeaveDetailModal()">&times;</span>
                    <h2 class="form-title">Butiran Cuti Pemandu</h2>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Pemandu</label>
                        <input type="text" id="leaveDetailNama" class="form-control" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Jenis cuti</label>
                        <input type="text" id="leaveDetailType" class="form-control" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tarikh mula</label>
                        <input type="text" id="leaveDetailStart" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Tarikh akhir</label>
                        <input type="text" id="leaveDetailEnd" class="form-control" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label>Catatan</label>
                    <textarea id="leaveDetailReason" class="form-control" rows="3" readonly style="resize:vertical;"></textarea>
                </div>

                <p id="leaveDetailNonEndHint" class="jadual-leave-hint" style="display:none;"></p>

                <div id="leaveDetailAdjustPanel" class="jadual-leave-adjust" style="display:none;">
                    <h3 class="jadual-leave-adjust__title">Urus cuti (hari terakhir sahaja)</h3>
                    <div id="leaveDetailShortenWrap" class="jadual-leave-adjust__block">
                        <p class="jadual-leave-adjust__label">Pendekkan tempoh cuti</p>
                        <form id="formShortenLeave" method="post" action="Jadual_pemandu.php" class="jadual-leave-form">
                            <input type="hidden" name="action" value="shorten_leave">
                            <input type="hidden" name="leave_id" id="shorten_leave_id" value="">
                            <input type="hidden" name="cal_month" value="<?= (int) $month ?>">
                            <input type="hidden" name="cal_year" value="<?= (int) $year ?>">
                            <div class="form-group">
                                <label class="form-label" for="shortenNewEnd">Tarikh akhir baru</label>
                                <input type="date" name="new_end_date" id="shortenNewEnd" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success jadual-leave-adjust__btn">
                                <i class="fas fa-compress-alt"></i> Simpan pendekkan
                            </button>
                        </form>
                    </div>
                    <p id="leaveDetailSingleDayNote" class="jadual-leave-hint jadual-leave-hint--muted" style="display:none;">
                        Cuti satu hari: hanya <strong>batal cuti penuh</strong> tersedia di bawah.
                    </p>
                    <div class="jadual-leave-adjust__block">
                        <p class="jadual-leave-adjust__label">Batalkan keseluruhan cuti</p>
                        <form id="formDeleteLeave" method="post" action="Jadual_pemandu.php" class="jadual-leave-form">
                            <input type="hidden" name="action" value="delete_leave">
                            <input type="hidden" name="leave_id" id="delete_leave_id" value="">
                            <input type="hidden" name="cal_month" value="<?= (int) $month ?>">
                            <input type="hidden" name="cal_year" value="<?= (int) $year ?>">
                            <button type="button" class="btn btn-cancel jadual-leave-adjust__btn" id="btnDeleteLeaveFull">
                                <i class="fas fa-trash-alt"></i> Batal cuti penuh
                            </button>
                        </form>
                    </div>
                </div>

                <div class="button-group">
                    <button type="button" class="btn btn-back" onclick="closeLeaveDetailModal()">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="addLeaveModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeModal('addLeaveModal')">&times;</span>
                    <h2 class="form-title">Tambah cuti pemandu</h2>
                </div>
                <form method="post" action="Jadual_pemandu.php">
                    <input type="hidden" name="action" value="add_leave">
                    <input type="hidden" name="cal_month" value="<?= (int) $month ?>">
                    <input type="hidden" name="cal_year" value="<?= (int) $year ?>">

                    <div class="form-group">
                        <label class="form-label" for="add_pemandu_id">Pemandu <span style="color:red">*</span></label>
                        <select name="pemandu_id" id="add_pemandu_id" class="form-control" required>
                            <option value="">— Pilih pemandu —</option>
                            <?php foreach ($drivers as $d): ?>
                                <option value="<?= (int) $d['id'] ?>">
                                    <?= htmlspecialchars(jadualPemanduNameUpper($d['namapemandu'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="add_start_date">Tarikh mula <span style="color:red">*</span></label>
                            <input type="date" name="start_date" id="add_start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="add_end_date">Tarikh akhir <span style="color:red">*</span></label>
                            <input type="date" name="end_date" id="add_end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="add_type_leave_id">Jenis cuti <span style="color:red">*</span></label>
                        <select name="type_leave_id" id="add_type_leave_id" class="form-control" required>
                            <option value="">— Pilih jenis —</option>
                            <?php foreach ($leaveTypes as $lt): ?>
                                <option value="<?= (int) $lt['id'] ?>"><?= htmlspecialchars($lt['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="add_reason">Catatan</label>
                        <textarea name="reason" id="add_reason" class="form-control" rows="3" placeholder="Pilihan"></textarea>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                        <button type="button" class="btn btn-cancel" onclick="closeModal('addLeaveModal')"><i class="fas fa-times"></i> Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let hoverTooltip = null;

        function positionLeaveTooltip(e) {
            if (!hoverTooltip) {
                return;
            }
            if (!e || typeof e.clientX !== 'number') {
                return;
            }
            var pad = 12;
            var tw = hoverTooltip.offsetWidth || 280;
            var th = hoverTooltip.offsetHeight || 80;
            var x = e.clientX + pad;
            var y = e.clientY + pad;
            if (x + tw > window.innerWidth - 8) {
                x = Math.max(8, e.clientX - tw - pad);
            }
            if (y + th > window.innerHeight - 8) {
                y = Math.max(8, e.clientY - th - pad);
            }
            hoverTooltip.style.left = Math.max(8, Math.min(x, window.innerWidth - tw - 8)) + 'px';
            hoverTooltip.style.top = Math.max(8, Math.min(y, window.innerHeight - th - 8)) + 'px';
        }

        function showQuickLeaveTooltip(bar, e) {
            if (!hoverTooltip) {
                hoverTooltip = document.createElement('div');
                hoverTooltip.className = 'leave-quick-tooltip';
                document.body.appendChild(hoverTooltip);
            }
            var nama = bar.getAttribute('data-nama') || '';
            var typ = bar.getAttribute('data-type') || '';
            var tcode = bar.getAttribute('data-type-code') || '';
            var desc = bar.getAttribute('data-leave-desc') || '';
            var headLine = nama + (tcode ? ' - ' + tcode : '');
            var html = '<div class="leave-tooltip-title">' + escapeHtml(headLine) + '</div>';
            if (desc) {
                html += '<div class="leave-tooltip-desc">' + escapeHtml(desc).replace(/\n/g, '<br>') + '</div>';
            } else {
                var start = bar.getAttribute('data-start') || '';
                var end = bar.getAttribute('data-end') || '';
                if (typ) {
                    html += '<div class="leave-tooltip-meta">' + escapeHtml(typ) + '</div>';
                }
                html += '<div class="leave-tooltip-meta">' + escapeHtml(start) + ' &rarr; ' + escapeHtml(end) + '</div>';
            }
            hoverTooltip.innerHTML = html;
            hoverTooltip.style.display = 'block';
            requestAnimationFrame(function () {
                positionLeaveTooltip(e);
            });
        }

        function moveQuickLeaveTooltip(bar, e) {
            if (!hoverTooltip || hoverTooltip.style.display !== 'block') {
                return;
            }
            positionLeaveTooltip(e);
        }

        function hideQuickLeaveTooltip() {
            if (hoverTooltip) {
                hoverTooltip.style.display = 'none';
            }
        }

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }

        function ymdDayBefore(ymd) {
            if (!ymd || ymd.length < 10) {
                return '';
            }
            var p = ymd.split('-').map(function (x) {
                return parseInt(x, 10);
            });
            var d = new Date(p[0], p[1] - 1, p[2]);
            d.setDate(d.getDate() - 1);
            var m = String(d.getMonth() + 1).padStart(2, '0');
            var day = String(d.getDate()).padStart(2, '0');
            return d.getFullYear() + '-' + m + '-' + day;
        }

        function showLeaveDetailModal(el) {
            hideQuickLeaveTooltip();
            var nama = el.getAttribute('data-nama') || '';
            var t = el.getAttribute('data-type') || '';
            var c = el.getAttribute('data-type-code') || '';
            var start = el.getAttribute('data-start') || '';
            var end = el.getAttribute('data-end') || '';
            var isEnd = el.getAttribute('data-is-end-day') === '1';
            var leaveId = el.getAttribute('data-leave-id') || '';

            document.getElementById('leaveDetailNama').value = nama;
            document.getElementById('leaveDetailType').value = c ? (t ? t + ' (' + c + ')' : c) : t;
            document.getElementById('leaveDetailStart').value = start;
            document.getElementById('leaveDetailEnd').value = end;
            document.getElementById('leaveDetailReason').value = el.getAttribute('data-reason') || '';

            document.getElementById('shorten_leave_id').value = leaveId;
            document.getElementById('delete_leave_id').value = leaveId;

            var hint = document.getElementById('leaveDetailNonEndHint');
            var panel = document.getElementById('leaveDetailAdjustPanel');
            var shortenWrap = document.getElementById('leaveDetailShortenWrap');
            var singleNote = document.getElementById('leaveDetailSingleDayNote');
            var shortenInput = document.getElementById('shortenNewEnd');

            if (isEnd) {
                hint.style.display = 'none';
                panel.style.display = 'block';
                if (start === end) {
                    shortenWrap.style.display = 'none';
                    singleNote.style.display = 'block';
                    shortenInput.removeAttribute('required');
                    shortenInput.value = '';
                } else {
                    shortenWrap.style.display = 'block';
                    singleNote.style.display = 'none';
                    shortenInput.setAttribute('required', 'required');
                    shortenInput.min = start;
                    var maxShort = ymdDayBefore(end);
                    shortenInput.max = maxShort;
                    shortenInput.value = maxShort;
                }
            } else {
                panel.style.display = 'none';
                hint.style.display = 'block';
                hint.textContent =
                    'Untuk memendekkan atau membatalkan cuti, buka petak pada hari terakhir cuti (tarikh akhir ' +
                    end +
                    ') pada kalendar.';
                shortenInput.removeAttribute('required');
            }

            document.getElementById('leaveDetailModal').style.display = 'block';
        }

        function closeLeaveDetailModal() {
            document.getElementById('leaveDetailModal').style.display = 'none';
        }

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.jadual-pemandu-page .leave-name').forEach(function (el) {
                el.addEventListener('mouseenter', function (ev) {
                    var bar = el.parentElement;
                    if (bar && bar.classList.contains('leave-event')) {
                        showQuickLeaveTooltip(bar, ev);
                    }
                });
                el.addEventListener('mousemove', function (ev) {
                    var bar = el.parentElement;
                    if (bar && bar.classList.contains('leave-event')) {
                        moveQuickLeaveTooltip(bar, ev);
                    }
                });
                el.addEventListener('mouseleave', hideQuickLeaveTooltip);
            });

            var btnDel = document.getElementById('btnDeleteLeaveFull');
            if (btnDel) {
                btnDel.addEventListener('click', function () {
                    Swal.fire({
                        title: 'Batalkan cuti?',
                        text: 'Rekod cuti akan dipadam sepenuhnya.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, batalkan',
                        cancelButtonText: 'Tidak'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            document.getElementById('formDeleteLeave').submit();
                        }
                    });
                });
            }

            <?php if (!empty($swalMessage)): ?>
            Swal.fire({
                title: '<?= $swalType === 'success' ? 'Berjaya!' : 'Ralat' ?>',
                text: <?= json_encode($swalMessage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                icon: '<?= htmlspecialchars($swalType, ENT_QUOTES, 'UTF-8') ?>'
            }).then(function() {
                <?php if ($swalType === 'success'): ?>
                var q = window.location.search;
                if (q.indexOf('saved=1') !== -1 || q.indexOf('adjusted=1') !== -1 || q.indexOf('deleted=1') !== -1) {
                    window.history.replaceState({}, document.title, 'Jadual_pemandu.php?month=<?= (int) $month ?>&year=<?= (int) $year ?>');
                }
                <?php endif; ?>
            });
            <?php endif; ?>
        });
    </script>
</body>

</html>
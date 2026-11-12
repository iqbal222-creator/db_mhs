<?php
session_start();

// Proteksi halaman: Jika belum login, kembalikan ke login.php
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

require 'functions.php';

$notifikasiSukses = $_SESSION['notifikasi_sukses'] ?? null;
unset($_SESSION['notifikasi_sukses']);

$keyword = '';

if (isset($_POST['cari'])) {
    $keyword = trim($_POST['keyword'] ?? '');
    $mahasiswa = cari($keyword);
} else {
    $mahasiswa = query("SELECT * FROM mahasiswa ORDER BY nama ASC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [hidden] { display: none !important; }

        .loading-screen {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow: hidden;
            background: rgba(248, 250, 252, 0.58);
            backdrop-filter: blur(12px) saturate(115%);
            -webkit-backdrop-filter: blur(12px) saturate(115%);
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }

        .loading-screen.is-leaving {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loading-screen.is-hidden { display: none; }

        .loading-card {
            position: relative;
            z-index: 1;
            width: min(100%, 360px);
            padding: 34px 30px 30px;
            overflow: hidden;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.76);
            box-shadow: 0 28px 80px rgba(30, 64, 175, 0.18), inset 0 1px 0 #fff;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            animation: loading-card-in 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .loading-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 15%;
            width: 70%;
            height: 1px;
            background: linear-gradient(90deg, transparent, #fff, transparent);
        }

        .loading-visual {
            position: relative;
            display: grid;
            width: 142px;
            height: 142px;
            margin: 0 auto 22px;
            place-items: center;
        }

        .loading-ring {
            position: absolute;
            border-radius: 50%;
        }

        .loading-ring-one {
            inset: 0;
            border: 2px solid rgba(147, 197, 253, 0.22);
            border-top-color: #3b82f6;
            border-right-color: #6366f1;
            animation: loading-spin 2.2s linear infinite;
        }

        .loading-ring-two {
            inset: 15px;
            border: 2px dashed rgba(79, 70, 229, 0.32);
            border-bottom-color: #2563eb;
            animation: loading-spin-reverse 5s linear infinite;
        }

        .loading-orbit {
            position: absolute;
            inset: -5px;
            animation: loading-spin 4s linear infinite;
        }

        .loading-orbit::before {
            content: "";
            position: absolute;
            top: 13px;
            right: 13px;
            width: 11px;
            height: 11px;
            border: 3px solid rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            background: #4f46e5;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }

        .loading-logo-glow {
            position: absolute;
            width: 90px;
            height: 90px;
            border-radius: 26px;
            background: rgba(59, 130, 246, 0.22);
            filter: blur(16px);
            animation: loading-logo-glow 2s ease-in-out infinite;
        }

        .loading-logo {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 76px;
            height: 76px;
            padding: 7px;
            border: 1px solid #fff;
            border-radius: 23px;
            background: linear-gradient(145deg, #fff, #eff6ff);
            box-shadow: 0 14px 32px rgba(37, 99, 235, 0.2);
            animation: loading-logo-float 2.8s ease-in-out infinite;
        }

        .loading-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 16px;
        }

        .loading-eyebrow {
            display: block;
            margin-bottom: 8px;
            color: #2563eb;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.14em;
        }

        .loading-title {
            color: #0f172a;
            font-size: 19px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .loading-subtitle {
            margin-top: 7px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
        }

        .loading-progress {
            position: relative;
            width: 100%;
            height: 6px;
            margin-top: 24px;
            overflow: hidden;
            border-radius: 999px;
            background: #dbeafe;
        }

        .loading-progress span {
            position: absolute;
            inset: 0 auto 0 0;
            width: 42%;
            border-radius: inherit;
            background: linear-gradient(90deg, #60a5fa, #4f46e5, #38bdf8);
            box-shadow: 0 0 16px rgba(59, 130, 246, 0.45);
            animation: loading-progress 1.35s ease-in-out infinite;
        }

        .loading-progress span::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.75), transparent);
            animation: loading-shine 1s linear infinite;
        }

        .loading-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin-top: 14px;
            color: #64748b;
            font-size: 11px;
            font-weight: 500;
        }

        .loading-status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
            animation: loading-status 1.5s ease-in-out infinite;
        }

        @keyframes loading-card-in {
            from { opacity: 0; transform: translateY(24px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes loading-spin {
            to { transform: rotate(360deg); }
        }

        @keyframes loading-spin-reverse {
            to { transform: rotate(-360deg); }
        }

        @keyframes loading-logo-glow {
            0%, 100% { opacity: 0.45; transform: scale(0.9); }
            50% { opacity: 0.9; transform: scale(1.15); }
        }

        @keyframes loading-logo-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes loading-progress {
            0% { left: -42%; }
            55% { left: 58%; width: 48%; }
            100% { left: 100%; width: 28%; }
        }

        @keyframes loading-shine {
            from { transform: translateX(-100%); }
            to { transform: translateX(100%); }
        }

        @keyframes loading-status {
            0%, 100% { opacity: 0.5; transform: scale(0.85); }
            50% { opacity: 1; transform: scale(1.1); }
        }

        @media (prefers-reduced-motion: reduce) {
            .loading-screen,
            .loading-card,
            .loading-ring,
            .loading-orbit,
            .loading-logo-glow,
            .loading-logo,
            .loading-progress span,
            .loading-progress span::after,
            .loading-status-dot {
                animation: none !important;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-[#F8FAFC] text-slate-800 antialiased">

<div id="loading-screen" class="loading-screen" role="status" aria-live="polite" aria-label="Memuat data mahasiswa">
    <div class="loading-card">
        <div class="loading-visual" aria-hidden="true">
            <span class="loading-ring loading-ring-one"></span>
            <span class="loading-ring loading-ring-two"></span>
            <span class="loading-orbit"></span>
            <span class="loading-logo-glow"></span>
            <div class="loading-logo">
                <img src="img/logo-politeknik-bengkalis.jpg" alt="">
            </div>
        </div>
        <span class="loading-eyebrow">SISTEM INFORMASI AKADEMIK</span>
        <p class="loading-title">Menyiapkan Data Mahasiswa</p>
        <p class="loading-subtitle">Mohon tunggu, data akademik sedang disiapkan untuk Anda.</p>
        <div class="loading-progress" aria-hidden="true"><span></span></div>
        <div class="loading-status">
            <span class="loading-status-dot" aria-hidden="true"></span>
            Sinkronisasi data akademik
        </div>
    </div>
</div>

<?php if ($notifikasiSukses !== null) : ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" id="notifikasi-sukses" role="status" aria-live="polite">
        <div class="w-full max-w-sm rounded-2xl bg-white p-8 text-center shadow-xl">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-semibold text-slate-900">Berhasil!</h2>
            <p class="mt-2 text-sm text-slate-500"><?= aman($notifikasiSukses); ?></p>
        </div>
    </div>
    <script>
        setTimeout(function () {
            var notifikasi = document.getElementById('notifikasi-sukses');
            if (notifikasi) { notifikasi.remove(); }
        }, 3500);
    </script>
<?php endif; ?>

<main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
    <header class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold tracking-wide text-blue-600">SISTEM INFORMASI AKADEMIK</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                Daftar Mahasiswa
            </h1>
            <p class="mt-2 text-sm text-slate-500">Jurusan Teknik Informatika</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="tambah.php" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" />
                </svg>
                Tambah Mahasiswa
            </a>
            <!-- Tombol Logout -->
            <a href="logout.php" class="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700">
                Logout
            </a>
        </div>
    </header>

    <section class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm">
        <div class="flex flex-col gap-5 border-b border-slate-100 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Data Mahasiswa</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola informasi mahasiswa yang terdaftar.</p>
            </div>

            <form method="post" class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-xl">
                <label for="keyword" class="sr-only">Cari mahasiswa</label>
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" stroke-width="1.8" />
                        <path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4" />
                    </svg>
                    <input type="text" id="keyword" name="keyword" placeholder="Cari nama, NIM, email, prodi, atau jurusan" value="<?= aman($keyword); ?>" autocomplete="off" class="w-full rounded-lg border border-slate-200 py-2.5 pl-10 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <button type="submit" name="cari" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                    Cari
                </button>
                <?php if ($keyword !== '') : ?>
                    <a href="index.php" class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200">
                        Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 font-semibold">No</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Nama</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">NIM</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Email</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Program Studi</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Jurusan</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Foto</th>
                        <th scope="col" class="px-5 py-3.5 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody id="skeleton-rows" class="divide-y divide-slate-100">
                    <?php for ($barisSkeleton = 0; $barisSkeleton < 4; $barisSkeleton++) : ?>
                        <tr class="animate-pulse">
                            <td class="px-5 py-4"><span class="block h-4 w-5 rounded bg-slate-200"></span></td>
                            <td class="px-5 py-4"><span class="block h-4 w-32 rounded bg-slate-200"></span></td>
                            <td class="px-5 py-4"><span class="block h-4 w-24 rounded bg-slate-200"></span></td>
                            <td class="px-5 py-4"><span class="block h-4 w-36 rounded bg-slate-200"></span></td>
                            <td class="px-5 py-4"><span class="block h-4 w-28 rounded bg-slate-200"></span></td>
                            <td class="px-5 py-4"><span class="block h-4 w-28 rounded bg-slate-200"></span></td>
                            <td class="px-5 py-4"><span class="block h-11 w-11 rounded-full bg-slate-200"></span></td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <span class="h-8 w-14 rounded-lg bg-slate-200"></span>
                                    <span class="h-8 w-14 rounded-lg bg-slate-200"></span>
                                </div>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
                <tbody id="data-rows" class="divide-y divide-slate-100" hidden>
                    <?php if (count($mahasiswa) > 0) : ?>
                        <?php $i = 1; ?>
                        <?php foreach ($mahasiswa as $row) : ?>
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="whitespace-nowrap px-5 py-4 text-slate-500"><?= $i; ?></td>
                                <td class="px-5 py-4 font-medium text-slate-900"><?= aman($row['nama']); ?></td>
                                <td class="whitespace-nowrap px-5 py-4 text-slate-600"><?= aman($row['nim']); ?></td>
                                <td class="px-5 py-4 text-slate-600"><?= aman($row['email']); ?></td>
                                <td class="px-5 py-4 text-slate-600"><?= aman($row['prodi']); ?></td>
                                <td class="px-5 py-4 text-slate-600"><?= aman($row['jurusan']); ?></td>
                                <td class="px-5 py-4">
                                    <?php if (!empty($row['gambar']) && file_exists(__DIR__ . '/img/' . $row['gambar'])) : ?>
                                        <img src="img/<?= rawurlencode($row['gambar']); ?>" class="h-11 w-11 rounded-xl border border-slate-200 object-cover" alt="Foto">
                                    <?php else : ?>
                                        <span class="text-xs text-slate-400">Belum ada foto</span>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="ubah.php?nim=<?= urlencode($row['nim']); ?>" class="inline-flex items-center rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                            Ubah
                                        </a>
                                        <form action="hapus.php" method="post" class="form-hapus" data-nama="<?= aman($row['nama']); ?>">
                                            <input type="hidden" name="nim" value="<?= aman($row['nim']); ?>">
                                            <button type="submit" class="inline-flex items-center rounded-lg bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php $i++; ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <p class="font-medium text-slate-900">
                                    <?= $keyword !== '' ? 'Data mahasiswa tidak ditemukan.' : 'Belum ada data mahasiswa.'; ?>
                                </p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<div id="dialog-hapus" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" role="alertdialog" aria-modal="true" tabindex="-1" hidden>
    <div class="w-full max-w-sm rounded-2xl border border-slate-100 bg-white p-7 shadow-xl">
        <h2 class="mt-4 text-center text-lg font-semibold text-slate-900">Hapus data mahasiswa?</h2>
        <p id="dialog-hapus-nama" class="mt-2 text-center text-sm font-medium text-slate-700"></p>
        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
            <button type="button" id="batal-hapus" class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-600">Batal</button>
            <button type="button" id="konfirmasi-hapus" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white">Ya, Hapus</button>
        </div>
    </div>
</div>

<script>
    var dialogHapus = document.getElementById('dialog-hapus');
    var namaHapus = document.getElementById('dialog-hapus-nama');
    var tombolBatalHapus = document.getElementById('batal-hapus');
    var tombolKonfirmasiHapus = document.getElementById('konfirmasi-hapus');
    var formHapusTerpilih = null;

    document.querySelectorAll('.form-hapus').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            formHapusTerpilih = form;
            namaHapus.textContent = form.dataset.nama;
            dialogHapus.hidden = false;
        });
    });

    tombolBatalHapus.addEventListener('click', function() { dialogHapus.hidden = true; });
    tombolKonfirmasiHapus.addEventListener('click', function () {
        if (formHapusTerpilih) { formHapusTerpilih.submit(); }
    });
</script>

<script>
    (function () {
        var loadingScreen = document.getElementById('loading-screen');
        var skeletonRows = document.getElementById('skeleton-rows');
        var dataRows = document.getElementById('data-rows');
        setTimeout(function() {
            loadingScreen.classList.add('is-leaving');
            skeletonRows.hidden = true;
            dataRows.hidden = false;

            setTimeout(function() {
                loadingScreen.classList.add('is-hidden');
            }, 400);
        }, 1000);
    })();
</script>

</body>
</html>
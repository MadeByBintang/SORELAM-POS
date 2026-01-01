<?php
session_start(); // Wajib ada untuk Toast

$host = "localhost";
$user = "sorelamuser";
$password = "sorelam123";
$db = "sorelam";

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("❌ Koneksi gagal: " . mysqli_connect_error());
}
// Update status dari halaman rekap
if (isset($_GET['selesai'])) {
    $id = $_GET['selesai'];
    // Update status jadi Selesai
    mysqli_query($conn, "UPDATE pesanan SET status='Selesai' WHERE id=$id");

    // Set pesan Toast
    $_SESSION['toast'] = "Pesanan berhasil disajikan!";

    header("Location: rekap.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Pesanan - Dapur SORELAM</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#006837',
                        secondary: '#f3f4f6',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        /* Sembunyikan scrollbar untuk Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Sembunyikan scrollbar untuk IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
    <script>
        function konfirmasiSelesai(id) {
            Swal.fire({
                title: "Pesanan Siap?",
                text: "Pastikan pesanan sudah sesuai & siap saji!",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#006837", // Warna Primary
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Sajikan!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = "rekap.php?selesai=" + id;
                }
            });
        }
    </script>

    <?php if (isset($_SESSION['toast'])): ?>
        <script>
            Swal.fire({
                toast: true,
                icon: "success",
                title: "<?= $_SESSION['toast']; ?>",
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
        </script>
    <?php unset($_SESSION['toast']);
    endif; ?>

    <nav class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-fire-burner text-white text-2xl"></i>
                    <span class="text-white text-xl font-bold tracking-wide">Dapur / Bar</span>
                </div>
                <div>
                    <a href="index.php" class="text-white hover:text-green-200 text-sm font-medium transition flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> Kembali ke Kasir
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">📋 Rekap Pesanan Masuk</h1>
                <p class="text-gray-500 text-sm mt-1">Pantau pesanan yang masuk secara realtime.</p>
            </div>
            <div class="flex gap-2">
                <button onclick="window.location.reload();" class="bg-white border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition shadow-sm">
                    <i class="fa-solid fa-rotate-right mr-1"></i> Refresh
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">

            <div class="overflow-x-auto max-h-[65vh] overflow-y-auto no-scrollbar">

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-primary text-white sticky top-0 z-10">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider w-16">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Waktu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama & Menu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Catatan</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Aksi Dapur</th>
                        </tr>
                    </thead>
                    <tbody id="dapur-table" class="bg-white divide-y divide-gray-200">
                        <?php
                        $query = "SELECT * FROM pesanan ORDER BY status ASC, id DESC";
                        $result = mysqli_query($conn, $query);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Styling Status
                                $isPending = ($row['status'] == 'Pending' || empty($row['status']));
                                $statusClass = $isPending
                                    ? 'bg-yellow-100 text-yellow-800 border-yellow-200'
                                    : 'bg-green-100 text-green-800 border-green-200';
                                $rowBg = $isPending ? 'bg-white' : 'bg-gray-50 opacity-75';
                        ?>
                                <tr class="<?= $rowBg; ?> hover:bg-gray-50 transition">

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        #<?= $row['id']; ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-regular fa-clock text-xs"></i>
                                            <?= isset($row['waktu']) ? date('H:i', strtotime($row['waktu'])) : '-'; ?> WIB
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($row['nama']); ?></div>
                                        <div class="text-sm text-primary font-medium mt-1">
                                            <i class="fa-solid fa-utensils text-xs mr-1"></i> <?= htmlspecialchars($row['menu']); ?>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <?php if ($row['catatan']): ?>
                                            <div class="text-sm text-red-600 font-semibold bg-red-50 p-2 rounded border border-red-100 inline-block">
                                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> "<?= htmlspecialchars($row['catatan']); ?>"
                                            </div>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border <?= $statusClass; ?>">
                                            <?= $isPending ? 'Pending' : 'Selesai'; ?>
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <?php if ($isPending): ?>
                                            <button onclick="konfirmasiSelesai(<?= $row['id']; ?>)"
                                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                                                <i class="fa-solid fa-check mr-2"></i> Tandai Selesai
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-400 cursor-not-allowed flex items-center justify-end">
                                                <i class="fa-solid fa-check-double mr-1"></i> Disajikan
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    <i class="fa-solid fa-clipboard-list text-4xl mb-3 text-gray-300"></i>
                                    <p>Belum ada data pesanan masuk.</p>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 text-xs text-gray-500 flex justify-between">
                <span>Total Data: <strong><?= mysqli_num_rows($result); ?></strong></span>
                <span>Diurutkan: Pending paling atas</span>
            </div>
        </div>

    </div>

</body>

</html>
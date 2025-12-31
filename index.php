<?php
session_start();

$host = "localhost";
$user = "sorelamuser";
$password = "sorelam123";
$db = "sorelam";

$conn = mysqli_connect($host, $user, $password, $db);
if (!$conn) {
    die("❌ Koneksi gagal: " . mysqli_connect_error());
}
// --- LOGIC PHP ---

// 1. CREATE
if (isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $menu = mysqli_real_escape_string($conn, $_POST['menu']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);

    mysqli_query($conn, "INSERT INTO pesanan (nama, menu, catatan, status) VALUES ('$nama','$menu','$catatan','Pending')");
    $_SESSION['toast'] = "Pesanan berhasil ditambahkan!";
    header("Location: index.php");
    exit;
}

// 2. UPDATE
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    mysqli_query($conn, "UPDATE pesanan SET nama='$_POST[nama]', menu='$_POST[menu]', catatan='$_POST[catatan]' WHERE id=$id");
    $_SESSION['toast'] = "Perubahan berhasil disimpan!";
    header("Location: index.php");
    exit;
}

// 3. DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM pesanan WHERE id=$id");
    $_SESSION['toast'] = "Pesanan berhasil dihapus!";
    header("Location: index.php");
    exit;
}

// 4. PERSIAPAN DATA UNTUK FORM (Edit vs Create)
$edit_mode = false;
$data_edit = ['id' => '', 'nama' => '', 'menu' => '', 'catatan' => ''];

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result_edit = mysqli_query($conn, "SELECT * FROM pesanan WHERE id=$id");
    if (mysqli_num_rows($result_edit) > 0) {
        $data_edit = mysqli_fetch_assoc($result_edit);
        $edit_mode = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Kasir - SORELAM Kopitiam</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#006837', // Warna Hijau Sorelam
                        secondary: '#f3f4f6',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
    <script>
        function hapusPesanan(id) {
            Swal.fire({
                title: "Hapus pesanan?",
                text: "Data tidak dapat dikembalikan setelah dihapus!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#aaa",
                confirmButtonText: "Ya, hapus!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = "index.php?delete=" + id;
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
                timerProgressBar: true
            });
        </script>
    <?php unset($_SESSION['toast']);
    endif; ?>

    <nav class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-mug-hot text-white text-2xl"></i>
                    <span class="text-white text-xl font-bold tracking-wide">SORELAM Kopitiam</span>
                </div>
                <a href="rekap.php"
                    class="flex items-center gap-2 bg-transparent border border-white/40 text-white text-sm font-medium px-4 py-2 rounded-lg 
hover:bg-white hover:text-primary hover:border-white transition duration-200">
                    <i class="fa-solid fa-kitchen-set"></i>
                    Masuk ke Dapur
                </a>
                <div class="text-white text-sm opacity-90">
                    <i class="fa-regular fa-calendar md:mr-1"></i> <span class="hidden md:inline"><?= date('l, d M Y'); ?></span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 sticky top-24">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-gray-700 flex items-center gap-2">
                            <i class="fa-solid <?= $edit_mode ? 'fa-pen-to-square' : 'fa-circle-plus'; ?> text-primary"></i>
                            <?= $edit_mode ? 'Edit Pesanan' : 'Buat Pesanan Baru'; ?>
                        </h2>
                    </div>

                    <form method="POST" class="p-6 space-y-4">
                        <input type="hidden" name="id" value="<?= $data_edit['id']; ?>">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemesan</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-user text-gray-400"></i>
                                </div>
                                <input type="text" name="nama" value="<?= $data_edit['nama']; ?>" required
                                    class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 border focus:bg-white focus:ring-primary focus:border-primary sm:text-sm p-2.5 transition"
                                    placeholder="Contoh: Budi">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Menu Pesanan</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-utensils text-gray-400"></i>
                                </div>
                                <input type="text" name="menu" value="<?= $data_edit['menu']; ?>" required
                                    class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 border focus:bg-white focus:ring-primary focus:border-primary sm:text-sm p-2.5 transition"
                                    placeholder="Contoh: Kopi O & Roti Bakar">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                            <textarea name="catatan" rows="3"
                                class="block w-full rounded-lg border-gray-300 bg-gray-50 border focus:bg-white focus:ring-primary focus:border-primary sm:text-sm p-2.5 transition"
                                placeholder="Contoh: Kurangi manis, bungkus..."><?= $data_edit['catatan']; ?></textarea>
                        </div>

                        <div class="pt-2">
                            <?php if ($edit_mode): ?>
                                <button type="submit" name="update" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition">
                                    <i class="fa-solid fa-floppy-disk mr-2 mt-0.5"></i> Simpan Perubahan
                                </button>
                                <a href="index.php"
                                    class="w-full flex justify-center py-2.5 px-4 mt-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition">
                                    <i class="fa-solid fa-xmark mr-2 mt-0.5"></i> Batal Edit
                                </a>
                            <?php else: ?>
                                <button type="submit" name="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition">
                                    <i class="fa-solid fa-plus mr-2 mt-0.5"></i> Tambah Pesanan
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-700">
                            <i class="fa-solid fa-list-check text-primary mr-2"></i> Daftar Pesanan
                        </h2>
                        <span class="bg-green-100 text-primary text-xs font-semibold px-2.5 py-0.5 rounded border border-green-200">
                            Realtime Data
                        </span>
                    </div>

                    <div class="overflow-x-auto max-h-[405px] overflow-y-scroll scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider w-12">No</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama & Menu</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Catatan</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="kasir-table" class="bg-white divide-y divide-gray-200">
                                <?php
                                $no = 1;
                                $result = mysqli_query($conn, "SELECT * FROM pesanan ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Logic warna status
                                    $statusClass = ($row['status'] == 'Selesai')
                                        ? 'bg-green-100 text-green-800 border-green-200'
                                        : 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                    $statusIcon = ($row['status'] == 'Selesai') ? 'fa-check' : 'fa-clock';
                                ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            <?= $no++; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($row['nama']); ?></div>
                                            <div class="text-sm text-gray-500"><i class="fa-solid fa-bowl-food text-xs mr-1"></i> <?= htmlspecialchars($row['menu']); ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if ($row['catatan']): ?>
                                                <div class="text-sm text-gray-600 italic bg-gray-50 p-2 rounded border border-gray-100">
                                                    "<?= htmlspecialchars($row['catatan']); ?>"
                                                </div>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border <?= $statusClass; ?>">
                                                <i class="fa-solid <?= $statusIcon; ?> mr-1 mt-0.5"></i> <?= $row['status'] ? $row['status'] : 'Pending'; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end gap-2">
                                                <a href="index.php?edit=<?= $row['id']; ?>" class="text-yellow-600 hover:text-yellow-900 bg-yellow-50 hover:bg-yellow-100 p-2 rounded transition" title="Edit">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <a href="#" onclick="hapusPesanan(<?= $row['id']; ?>)"
                                                    class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded transition">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if (mysqli_num_rows($result) == 0): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                            <i class="fa-solid fa-inbox text-4xl mb-3 text-gray-300"></i>
                                            <p>Belum ada pesanan saat ini.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 text-xs text-gray-500">
                        Total Pesanan: <strong><?= mysqli_num_rows($result); ?></strong>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>

</html>
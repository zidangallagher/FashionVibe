<?php
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
AdminMiddleware::handle();

require_once __DIR__ . '/../templates/admin_header.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Load environment variables if not loaded
if (!isset($_ENV['ADMIN_EMAIL'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../..');
    try {
        $dotenv->load();
    } catch (Exception $e) {
        // Set default admin email if .env loading fails
        $_ENV['ADMIN_EMAIL'] = 'admin@fashionvibe.com';
    }
}

$authController = new AuthController();
$usersResult = $authController->getAllUsers();
$users = $usersResult['success'] ? $usersResult['data'] : [];

// Default admin email if not set in .env
$adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@fashionvibe.com';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header dengan tombol kembali -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <a href="/admin" class="flex items-center text-blue-600 hover:text-blue-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.707 3.293a1 1 0 010 1.414L6.414 9H17a1 1 0 110 2H6.414l4.293 4.293a1 1 0 11-1.414 1.414l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manajemen Pengguna</h1>
    </div>

    <!-- Filter dan Pencarian -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" id="search" placeholder="Cari pengguna..." class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex-1">
                <select id="role" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Role</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
            <button class="bg-gray-100 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-200 transition-colors">
                Filter
            </button>
        </div>
    </div>

    <!-- Tabel Pengguna -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Registrasi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user->name) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($user->email) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <select onchange="updateUserRole('<?= $user->_id ?>', this.value)" 
                            class="text-sm rounded-full px-3 py-1 <?= getRoleColor($user->role) ?>"
                            <?= $user->email === $adminEmail ? 'disabled' : '' ?>>
                            <option value="admin" <?= $user->role === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="user" <?= $user->role === 'user' ? 'selected' : '' ?>>User</option>
                        </select>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <select onchange="updateUserStatus('<?= $user->_id ?>', this.value)" 
                            class="text-sm rounded-full px-3 py-1 <?= getStatusColor($user->status ?? 'active') ?>"
                            <?= $user->email === $adminEmail ? 'disabled' : '' ?>>
                            <option value="active" <?= ($user->status ?? 'active') === 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="suspended" <?= ($user->status ?? 'active') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            <?= date('d/m/Y H:i', $user->created_at->toDateTime()->getTimestamp()) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button onclick="viewUserDetails('<?= $user->_id ?>')" 
                                class="text-blue-600 hover:text-blue-900">Detail</button>
                            <?php if ($user->email !== $adminEmail): ?>
                            <button onclick="deleteUser('<?= $user->_id ?>')" 
                                class="text-red-600 hover:text-red-900">Hapus</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail Pengguna -->
<div id="userModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detail Pengguna</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="userDetails" class="mt-4">
                <!-- Detail pengguna akan dimuat di sini -->
            </div>
        </div>
    </div>
</div>

<?php
function getRoleColor($role) {
    switch ($role) {
        case 'admin':
            return 'bg-purple-100 text-purple-800';
        case 'user':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getStatusColor($status) {
    switch ($status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'suspended':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?>

<script>
const modal = document.getElementById('userModal');
const userDetails = document.getElementById('userDetails');
const adminEmail = '<?= $adminEmail ?>';

function viewUserDetails(userId) {
    fetch(`/api/users/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.data;
                userDetails.innerHTML = `
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-medium mb-2">Informasi Dasar</h4>
                            <div class="text-sm">
                                <p class="mb-1">Nama: ${user.name}</p>
                                <p class="mb-1">Email: ${user.email}</p>
                                <p class="mb-1">Role: ${user.role}</p>
                                <p class="mb-1">Status: ${user.status || 'active'}</p>
                                <p class="mb-1">Tanggal Registrasi: ${new Date(user.created_at.$date).toLocaleString()}</p>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium mb-2">Statistik</h4>
                            <div class="text-sm">
                                <p class="mb-1">Total Pesanan: ${user.order_count || 0}</p>
                                <p class="mb-1">Total Pembelian: Rp ${numberFormat(user.total_spent || 0)}</p>
                                <p class="mb-1">Login Terakhir: ${user.last_login ? new Date(user.last_login.$date).toLocaleString() : 'Belum pernah login'}</p>
                            </div>
                        </div>
                    </div>
                `;
                modal.classList.remove('hidden');
            } else {
                alert('Gagal memuat detail pengguna: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        });
}

function updateUserRole(userId, role) {
    if (!confirm('Apakah Anda yakin ingin mengubah role pengguna ini?')) {
        return;
    }

    fetch(`/api/users/${userId}/role`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ role: role })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Role pengguna berhasil diperbarui');
            location.reload();
        } else {
            alert('Gagal memperbarui role: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
    });
}

function updateUserStatus(userId, status) {
    if (!confirm('Apakah Anda yakin ingin mengubah status pengguna ini?')) {
        return;
    }

    fetch(`/api/users/${userId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status pengguna berhasil diperbarui');
            location.reload();
        } else {
            alert('Gagal memperbarui status: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
    });
}

function deleteUser(userId) {
    if (!confirm('Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.')) {
        return;
    }

    fetch(`/api/users/${userId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Pengguna berhasil dihapus');
            location.reload();
        } else {
            alert('Gagal menghapus pengguna: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
    });
}

function closeModal() {
    modal.classList.add('hidden');
    userDetails.innerHTML = '';
}

function numberFormat(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

// Filter pengguna
document.getElementById('role').addEventListener('change', function(e) {
    const role = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const userRole = row.querySelector('td:nth-child(3) select').value.toLowerCase();
        if (!role || userRole === role) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Pencarian pengguna
document.getElementById('search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const name = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
        const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        if (name.includes(searchTerm) || email.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Close modal when clicking outside
modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        closeModal();
    }
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?> 
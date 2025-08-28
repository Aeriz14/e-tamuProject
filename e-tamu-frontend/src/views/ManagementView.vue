<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

// State reaktif
const router = useRouter();
const users = ref([]);
const divisions = ref([]);
const isLoading = ref(true);
const errorMessage = ref('');
const successMessage = ref('');

// State untuk form
const newUser = ref({
  name: '',
  email: '',
  password: '',
  role: 'admin',
  division_id: null,
});
const newDivision = ref({
  name: '',
});

// State untuk UI
const showUserModal = ref(false);
const showDivisionModal = ref(false);

// --- API Calls ---
const API_BASE_URL = 'http://127.0.0.1:8000/api';

const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get(`${API_BASE_URL}/admin/management-data`);
    users.value = response.data.users;
    divisions.value = response.data.divisions;
  } catch (error) {
    errorMessage.value = 'Gagal memuat data. Anda mungkin tidak memiliki akses.';
    console.error(error);
  } finally {
    isLoading.value = false;
  }
};

const addUser = async () => {
  try {
    await axios.post(`${API_BASE_URL}/admin/users`, newUser.value);
    successMessage.value = 'Pengguna berhasil ditambahkan!';
    showUserModal.value = false;
    fetchData(); // Muat ulang data
    resetNewUserForm();
  } catch (error) {
    errorMessage.value = 'Gagal menambahkan pengguna. Periksa kembali isian Anda.';
    console.error(error.response.data);
  }
};

const addDivision = async () => {
  try {
    await axios.post(`${API_BASE_URL}/admin/divisions`, newDivision.value);
    successMessage.value = 'Divisi berhasil ditambahkan!';
    showDivisionModal.value = false;
    fetchData(); // Muat ulang data
    newDivision.value.name = '';
  } catch (error) {
    errorMessage.value = 'Gagal menambahkan divisi. Mungkin nama sudah ada.';
    console.error(error.response.data);
  }
};

const deleteUser = async (userId) => {
  if (confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
    try {
      await axios.delete(`${API_BASE_URL}/admin/users/${userId}`);
      successMessage.value = 'Pengguna berhasil dihapus.';
      fetchData(); // Muat ulang data
    } catch (error) {
      errorMessage.value = error.response.data.message || 'Gagal menghapus pengguna.';
      console.error(error.response.data);
    }
  }
};

const deleteDivision = async (divisionId) => {
  if (confirm('Apakah Anda yakin ingin menghapus divisi ini?')) {
    try {
      await axios.delete(`${API_BASE_URL}/admin/divisions/${divisionId}`);
      successMessage.value = 'Divisi berhasil dihapus.';
      fetchData(); // Muat ulang data
    } catch (error) {
      errorMessage.value = error.response.data.message || 'Gagal menghapus divisi.';
      console.error(error.response.data);
    }
  }
};

const resetNewUserForm = () => {
    newUser.value = { name: '', email: '', password: '', role: 'admin', division_id: null };
};

onMounted(() => {
    // Set token untuk semua request axios
    const token = localStorage.getItem('auth_token');
    if (token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
        router.push('/login');
        return;
    }
    fetchData();
});
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header sederhana -->
    <header class="bg-white shadow-sm">
      <div class="container mx-auto px-4 lg:px-8 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800">Manajemen Admin</h1>
        <button @click="router.push('/dashboard')" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
          Kembali ke Dashboard
        </button>
      </div>
    </header>

    <!-- Konten Utama -->
    <main class="container mx-auto p-4 lg:p-8">
      <div v-if="isLoading" class="text-center py-10">Memuat data...</div>
      <div v-else>
        <!-- Notifikasi -->
        <div v-if="successMessage" class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ successMessage }}</div>
        <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">{{ errorMessage }}</div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Kolom Manajemen Pengguna -->
          <div class="bg-white p-6 rounded-xl shadow-md border">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-bold">Daftar Pengguna</h2>
              <button @click="showUserModal = true" class="px-3 py-1.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">+ Tambah Pengguna</button>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="bg-gray-100"><th class="px-4 py-2 text-left">Nama</th><th class="px-4 py-2 text-left">Email</th><th class="px-4 py-2 text-left">Divisi</th><th class="px-4 py-2 text-right">Aksi</th></tr>
                </thead>
                <tbody>
                  <tr v-for="user in users" :key="user.id" class="border-b">
                    <td class="px-4 py-2">{{ user.name }} ({{ user.role }})</td>
                    <td class="px-4 py-2">{{ user.email }}</td>
                    <td class="px-4 py-2">{{ user.division?.name || '-' }}</td>
                    <td class="px-4 py-2 text-right">
                      <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-800">Hapus</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Kolom Manajemen Divisi -->
          <div class="bg-white p-6 rounded-xl shadow-md border">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-bold">Daftar Divisi</h2>
              <button @click="showDivisionModal = true" class="px-3 py-1.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">+ Tambah Divisi</button>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="bg-gray-100"><th class="px-4 py-2 text-left">Nama Divisi</th><th class="px-4 py-2 text-right">Aksi</th></tr>
                </thead>
                <tbody>
                  <tr v-for="division in divisions" :key="division.id" class="border-b">
                    <td class="px-4 py-2">{{ division.name }}</td>
                    <td class="px-4 py-2 text-right">
                      <button @click="deleteDivision(division.id)" class="text-red-600 hover:text-red-800">Hapus</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal Tambah Pengguna -->
    <div v-if="showUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <h3 class="text-xl font-bold mb-6">Tambah Pengguna Baru</h3>
        <form @submit.prevent="addUser" class="space-y-4">
          <div><label class="block text-sm font-medium">Nama</label><input v-model="newUser.name" type="text" required class="mt-1 w-full border rounded-md px-3 py-2"></div>
          <div><label class="block text-sm font-medium">Email</label><input v-model="newUser.email" type="email" required class="mt-1 w-full border rounded-md px-3 py-2"></div>
          <div><label class="block text-sm font-medium">Password</label><input v-model="newUser.password" type="password" required class="mt-1 w-full border rounded-md px-3 py-2"></div>
          <div><label class="block text-sm font-medium">Peran</label><select v-model="newUser.role" class="mt-1 w-full border rounded-md px-3 py-2"><option value="admin">Admin</option><option value="super_admin">Super Admin</option></select></div>
          <div v-if="newUser.role === 'admin'"><label class="block text-sm font-medium">Divisi</label><select v-model="newUser.division_id" class="mt-1 w-full border rounded-md px-3 py-2"><option :value="null" disabled>Pilih Divisi</option><option v-for="div in divisions" :key="div.id" :value="div.id">{{ div.name }}</option></select></div>
          <div class="flex justify-end gap-4 pt-4">
            <button type="button" @click="showUserModal = false" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Tambah Divisi -->
    <div v-if="showDivisionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <h3 class="text-xl font-bold mb-6">Tambah Divisi Baru</h3>
        <form @submit.prevent="addDivision" class="space-y-4">
          <div><label class="block text-sm font-medium">Nama Divisi</label><input v-model="newDivision.name" type="text" required class="mt-1 w-full border rounded-md px-3 py-2"></div>
          <div class="flex justify-end gap-4 pt-4">
            <button type="button" @click="showDivisionModal = false" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

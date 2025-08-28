<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

// Inisialisasi state untuk form appointment
const appointment = ref({
  full_name: '',
  email: '',
  institution: '',
  purpose: '',
  division_id: null,
  status_pertemuan: 'pending',
});

const divisions = ref([]); // State untuk menampung daftar divisi
const appointmentId = route.params.id;
const isLoading = ref(true);
const errorMessage = ref('');
const successMessage = ref('');

// --- API Calls ---
const API_BASE_URL = 'http://127.0.0.1:8000/api';

// Fungsi untuk mengambil daftar semua divisi
const fetchDivisions = async (token) => {
  try {
    const response = await axios.get(`${API_BASE_URL}/divisions`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    divisions.value = response.data;
  } catch (error) {
    console.error('Gagal mengambil daftar divisi:', error);
    errorMessage.value = 'Gagal memuat daftar divisi.';
  }
};

// Fungsi untuk mengambil detail appointment yang akan diedit
const fetchAppointmentDetail = async (token) => {
  try {
    const response = await axios.get(`${API_BASE_URL}/appointments/${appointmentId}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    appointment.value = response.data;
  } catch (error) {
    console.error('Gagal mengambil detail janji temu:', error);
    errorMessage.value = 'Gagal memuat data janji temu. Mungkin data tidak ditemukan.';
  }
};

// Ambil semua data yang diperlukan saat komponen dimuat
onMounted(async () => {
  const token = localStorage.getItem('auth_token');
  if (!token) {
    router.push('/');
    return;
  }

  isLoading.value = true;
  errorMessage.value = '';
  await fetchDivisions(token);
  await fetchAppointmentDetail(token);
  isLoading.value = false;
});

// Fungsi untuk meng-update data (hanya status)
const updateAppointment = async () => {
  const token = localStorage.getItem('auth_token');
  errorMessage.value = '';
  successMessage.value = '';

  try {
    // Kirim hanya status_pertemuan yang diperbarui
    await axios.put(`${API_BASE_URL}/appointments/${appointmentId}`, {
      status_pertemuan: appointment.value.status_pertemuan,
    }, {
      headers: { Authorization: `Bearer ${token}` }
    });

    successMessage.value = 'Status janji temu berhasil diperbarui!';

    // Kembali ke dashboard setelah 2 detik
    setTimeout(() => {
        router.push('/dashboard');
    }, 2000);

  } catch (error) {
    console.error('Gagal memperbarui data:', error.response?.data);
    errorMessage.value = 'Gagal memperbarui status. Periksa kembali isian Anda.';
  }
};
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl mx-auto bg-white rounded-xl shadow-lg border border-gray-200 p-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-2">Edit Janji Temu</h1>
      <p class="text-gray-500 mb-8">Perbarui status pertemuan tamu di bawah ini.</p>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-10">
        <p class="text-gray-600">Memuat data...</p>
      </div>

      <!-- Error/Success Messages -->
      <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
        <span class="block sm:inline">{{ errorMessage }}</span>
      </div>
       <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
        <span class="block sm:inline">{{ successMessage }}</span>
      </div>

      <!-- Form -->
      <form v-if="!isLoading && appointment.id" @submit.prevent="updateAppointment" class="space-y-6">
        <div>
          <label for="full_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
          <input v-model="appointment.full_name" type="text" id="full_name" disabled class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed">
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input v-model="appointment.email" type="email" id="email" disabled class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed">
        </div>

        <div>
          <label for="institution" class="block text-sm font-medium text-gray-700">Instansi</label>
          <input v-model="appointment.institution" type="text" id="institution" disabled class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed">
        </div>

        <!-- Dropdown untuk Divisi -->
        <div>
          <label for="division" class="block text-sm font-medium text-gray-700">Divisi Tujuan</label>
          <select v-model="appointment.division_id" id="division" disabled class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed">
            <option :value="null" disabled>-- Pilih Divisi --</option>
            <option v-for="division in divisions" :key="division.id" :value="division.id">
              {{ division.name }}
            </option>
          </select>
        </div>

        <div>
          <label for="purpose" class="block text-sm font-medium text-gray-700">Tujuan Kunjungan</label>
          <textarea v-model="appointment.purpose" id="purpose" rows="3" disabled class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed"></textarea>
        </div>

        <!-- Dropdown untuk Status Pertemuan (INI SATU-SATUNYA YANG BISA DIUBAH) -->
        <div>
          <label for="status_pertemuan" class="block text-sm font-medium text-gray-700">Status Pertemuan</label>
          <select v-model="appointment.status_pertemuan" id="status_pertemuan" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            <option value="pending">Pending</option>
            <option value="selesai">Selesai</option>
            <option value="ditolak">Ditolak</option>
          </select>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end items-center space-x-4 pt-4">
          <button @click="$router.push('/dashboard')" type="button" class="px-5 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
            Batal
          </button>
          <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

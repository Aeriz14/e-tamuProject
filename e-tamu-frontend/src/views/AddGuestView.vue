<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

// State untuk data form
const guestData = ref({
  full_name: '',
  email: '',
  phone_number: '', // PERUBAHAN: Ditambahkan
  institution: '',
  purpose: '',
  face_photo: null,
  division_id: '', // PERUBAHAN: Ditambahkan
});

// State untuk UI
const divisions = ref([]); // PERUBAHAN: Ditambahkan
const errors = ref({});
const isLoading = ref(false);
const showSuccessModal = ref(false);
const showSuccessMessage = ref('');
const showErrorModal = ref(false);
const showErrorMessage = ref('');

// PERUBAHAN: Fungsi untuk mengambil daftar divisi
const fetchDivisions = async () => {
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/divisions');
    if (Array.isArray(response.data)) {
      divisions.value = response.data;
    }
  } catch (error) {
    console.error('Error fetching divisions:', error);
  }
};

// Panggil fetchDivisions saat komponen dimuat
onMounted(fetchDivisions);

// Fungsi untuk menangani file upload
const handleFileUpload = (event) => {
  guestData.value.face_photo = event.target.files[0];
  if (errors.value.face_photo) {
    delete errors.value.face_photo;
  }
};

// Fungsi untuk submit form
const handleSubmit = async () => {
  isLoading.value = true;
  errors.value = {};

  const formData = new FormData();
  for (const key in guestData.value) {
    if (guestData.value[key]) {
      formData.append(key, guestData.value[key]);
    }
  }

  try {
    const response = await axios.post('http://127.0.0.1:8000/api/register', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    if (response.status === 201) {
      showSuccessMessage.value = response.data.message;
      showSuccessModal.value = true;
      // Reset form
      guestData.value = {
        full_name: '',
        email: '',
        phone_number: '',
        institution: '',
        purpose: '',
        face_photo: null,
        division_id: '',
      };
      // Membersihkan input file
      document.getElementById('face_photo').value = '';
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || error.response.data;
    } else {
      showErrorMessage.value = 'Terjadi kesalahan saat pendaftaran. Silakan coba lagi.';
      showErrorModal.value = true;
    }
    console.error('Error during registration:', error);
  } finally {
    isLoading.value = false;
  }
};

const goBack = () => {
  // Cek apakah ada token, jika ada kembali ke dashboard, jika tidak kembali ke halaman awal
  if (localStorage.getItem('auth_token')) {
    router.push('/dashboard');
  } else {
    router.push('/');
  }
};
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-2xl">
      <div class="flex items-center justify-center mb-6">
        <img src="/src/assets/img/diskominfologo.png" alt="Logo Diskominfo" class="h-16 w-auto">
      </div>
      <h1 class="text-3xl font-extrabold text-center text-indigo-700 mb-2">Pendaftaran Kunjungan</h1>
      <p class="text-center text-gray-500 mb-8">Silakan isi data diri Anda untuk mencatat kunjungan.<br>Terima kasih!</p>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Nama Lengkap -->
        <div>
          <label for="full_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
          <input id="full_name" v-model="guestData.full_name" type="text" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="Contoh: Budi Santoso" />
          <p v-if="errors.full_name" class="mt-1 text-red-500 text-xs">{{ errors.full_name[0] }}</p>
        </div>

        <!-- Alamat Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
          <input id="email" v-model="guestData.email" type="email" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="contoh@email.com" />
          <p v-if="errors.email" class="mt-1 text-red-500 text-xs">{{ errors.email[0] }}</p>
        </div>

        <!-- PERUBAHAN: Input Nomor HP -->
        <div>
          <label for="phone_number" class="block text-sm font-medium text-gray-700">Nomor HP</label>
          <input id="phone_number" v-model="guestData.phone_number" type="tel" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="Contoh: 081234567890" />
          <p v-if="errors.phone_number" class="mt-1 text-red-500 text-xs">{{ errors.phone_number[0] }}</p>
        </div>

        <!-- Asal Instansi -->
        <div>
          <label for="institution" class="block text-sm font-medium text-gray-700">Asal Instansi</label>
          <input id="institution" v-model="guestData.institution" type="text" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="Contoh: Universitas Gadjah Mada" />
          <p v-if="errors.institution" class="mt-1 text-red-500 text-xs">{{ errors.institution[0] }}</p>
        </div>

        <!-- PERUBAHAN: Dropdown Divisi Tujuan -->
        <div>
          <label for="division_id" class="block text-sm font-medium text-gray-700">Divisi Tujuan</label>
          <select id="division_id" v-model="guestData.division_id" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all">
            <option value="" disabled>Pilih Divisi</option>
            <option v-for="division in divisions" :key="division.id" :value="division.id">{{ division.name }}</option>
          </select>
          <p v-if="errors.division_id" class="mt-1 text-red-500 text-xs">{{ errors.division_id[0] }}</p>
        </div>

        <!-- Tujuan Kunjungan -->
        <div>
          <label for="purpose" class="block text-sm font-medium text-gray-700">Tujuan Kunjungan</label>
          <textarea id="purpose" v-model="guestData.purpose" rows="3" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="Jelaskan tujuan kunjungan Anda secara singkat"></textarea>
          <p v-if="errors.purpose" class="mt-1 text-red-500 text-xs">{{ errors.purpose[0] }}</p>
        </div>

        <!-- Unggah Foto Wajah -->
        <div>
          <label for="face_photo" class="block text-sm font-medium text-gray-700">Unggah Foto Wajah</label>
          <input id="face_photo" type="file" @change="handleFileUpload" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" />
          <p v-if="errors.face_photo" class="mt-1 text-red-500 text-xs">{{ errors.face_photo[0] }}</p>
        </div>

        <!-- Tombol Submit dan Batal -->
        <div class="flex justify-end space-x-4">
          <button type="button" @click="goBack" class="w-full sm:w-auto px-6 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-all">
            Kembali
          </button>
          <button type="submit" :disabled="isLoading" class="w-full sm:w-auto px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 disabled:bg-indigo-400 transition-all">
            {{ isLoading ? 'Memproses...' : 'Daftar Kunjungan' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Sukses -->
  <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-sm mx-auto text-center">
      <div class="flex items-center justify-center mx-auto mb-4 size-16 rounded-full bg-green-100 text-green-500">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-10"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
      </div>
      <h3 class="text-xl font-bold text-gray-900 mb-4">Pendaftaran Berhasil!</h3>
      <p class="text-sm text-gray-600 mb-6">{{ showSuccessMessage }}</p>
      <button @click="showSuccessModal = false; goBack();" class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
        Selesai
      </button>
    </div>
  </div>

  <!-- Modal Error -->
  <div v-if="showErrorModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-sm mx-auto text-center">
      <div class="flex items-center justify-center mx-auto mb-4 size-16 rounded-full bg-red-100 text-red-500">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-10"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94l-1.72-1.72Z" clip-rule="evenodd" /></svg>
      </div>
      <h3 class="text-xl font-bold text-gray-900 mb-4">Pendaftaran Gagal</h3>
      <p class="text-sm text-gray-600 mb-6">{{ showErrorMessage }}</p>
      <button @click="showErrorModal = false" class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
        Tutup
      </button>
    </div>
  </div>
</template>

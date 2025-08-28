<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

// State untuk data form
const appointmentData = ref({
  full_name: '',
  email: '',
  phone_number: '', // PERBAIKAN: Ditambahkan
  institution: '',
  purpose: '',
  face_photo: null,
  division_id: '',
  custom_division: '',
  appointment_date: '',
  appointment_time: '',
});

// State untuk UI
const divisions = ref([]);
const errors = ref({});
const isLoading = ref(false);
const showSuccessModal = ref(false);
const showSuccessMessage = ref('');
const showErrorModal = ref(false);
const showErrorMessage = ref('');
const isCustomDivision = ref(false);

const fetchDivisions = async () => {
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/divisions');
    if (Array.isArray(response.data)) {
      divisions.value = response.data;
    } else if (Array.isArray(response.data.data)) {
      divisions.value = response.data.data;
    } else {
      divisions.value = [];
    }
  } catch (error) {
    console.error('Error fetching divisions:', error);
  }
};

const handleDivisionChange = () => {
  isCustomDivision.value = appointmentData.value.division_id === 'custom';
  if (!isCustomDivision.value) {
    appointmentData.value.custom_division = '';
  }
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  appointmentData.value.face_photo = file;
  if (errors.value.face_photo) {
    delete errors.value.face_photo;
  }
};

const handleSubmit = async () => {
  isLoading.value = true;
  errors.value = {};

  const formData = new FormData();
  for (const key in appointmentData.value) {
    if (key === 'face_photo' && appointmentData.value.face_photo) {
      formData.append('face_photo', appointmentData.value.face_photo);
    } else if (key !== 'face_photo' && appointmentData.value[key]) {
      formData.append(key, appointmentData.value[key]);
    }
  }

  try {
    const response = await axios.post('http://127.0.0.1:8000/api/appointments', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    if (response.status === 201) {
      showSuccessMessage.value = response.data.message;
      showSuccessModal.value = true;
      appointmentData.value = {
        full_name: '', email: '', phone_number: '', institution: '', purpose: '',
        face_photo: null, division_id: '', custom_division: '',
        appointment_date: '', appointment_time: '',
      };
      document.getElementById('face_photo').value = '';
      isCustomDivision.value = false;
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || error.response.data;
    } else {
      showErrorMessage.value = 'Terjadi kesalahan saat membuat janji. Silakan coba lagi.';
      showErrorModal.value = true;
    }
    console.error('Error during appointment creation:', error);
  } finally {
    isLoading.value = false;
  }
};

const goToHome = () => {
  router.push('/');
};

onMounted(() => {
  fetchDivisions();
});
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-2xl">
      <div class="flex items-center justify-center mb-6">
        <img src="/src/assets/img/diskominfologo.png" alt="Logo Diskominfo" class="h-16 w-auto">
      </div>
      <h1 class="text-3xl font-extrabold text-center text-indigo-700 mb-2">Buat Janji Temu</h1>
      <p class="text-center text-gray-500 mb-8">Silakan isi data untuk membuat janji temu.<br>Terima kasih!</p>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Nama Lengkap -->
        <div>
          <label for="full_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
          <input id="full_name" v-model="appointmentData.full_name" type="text" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: Budi Santoso" />
          <p v-if="errors.full_name" class="mt-1 text-red-500 text-xs">{{ errors.full_name[0] }}</p>
        </div>

        <!-- Alamat Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
          <input id="email" v-model="appointmentData.email" type="email" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="contoh@email.com" />
          <p v-if="errors.email" class="mt-1 text-red-500 text-xs">{{ errors.email[0] }}</p>
        </div>

        <!-- PERBAIKAN: Input Nomor HP -->
        <div>
          <label for="phone_number" class="block text-sm font-medium text-gray-700">Nomor HP</label>
          <input id="phone_number" v-model="appointmentData.phone_number" type="tel" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: 081234567890" />
          <p v-if="errors.phone_number" class="mt-1 text-red-500 text-xs">{{ errors.phone_number[0] }}</p>
        </div>

        <!-- Asal Instansi -->
        <div>
          <label for="institution" class="block text-sm font-medium text-gray-700">Asal Instansi</label>
          <input id="institution" v-model="appointmentData.institution" type="text" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: Universitas Gadjah Mada" />
          <p v-if="errors.institution" class="mt-1 text-red-500 text-xs">{{ errors.institution[0] }}</p>
        </div>

        <!-- Tujuan Kunjungan -->
        <div>
          <label for="purpose" class="block text-sm font-medium text-gray-700">Tujuan Kunjungan</label>
          <textarea id="purpose" v-model="appointmentData.purpose" rows="3" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Jelaskan tujuan kunjungan Anda secara singkat"></textarea>
          <p v-if="errors.purpose" class="mt-1 text-red-500 text-xs">{{ errors.purpose[0] }}</p>
        </div>

        <!-- Divisi Tujuan -->
        <div>
          <label for="division_id" class="block text-sm font-medium text-gray-700">Divisi Tujuan</label>
          <select id="division_id" v-model="appointmentData.division_id" @change="handleDivisionChange" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="" disabled>Pilih Divisi</option>
            <option v-for="division in divisions" :key="division.id" :value="division.id">{{ division.name }}</option>
            <option value="custom">Lainnya (Tambah Divisi Baru)</option>
          </select>
          <p v-if="errors.division_id" class="mt-1 text-red-500 text-xs">{{ errors.division_id[0] }}</p>
        </div>

        <!-- Input Divisi Kustom -->
        <div v-if="isCustomDivision">
          <label for="custom_division" class="block text-sm font-medium text-gray-700">Nama Divisi Baru</label>
          <input id="custom_division" v-model="appointmentData.custom_division" type="text" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Masukkan nama divisi baru" />
          <p v-if="errors.custom_division" class="mt-1 text-red-500 text-xs">{{ errors.custom_division[0] }}</p>
        </div>

        <!-- Tanggal dan Waktu Janji -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label for="appointment_date" class="block text-sm font-medium text-gray-700">Tanggal Janji</label>
            <input id="appointment_date" v-model="appointmentData.appointment_date" type="date" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
            <p v-if="errors.appointment_date" class="mt-1 text-red-500 text-xs">{{ errors.appointment_date[0] }}</p>
          </div>
          <div>
            <label for="appointment_time" class="block text-sm font-medium text-gray-700">Waktu Janji</label>
            <input id="appointment_time" v-model="appointmentData.appointment_time" type="time" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
            <p v-if="errors.appointment_time" class="mt-1 text-red-500 text-xs">{{ errors.appointment_time[0] }}</p>
          </div>
        </div>

        <!-- Upload Foto Tamu -->
        <div>
          <label for="face_photo" class="block text-sm font-medium text-gray-700">Unggah Foto Wajah</label>
          <input id="face_photo" type="file" accept="image/*" @change="handleFileUpload" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" />
          <p v-if="errors.face_photo" class="mt-1 text-red-500 text-xs">{{ errors.face_photo[0] }}</p>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex justify-end space-x-4">
          <button type="button" @click="goToHome" class="w-full sm:w-auto px-6 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Batal</button>
          <button type="submit" :disabled="isLoading" class="w-full sm:w-auto px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 disabled:bg-indigo-400">
            {{ isLoading ? 'Memproses...' : 'Buat Janji' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Sukses & Error (Sama seperti sebelumnya) -->
  <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-sm mx-auto text-center">
        <div class="flex items-center justify-center mx-auto mb-4 size-16 rounded-full bg-green-100 text-green-500">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-10"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-4">Janji Temu Berhasil Dibuat!</h3>
        <p class="text-sm text-gray-600 mb-6">{{ showSuccessMessage }}</p>
        <button @click="showSuccessModal = false; goToHome();" class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Kembali ke Beranda</button>
    </div>
  </div>
  <div v-if="showErrorModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-sm mx-auto text-center">
        <div class="flex items-center justify-center mx-auto mb-4 size-16 rounded-full bg-red-100 text-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-10"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94l-1.72-1.72Z" clip-rule="evenodd" /></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-4">Janji Temu Gagal</h3>
        <p class="text-sm text-gray-600 mb-6">{{ showErrorMessage }}</p>
        <button @click="showErrorModal = false" class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Tutup</button>
    </div>
  </div>
</template>

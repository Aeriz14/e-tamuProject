<template>
  <div class="min-h-screen font-sans text-gray-800 bg-gray-50 flex items-center justify-center p-4">
    <div class="w-full max-w-4xl mx-auto bg-white rounded-3xl shadow-xl flex flex-col md:flex-row overflow-hidden">

      <div class="md:w-1/2 flex flex-col justify-center p-8 sm:p-12">
        <div class="w-full max-w-md">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Selamat Datang</h2>
          <p class="text-gray-500 mb-8">Silakan masukkan detail Anda untuk masuk.</p>

          <form class="space-y-6" @submit.prevent="handleLogin">
            <div>
              <label for="email" class="sr-only">Email</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M3 4a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2H3zm12 2.25L10 9.625 5 6.25V6h10v.25zM5 14V8.125l5 3.125 5-3.125V14H5z" />
                  </svg>
                </div>
                <input
                  id="email"
                  name="email"
                  type="email"
                  v-model="email"
                  autocomplete="email"
                  required
                  placeholder="email@example.com"
                  class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div>
              <label for="password" class="sr-only">Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                  </svg>
                </div>
                <input
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  name="password"
                  v-model="password"
                  autocomplete="current-password"
                  required
                  placeholder="Password"
                  class="w-full pl-10 pr-12 py-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button
                  type="button"
                  class="absolute top-1/2 right-3 -translate-y-1/2 flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600"
                  @click="showPassword = !showPassword"
                >
                  <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-5.186 0-9.42-3.582-10.5-8 1.08-4.418 5.314-8 10.5-8 1.78 0 3.46.48 4.905 1.333M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6" /></svg>
                </button>
              </div>
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="w-full py-3 rounded-lg bg-blue-600 text-white font-semibold shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all disabled:bg-blue-400"
            >
              {{ isLoading ? 'Memproses...' : 'Masuk' }}
            </button>
          </form>

          <div v-if="errorMessage" class="mt-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-3">
            <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.707-4.293a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L10 11.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0z" clip-rule="evenodd" /></svg>
            <span>{{ errorMessage }}</span>
          </div>
        </div>
      </div>

      <div class="hidden md:flex md:w-1/2 items-center justify-center bg-gradient-to-br from-blue-600 to-blue-800 p-12 text-white text-center">
        <div class="flex flex-col items-center">
          <img
            :src="loginImage"
            alt="Login Illustration"
            class="w-64 h-64 object-contain mb-8"
          />
          <h1 class="text-3xl font-bold mb-2">Sederhanakan Alur Kerja</h1>
          <p class="max-w-sm">
            Akses dashboard Anda untuk mengelola janji temu, melacak progres, dan berkolaborasi dengan tim Anda secara lancar.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useRouter } from 'vue-router';
import axios from 'axios';
import { ref } from 'vue';
import loginImage from '../assets/bc.png';

export default {
  setup() {
    const router = useRouter();
    const email = ref('');
    const password = ref('');
    const errorMessage = ref(null);
    const showPassword = ref(false);
    const isLoading = ref(false);

    const handleLogin = async () => {
      isLoading.value = true;
      errorMessage.value = null;

      try {
        // PERBAIKAN: Mengirim data dari ref, bukan dari event target
        const response = await axios.post('http://127.0.0.1:8000/api/login', {
          email: email.value,
          password: password.value,
        });

        if (response.data.access_token) {
          // PERBAIKAN KRITIS: Simpan SEMUA data yang dibutuhkan sebelum pindah halaman
          localStorage.setItem('auth_token', response.data.access_token);
          localStorage.setItem('user_role', response.data.user.role);
          localStorage.setItem('user_division_id', response.data.user.division_id);

          // Beri tahu Axios untuk menggunakan token ini di semua request berikutnya
          axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`;

          router.push('/dashboard');
        }
      } catch (error) {
        // Menampilkan pesan error yang lebih jelas dari backend
        if (error.response && error.response.data && error.response.data.message) {
          errorMessage.value = error.response.data.message;
        } else if (error.response && error.response.data && error.response.data.errors) {
            // Menangani error validasi spesifik
            const errors = error.response.data.errors;
            const firstErrorKey = Object.keys(errors)[0];
            errorMessage.value = errors[firstErrorKey][0];
        }
        else {
          errorMessage.value = 'Login gagal. Periksa kembali kredensial Anda.';
        }
        console.error('Login error:', error);
      } finally {
        isLoading.value = false;
      }
    };

    return {
      email,
      password,
      handleLogin,
      errorMessage,
      loginImage,
      showPassword,
      isLoading,
    };
  },
};
</script>

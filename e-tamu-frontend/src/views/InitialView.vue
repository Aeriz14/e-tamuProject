<template>
  <!-- Main container with a clean, light background -->
  <div class="min-h-screen bg-gray-50 font-sans antialiased text-gray-800">

    <!-- Navbar with a subtle border -->
    <header class="bg-white/80 backdrop-blur-lg sticky top-0 z-50 border-b border-gray-200">
      <div class="container mx-auto flex justify-between items-center py-4 px-4 lg:px-8">
        <!-- Logo -->
        <div class="flex items-center gap-3">
          <img src="/src/assets/img/diskominfologo.png" alt="Logo Diskominfo" class="h-10 w-auto">
          <span class="font-bold text-lg text-gray-800 hidden sm:block">E-Tamu</span>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center gap-8">
          <RouterLink to="/register" class="text-gray-600 hover:text-blue-600 font-medium transition-colors duration-300">
            Pendaftaran Tamu
          </RouterLink>
          <RouterLink to="/appointment" class="text-gray-600 hover:text-blue-600 font-medium transition-colors duration-300">
            Buat Janji
          </RouterLink>
        </nav>

        <!-- Login Button for Desktop and Mobile -->
        <div class="flex items-center">
          <RouterLink to="/login" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 shadow-lg hover:shadow-blue-500/20">
            Login Admin
          </RouterLink>
        </div>
      </div>
       <!-- Mobile Navigation Links (Simplified) -->
       <nav class="w-full md:hidden py-3 border-t border-gray-200 bg-white">
          <div class="flex justify-center items-center space-x-6">
            <RouterLink to="/register" class="text-gray-600 hover:text-blue-600 font-medium transition-colors duration-300">Pendaftaran</RouterLink>
            <RouterLink to="/appointment" class="text-gray-600 hover:text-blue-600 font-medium transition-colors duration-300">Buat Janji</RouterLink>
          </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <main class="relative overflow-hidden">
        <div class="container mx-auto px-4 lg:px-8 py-24 sm:py-32 text-center">
            <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 tracking-tight">
          <span class="text-blue-600">E-Tamu</span> Diskominfo Garut
        </h1>
        <p class="mt-6 text-lg text-gray-600 max-w-2xl mx-auto">
          Selamat datang di platform digital resmi <b>Dinas Komunikasi dan Informatika Kabupaten Garut</b>.<br>
          E-Tamu menghadirkan solusi profesional untuk pengelolaan kunjungan, pendaftaran, dan janji temu secara terintegrasi, aman, dan efisien.
        </p>
        <div class="mt-10">
          <RouterLink to="/appointment" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-bold rounded-full shadow-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-transform duration-300 hover:scale-105">
            Ajukan Janji Temu
          </RouterLink>
        </div>
            </div>
        </div>
    </main>

    <!-- Services Section -->
    <section class="bg-white py-24 sm:py-32">
      <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center max-w-3xl mx-auto">
      <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl tracking-tight">
        Layanan Digital Tamu Terpadu
      </h2>
      <p class="mt-4 text-lg text-gray-600">
        E-Tamu mendukung pelayanan publik yang transparan, ramah, dan profesional. Semua proses kunjungan dan administrasi tamu kini dapat dilakukan secara online, terstruktur, dan terdokumentasi dengan baik.
      </p>
        </div>

        <div class="mt-20 grid md:grid-cols-2 lg:grid-cols-3 gap-10">
          <!-- Card 1: Pendaftaran Online -->
          <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100 transition-all duration-300 hover:shadow-2xl hover:-translate-y-2">
            <div class="flex items-center justify-center h-16 w-16 rounded-2xl bg-blue-100 text-blue-600 mb-6">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
              <h3 class="text-xl font-bold mb-3 text-gray-900">Pendaftaran Tamu Digital</h3>
              <p class="text-gray-600 leading-relaxed">
                Setiap tamu dapat melakukan registrasi secara mandiri melalui sistem online yang mudah digunakan, memastikan data terinput dengan akurat dan cepat.
              </p>
          </div>

          <!-- Card 2: Dashboard Admin -->
          <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100 transition-all duration-300 hover:shadow-2xl hover:-translate-y-2">
            <div class="flex items-center justify-center h-16 w-16 rounded-2xl bg-blue-100 text-blue-600 mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
              <h3 class="text-xl font-bold mb-3 text-gray-900">Dashboard Pengelolaan</h3>
              <p class="text-gray-600 leading-relaxed">
                Admin dapat memantau seluruh aktivitas kunjungan, mengelola data tamu, serta memonitor statistik secara real-time melalui dashboard yang informatif dan interaktif.
              </p>
          </div>

          <!-- Card 3: Manajemen Janji Temu -->
          <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100 transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 md:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-center h-16 w-16 rounded-2xl bg-blue-100 text-blue-600 mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
              <h3 class="text-xl font-bold mb-3 text-gray-900">Manajemen Janji Temu Profesional</h3>
              <p class="text-gray-600 leading-relaxed">
                Proses penjadwalan, konfirmasi, dan pelaporan janji temu dilakukan secara digital, mendukung pelayanan publik yang efisien, transparan, dan terstandar.
              </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
      <div class="container mx-auto py-8 px-4 lg:px-8 text-center">
  <p class="text-gray-400">&copy; {{ new Date().getFullYear() }} Dinas Komunikasi dan Informatika Kabupaten Garut.<br>Seluruh hak cipta dilindungi undang-undang.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>

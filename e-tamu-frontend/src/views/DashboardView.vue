<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import axios from 'axios';
import { useRouter, RouterLink } from 'vue-router';
import Chart from 'chart.js/auto';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

// Inisialisasi state reaktif
const router = useRouter();
const appointments = ref([]);
const totalGuests = ref(0);
const admins = ref(0);
const statusStats = ref({ pending: 0, selesai: 0, ditolak: 0 });
const monthlyData = ref([]);
const divisionVisitData = ref([]);
const pendingCount = ref(0);
const isLoading = ref(true);
const userRole = localStorage.getItem('user_role');

// State untuk UI
const isMobileNavOpen = ref(false);
const showDeleteModal = ref(false);
const guestToDeleteId = ref(null);
const showSuccessModal = ref(false);
const showErrorModal = ref(false);
const showDetailModal = ref(false);
const selectedGuest = ref(null);
const searchQuery = ref('');

let myChart = null;

// --- API Calls ---
const API_BASE_URL = 'http://127.0.0.1:8000/api';

const fetchDashboardSummary = async () => {
  const token = localStorage.getItem('auth_token');
  if (!token) {
    router.push('/');
    return;
  }
  isLoading.value = true;
  try {
    const response = await axios.get(`${API_BASE_URL}/dashboard/summary`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    const summary = response.data;
    appointments.value = summary.appointments;
    totalGuests.value = summary.total_guests;
    admins.value = summary.total_admins;
    statusStats.value = summary.status_stats;
    monthlyData.value = summary.monthly_data;
    divisionVisitData.value = summary.visits_by_division;
    pendingCount.value = summary.pending_count;
    await nextTick();
    createChart();
  } catch (error) {
    console.error('Gagal mengambil ringkasan dashboard:', error);
    if (error.response?.status === 401) {
      logout();
    }
  } finally {
    isLoading.value = false;
  }
};

// --- Fungsi Ekspor PDF ---
const exportPDF = () => {
  const doc = new jsPDF();
  doc.setFontSize(18);
  doc.text('Daftar Janji Temu - E-Tamu Diskominfo Garut', 14, 22);
  doc.setFontSize(11);
  doc.setTextColor(100);
  doc.text(`Diekspor pada: ${new Date().toLocaleString('id-ID')}`, 14, 30);
  const tableColumn = ["No", "Nama Lengkap", "Nomor HP", "Instansi", "Divisi Tujuan", "Status"];
  const tableRows = [];
  filteredAppointments.value.forEach((appointment, index) => {
    const appointmentData = [
      index + 1,
      appointment.full_name,
      appointment.phone_number || '-',
      appointment.institution,
      appointment.division ? appointment.division.name : '-',
      appointment.status_pertemuan,
    ];
    tableRows.push(appointmentData);
  });
  autoTable(doc, {
    head: [tableColumn],
    body: tableRows,
    startY: 35,
    theme: 'grid',
    styles: { font: 'helvetica', fontSize: 10, cellPadding: 2 },
    headStyles: { fillColor: [41, 128, 185], textColor: 255, fontStyle: 'bold' }
  });
  doc.save('daftar-janji-temu.pdf');
};

// --- Logika Chart ---
const createChart = () => {
  if (myChart) {
    myChart.destroy();
  }
  const ctx = document.getElementById('myChart');
  if (ctx) {
    myChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
          label: 'Jumlah Kunjungan',
          data: monthlyData.value,
          backgroundColor: 'rgba(59, 130, 246, 0.7)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 1,
          borderRadius: 6,
          hoverBackgroundColor: 'rgba(37, 99, 235, 0.8)'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true, grid: { color: '#e5e7eb' }, ticks: { precision: 0 } },
          x: { grid: { display: false } }
        },
        plugins: { legend: { display: false } }
      }
    });
  }
};

// --- Aksi UI & Modal ---
const confirmDelete = (id) => {
  guestToDeleteId.value = id;
  showDeleteModal.value = true;
};

const deleteGuest = async () => {
  const token = localStorage.getItem('auth_token');
  try {
    await axios.delete(`${API_BASE_URL}/appointments/${guestToDeleteId.value}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    showDeleteModal.value = false;
    showSuccessModal.value = true;
    fetchDashboardSummary();
  } catch (error) {
    console.error('Gagal menghapus data:', error);
    showErrorModal.value = true;
  }
};

const showGuestDetail = (guest) => {
  selectedGuest.value = guest;
  showDetailModal.value = true;
};

const logout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user_role');
  localStorage.removeItem('user_division_id');
  router.push('/');
};

const showPendingAppointments = () => {
  searchQuery.value = 'pending';
  const tableElement = document.getElementById('appointments-table');
  if (tableElement) {
    tableElement.scrollIntoView({ behavior: 'smooth' });
  }
};

// --- Computed Properties ---
const filteredAppointments = computed(() => {
  if (!searchQuery.value) {
    return appointments.value;
  }
  const query = searchQuery.value.toLowerCase();
  return appointments.value.filter(appointment =>
    appointment.full_name?.toLowerCase().includes(query) ||
    appointment.phone_number?.toLowerCase().includes(query) ||
    appointment.institution?.toLowerCase().includes(query) ||
    appointment.status_pertemuan?.toLowerCase().includes(query)
  );
});

const getGuestPhotoUrl = computed(() => {
  if (selectedGuest.value?.face_photo_path) {
    return `http://127.0.0.1:8000/storage/${selectedGuest.value.face_photo_path}`;
  }
  return 'https://placehold.co/150x150/E2E8F0/475569?text=No+Photo';
});

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Lifecycle hook
onMounted(fetchDashboardSummary);
</script>

<template>
  <div class="min-h-screen bg-gray-50 font-sans antialiased text-gray-900">
    <!-- Header (Navbar) -->
    <header id="page-header" class="flex flex-none items-center bg-white shadow-sm sticky top-0 z-40">
      <div class="container mx-auto flex justify-between items-center py-3 px-4 lg:px-8">
        <div class="flex items-center gap-3">
            <img src="/src/assets/img/diskominfologo.png" alt="Logo Diskominfo" class="h-10 w-auto">
            <div class="hidden sm:block">
                 <h1 class="text-lg font-bold text-blue-700 tracking-wide">E-TAMU DISKOMINFO</h1>
                <p class="text-xs text-gray-500">Kabupaten Garut</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
          <!-- Tombol Manajemen (Hanya untuk Super Admin) -->
          <RouterLink v-if="userRole === 'super_admin'" to="/management" class="px-4 py-2 text-sm font-semibold text-white bg-gray-700 rounded-lg shadow-md hover:bg-gray-800 transition-all flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4s-4 1.79-4 4s1.79 4 4 4m0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4"/></svg>
            <span class="hidden sm:inline">Manajemen</span>
          </RouterLink>

          <div class="relative cursor-pointer" @click="showPendingAppointments">
            <svg class="h-6 w-6 text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M2.5 6A2.5 2.5 0 0 1 5 3.5h14A2.5 2.5 0 0 1 21.5 6v13.5a1 1 0 0 1-1 1h-17a1 1 0 0 1-1-1zm2 0a.5.5 0 0 0-.5.5v2.893l7.26 4.356a.5.5 0 0 0 .48 0L20 9.395V6.5a.5.5 0 0 0-.5-.5zm.5 4.707L4.5 17h15l-1-6.294zM12 11.5L4.5 6h15z"/></svg>
            <span v-if="pendingCount > 0" class="absolute top-0 right-0 inline-flex items-center justify-center size-5 text-xs font-bold text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
              {{ pendingCount }}
            </span>
          </div>

          <button @click="logout" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#fff" d="m17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5M4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4z"/></svg>
            <span class="hidden sm:inline">Logout</span>
          </button>

          <div class="lg:hidden">
            <button @click="isMobileNavOpen = !isMobileNavOpen" type="button" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:bg-gray-100">
              <svg fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" class="size-6"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
            </button>
          </div>
        </div>
      </div>
      <div v-if="isMobileNavOpen" class="lg:hidden absolute top-full left-0 w-full bg-white shadow-md">
        <nav class="flex flex-col gap-2 border-t border-gray-200 py-4 px-4">
          <RouterLink v-if="userRole === 'super_admin'" to="/management" @click="isMobileNavOpen = false" class="rounded-lg px-4 py-2 text-base font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700">Manajemen</RouterLink>
          <RouterLink to="/dashboard" @click="isMobileNavOpen = false" class="rounded-lg px-4 py-2 text-base font-medium text-gray-700 bg-blue-100 text-blue-700">Dashboard</RouterLink>
        </nav>
      </div>
    </header>

    <!-- Konten Utama -->
    <main id="page-content" class="container mx-auto flex w-full flex-auto flex-col p-4 sm:p-6 lg:p-8">
      <div v-if="isLoading" class="text-center py-20">
        <p class="text-gray-600">Memuat data dashboard...</p>
      </div>
      <div v-else>
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-800">Dashboard Admin</h1>
          <h2 class="text-md text-gray-500">Selamat datang, saat ini ada <strong>{{ totalGuests }}</strong> total janji temu terdaftar.</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="flex items-center p-5 bg-white rounded-xl shadow-md border border-gray-200">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8"><path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" clip-rule="evenodd" /></svg></div>
            <div class="ml-4"><p class="text-sm font-medium text-gray-500">Total Janji Temu</p><p class="text-2xl font-bold text-gray-800">{{ totalGuests }}</p></div>
          </div>
          <div class="flex items-center p-5 bg-white rounded-xl shadow-md border border-gray-200">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>
            <div class="ml-4"><p class="text-sm font-medium text-gray-500">Pending</p><p class="text-2xl font-bold text-gray-800">{{ statusStats.pending }}</p></div>
          </div>
          <div class="flex items-center p-5 bg-white rounded-xl shadow-md border border-gray-200">
            <div class="p-3 rounded-full bg-green-100 text-green-600"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>
            <div class="ml-4"><p class="text-sm font-medium text-gray-500">Selesai</p><p class="text-2xl font-bold text-gray-800">{{ statusStats.selesai }}</p></div>
          </div>
          <div class="flex items-center p-5 bg-white rounded-xl shadow-md border border-gray-200">
            <div class="p-3 rounded-full bg-red-100 text-red-600"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>
            <div class="ml-4"><p class="text-sm font-medium text-gray-500">Ditolak</p><p class="text-2xl font-bold text-gray-800">{{ statusStats.ditolak }}</p></div>
          </div>
        </div>

        <div v-if="userRole === 'super_admin'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div v-for="division in divisionVisitData" :key="division.name" class="flex items-center p-4 bg-white rounded-xl shadow-md border border-gray-200">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 15 15"><path fill="currentColor" fill-rule="evenodd" d="M12 2h1.5A1.5 1.5 0 0 1 15 3.5v10a1.5 1.5 0 0 1-1.5 1.5h-12A1.5 1.5 0 0 1 0 13.5v-10A1.5 1.5 0 0 1 1.5 2H3V0h1v2h7V0h1zM6 8H3V7h3zm6-1H9v1h3zm-6 4H3v-1h3zm3 0h3v-1H9z" clip-rule="evenodd"/></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ division.name }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ division.total }}</p>
                </div>
            </div>
            <div class="flex items-center p-5 bg-white rounded-xl shadow-md border border-gray-200">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8"><path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 5.69 3.117.75.75 0 0 1-.981 1.138A5.247 5.247 0 0 0 12 13.5a5.247 5.247 0 0 0-4.709 2.755.75.75 0 0 1-.98-1.138Z" clip-rule="evenodd" /></svg></div>
                <div class="ml-4"><p class="text-sm font-medium text-gray-500">Total Admin</p><p class="text-2xl font-bold text-gray-800">{{ admins }}</p></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-3 bg-white rounded-xl shadow-md p-6 border border-gray-200">
                <h2 class="text-lg font-bold text-gray-800">Data Kunjungan Bulanan</h2>
                <p class="text-sm text-gray-500 mb-4">Grafik jumlah janji temu yang dibuat setiap bulan.</p>
                <div class="relative h-80"><canvas id="myChart"></canvas></div>
            </div>

            <div id="appointments-table" class="lg:col-span-3 bg-white rounded-xl shadow-md border border-gray-200">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 border-b border-gray-200">
                    <div>
                        <h2 class="font-bold text-lg text-gray-800">Daftar Janji Temu</h2>
                        <p class="text-sm text-gray-500">Kelola semua janji temu yang terdaftar.</p>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative w-full sm:w-64">
                            <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3"><svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/></svg></div>
                            <input type="text" v-model="searchQuery" placeholder="Cari tamu atau status..." class="block w-full rounded-lg border border-gray-300 py-2 ps-10 pe-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50" />
                        </div>
                        <RouterLink :to="{ name: 'add-guest' }" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" /></svg>
                            <span>Walk-in</span>
                        </RouterLink>
                        <button @click="exportPDF" class="inline-flex items-center justify-center gap-2 px-3 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-green-700">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M18 7H6V3h12zM8 17h8v-2H8zm10-6H6v10h12z"/></svg>
                            <span>PDF</span>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-b-xl">
                    <table class="min-w-full text-sm align-middle">
                        <thead>
                            <tr class="bg-blue-50/70 text-blue-900"><th class="px-5 py-3 text-left text-xs font-semibold tracking-wide uppercase">Nama Lengkap</th><th class="px-5 py-3 text-left text-xs font-semibold tracking-wide uppercase hidden md:table-cell">Instansi</th><th class="px-5 py-3 text-left text-xs font-semibold tracking-wide uppercase hidden lg:table-cell">Divisi Tujuan</th><th class="px-5 py-3 text-center text-xs font-semibold tracking-wide uppercase">Status</th><th class="px-5 py-3 text-right text-xs font-semibold tracking-wide uppercase">Aksi</th></tr>
                        </thead>
                        <tbody>
                            <tr v-if="!filteredAppointments || filteredAppointments.length === 0"><td colspan="5" class="py-12 text-center text-gray-400 font-medium bg-gray-50">Tidak ada data janji temu.</td></tr>
                            <tr v-for="(appointment, idx) in filteredAppointments" :key="appointment.id" :class="['group transition-all duration-150', idx % 2 === 0 ? 'bg-white' : 'bg-gray-50', 'hover:bg-blue-50/50']">
                                <td class="px-5 py-4 font-medium text-gray-900 whitespace-nowrap">{{ appointment.full_name }}</td>
                                <td class="px-5 py-4 text-gray-700 whitespace-nowrap hidden md:table-cell">{{ appointment.institution }}</td>
                                <td class="px-5 py-4 text-gray-700 whitespace-nowrap hidden lg:table-cell">{{ appointment.division ? appointment.division.name : '-' }}</td>
                                <td class="px-5 py-4 text-center"><span class="px-3 py-0.5 text-xs font-medium rounded-full border" :class="{'bg-green-50 text-green-700 border-green-100': appointment.status_pertemuan?.toLowerCase() === 'selesai', 'bg-yellow-50 text-yellow-700 border-yellow-100': appointment.status_pertemuan?.toLowerCase() === 'pending', 'bg-red-50 text-red-700 border-red-100': appointment.status_pertemuan?.toLowerCase() === 'ditolak'}">{{ appointment.status_pertemuan }}</span></td>
                                <td class="px-5 py-4 text-right font-medium whitespace-nowrap"><div class="flex items-center justify-end gap-2"><button @click="showGuestDetail(appointment)" class="p-2 rounded-full text-blue-600 bg-blue-50 hover:bg-blue-100"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M12 17q.425 0 .713-.288T13 16v-4q0-.425-.288-.712T12 11t-.712.288T11 12v4q0 .425.288.713T12 17m0-8q.425 0 .713-.288T13 8t-.288-.712T12 7t-.712.288T11 8t.288.713T12 9m0 13q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788-3.9t-2.137 3.175t-3.175 2.138T12 22"/></svg></button><RouterLink :to="{ name: 'edit-guest', params: { id: appointment.id } }" class="inline-block p-2 rounded-full text-yellow-600 bg-yellow-50 hover:bg-yellow-100"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5"><path d="m2.695 14.762-1.262 3.155a.5.5 0 0 0 .65.65l3.155-1.262a4 4 0 0 0 1.343-.885L17.5 5.5a2.121 2.121 0 0 0-3-3L3.58 13.42a4 4 0 0 0-.885 1.343Z" /></svg></RouterLink><button @click="confirmDelete(appointment.id)" class="p-2 rounded-full text-red-600 bg-red-50 hover:bg-red-100"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.58.22-2.365.468a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193v-.443A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Z" clip-rule="evenodd" /></svg></button></div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </div>
    </main>

    <!-- Modals -->
    <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-auto">
            <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">Detail Janji Temu</h3>
            <div v-if="selectedGuest">
                <div class="flex justify-center mb-4"><img :src="getGuestPhotoUrl" alt="Foto Tamu" class="w-32 h-32 object-cover rounded-full shadow-lg border-4 border-gray-200"></div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><strong class="font-semibold text-gray-500">Nama:</strong> <span class="text-gray-800 text-right">{{ selectedGuest.full_name }}</span></div>
                    <div class="flex justify-between"><strong class="font-semibold text-gray-500">Nomor HP:</strong> <span class="text-gray-800 text-right">{{ selectedGuest.phone_number || '-' }}</span></div>
                    <div class="flex justify-between"><strong class="font-semibold text-gray-500">Instansi:</strong> <span class="text-gray-800 text-right">{{ selectedGuest.institution }}</span></div>
                    <div class="flex justify-between"><strong class="font-semibold text-gray-500">Tujuan:</strong> <span class="text-gray-800 text-right">{{ selectedGuest.purpose }}</span></div>
                    <div class="flex justify-between"><strong class="font-semibold text-gray-500">Tgl Pertemuan:</strong> <span class="text-gray-800 text-right">{{ formatDate(selectedGuest.appointment_date) }}</span></div>
                    <div class="flex justify-between"><strong class="font-semibold text-gray-500">Waktu:</strong> <span class="text-gray-800 text-right">{{ selectedGuest.appointment_time }}</span></div>
                    <div class="flex justify-between items-center"><strong class="font-semibold text-gray-500">Status:</strong> <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="{'bg-green-100 text-green-800': selectedGuest.status_pertemuan?.toLowerCase() === 'selesai', 'bg-yellow-100 text-yellow-800': selectedGuest.status_pertemuan?.toLowerCase() === 'pending', 'bg-red-100 text-red-800': selectedGuest.status_pertemuan?.toLowerCase() === 'ditolak'}">{{ selectedGuest.status_pertemuan }}</span></div>
                </div>
            </div>
            <div class="flex justify-end mt-6"><button @click="showDetailModal = false" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Tutup</button></div>
        </div>
    </div>
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm mx-auto"><h3 class="text-xl font-bold text-gray-900 mb-2">Konfirmasi Hapus</h3><p class="text-sm text-gray-600 mb-6">Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p><div class="flex justify-end space-x-3"><button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Batal</button><button @click="deleteGuest" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">Hapus</button></div></div>
    </div>
    <div v-if="showSuccessModal || showErrorModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm mx-auto"><div class="flex flex-col items-center text-center"><div v-if="showSuccessModal" class="p-3 rounded-full bg-green-100 text-green-600 mb-4"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.06-1.06L10.5 12.94 8.72 11.16a.75.75 0 1 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.06 0l4.5-4.5Z" clip-rule="evenodd" /></svg></div><div v-if="showErrorModal" class="p-3 rounded-full bg-red-100 text-red-600 mb-4"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd" /></svg></div><h3 class="text-xl font-bold text-gray-900 mb-2">{{ showSuccessModal ? 'Berhasil' : 'Error' }}</h3><p class="text-sm text-gray-600 mb-6">{{ showSuccessModal ? 'Data berhasil dihapus.' : 'Gagal menghapus data.' }}</p><button @click="showSuccessModal = false; showErrorModal = false" class="w-full px-4 py-2 text-sm font-semibold text-white rounded-lg" :class="showSuccessModal ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'">Tutup</button></div></div>
    </div>

    <!-- Footer -->
    <footer id="page-footer" class="flex-none bg-white mt-8 border-t border-gray-200">
      <div class="container mx-auto flex flex-col sm:flex-row items-center justify-between py-5 px-4 lg:px-8 text-sm text-gray-600">
        <div>© <span class="font-medium">E-Tamu Diskominfo Garut</span></div>
        <div class="text-center sm:text-right mt-2 sm:mt-0">Crafted By: <span class="font-bold text-blue-600">Kelompok 1 & 2</span></div>
      </div>
    </footer>
  </div>
</template>

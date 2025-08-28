import { createRouter, createWebHistory } from 'vue-router';
import InitialView from '../views/InitialView.vue';
import LoginView from '../views/LoginView.vue';
import DashboardView from '../views/DashboardView.vue';
import EditView from '../views/EditView.vue';
import KioskView from '../views/KioskView.vue';
import AddGuestView from '../views/AddGuestView.vue';
import AppointmentView from '../views/AppointmentView.vue';
import ManagementView from '../views/ManagementView.vue'; // 1. Impor halaman baru

// Penjaga rute umum (memastikan sudah login)
const requireAuth = (to, from, next) => {
  if (localStorage.getItem('auth_token')) {
    next();
  } else {
    next({ name: 'login' });
  }
};

// Penjaga rute khusus Super Admin
const requireSuperAdmin = (to, from, next) => {
    const userRole = localStorage.getItem('user_role');
    if (localStorage.getItem('auth_token') && userRole === 'super_admin') {
        next();
    } else {
        // Jika bukan super admin, arahkan ke dashboard biasa atau halaman login
        next({ name: 'dashboard' });
    }
};


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'initial',
      component: InitialView
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: DashboardView,
      beforeEnter: requireAuth,
    },
    {
      path: '/edit/:id',
      name: 'edit-guest',
      component: EditView,
      beforeEnter: requireAuth,
    },
    {
      path: '/kiosk',
      name: 'kiosk',
      component: KioskView
    },
    {
      path: '/add-guest',
      name: 'add-guest',
      component: AddGuestView,
      // Rute ini bisa diakses publik atau oleh admin, jadi requireAuth bisa opsional
    },
    {
      path: '/appointment',
      name: 'appointment',
      component: AppointmentView
    },
    // --- RUTE BARU UNTUK MANAJEMEN ---
    // 2. Tambahkan rute baru di sini
    {
      path: '/management',
      name: 'management',
      component: ManagementView,
      beforeEnter: requireSuperAdmin, // Gunakan penjaga khusus super admin
    },
    {
      path: '/register',
      name: 'register',
      component: AddGuestView
    }
  ]
});

export default router;

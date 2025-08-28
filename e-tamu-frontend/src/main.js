import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios' // 1. Impor axios di sini

// --- PERBAIKAN UNTUK ERROR 422 ---
// 2. Tambahkan Konfigurasi Global untuk Axios.
// Ini memberitahu "kurir" (Axios) untuk selalu meminta "struk digital" (respons JSON)
// dari "dapur" (backend). Ini memastikan kita selalu mendapatkan detail error
// yang jelas jika ada masalah validasi pada formulir.
axios.defaults.headers.common['Accept'] = 'application/json';


const app = createApp(App)

app.use(router)

app.mount('#app')

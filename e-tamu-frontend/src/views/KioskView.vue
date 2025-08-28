<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const video = ref(null);
const canvas = ref(null);
const stream = ref(null);
const statusMessage = ref('Posisikan wajah Anda di depan kamera...');
const guestName = ref('');
const showSuccess = ref(false);

// Fungsi untuk memulai kamera
const startCamera = async () => {
  try {
    stream.value = await navigator.mediaDevices.getUserMedia({ video: true });
    if (video.value) {
      video.value.srcObject = stream.value;
    }
  } catch (error) {
    console.error("Error mengakses kamera:", error);
    statusMessage.value = "Kamera tidak dapat diakses.";
  }
};

// Fungsi untuk mengambil gambar dan mengirim ke backend
const captureAndCheckIn = async () => {
  if (!video.value || !canvas.value) return;

  const context = canvas.value.getContext('2d');
  canvas.value.width = video.value.videoWidth;
  canvas.value.height = video.value.videoHeight;
  context.drawImage(video.value, 0, 0, canvas.value.width, canvas.value.height);

  canvas.value.toBlob(async (blob) => {
    const formData = new FormData();
    formData.append('image', blob, 'capture.jpg');

    try {
      statusMessage.value = "Memverifikasi...";
  const response = await axios.post('http://127.0.0.1:8000/api/check-in', formData);

      if (response.data.success) {
        guestName.value = response.data.name;
        statusMessage.value = `Selamat Datang, ${guestName.value}!`;
        showSuccess.value = true;
      } else {
        statusMessage.value = "Wajah tidak dikenali. Coba lagi.";
      }
    } catch (error) {
      console.error("Error saat check-in:", error);
      statusMessage.value = "Terjadi kesalahan saat verifikasi.";
    }
  }, 'image/jpeg');
};

// Saat komponen dimuat, mulai kamera
onMounted(() => {
  startCamera();
  // Set interval untuk check-in setiap 5 detik
  const intervalId = setInterval(captureAndCheckIn, 5000);
  onUnmounted(() => clearInterval(intervalId));
});

// Saat komponen dihancurkan, matikan kamera
onUnmounted(() => {
  if (stream.value) {
    stream.value.getTracks().forEach(track => track.stop());
  }
});
</script>

<template>
  <div class="relative flex items-center justify-center min-h-screen bg-black">
    <video ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
    <canvas ref="canvas" class="hidden"></canvas>

    <div class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-50">
      <div class="text-center text-white">
        <h1 v-if="!showSuccess" class="text-4xl font-bold">{{ statusMessage }}</h1>
        <div v-if="showSuccess" class="p-8 bg-green-500 rounded-lg">
          <h1 class="text-5xl font-bold">{{ statusMessage }}</h1>
          <p class="text-xl">Check-in berhasil.</p>
        </div>
      </div>
    </div>
  </div>
</template>

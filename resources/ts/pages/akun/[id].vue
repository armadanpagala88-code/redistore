<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const route = useRoute()
const router = useRouter()
const akunId = route.params.id as string

const akun = ref<any>(null)
const loading = ref(true)

const isImageDialogVisible = ref(false)
const fullScreenImage = ref('')

const openFullScreen = (imageUrl: string) => {
  if (!imageUrl) return
  fullScreenImage.value = imageUrl
  isImageDialogVisible.value = true
}

const activeImageIndex = ref(0)
const allImages = computed(() => {
  if (!akun.value) return []
  const imgs = []
  if (akun.value.gambar_utama) imgs.push(akun.value.gambar_utama)
  if (akun.value.gambar_lainnya && akun.value.gambar_lainnya.length > 0) {
    imgs.push(...akun.value.gambar_lainnya)
  }
  return imgs
})

// Checkout form state
const form = ref({
  tipe_transaksi: 'BeliAkun',
  akun_game_id: akunId,
  no_whatsapp: '',
  email_pembeli: ''
})

const isCheckingOut = ref(false)
const isStartingChat = ref(false)
const userData = ref<any>(null)

onMounted(async () => {
  // Try to get user data if logged in
  const userStr = localStorage.getItem('user_data')
  if (userStr) {
    try {
      userData.value = JSON.parse(userStr)
      form.value.no_whatsapp = userData.value.no_telepon || ''
      form.value.email_pembeli = userData.value.email || ''
    } catch (e) {}
  }

  try {
    const res = await axios.get(`/api/akun-games/${akunId}`)
    akun.value = res.data.data
  } catch (error: any) {
    console.error('Error fetching akun:', error)
    if (error.response?.status === 404) {
      alert('Akun game tidak ditemukan atau sudah tidak tersedia.')
      router.push('/')
    }
  } finally {
    loading.value = false
  }
})

const getAkunImage = (path: string) => {
  if (!path) return ''
  return `/img/akun/${path}`
}

const getImageUrl = (path: string) => {
  if (!path) return 'https://placehold.co/800x600/f1f5f9/94a3b8.png?text=Image'
  return path.startsWith('http') ? path : `/images/${path}`
}

const formatRupiah = (angka: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka)
}

const isWhatsAppValid = computed(() => {
  return form.value.no_whatsapp && form.value.no_whatsapp.length >= 10
})

const submitCheckout = async () => {
  if (!isWhatsAppValid.value) {
    alert('Nomor WhatsApp tidak valid')
    return
  }

  isCheckingOut.value = true
  try {
    const res = await axios.post('/api/checkout', form.value)
    if (res.data.success) {
      router.push(`/invoice/${res.data.data.id}`)
    }
  } catch (e: any) {
    if (e.response && e.response.data && e.response.data.message) {
      alert(e.response.data.message)
    } else {
      alert('Gagal membuat pesanan')
    }
  } finally {
    isCheckingOut.value = false
  }
}

const startChat = async () => {
  if (!userData.value) {
    alert('Silakan login terlebih dahulu untuk memulai chat')
    router.push('/login')
    return
  }
  
  if (userData.value.id === akun.value.user_id) {
    alert('Anda tidak bisa chat dengan akun milik Anda sendiri')
    return
  }

  isStartingChat.value = true
  try {
    const res = await axios.post('/api/chat/start', { akun_game_id: akun.value.id })
    if (res.data.success) {
      router.push(`/member/chat/${res.data.data.id}`)
    }
  } catch (e: any) {
    alert('Gagal memulai obrolan: ' + (e.response?.data?.message || 'Error'))
  } finally {
    isStartingChat.value = false
  }
}
</script>

<template>
  <VContainer class="py-8" style="max-width: 1000px;">
    <VRow v-if="loading">
      <VCol cols="12" class="text-center mt-12">
        <VProgressCircular indeterminate color="primary" size="64" width="5" />
      </VCol>
    </VRow>

    <template v-else-if="akun">
      <!-- Back Button -->
      <VBtn variant="text" prepend-icon="ri-arrow-left-line" to="/" class="mb-4 text-high-emphasis">
        Kembali ke Katalog
      </VBtn>

      <VRow>
        <VCol cols="12" md="7">
          <VCard elevation="5" class="rounded-lg overflow-hidden mb-4 bg-grey-lighten-4">
            <template v-if="allImages.length > 0">
              <VCarousel v-model="activeImageIndex" height="400" hide-delimiters show-arrows="hover">
                <VCarouselItem v-for="(img, idx) in allImages" :key="idx">
                  <div @click="openFullScreen(getAkunImage(img))" class="cursor-pointer h-100 w-100 position-relative">
                    <VImg :src="getAkunImage(img)" height="400" class="bg-grey-lighten-4">
                      <template #placeholder>
                        <div class="d-flex align-center justify-center h-100 w-100">
                          <VProgressCircular indeterminate color="primary" />
                        </div>
                      </template>
                    </VImg>
                  </div>
                </VCarouselItem>
              </VCarousel>
            </template>
            <template v-else>
              <div @click="openFullScreen(getImageUrl(akun.kategori?.gambar_logo))" class="cursor-pointer h-100 w-100 position-relative">
                <VImg :src="getImageUrl(akun.kategori?.gambar_logo)" height="400" class="bg-grey-lighten-4">
                  <template #placeholder>
                    <div class="d-flex align-center justify-center h-100 w-100">
                      <VProgressCircular indeterminate color="primary" />
                    </div>
                  </template>
                </VImg>
              </div>
            </template>
          </VCard>

          <!-- Thumbnails Row -->
          <VRow dense v-if="allImages.length > 1" class="mb-6">
            <VCol v-for="(img, idx) in allImages" :key="'thumb-'+idx" cols="3">
              <VCard 
                @click="activeImageIndex = idx" 
                :elevation="activeImageIndex === idx ? 4 : 1"
                :class="['cursor-pointer rounded-lg overflow-hidden', activeImageIndex === idx ? 'border-primary' : '']"
                style="height: 80px;"
              >
                <VImg :src="getAkunImage(img)" height="100%" cover class="bg-grey-lighten-4" :style="activeImageIndex !== idx ? 'opacity: 0.6;' : ''" />
              </VCard>
            </VCol>
          </VRow>

          <VCard elevation="3" class="rounded-lg border-t-primary mb-6">
            <VCardText class="pa-6">
              <VChip color="primary" size="small" variant="elevated" class="mb-4 font-weight-bold">
                {{ akun.kategori?.nama_game }}
              </VChip>
              <h1 class="text-h4 font-weight-bold text-high-emphasis mb-2" style="line-height: 1.3;">{{ akun.judul_akun }}</h1>
              
              <div class="d-flex align-center gap-4 mb-6">
                <div class="d-flex align-center gap-2 text-medium-emphasis">
                  <VIcon icon="ri-login-box-line" />
                  <span>Login Via: <strong>{{ akun.login_via }}</strong></span>
                </div>
                <div class="d-flex align-center gap-2 text-medium-emphasis">
                  <VIcon icon="ri-store-2-line" />
                  <span>Penjual: <strong>{{ akun.penjual?.username || 'Member' }}</strong></span>
                </div>
                
                <VBtn
                  v-if="akun.penjual"
                  color="info"
                  variant="tonal"
                  size="small"
                  class="ml-auto"
                  prepend-icon="ri-chat-3-line"
                  :loading="isStartingChat"
                  @click="startChat"
                >
                  Chat Penjual
                </VBtn>
              </div>

              <VDivider class="mb-6" />

              <h3 class="text-h6 font-weight-bold mb-3 d-flex align-center gap-2">
                <VIcon icon="ri-file-text-line" color="primary" /> Deskripsi Akun
              </h3>
              <div class="text-body-1 text-high-emphasis" style="line-height: 1.8; white-space: pre-wrap;">
                {{ akun.deskripsi_akun }}
              </div>

              <template v-if="akun.catatan_akun">
                <VDivider class="my-6" />
                <h3 class="text-h6 font-weight-bold mb-3 d-flex align-center gap-2">
                  <VIcon icon="ri-sticky-note-line" color="warning" /> Catatan Penjual
                </h3>
                <VAlert type="warning" variant="tonal" class="text-body-2">
                  {{ akun.catatan_akun }}
                </VAlert>
              </template>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Right Column: Checkout -->
        <VCol cols="12" md="5">
          <VCard elevation="10" class="rounded-xl overflow-hidden checkout-card sticky-card">
            <div class="bg-primary pa-6 text-white text-center position-relative overflow-hidden">
              <div class="position-absolute bg-white opacity-10 rounded-circle" style="width: 150px; height: 150px; top: -50px; right: -50px;"></div>
              <h3 class="text-h6 font-weight-regular mb-1 opacity-90">Harga Akun</h3>
              <div class="text-h3 font-weight-black">{{ formatRupiah(akun.harga) }}</div>
            </div>

            <VCardText class="pa-6">
              <VAlert type="info" variant="tonal" class="mb-6 text-body-2" icon="ri-shield-check-line">
                <strong>Transaksi Aman 100%!</strong> Dana Anda ditahan oleh sistem dan baru diteruskan ke penjual setelah akun game berhasil dikirim dan diverifikasi.
              </VAlert>

              <VForm @submit.prevent="submitCheckout">
                <div class="mb-4">
                  <label class="text-subtitle-2 font-weight-bold text-high-emphasis mb-2 d-block">No. WhatsApp <span class="text-error">*</span></label>
                  <VTextField
                    v-model="form.no_whatsapp"
                    placeholder="Contoh: 08123456789"
                    variant="outlined"
                    density="comfortable"
                    color="primary"
                    prepend-inner-icon="ri-whatsapp-line"
                    hint="Penting! Detail pesanan & login akun akan dikirim ke WA ini"
                    persistent-hint
                    :rules="[v => !!v || 'WhatsApp wajib diisi']"
                  />
                </div>

                <div class="mb-6">
                  <label class="text-subtitle-2 font-weight-bold text-high-emphasis mb-2 d-block">Alamat Email (Opsional)</label>
                  <VTextField
                    v-model="form.email_pembeli"
                    type="email"
                    placeholder="Untuk struk pembayaran"
                    variant="outlined"
                    density="comfortable"
                    color="primary"
                    prepend-inner-icon="ri-mail-line"
                  />
                </div>

                <VBtn
                  type="submit"
                  color="primary"
                  size="x-large"
                  block
                  class="rounded-pill font-weight-bold checkout-btn text-h6"
                  :loading="isCheckingOut"
                  :disabled="!isWhatsAppValid"
                  elevation="4"
                >
                  <VIcon icon="ri-shopping-cart-2-line" class="mr-2" />
                  Beli Sekarang
                </VBtn>
                
                <p class="text-center text-caption text-medium-emphasis mt-4 mb-0">
                  Dengan mengklik Beli Sekarang, Anda menyetujui syarat & ketentuan layanan.
                </p>
              </VForm>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
      <!-- Full Screen Image Dialog -->
      <VDialog v-model="isImageDialogVisible" max-width="90vw">
        <VCard color="transparent" class="elevation-0 d-flex justify-center align-center position-relative">
          <VBtn 
            icon="ri-close-line" 
            variant="flat" 
            color="error" 
            class="position-absolute" 
            style="top: 0; right: 0; z-index: 100;" 
            @click="isImageDialogVisible = false" 
          />
          <img :src="fullScreenImage" style="max-width: 100%; max-height: 90vh; object-fit: contain; border-radius: 8px;" />
        </VCard>
      </VDialog>
    </template>
  </VContainer>
</template>

<style scoped>
.z-index-highest {
  z-index: 9999 !important;
}
.border-t-primary {
  border-top: 4px solid rgb(var(--v-theme-primary)) !important;
}
.cursor-pointer {
  cursor: pointer;
}
.cursor-pointer:hover {
  opacity: 0.9;
  transition: opacity 0.2s ease;
}
.checkout-card {
  border: 1px solid rgba(var(--v-theme-primary), 0.1);
}

.border-primary {
  border: 2px solid rgb(var(--v-theme-primary)) !important;
}

.checkout-btn {
  letter-spacing: 0.5px;
  text-transform: none;
  height: 56px !important;
}

.sticky-card {
  position: sticky;
  top: 100px;
}
</style>

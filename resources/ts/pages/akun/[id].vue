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
  return `/images/akun/${path}`
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
        <!-- Left Column: Image & Details -->
        <VCol cols="12" md="7">
          <VCard elevation="5" class="rounded-lg overflow-hidden mb-6">
            <VImg :src="akun.gambar_utama ? getAkunImage(akun.gambar_utama) : getImageUrl(akun.kategori?.gambar_logo)" height="400" cover class="bg-grey-lighten-4" />
          </VCard>

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
    </template>
  </VContainer>
</template>

<style scoped>
.border-t-primary {
  border-top: 5px solid rgb(var(--v-theme-primary)) !important;
}

.checkout-card {
  border: 1px solid rgba(var(--v-theme-primary), 0.1);
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

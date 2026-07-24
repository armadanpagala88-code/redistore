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
const category = ref<any>(null)
const ulasans = ref<any[]>([])
const loading = ref(true)

const form = ref({
  user_id_game: '',
  produk_voucher_id: null,
  no_whatsapp: '',
  nama_pembeli: '',
  email_pembeli: '',
  kode_voucher: ''
})

const promoState = ref({
  loading: false,
  applied: false,
  message: '',
  error: false,
  nominal_diskon: 0
})

onMounted(async () => {
  try {
    const slug = route.params.slug
    const response = await axios.get(`/api/kategori-game/${slug}`)
    category.value = response.data.data
    
    // Fetch Ulasan untuk game ini
    if (category.value && category.value.id) {
      const ulasanRes = await axios.get(`/api/public/ulasan/kategori/${category.value.id}`)
      ulasans.value = ulasanRes.data.data
    }
  } catch (error) {
    console.error('Error fetching game details:', error)
  } finally {
    loading.value = false
  }
})

const getImageUrl = (path: string) => {
  if (!path) return 'https://placehold.co/1200x400?text=Game+Banner'
  return path.startsWith('http') ? path : `/images/${path}`
}

const selectProduct = (id: number) => {
  form.value.produk_voucher_id = id as any
  if (promoState.value.applied) {
    promoState.value.applied = false
    promoState.value.message = 'Promo direset karena ganti nominal'
    promoState.value.error = true
    promoState.value.nominal_diskon = 0
  }
}

const selectedProductDetails = computed(() => {
  if (!category.value || !form.value.produk_voucher_id) return null
  return category.value.produks.find((p: any) => p.id === form.value.produk_voucher_id)
})

const checkPromo = async () => {
  if (!form.value.kode_voucher) {
    promoState.value.message = 'Masukkan kode voucher terlebih dahulu'
    promoState.value.error = true
    return
  }
  if (!selectedProductDetails.value) {
    promoState.value.message = 'Pilih nominal top up terlebih dahulu'
    promoState.value.error = true
    return
  }

  promoState.value.loading = true
  promoState.value.message = ''
  promoState.value.error = false

  try {
    const res = await axios.post('/api/promo/check', {
      kode_voucher: form.value.kode_voucher,
      total_bayar: selectedProductDetails.value.harga_jual
    })

    if (res.data.success) {
      promoState.value.applied = true
      promoState.value.error = false
      promoState.value.message = `Berhasil! Diskon Rp ${Number(res.data.data.nominal_diskon).toLocaleString('id-ID')} diterapkan.`
      promoState.value.nominal_diskon = res.data.data.nominal_diskon
    }
  } catch (error: any) {
    promoState.value.applied = false
    promoState.value.error = true
    promoState.value.message = error.response?.data?.message || 'Kode promo tidak valid'
    promoState.value.nominal_diskon = 0
  } finally {
    promoState.value.loading = false
  }
}

const removePromo = () => {
  form.value.kode_voucher = ''
  promoState.value.applied = false
  promoState.value.error = false
  promoState.value.message = ''
  promoState.value.nominal_diskon = 0
}

const formatRupiah = (angka: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka)
}

const checkout = async () => {
  if (!form.value.user_id_game || !form.value.produk_voucher_id || !form.value.no_whatsapp) {
    alert('Harap lengkapi User ID, Nominal Top Up, dan No WhatsApp')
    return
  }
  
  try {
    const payload = { ...form.value }
    if (!promoState.value.applied) {
      payload.kode_voucher = ''
    }

    const response = await axios.post('/api/checkout', payload)
    if (response.data.success) {
      router.push(`/invoice/${response.data.data.id}`)
    }
  } catch (error) {
    console.error('Checkout error:', error)
    alert('Gagal membuat pesanan. Silakan coba lagi.')
  }
}
</script>

<template>
  <div class="checkout-wrapper w-100" style="margin-top: -48px;">
    <VRow v-if="loading" class="mt-16 pt-16">
      <VCol cols="12" class="text-center">
        <VProgressCircular indeterminate color="primary" size="64" />
      </VCol>
    </VRow>

    <template v-else-if="category">
      <!-- Massive Hero Banner -->
      <div class="checkout-hero position-relative">
        <div class="hero-bg" :style="`background-image: url('${getImageUrl(category.gambar_logo)}')`"></div>
        <div class="hero-overlay"></div>
        
        <VContainer class="hero-content h-100 d-flex align-end pb-16" style="max-width: 1200px;">
          <div>
            <VBtn variant="tonal" color="primary" size="small" class="mb-4" to="/" prepend-icon="ri-arrow-left-line">
              Kembali
            </VBtn>
            <div class="d-flex align-center gap-4">
              <VAvatar size="80" rounded="lg" class="elevation-10 border-2" style="border-color: rgba(0,0,0,0.1) !important;">
                <VImg :src="getImageUrl(category.gambar_logo)" cover />
              </VAvatar>
              <div>
                <h1 class="text-h3 font-weight-bold text-high-emphasis mb-1 text-shadow">{{ category.nama_game }}</h1>
                <p class="text-subtitle-1 text-medium-emphasis text-shadow-sm mb-0">Proses Cepat & Otomatis 24 Jam</p>
              </div>
            </div>
          </div>
        </VContainer>
      </div>

      <!-- Main Form Section with Negative Margin -->
      <VContainer class="form-container position-relative z-1" style="max-width: 1200px; margin-top: -60px;">
        <VRow>
          <!-- Left Column: Instructions -->
          <VCol cols="12" md="4">
            <VCard elevation="10" class="glass-card mb-6 sticky-card border-primary-top">
              <VCardItem class="pb-2">
                <VCardTitle class="text-h6 font-weight-bold d-flex align-center gap-2">
                  <VIcon icon="ri-information-line" color="primary" />
                  Cara Top Up
                </VCardTitle>
              </VCardItem>
              <VCardText>
                <p class="text-body-2 text-medium-emphasis mb-4">{{ category.deskripsi }}</p>
                <div class="step-list">
                  <div class="step-item d-flex gap-3 mb-3">
                    <VAvatar color="primary" variant="tonal" size="28" class="font-weight-bold mt-1">1</VAvatar>
                    <div class="text-body-2">Masukkan User ID.</div>
                  </div>
                  <div class="step-item d-flex gap-3 mb-3">
                    <VAvatar color="primary" variant="tonal" size="28" class="font-weight-bold mt-1">2</VAvatar>
                    <div class="text-body-2">Pilih Nominal Top Up yang diinginkan.</div>
                  </div>
                  <div class="step-item d-flex gap-3 mb-3">
                    <VAvatar color="primary" variant="tonal" size="28" class="font-weight-bold mt-1">3</VAvatar>
                    <div class="text-body-2">Masukkan Nomor WhatsApp aktif.</div>
                  </div>
                  <div class="step-item d-flex gap-3 mb-3">
                    <VAvatar color="primary" variant="tonal" size="28" class="font-weight-bold mt-1">4</VAvatar>
                    <div class="text-body-2">Gunakan Voucher (opsional) lalu klik Beli Sekarang.</div>
                  </div>
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <!-- Right Column: Forms -->
          <VCol cols="12" md="8">
            <!-- Step 1: User ID -->
            <VCard elevation="10" class="glass-card mb-6 border-primary-left">
              <VCardItem class="bg-grey-lighten-4 pa-4">
                <VCardTitle class="d-flex align-center gap-3 text-h6 font-weight-bold">
                  <VAvatar color="primary" size="36" class="text-white font-weight-bold elevation-4">1</VAvatar>
                  Masukkan Data Akun
                </VCardTitle>
              </VCardItem>
              <VCardText class="pa-6">
                <VRow>
                  <VCol cols="12">
                    <VTextField
                      v-model="form.user_id_game"
                      label="User ID"
                      placeholder="Contoh: 12345678"
                      variant="outlined"
                      color="primary"
                      required
                      hide-details="auto"
                      class="custom-input"
                    />
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <!-- Step 2: Nominal -->
            <VCard elevation="10" class="glass-card mb-6 border-primary-left">
              <VCardItem class="bg-grey-lighten-4 pa-4">
                <VCardTitle class="d-flex align-center gap-3 text-h6 font-weight-bold">
                  <VAvatar color="primary" size="36" class="text-white font-weight-bold elevation-4">2</VAvatar>
                  Pilih Nominal Top Up
                </VCardTitle>
              </VCardItem>
              <VCardText class="pa-6">
                <VRow v-if="category.produks && category.produks.length > 0">
                  <VCol
                    v-for="prod in category.produks"
                    :key="prod.id"
                    cols="6"
                    md="4"
                  >
                    <VCard
                      variant="outlined"
                      class="nominal-card cursor-pointer text-center pa-4 transition-swing h-100 d-flex flex-column justify-center"
                      :class="{ 'selected-card': form.produk_voucher_id === prod.id }"
                      @click="selectProduct(prod.id)"
                    >
                      <div class="selected-badge" v-if="form.produk_voucher_id === prod.id">
                        <VIcon icon="ri-check-line" color="white" size="16" />
                      </div>
                      <div class="text-subtitle-1 font-weight-bold mb-1">{{ prod.nominal }}</div>
                      <div class="text-primary font-weight-bold mt-auto">{{ formatRupiah(prod.harga_jual) }}</div>
                    </VCard>
                  </VCol>
                </VRow>
                <VAlert v-else type="info" variant="tonal">
                  Produk belum tersedia untuk game ini.
                </VAlert>
              </VCardText>
            </VCard>

            <!-- Step 3: Checkout -->
            <VCard elevation="10" class="glass-card mb-12 border-primary-left">
              <VCardItem class="bg-grey-lighten-4 pa-4">
                <VCardTitle class="d-flex align-center gap-3 text-h6 font-weight-bold">
                  <VAvatar color="primary" size="36" class="text-white font-weight-bold elevation-4">3</VAvatar>
                  Beli Sekarang
                </VCardTitle>
              </VCardItem>
              <VCardText class="pa-6">
                <VTextField
                  v-model="form.no_whatsapp"
                  label="Nomor WhatsApp (Aktif)"
                  placeholder="Contoh: 08123456789"
                  variant="outlined"
                  color="primary"
                  class="mb-4 custom-input"
                  required
                  prepend-inner-icon="ri-whatsapp-line"
                />

                <VTextField
                  v-model="form.nama_pembeli"
                  label="Nama Lengkap (Opsional)"
                  placeholder="Masukkan nama Anda"
                  variant="outlined"
                  color="primary"
                  class="mb-4 custom-input"
                  prepend-inner-icon="ri-user-line"
                />

                <VTextField
                  v-model="form.email_pembeli"
                  label="Alamat Email (Opsional)"
                  placeholder="Untuk pengiriman struk bukti pembayaran"
                  variant="outlined"
                  color="primary"
                  class="mb-6 custom-input"
                  prepend-inner-icon="ri-mail-line"
                  type="email"
                />

                <!-- Promo Code Section -->
                <div class="promo-box mb-6 p-4 rounded-lg pa-5">
                  <div class="d-flex align-center gap-2 mb-3">
                    <VIcon icon="ri-coupon-3-fill" color="warning" />
                    <span class="text-subtitle-2 font-weight-bold">Punya Kode Voucher?</span>
                  </div>
                  <div class="d-flex gap-3">
                    <VTextField
                      v-model="form.kode_voucher"
                      placeholder="Masukkan Kode"
                      variant="outlined"
                      density="compact"
                      hide-details
                      bg-color="surface"
                      :disabled="promoState.applied"
                      class="promo-input"
                    />
                    <VBtn 
                      v-if="!promoState.applied"
                      color="primary" 
                      variant="elevated" 
                      @click="checkPromo" 
                      :loading="promoState.loading"
                      class="px-6 font-weight-bold"
                    >
                      Klaim
                    </VBtn>
                    <VBtn 
                      v-else
                      color="error" 
                      variant="tonal" 
                      @click="removePromo"
                      class="px-6 font-weight-bold"
                    >
                      Batal
                    </VBtn>
                  </div>
                  <div v-if="promoState.message" class="mt-3 text-caption font-weight-bold" :class="promoState.error ? 'text-error' : 'text-success'">
                    <VIcon :icon="promoState.error ? 'ri-error-warning-line' : 'ri-check-line'" size="16" class="mr-1 mb-1" />
                    {{ promoState.message }}
                  </div>
                </div>

                <!-- Ringkasan Harga -->
                <div v-if="selectedProductDetails" class="summary-box mb-6 pa-5 rounded-lg border">
                  <div class="text-subtitle-2 text-medium-emphasis mb-3 text-uppercase font-weight-bold">Ringkasan Pembayaran</div>
                  
                  <div class="d-flex justify-space-between align-center mb-2">
                    <span class="text-body-1">{{ selectedProductDetails.nominal }}</span>
                    <span class="font-weight-medium">{{ formatRupiah(selectedProductDetails.harga_jual) }}</span>
                  </div>
                  
                  <div v-if="promoState.applied" class="d-flex justify-space-between align-center mb-2 text-success">
                    <span class="text-body-1">Diskon Voucher</span>
                    <span class="font-weight-bold">- {{ formatRupiah(promoState.nominal_diskon) }}</span>
                  </div>
                  
                  <VDivider class="my-3 border-opacity-25" />
                  
                  <div class="d-flex justify-space-between align-end">
                    <span class="text-h6 font-weight-bold">Total Bayar</span>
                    <span class="text-h4 font-weight-black text-primary">{{ formatRupiah(selectedProductDetails.harga_jual - promoState.nominal_diskon) }}</span>
                  </div>
                </div>

                <VBtn
                  color="primary"
                  size="x-large"
                  block
                  elevation="8"
                  @click="checkout"
                  class="font-weight-bold text-h6 rounded-pill py-3"
                  style="height: 60px;"
                >
                  <VIcon start icon="ri-shopping-cart-2-line" class="mr-2" />
                  Konfirmasi Pembelian
                </VBtn>
              </VCardText>
            </VCard>

          </VCol>
        </VRow>
        
        <!-- Section Ulasan Pelanggan -->
        <VRow v-if="ulasans.length > 0" class="mt-8">
          <VCol cols="12">
            <h3 class="text-h5 font-weight-bold d-flex align-center gap-2 mb-4">
              <VIcon icon="ri-chat-smile-2-fill" color="primary" />
              Ulasan Pelanggan
            </h3>
            
            <VRow>
              <VCol v-for="ulasan in ulasans" :key="ulasan.id" cols="12" md="6">
                <VCard elevation="1" class="rounded-lg pa-4 h-100">
                  <div class="d-flex align-center mb-3">
                    <VAvatar color="primary" size="40" class="mr-3 font-weight-bold">
                      {{ ulasan.user?.nama_lengkap?.charAt(0) || 'A' }}
                    </VAvatar>
                    <div>
                      <div class="font-weight-bold text-subtitle-1">{{ ulasan.user?.nama_lengkap || 'Anonim' }}</div>
                      <div class="text-caption text-medium-emphasis">{{ new Date(ulasan.created_at).toLocaleDateString('id-ID') }}</div>
                    </div>
                    <VSpacer />
                    <VChip size="small" color="success" variant="elevated" class="font-weight-bold">
                      <VIcon start icon="ri-star-fill" size="small" /> {{ ulasan.rating }}
                    </VChip>
                  </div>
                  
                  <VChip size="small" variant="tonal" color="primary" class="mb-2">
                    Beli: {{ ulasan.produk_voucher?.nama_produk }}
                  </VChip>
                  
                  <p class="text-body-1 mb-0">"{{ ulasan.komentar }}"</p>
                </VCard>
              </VCol>
            </VRow>
          </VCol>
        </VRow>
        
      </VContainer>
    </template>
  </div>
</template>

<style scoped>
/* Hero Banner */
.checkout-hero {
  height: 400px;
  width: 100%;
}

.hero-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center 20%;
  filter: blur(2px);
}

/* Hero Section */
.hero-section {
  position: relative;
  overflow: hidden;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to bottom, rgba(248, 250, 252, 0.4) 0%, rgba(248, 250, 252, 0.9) 70%, rgba(248, 250, 252, 1) 100%);
}
/* Layout */
.game-detail-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* Background & Typography */
.text-shadow {
  text-shadow: none;
}

.text-high-emphasis {
  color: #1e293b !important;
}

.text-medium-emphasis {
  color: #475569 !important;
}

/* Cards */
.glass-card {
  background: #ffffff !important;
  border: 1px solid rgba(0, 0, 0, 0.05);
  box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important;
  border-radius: 16px !important;
  overflow: hidden;
  transition: all 0.3s ease;
}

.glass-card:hover {
  box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important;
}

.border-primary-top {
  border-top: 4px solid rgb(var(--v-theme-primary)) !important;
}

.border-primary-left {
  border-left: 4px solid rgb(var(--v-theme-primary)) !important;
}

.sticky-card {
  position: sticky;
  top: 100px;
}

/* Nominal Cards */
.nominal-card {
  border: 2px solid transparent !important;
  border-radius: 12px !important;
  background: #f8fafc !important;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
}

.nominal-card:hover {
  border-color: rgba(var(--v-theme-primary), 0.3) !important;
  background: #ffffff !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
}

.selected-card {
  border-color: rgb(var(--v-theme-primary)) !important;
  background: rgba(var(--v-theme-primary), 0.05) !important;
}

.selected-badge {
  position: absolute;
  top: 0;
  right: 0;
  background: rgb(var(--v-theme-primary));
  width: 30px;
  height: 30px;
  border-bottom-left-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Inputs & Boxes */
.custom-input {
  --v-input-control-bg: #f1f5f9;
}

.promo-box {
  background: #f8fafc;
  border: 1px dashed rgba(var(--v-theme-warning), 0.5);
}

.promo-input {
  border-radius: 6px;
}

.summary-box {
  background: rgba(var(--v-theme-primary), 0.05);
  border: 1px solid rgba(var(--v-theme-primary), 0.2) !important;
}

.z-1 {
  z-index: 1;
}
</style>

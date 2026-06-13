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
const loading = ref(true)

const form = ref({
  user_id_game: '',
  zone_id: '',
  produk_voucher_id: null,
  no_whatsapp: '',
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
  } catch (error) {
    console.error('Error fetching game details:', error)
  } finally {
    loading.value = false
  }
})

const selectProduct = (id: number) => {
  form.value.produk_voucher_id = id as any
  // Reset promo if product changed
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
      payload.kode_voucher = '' // Jangan kirim kode jika belum applied
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
  <div>
    <VRow v-if="loading">
      <VCol cols="12" class="text-center mt-12">
        <VProgressCircular indeterminate color="primary" size="64" />
      </VCol>
    </VRow>

    <VRow v-else-if="category">
      <!-- Info Game -->
      <VCol cols="12" md="4">
        <VCard elevation="3" class="mb-6 sticky-card">
          <VImg
            :src="category.gambar_logo ? `/images/${category.gambar_logo}` : 'https://placehold.co/400x200?text=Game'"
            height="200"
            cover
          />
          <VCardItem>
            <VCardTitle class="text-h5 font-weight-bold">{{ category.nama_game }}</VCardTitle>
            <VCardSubtitle class="mt-2">{{ category.deskripsi }}</VCardSubtitle>
          </VCardItem>
          <VCardText>
            <p class="text-body-2 mb-0">Cara Top Up:</p>
            <ol class="text-body-2 pl-4">
              <li>Masukkan User ID (dan Zone ID bila ada)</li>
              <li>Pilih Nominal Top Up</li>
              <li>Masukkan Nomor WhatsApp</li>
              <li>Klik Beli Sekarang</li>
            </ol>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Form Transaksi -->
      <VCol cols="12" md="8">
        <!-- Step 1: User ID -->
        <VCard elevation="3" class="mb-6">
          <VCardItem>
            <VCardTitle class="d-flex align-center gap-2">
              <VAvatar color="primary" size="32" class="text-white font-weight-bold">1</VAvatar>
              Masukkan Data Akun
            </VCardTitle>
          </VCardItem>
          <VCardText>
            <VRow>
              <VCol cols="12" sm="6">
                <VTextField
                  v-model="form.user_id_game"
                  label="User ID"
                  placeholder="Contoh: 12345678"
                  variant="outlined"
                  required
                />
              </VCol>
              <VCol cols="12" sm="6">
                <VTextField
                  v-model="form.zone_id"
                  label="Zone ID / Server"
                  placeholder="Contoh: 1234 (Opsional)"
                  variant="outlined"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>

        <!-- Step 2: Nominal -->
        <VCard elevation="3" class="mb-6">
          <VCardItem>
            <VCardTitle class="d-flex align-center gap-2">
              <VAvatar color="primary" size="32" class="text-white font-weight-bold">2</VAvatar>
              Pilih Nominal Top Up
            </VCardTitle>
          </VCardItem>
          <VCardText>
            <VRow v-if="category.produks && category.produks.length > 0">
              <VCol
                v-for="prod in category.produks"
                :key="prod.id"
                cols="6"
                sm="4"
              >
                <VCard
                  variant="outlined"
                  class="cursor-pointer text-center pa-4 transition-swing"
                  :class="{ 'border-primary bg-primary-lighten-5': form.produk_voucher_id === prod.id }"
                  @click="selectProduct(prod.id)"
                  style="border-width: 2px !important;"
                >
                  <div class="text-subtitle-1 font-weight-bold mb-1">{{ prod.nominal }}</div>
                  <div class="text-primary font-weight-bold">Rp {{ Number(prod.harga_jual).toLocaleString('id-ID') }}</div>
                </VCard>
              </VCol>
            </VRow>
            <VAlert v-else type="info" variant="tonal">
              Produk belum tersedia untuk game ini.
            </VAlert>
          </VCardText>
        </VCard>

        <!-- Step 3: Checkout -->
        <VCard elevation="3">
          <VCardItem>
            <VCardTitle class="d-flex align-center gap-2">
              <VAvatar color="primary" size="32" class="text-white font-weight-bold">3</VAvatar>
              Konfirmasi Pembelian
            </VCardTitle>
          </VCardItem>
          <VCardText>
            <VTextField
              v-model="form.no_whatsapp"
              label="Nomor WhatsApp"
              placeholder="Contoh: 08123456789"
              variant="outlined"
              class="mb-6"
              required
            />

            <!-- Promo Code Section -->
            <div class="mb-6 p-4 border rounded pa-4 bg-grey-lighten-4">
              <div class="text-subtitle-2 font-weight-bold mb-2">Punya Kode Voucher / Promo?</div>
              <div class="d-flex gap-4">
                <VTextField
                  v-model="form.kode_voucher"
                  placeholder="Masukkan Kode"
                  variant="outlined"
                  density="compact"
                  hide-details
                  :disabled="promoState.applied"
                />
                <VBtn 
                  v-if="!promoState.applied"
                  color="primary" 
                  variant="tonal" 
                  @click="checkPromo" 
                  :loading="promoState.loading"
                >
                  Gunakan
                </VBtn>
                <VBtn 
                  v-else
                  color="error" 
                  variant="tonal" 
                  @click="removePromo"
                >
                  Batal
                </VBtn>
              </div>
              <div v-if="promoState.message" class="mt-2 text-caption font-weight-bold" :class="promoState.error ? 'text-error' : 'text-success'">
                <VIcon :icon="promoState.error ? 'ri-error-warning-line' : 'ri-check-line'" size="14" class="mr-1" />
                {{ promoState.message }}
              </div>
            </div>

            <!-- Ringkasan Harga -->
            <div v-if="selectedProductDetails" class="mb-6">
              <div class="d-flex justify-space-between mb-1">
                <span>Harga Normal</span>
                <span class="font-weight-medium">{{ formatRupiah(selectedProductDetails.harga_jual) }}</span>
              </div>
              <div v-if="promoState.applied" class="d-flex justify-space-between mb-1 text-success">
                <span>Diskon Voucher</span>
                <span class="font-weight-medium">- {{ formatRupiah(promoState.nominal_diskon) }}</span>
              </div>
              <VDivider class="my-2" />
              <div class="d-flex justify-space-between text-h6 font-weight-bold">
                <span>Total Bayar</span>
                <span class="text-primary">{{ formatRupiah(selectedProductDetails.harga_jual - promoState.nominal_diskon) }}</span>
              </div>
            </div>

            <VBtn
              color="primary"
              size="x-large"
              block
              elevation="2"
              @click="checkout"
            >
              Beli Sekarang
            </VBtn>
          </VCardText>
        </VCard>

      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.sticky-card {
  position: sticky;
  top: 24px;
}
.cursor-pointer {
  cursor: pointer;
}
.border-primary {
  border-color: rgb(var(--v-theme-primary)) !important;
}
.bg-primary-lighten-5 {
  background-color: rgba(var(--v-theme-primary), 0.05);
}
</style>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

definePage({
  meta: {
    layout: 'frontend',
    public: false,
  },
})

const router = useRouter()
const user = ref<any>(null)
const mutasi = ref<any[]>([])
const transaksi = ref<any[]>([])
const chats = ref<any[]>([])
const loading = ref(true)

const fetchDashboard = async () => {
  try {
    const [dashRes, chatRes] = await Promise.all([
      axios.get('/api/member/dashboard'),
      axios.get('/api/chat/conversations')
    ])
    user.value = dashRes.data.data.user
    mutasi.value = dashRes.data.data.mutasi_terakhir
    transaksi.value = dashRes.data.data.transaksi_terakhir
    chats.value = chatRes.data.data.slice(0, 5) // Ambil 5 terbaru
  } catch (error) {
    console.error(error)
    router.push('/login')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboard()
})

const formatRupiah = (angka: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka)
}

const formatDate = (dateString: string) => {
  const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit' }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}

const getOtherParty = (conv: any) => {
  if (!user.value) return 'Unknown'
  return conv.buyer_id === user.value.id ? conv.seller?.username : conv.buyer?.username
}

const getRole = (conv: any) => {
  if (!user.value) return ''
  return conv.buyer_id === user.value.id ? 'Penjual' : 'Pembeli'
}

const isRedeemDialogVisible = ref(false)
const redeemLoading = ref(false)
const redeemAmount = ref(100)

const doRedeemPoints = async () => {
  if (redeemAmount.value < 100) {
    alert('Minimal penukaran adalah 100 Poin')
    return
  }
  if (user.value.poin < redeemAmount.value) {
    alert('Poin Anda tidak cukup')
    return
  }
  
  redeemLoading.value = true
  try {
    const res = await axios.post('/api/member/redeem-points', { points: redeemAmount.value })
    alert(res.data.message)
    isRedeemDialogVisible.value = false
    fetchDashboard()
  } catch (e: any) {
    if (e.response && e.response.data && e.response.data.message) {
      alert(e.response.data.message)
    } else {
      alert('Gagal menukar poin')
    }
  } finally {
    redeemLoading.value = false
  }
}

const photoInput = ref<HTMLInputElement | null>(null)
const uploadLoading = ref(false)

const handlePhotoUpload = async (event: any) => {
  const file = event.target.files[0]
  if (!file) return

  const formData = new FormData()
  formData.append('foto', file)

  uploadLoading.value = true
  try {
    const res = await axios.post('/api/member/update-photo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    if (res.data.success) {
      alert(res.data.message)
      fetchDashboard() // Refresh data
    }
  } catch (e: any) {
    if (e.response && e.response.data && e.response.data.message) {
      alert(e.response.data.message)
    } else {
      alert('Gagal mengupload foto profil')
    }
  } finally {
    uploadLoading.value = false
    if (photoInput.value) {
      photoInput.value.value = '' // Reset input
    }
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

    <div v-else>
      <VRow>
        <VCol cols="12" md="4">
          <!-- Profile Card -->
          <VCard elevation="3" class="mb-6 text-center pa-4">
            <div class="position-relative d-inline-block mx-auto mb-3">
              <VAvatar size="100" color="primary" variant="tonal" class="elevation-2">
                <VImg v-if="user.foto_profil" :src="`/uploads/profil/${user.foto_profil}`" cover />
                <span v-else class="text-h3 font-weight-bold">{{ user.nama_lengkap?.charAt(0) }}</span>
              </VAvatar>
              <VBtn 
                icon="ri-camera-lens-line" 
                size="small" 
                color="primary" 
                variant="elevated" 
                class="position-absolute" 
                style="bottom: 0; right: -10px; z-index: 2;"
                :loading="uploadLoading"
                @click="photoInput?.click()"
              />
              <input 
                type="file" 
                ref="photoInput" 
                accept="image/*" 
                class="d-none" 
                @change="handlePhotoUpload"
              />
            </div>
            <h3 class="text-h5 font-weight-bold mb-1">{{ user.nama_lengkap }}</h3>
            <p class="text-medium-emphasis mb-4">{{ user.no_telepon }}</p>

            <VChip color="info" size="small" class="mb-4 text-uppercase font-weight-bold">
              Level: {{ user.level }}
            </VChip>

            <VCard variant="tonal" color="success" class="pa-4 text-left mb-4">
              <div class="text-subtitle-2 mb-1">Saldo Pendapatan</div>
              <div class="d-flex justify-space-between align-center">
                <div class="text-h4 font-weight-bold">{{ formatRupiah(user.saldo) }}</div>
                <VBtn size="small" color="success" variant="elevated" to="/member/withdrawals">Tarik</VBtn>
              </div>
              <div class="text-caption mt-2 opacity-80">
                Hasil penjualan akun Anda. Bisa ditarik ke bank/e-wallet.
              </div>
            </VCard>

            <VCard variant="tonal" color="warning" class="pa-4 text-left mb-4 d-flex justify-space-between align-center">
              <div>
                <div class="text-subtitle-2 mb-1">Loyalty Points</div>
                <div class="text-h5 font-weight-bold d-flex align-center gap-2">
                  <VIcon icon="ri-copper-coin-line" color="warning" />
                  {{ user.poin }} Pts
                </div>
              </div>
              <VBtn size="small" color="warning" variant="elevated" @click="isRedeemDialogVisible = true">Tukar</VBtn>
            </VCard>

            <VBtn block color="primary" variant="elevated" class="mb-3" to="/member/jual-akun" prepend-icon="ri-store-2-line">
              Mulai Jual Akun Game
            </VBtn>
            
            <VBtn block color="secondary" variant="tonal" class="mb-3" to="/member/tickets" prepend-icon="ri-customer-service-2-line">
              Pusat Bantuan (Tiket)
            </VBtn>
          </VCard>
        </VCol>

        <VCol cols="12" md="8">
          <!-- Riwayat Transaksi -->
          <VCard elevation="3" class="mb-6">
            <VCardTitle class="px-4 pt-4 pb-2 text-h6 font-weight-bold">Riwayat Pembelian Terakhir</VCardTitle>
            <VTable hover v-if="transaksi.length > 0">
              <thead>
                <tr>
                  <th>Waktu</th>
                  <th>ID Transaksi</th>
                  <th>Game</th>
                  <th class="text-right">Total</th>
                  <th class="text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in transaksi" :key="item.id" @click="router.push(`/invoice/${item.id}`)" style="cursor: pointer;">
                  <td class="text-body-2">{{ formatDate(item.created_at) }}</td>
                  <td class="font-weight-bold text-primary">{{ item.id }}</td>
                  <td>{{ item.details[0]?.nama_game }} - {{ item.details[0]?.nama_produk }}</td>
                  <td class="text-right font-weight-bold">Rp {{ Number(item.total_bayar).toLocaleString('id-ID') }}</td>
                  <td class="text-center">
                    <VChip :color="item.status_transaksi === 'Success' ? 'success' : (item.status_transaksi === 'Unpaid' ? 'error' : 'warning')" size="small">
                      {{ item.status_transaksi }}
                    </VChip>
                  </td>
                </tr>
              </tbody>
            </VTable>
            <VCardText v-else class="text-center text-medium-emphasis py-6">
              Belum ada riwayat pembelian.
            </VCardText>
          </VCard>

          <!-- Riwayat Mutasi Saldo -->
          <VCard elevation="3">
            <VCardTitle class="px-4 pt-4 pb-2 text-h6 font-weight-bold">Mutasi Saldo</VCardTitle>
            <VTable hover v-if="mutasi.length > 0">
              <thead>
                <tr>
                  <th>Waktu</th>
                  <th>Tipe</th>
                  <th class="text-right">Nominal</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in mutasi" :key="item.id">
                  <td class="text-body-2">{{ formatDate(item.created_at) }}</td>
                  <td>
                    <VChip :color="item.tipe === 'Masuk' ? 'success' : 'error'" size="small">
                      {{ item.tipe }}
                    </VChip>
                  </td>
                  <td class="text-right font-weight-bold" :class="item.tipe === 'Masuk' ? 'text-success' : 'text-error'">
                    {{ item.tipe === 'Masuk' ? '+' : '-' }} {{ formatRupiah(item.nominal) }}
                  </td>
                  <td>{{ item.keterangan }}</td>
                </tr>
              </tbody>
            </VTable>
            <VCardText v-else class="text-center text-medium-emphasis py-6">
              Belum ada mutasi saldo.
            </VCardText>
          </VCard>

          <!-- Riwayat Obrolan / Chat -->
          <VCard elevation="3" class="mt-6 mb-6">
            <div class="px-4 pt-4 pb-2 d-flex justify-space-between align-center">
              <VCardTitle class="text-h6 font-weight-bold pa-0">Riwayat Chat Terbaru</VCardTitle>
              <VBtn size="small" variant="text" color="primary" to="/member/chat" append-icon="ri-arrow-right-line">
                Lihat Semua
              </VBtn>
            </div>
            <VList v-if="chats.length > 0" lines="two" class="bg-transparent">
              <template v-for="(conv, index) in chats" :key="conv.id">
                <VListItem @click="router.push(`/member/chat/${conv.id}`)" class="py-3 cursor-pointer">
                  <template #prepend>
                    <VAvatar color="info" variant="tonal" size="48">
                      <VIcon icon="ri-chat-3-line" size="24" />
                    </VAvatar>
                  </template>

                  <VListItemTitle class="font-weight-bold text-subtitle-1 mb-1">
                    {{ getOtherParty(conv) }}
                    <VChip size="x-small" :color="getRole(conv) === 'Penjual' ? 'warning' : 'info'" class="ml-2">
                      {{ getRole(conv) }}
                    </VChip>
                  </VListItemTitle>
                  
                  <VListItemSubtitle class="text-medium-emphasis">
                    <div class="d-flex align-center gap-1">
                      <VIcon icon="ri-gamepad-line" size="small" />
                      {{ conv.akun_game?.judul_akun || 'Akun Game Terhapus' }}
                    </div>
                  </VListItemSubtitle>

                  <template #append>
                    <div class="text-caption text-medium-emphasis">
                      {{ new Date(conv.updated_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) }}
                    </div>
                  </template>
                </VListItem>
                <VDivider v-if="index < chats.length - 1" />
              </template>
            </VList>
            <VCardText v-else class="text-center text-medium-emphasis py-6">
              <VIcon icon="ri-chat-1-line" size="32" class="opacity-50 mb-2" />
              <p class="mb-0">Belum ada riwayat obrolan.</p>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </div>

    <!-- Dialog Tukar Poin -->
    <VDialog v-model="isRedeemDialogVisible" max-width="400">
      <VCard>
        <VCardTitle class="bg-warning text-white">Tukar Loyalty Points</VCardTitle>
        <VCardText class="pt-6">
          <div class="text-center mb-6">
            <VIcon icon="ri-exchange-dollar-line" size="64" color="warning" />
            <div class="text-h6 mt-2">Poin Anda: <strong>{{ user?.poin }}</strong></div>
            <div class="text-body-2 text-medium-emphasis">1 Poin = Rp 10</div>
          </div>

          <VTextField
            v-model.number="redeemAmount"
            label="Jumlah poin yang ingin ditukar"
            type="number"
            min="100"
            hint="Minimal 100 poin"
            persistent-hint
          />
          
          <VAlert v-if="redeemAmount >= 100" type="info" variant="tonal" class="mt-4">
            Anda akan mendapatkan Saldo sebesar: <strong>{{ formatRupiah(redeemAmount * 10) }}</strong>
          </VAlert>
        </VCardText>
        <VCardActions class="px-6 pb-6">
          <VSpacer />
          <VBtn variant="outlined" color="secondary" @click="isRedeemDialogVisible = false">Batal</VBtn>
          <VBtn color="warning" variant="elevated" :loading="redeemLoading" @click="doRedeemPoints">Tukar Sekarang</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.cursor-pointer {
  cursor: pointer;
  transition: background-color 0.2s;
}
.cursor-pointer:hover {
  background-color: rgba(var(--v-theme-primary), 0.05);
}
</style>

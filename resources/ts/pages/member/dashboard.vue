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
const loading = ref(true)

const fetchDashboard = async () => {
  try {
    const res = await axios.get('/api/member/dashboard')
    user.value = res.data.data.user
    mutasi.value = res.data.data.mutasi_terakhir
    transaksi.value = res.data.data.transaksi_terakhir
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
            <VAvatar size="80" color="primary" variant="tonal" class="mb-3 mx-auto">
              <span class="text-h4">{{ user.nama_lengkap?.charAt(0) }}</span>
            </VAvatar>
            <h3 class="text-h5 font-weight-bold mb-1">{{ user.nama_lengkap }}</h3>
            <p class="text-medium-emphasis mb-4">{{ user.no_telepon }}</p>

            <VChip color="info" size="small" class="mb-4 text-uppercase font-weight-bold">
              Level: {{ user.level }}
            </VChip>

            <VCard variant="tonal" color="success" class="pa-4 text-left">
              <div class="text-subtitle-2 mb-1">Saldo Wallet</div>
              <div class="text-h4 font-weight-bold">{{ formatRupiah(user.saldo) }}</div>
            </VCard>

            <VBtn block color="primary" class="mt-4" prepend-icon="ri-wallet-3-line">
              Top Up Saldo
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
        </VCol>
      </VRow>
    </div>
  </div>
</template>

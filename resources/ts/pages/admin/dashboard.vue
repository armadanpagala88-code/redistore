<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'

const router = useRouter()
const vuetifyTheme = useTheme()
const transactions = ref<any[]>([])
const loading = ref(true)

const stats = ref({
  total_pendapatan: 0,
  transaksi_sukses: 0,
  transaksi_pending: 0,
  chart_data: [] as any[]
})

const fetchStats = async () => {
  try {
    const res = await axios.get('/api/admin/dashboard-stats')
    stats.value = res.data.data
  } catch (error) {
    console.error(error)
  }
}

const fetchTransactions = async () => {
  try {
    const res = await axios.get('/api/admin/transaksi')
    transactions.value = res.data.data
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (!localStorage.getItem('admin_token')) {
    router.push('/login')
    return
  }
  fetchStats()
  fetchTransactions()
})

const updateStatus = async (id: string, status: string) => {
  if (!confirm(`Yakin ingin mengubah status pesanan ini menjadi ${status}?`)) return
  try {
    await axios.put(`/api/admin/transaksi/${id}/status`, { status })
    fetchTransactions()
    fetchStats()
  } catch (e) {
    alert('Gagal mengubah status')
  }
}

const formatRupiah = (angka: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka)
}

const chartOptions = computed(() => {
  return {
    chart: {
      type: 'bar',
      toolbar: { show: false },
    },
    colors: [vuetifyTheme.current.value.colors.primary],
    plotOptions: {
      bar: {
        borderRadius: 4,
        columnWidth: '50%',
      }
    },
    dataLabels: { enabled: false },
    xaxis: {
      categories: stats.value.chart_data.map((d: any) => d.date),
      labels: { show: false }
    },
    yaxis: {
      labels: {
        formatter: (value: number) => 'Rp ' + (value / 1000) + 'k'
      }
    },
    tooltip: {
      y: {
        formatter: (val: number) => formatRupiah(val)
      }
    }
  }
})

const chartSeries = computed(() => {
  return [{
    name: 'Pendapatan',
    data: stats.value.chart_data.map((d: any) => d.total)
  }]
})
</script>

<template>
  <div class="pa-4">
    <div class="mb-6">
      <h2 class="text-h4 font-weight-bold">Dashboard Statistik</h2>
      <p class="text-body-1 text-medium-emphasis">Ringkasan performa penjualan Redistore</p>
    </div>

    <VRow class="mb-6">
      <VCol cols="12" md="4">
        <VCard elevation="3" class="pa-4">
          <div class="d-flex justify-space-between align-center">
            <div>
              <div class="text-subtitle-1 text-medium-emphasis">Total Pendapatan (Sukses)</div>
              <div class="text-h4 font-weight-bold text-success mt-1">{{ formatRupiah(stats.total_pendapatan) }}</div>
            </div>
            <VAvatar color="success" variant="tonal" size="54" rounded>
              <VIcon icon="ri-money-dollar-circle-line" size="32" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard elevation="3" class="pa-4">
          <div class="d-flex justify-space-between align-center">
            <div>
              <div class="text-subtitle-1 text-medium-emphasis">Transaksi Sukses</div>
              <div class="text-h4 font-weight-bold text-primary mt-1">{{ stats.transaksi_sukses }} <span class="text-h6 font-weight-regular text-medium-emphasis">trx</span></div>
            </div>
            <VAvatar color="primary" variant="tonal" size="54" rounded>
              <VIcon icon="ri-check-double-line" size="32" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard elevation="3" class="pa-4">
          <div class="d-flex justify-space-between align-center">
            <div>
              <div class="text-subtitle-1 text-medium-emphasis">Menunggu Pembayaran / Validasi</div>
              <div class="text-h4 font-weight-bold text-warning mt-1">{{ stats.transaksi_pending }} <span class="text-h6 font-weight-regular text-medium-emphasis">trx</span></div>
            </div>
            <VAvatar color="warning" variant="tonal" size="54" rounded>
              <VIcon icon="ri-time-line" size="32" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>
    </VRow>

    <VCard elevation="3" class="mb-8 pa-4">
      <VCardTitle class="px-0 pt-0">Grafik Penjualan (30 Hari Terakhir)</VCardTitle>
      <div style="height: 300px;">
        <VueApexCharts
          v-if="stats.chart_data.length > 0"
          type="bar"
          height="300"
          :options="chartOptions"
          :series="chartSeries"
        />
      </div>
    </VCard>

    <h3 class="text-h5 font-weight-bold mb-4">Transaksi Terbaru</h3>
    <VCard elevation="3">
      <VCardText v-if="loading" class="text-center pa-6">
        <VProgressCircular indeterminate color="primary" />
      </VCardText>
      
      <VTable v-else hover>
        <thead>
          <tr>
            <th class="text-uppercase">ID Transaksi</th>
            <th class="text-uppercase">Tanggal</th>
            <th class="text-uppercase">Produk</th>
            <th class="text-uppercase">User ID</th>
            <th class="text-uppercase">No. WA</th>
            <th class="text-uppercase text-right">Total Bayar</th>
            <th class="text-uppercase text-center">Status</th>
            <th class="text-uppercase text-center">Bukti</th>
            <th class="text-uppercase text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="trx in transactions" :key="trx.id">
            <td class="font-weight-medium">{{ trx.id }}</td>
            <td>{{ new Date(trx.tgl_transaksi).toLocaleString('id-ID') }}</td>
            <td>
              <div v-for="detail in trx.details" :key="detail.id">
                {{ detail.nama_produk }} ({{ detail.nama_game }})
              </div>
            </td>
            <td>{{ trx.user_id_game }} {{ trx.zone_id ? `(${trx.zone_id})` : '' }}</td>
            <td>{{ trx.no_whatsapp }}</td>
            <td class="text-right font-weight-bold">{{ formatRupiah(trx.total_bayar) }}</td>
            <td class="text-center">
              <VChip
                :color="trx.status_transaksi === 'Success' ? 'success' : (trx.status_transaksi === 'Pending' ? 'warning' : 'error')"
                size="small"
              >
                {{ trx.status_transaksi }}
              </VChip>
            </td>
            <td class="text-center">
              <a v-if="trx.bukti_pembayaran" :href="`/uploads/bukti/${trx.bukti_pembayaran}`" target="_blank" class="text-primary text-decoration-none">
                Lihat
              </a>
              <span v-else class="text-medium-emphasis">-</span>
            </td>
            <td class="text-center">
              <div class="d-flex gap-2 justify-center" v-if="trx.status_transaksi === 'Pending'">
                <VBtn size="small" color="success" variant="elevated" @click="updateStatus(trx.id, 'Success')">Terima</VBtn>
                <VBtn size="small" color="error" variant="elevated" @click="updateStatus(trx.id, 'Failed')">Tolak</VBtn>
              </div>
              <span v-else class="text-medium-emphasis">-</span>
            </td>
          </tr>
          <tr v-if="transactions.length === 0">
            <td colspan="9" class="text-center pa-4">Belum ada transaksi</td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Dialog Pratinjau Foto -->
    <VDialog v-model="isPreviewOpen" max-width="500">
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center">
          Bukti Pembayaran
          <VBtn icon="ri-close-line" variant="text" size="small" @click="isPreviewOpen = false" />
        </VCardTitle>
        <VCardText class="pa-0">
          <VImg :src="currentProof" cover />
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

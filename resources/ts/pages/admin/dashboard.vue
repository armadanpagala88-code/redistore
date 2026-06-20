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
      labels: { show: true }
    },
    yaxis: {
      decimalsInFloat: 0,
      labels: {
        formatter: (value: number) => {
          if (value >= 1000000) return 'Rp ' + +(value / 1000000).toFixed(1) + ' Jt'
          if (value >= 1000) return 'Rp ' + +(value / 1000).toFixed(1) + ' Rb'
          return 'Rp ' + Math.round(value)
        }
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
    <div class="mb-6 d-flex align-center gap-3">
      <VAvatar color="primary" variant="tonal" rounded="lg" size="48">
        <VIcon icon="ri-dashboard-3-line" size="28" />
      </VAvatar>
      <div>
        <h2 class="text-h4 font-weight-bold">Dashboard Statistik</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Ringkasan performa penjualan dan pesanan Redistore</p>
      </div>
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

    <VCard elevation="6" class="mb-8 rounded-lg border-t-primary overflow-hidden">
      <VCardTitle class="px-6 pt-6 pb-2 text-h6 font-weight-bold d-flex align-center gap-2">
        <VIcon icon="ri-bar-chart-box-line" color="primary" />
        Grafik Penjualan (Tahun Ini)
      </VCardTitle>
      <div style="height: 300px;" class="px-4 pb-4">
        <VueApexCharts
          v-if="stats.chart_data.length > 0"
          type="bar"
          height="300"
          :options="chartOptions"
          :series="chartSeries"
        />
      </div>
    </VCard>

    <div class="d-flex align-center gap-2 mb-4">
      <VIcon icon="ri-history-line" color="primary" size="24" />
      <h3 class="text-h5 font-weight-bold mb-0">Transaksi Terbaru</h3>
    </div>
    <VCard elevation="6" class="rounded-lg border-t-primary overflow-hidden">
      <VCardText v-if="loading" class="text-center pa-10">
        <VProgressCircular indeterminate color="primary" size="48" width="4" />
        <div class="mt-4 text-medium-emphasis font-weight-medium">Memuat transaksi terbaru...</div>
      </VCardText>
      
      <VTable v-else hover class="custom-table text-no-wrap">
        <thead class="bg-grey-lighten-4">
          <tr>
            <th class="text-uppercase text-caption font-weight-bold">ID Transaksi</th>
            <th class="text-uppercase text-caption font-weight-bold">Tanggal</th>
            <th class="text-uppercase text-caption font-weight-bold">Produk</th>
            <th class="text-uppercase text-caption font-weight-bold">User ID</th>
            <th class="text-uppercase text-caption font-weight-bold">No. WA</th>
            <th class="text-uppercase text-caption font-weight-bold text-right">Total Bayar</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Status</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Bukti</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="trx in transactions" :key="trx.id" class="transition-swing">
            <td class="font-weight-medium py-3">{{ trx.id }}</td>
            <td>
              <div class="font-weight-medium">{{ new Date(trx.tgl_transaksi).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) }}</div>
              <div class="text-caption text-medium-emphasis">{{ new Date(trx.tgl_transaksi).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) }}</div>
            </td>
            <td>
              <div v-for="detail in trx.details" :key="detail.id">
                <div class="font-weight-bold">{{ detail.nama_produk }}</div>
                <div class="text-caption text-medium-emphasis">{{ detail.nama_game }}</div>
              </div>
            </td>
            <td>
              <div class="font-weight-medium">{{ trx.user_id_game }}</div>
              <div class="text-caption text-medium-emphasis" v-if="trx.zone_id">Zone: {{ trx.zone_id }}</div>
            </td>
            <td>
              <div class="d-flex align-center gap-1">
                <VIcon icon="ri-whatsapp-line" size="14" class="text-success" />
                {{ trx.no_whatsapp }}
              </div>
            </td>
            <td class="text-right font-weight-bold text-primary">{{ formatRupiah(trx.total_bayar) }}</td>
            <td class="text-center">
              <VChip
                :color="trx.status_transaksi === 'Success' ? 'success' : (trx.status_transaksi === 'Pending' ? 'warning' : 'error')"
                size="small"
                variant="elevated"
                class="font-weight-bold"
              >
                {{ trx.status_transaksi }}
              </VChip>
            </td>
            <td class="text-center">
              <VBtn v-if="trx.bukti_pembayaran" :href="`/uploads/bukti/${trx.bukti_pembayaran}`" target="_blank" size="small" variant="tonal" color="info" icon="ri-image-line" />
              <span v-else class="text-medium-emphasis">-</span>
            </td>
            <td class="text-center">
              <div class="d-flex gap-2 justify-center" v-if="trx.status_transaksi === 'Pending'">
                <VBtn size="small" color="success" variant="elevated" @click="updateStatus(trx.id, 'Success')" icon="ri-check-line" />
                <VBtn size="small" color="error" variant="elevated" @click="updateStatus(trx.id, 'Failed')" icon="ri-close-line" />
              </div>
              <span v-else class="text-medium-emphasis">-</span>
            </td>
          </tr>
          <tr v-if="transactions.length === 0">
            <td colspan="9" class="text-center pa-8">
              <VIcon icon="ri-inbox-line" size="48" color="grey-lighten-1" class="mb-3" />
              <div class="text-h6 text-medium-emphasis">Belum ada transaksi</div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>
</template>

<style scoped>
.border-t-primary {
  border-top: 4px solid rgb(var(--v-theme-primary)) !important;
}
.custom-table tbody tr:hover {
  background-color: rgba(var(--v-theme-primary), 0.03) !important;
}
</style>

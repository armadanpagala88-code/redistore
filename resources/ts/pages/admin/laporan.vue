<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

const laporan = ref<any[]>([])
const loading = ref(false)
const filter = ref({
  start_date: '',
  end_date: ''
})

const fetchLaporan = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/admin/laporan', { params: filter.value })
    laporan.value = res.data.data
  } catch (error) {
    console.error('Failed to fetch laporan', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  // Set default to current month
  const date = new Date()
  filter.value.start_date = new Date(date.getFullYear(), date.getMonth(), 1).toISOString().substring(0, 10)
  filter.value.end_date = new Date(date.getFullYear(), date.getMonth() + 1, 0).toISOString().substring(0, 10)
  
  fetchLaporan()
})

const formatRupiah = (angka: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka)
}

const hitungTotal = () => {
  return laporan.value.reduce((total, item) => total + Number(item.total_bayar), 0)
}

const hitungTotalDiskon = () => {
  return laporan.value.reduce((total, item) => total + Number(item.nominal_diskon), 0)
}

const printLaporan = () => {
  window.print()
}
</script>

<template>
  <div class="pa-4">
    <!-- Header: Hide when printing -->
    <div class="d-flex flex-column flex-md-row justify-space-between align-md-center mb-6 gap-4 d-print-none">
      <div>
        <h2 class="text-h4 font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-bar-chart-box-line" color="primary" />
          Laporan Penjualan
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0 mt-1">Filter dan cetak laporan transaksi sukses berdasarkan periode tanggal.</p>
      </div>
    </div>

    <!-- Filter Card: Hide when printing -->
    <VCard elevation="10" class="mb-6 d-print-none rounded-lg border-t-primary">
      <VCardText class="pa-6">
        <VForm @submit.prevent="fetchLaporan">
          <VRow align="center">
            <VCol cols="12" md="4">
              <VTextField
                v-model="filter.start_date"
                label="Tanggal Awal"
                type="date"
                variant="outlined"
                density="comfortable"
                prepend-inner-icon="ri-calendar-line"
                hide-details
              />
            </VCol>
            <VCol cols="12" md="4">
              <VTextField
                v-model="filter.end_date"
                label="Tanggal Akhir"
                type="date"
                variant="outlined"
                density="comfortable"
                prepend-inner-icon="ri-calendar-check-line"
                hide-details
              />
            </VCol>
            <VCol cols="12" md="4" class="d-flex gap-3">
              <VBtn color="primary" type="submit" :loading="loading" variant="elevated" class="rounded-lg font-weight-bold flex-grow-1">
                <VIcon start icon="ri-search-line" /> Filter
              </VBtn>
              <VBtn color="secondary" variant="tonal" @click="printLaporan" :disabled="laporan.length === 0" class="rounded-lg font-weight-bold flex-grow-1">
                <VIcon start icon="ri-printer-line" /> Cetak PDF
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>

    <!-- Print Header: Show ONLY when printing -->
    <div class="d-none d-print-block text-center mb-6">
      <h2 class="text-h4 font-weight-bold mb-2">REDISTORE</h2>
      <h3 class="text-h5">Laporan Penjualan</h3>
      <p>Periode: {{ filter.start_date }} s.d {{ filter.end_date }}</p>
      <hr class="mt-4 mb-4">
    </div>

    <!-- Report Table -->
    <VCard elevation="10" class="print-card rounded-lg overflow-hidden border-t-primary">
      <VCardText v-if="loading" class="text-center pa-10 d-print-none">
        <VProgressCircular indeterminate color="primary" size="48" width="4" />
        <div class="mt-4 text-medium-emphasis font-weight-medium">Menganalisa data laporan...</div>
      </VCardText>
      
      <VTable v-else hover class="laporan-table custom-table text-no-wrap">
        <thead class="bg-grey-lighten-4">
          <tr>
            <th class="text-left text-caption font-weight-bold">No</th>
            <th class="text-left text-caption font-weight-bold">ID Transaksi</th>
            <th class="text-left text-caption font-weight-bold">Pelanggan</th>
            <th class="text-left text-caption font-weight-bold">Tanggal</th>
            <th class="text-left text-caption font-weight-bold">Produk & Game</th>
            <th class="text-right text-caption font-weight-bold">Harga Jual</th>
            <th class="text-right text-caption font-weight-bold">Diskon Promo</th>
            <th class="text-right text-caption font-weight-bold">Total Diterima</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(trx, index) in laporan" :key="trx.id" class="transition-swing">
            <td class="font-weight-medium text-medium-emphasis">{{ index + 1 }}</td>
            <td class="font-weight-medium">{{ trx.id }}</td>
            <td>
              <div class="font-weight-bold">{{ trx.user ? trx.user.nama_lengkap : 'Tamu (Guest)' }}</div>
              <div class="text-caption text-medium-emphasis">{{ trx.user ? '@' + trx.user.username : trx.no_whatsapp }}</div>
            </td>
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
            <td class="text-right font-weight-medium">{{ formatRupiah(Number(trx.total_bayar) + Number(trx.nominal_diskon)) }}</td>
            <td class="text-right text-error font-weight-bold">{{ trx.nominal_diskon > 0 ? '-' + formatRupiah(trx.nominal_diskon) : '-' }}</td>
            <td class="text-right font-weight-bold text-primary">{{ formatRupiah(trx.total_bayar) }}</td>
          </tr>
          <tr v-if="laporan.length === 0">
            <td colspan="8" class="text-center pa-8">
              <div class="d-print-none">
                <VIcon icon="ri-folder-open-line" size="48" color="grey-lighten-1" class="mb-3" />
                <div class="text-h6 text-medium-emphasis">Tidak ada data laporan pada periode ini.</div>
              </div>
              <div class="d-none d-print-block">Tidak ada data laporan pada periode ini.</div>
            </td>
          </tr>
        </tbody>
        <tfoot v-if="laporan.length > 0" class="bg-grey-lighten-4">
          <tr>
            <td colspan="5" class="text-right font-weight-bold text-body-1 py-4">TOTAL KESELURUHAN:</td>
            <td class="text-right font-weight-bold text-body-1 py-4">{{ formatRupiah(hitungTotal() + hitungTotalDiskon()) }}</td>
            <td class="text-right font-weight-bold text-body-1 text-error py-4">-{{ formatRupiah(hitungTotalDiskon()) }}</td>
            <td class="text-right font-weight-bold text-h6 text-primary py-4">{{ formatRupiah(hitungTotal()) }}</td>
          </tr>
        </tfoot>
      </VTable>
    </VCard>

    <!-- Print Footer: Show ONLY when printing -->
    <div class="d-none d-print-block mt-8 text-right">
      <p class="mb-12">Jakarta, {{ new Date().toLocaleDateString('id-ID') }}</p>
      <br>
      <p class="font-weight-bold">Admin Redistore</p>
    </div>
  </div>
</template>

<style scoped>
@media print {
  @page {
    size: A4 landscape;
    margin: 1cm;
  }
  
  body {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    background-color: white !important;
  }
  
  .v-application {
    background-color: white !important;
  }

  .d-print-none {
    display: none !important;
  }

  .d-print-block {
    display: block !important;
  }

  .print-card {
    box-shadow: none !important;
    border: none !important;
  }

  .laporan-table {
    border-collapse: collapse;
    width: 100%;
  }

  .laporan-table th, .laporan-table td {
    border: 1px solid #ddd;
    padding: 8px;
  }

  .laporan-table th {
    background-color: #f2f2f2 !important;
  }
}
</style>

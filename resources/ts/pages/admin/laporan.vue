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
    <div class="d-print-none mb-6">
      <h2 class="text-h4 font-weight-bold">Laporan Penjualan</h2>
      <p class="text-body-1 text-medium-emphasis">Filter dan cetak laporan transaksi sukses.</p>
    </div>

    <!-- Filter Card: Hide when printing -->
    <VCard class="mb-6 d-print-none" elevation="3">
      <VCardText>
        <VForm @submit.prevent="fetchLaporan">
          <VRow align="center">
            <VCol cols="12" md="4">
              <VTextField
                v-model="filter.start_date"
                label="Tanggal Awal"
                type="date"
                hide-details
              />
            </VCol>
            <VCol cols="12" md="4">
              <VTextField
                v-model="filter.end_date"
                label="Tanggal Akhir"
                type="date"
                hide-details
              />
            </VCol>
            <VCol cols="12" md="4" class="d-flex gap-4">
              <VBtn color="primary" type="submit" :loading="loading">
                <VIcon start icon="ri-search-line" /> Filter
              </VBtn>
              <VBtn color="secondary" variant="outlined" @click="printLaporan" :disabled="laporan.length === 0">
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
    <VCard elevation="3" class="print-card">
      <VCardText v-if="loading" class="text-center pa-6 d-print-none">
        <VProgressCircular indeterminate color="primary" />
      </VCardText>
      
      <VTable v-else hover class="laporan-table">
        <thead>
          <tr>
            <th class="text-left font-weight-bold">No</th>
            <th class="text-left font-weight-bold">ID Transaksi</th>
            <th class="text-left font-weight-bold">Tanggal</th>
            <th class="text-left font-weight-bold">Produk & Game</th>
            <th class="text-right font-weight-bold">Harga Jual</th>
            <th class="text-right font-weight-bold">Diskon Promo</th>
            <th class="text-right font-weight-bold">Total Diterima</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(trx, index) in laporan" :key="trx.id">
            <td>{{ index + 1 }}</td>
            <td>{{ trx.id }}</td>
            <td>{{ new Date(trx.tgl_transaksi).toLocaleString('id-ID') }}</td>
            <td>
              <div v-for="detail in trx.details" :key="detail.id">
                {{ detail.nama_produk }} ({{ detail.nama_game }})
              </div>
            </td>
            <td class="text-right">{{ formatRupiah(Number(trx.total_bayar) + Number(trx.nominal_diskon)) }}</td>
            <td class="text-right text-error">{{ trx.nominal_diskon > 0 ? '-' + formatRupiah(trx.nominal_diskon) : '-' }}</td>
            <td class="text-right font-weight-medium">{{ formatRupiah(trx.total_bayar) }}</td>
          </tr>
          <tr v-if="laporan.length === 0">
            <td colspan="7" class="text-center pa-4">Tidak ada data laporan pada periode ini.</td>
          </tr>
        </tbody>
        <tfoot v-if="laporan.length > 0">
          <tr>
            <td colspan="4" class="text-right font-weight-bold text-h6">TOTAL KESELURUHAN:</td>
            <td class="text-right font-weight-bold text-h6">{{ formatRupiah(hitungTotal() + hitungTotalDiskon()) }}</td>
            <td class="text-right font-weight-bold text-h6 text-error">-{{ formatRupiah(hitungTotalDiskon()) }}</td>
            <td class="text-right font-weight-bold text-h6 text-primary">{{ formatRupiah(hitungTotal()) }}</td>
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

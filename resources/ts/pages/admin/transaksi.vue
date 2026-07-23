<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

const transaksis = ref<any[]>([])
const loading = ref(false)
const dialog = ref(false)
const selectedTrx = ref<any>(null)
const selectedStatus = ref('')
const loadingUpdate = ref(false)
const snackbar = ref(false)
const snackbarText = ref('')

const statusOptions = ['Unpaid', 'Pending', 'Success', 'Failed', 'Cancel', 'Refund']

const fetchTransaksi = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/admin/transaksi')
    transaksis.value = res.data.data
  } catch (error) {
    console.error('Failed to fetch transaksi', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchTransaksi()
})

const formatRupiah = (angka: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka)
}

const openDialog = (trx: any) => {
  selectedTrx.value = trx
  selectedStatus.value = trx.status_transaksi
  dialog.value = true
}

const updateStatus = async () => {
  if (!selectedTrx.value) return
  
  loadingUpdate.value = true
  try {
    const res = await axios.put(`/api/admin/transaksi/${selectedTrx.value.id}/status`, {
      status: selectedStatus.value
    })
    
    if (res.data.success) {
      snackbarText.value = 'Status transaksi berhasil diperbarui'
      snackbar.value = true
      dialog.value = false
      fetchTransaksi()
    }
  } catch (error: any) {
    console.error('Failed to update status', error)
    snackbarText.value = error.response?.data?.message || 'Terjadi kesalahan saat mengupdate status'
    snackbar.value = true
  } finally {
    loadingUpdate.value = false
  }
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'Success': return 'success'
    case 'Unpaid': return 'error'
    case 'Pending': return 'warning'
    default: return 'grey'
  }
}
</script>

<template>
  <div class="pa-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-space-between align-md-center mb-6 gap-4">
      <div>
        <h2 class="text-h4 font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-shopping-cart-line" color="primary" />
          Data Transaksi
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0 mt-1">Kelola semua transaksi dan ubah status pesanannya di sini.</p>
      </div>
      <VBtn color="primary" variant="tonal" @click="fetchTransaksi" :loading="loading">
        <VIcon start icon="ri-refresh-line" />
        Refresh
      </VBtn>
    </div>

    <!-- Data Table -->
    <VCard elevation="10" class="rounded-lg overflow-hidden border-t-primary">
      <VCardText v-if="loading" class="text-center pa-10">
        <VProgressCircular indeterminate color="primary" size="48" width="4" />
        <div class="mt-4 text-medium-emphasis font-weight-medium">Memuat data transaksi...</div>
      </VCardText>
      
      <VTable v-else hover class="custom-table text-no-wrap">
        <thead class="bg-grey-lighten-4">
          <tr>
            <th class="text-left font-weight-bold">ID Transaksi</th>
            <th class="text-left font-weight-bold">Waktu</th>
            <th class="text-left font-weight-bold">Pelanggan</th>
            <th class="text-left font-weight-bold">Detail Pesanan</th>
            <th class="text-right font-weight-bold">Total Bayar</th>
            <th class="text-center font-weight-bold">Status</th>
            <th class="text-center font-weight-bold">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="trx in transaksis" :key="trx.id">
            <td class="font-weight-medium">{{ trx.id }}</td>
            <td>
              <div class="font-weight-medium">{{ new Date(trx.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) }}</div>
              <div class="text-caption text-medium-emphasis">{{ new Date(trx.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) }}</div>
            </td>
            <td>
              <div class="font-weight-bold">{{ trx.user ? trx.user.nama_lengkap : 'Tamu (Guest)' }}</div>
              <div class="text-caption text-medium-emphasis">{{ trx.no_whatsapp }}</div>
            </td>
            <td>
              <div v-if="trx.details && trx.details.length > 0">
                <div class="font-weight-medium">{{ trx.details[0].nama_produk }}</div>
                <div class="text-caption text-medium-emphasis">{{ trx.details[0].nama_game }}</div>
              </div>
              <div v-else class="text-medium-emphasis text-caption">Tanpa Detail</div>
            </td>
            <td class="text-right font-weight-bold text-primary">{{ formatRupiah(trx.total_bayar) }}</td>
            <td class="text-center">
              <VChip :color="getStatusColor(trx.status_transaksi)" size="small" class="font-weight-bold px-3">
                {{ trx.status_transaksi }}
              </VChip>
            </td>
            <td class="text-center">
              <VBtn 
                v-if="trx.bukti_pembayaran"
                color="info" 
                variant="outlined" 
                size="small" 
                class="font-weight-bold rounded-lg mr-2" 
                :href="`/uploads/bukti/${trx.bukti_pembayaran}`"
                target="_blank"
              >
                Bukti TF
              </VBtn>
              <VBtn color="primary" variant="outlined" size="small" class="font-weight-bold rounded-lg" @click="openDialog(trx)">
                Ubah Status
              </VBtn>
            </td>
          </tr>
          <tr v-if="transaksis.length === 0">
            <td colspan="7" class="text-center pa-8 text-medium-emphasis">
              Belum ada transaksi sama sekali.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Dialog Ubah Status -->
    <VDialog v-model="dialog" max-width="500">
      <VCard class="rounded-lg">
        <VCardTitle class="pa-4 bg-primary text-white d-flex justify-space-between align-center">
          <span class="font-weight-bold">Ubah Status Transaksi</span>
          <VBtn icon="ri-close-line" variant="text" size="small" color="white" @click="dialog = false" />
        </VCardTitle>
        <VCardText class="pa-6">
          <div class="mb-4">
            <div class="text-caption text-medium-emphasis mb-1">ID Transaksi</div>
            <div class="font-weight-bold text-body-1">{{ selectedTrx?.id }}</div>
          </div>
          
          <VSelect
            v-model="selectedStatus"
            :items="statusOptions"
            label="Pilih Status Baru"
            variant="outlined"
            hide-details
            class="mb-2"
          />
          <VAlert
            v-if="selectedStatus === 'Success'"
            type="info"
            variant="tonal"
            density="compact"
            class="mt-4 text-caption"
          >
            Mengubah status menjadi Success akan secara otomatis memberikan poin ke pelanggan, menambah saldo penjual (jika jual-beli akun), dan mengirim WhatsApp sukses.
          </VAlert>
        </VCardText>
        <VCardActions class="pa-4 pt-0">
          <VSpacer />
          <VBtn color="secondary" variant="text" class="px-4 font-weight-bold" @click="dialog = false">Batal</VBtn>
          <VBtn color="primary" variant="elevated" class="px-6 font-weight-bold rounded-lg" :loading="loadingUpdate" @click="updateStatus">Simpan Status</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Snackbar -->
    <VSnackbar v-model="snackbar" timeout="3000" color="success" elevation="24">
      <div class="d-flex align-center gap-2 font-weight-medium">
        <VIcon icon="ri-checkbox-circle-line" />
        {{ snackbarText }}
      </div>
    </VSnackbar>
  </div>
</template>

<style scoped>
.custom-table {
  border-collapse: collapse;
  width: 100%;
}
.custom-table th, .custom-table td {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(0,0,0,0.05);
}
</style>

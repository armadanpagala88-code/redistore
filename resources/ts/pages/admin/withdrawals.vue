<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'


const items = ref<any[]>([])
const loading = ref(true)

const fetchItems = async () => {
  try {
    const res = await axios.get('/api/admin/withdrawals')
    items.value = res.data.data
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchItems()
})

const isDialogVisible = ref(false)
const selectedItem = ref<any>(null)
const processStatus = ref('Approved')
const processNote = ref('')
const isProcessing = ref(false)

const openProcessDialog = (item: any) => {
  selectedItem.value = item
  processStatus.value = 'Approved'
  processNote.value = ''
  isDialogVisible.value = true
}

const submitProcess = async () => {
  if (processStatus.value === 'Rejected' && !processNote.value) {
    alert('Alasan penolakan wajib diisi!')
    return
  }

  isProcessing.value = true
  try {
    const res = await axios.put(`/api/admin/withdrawals/${selectedItem.value.id}/status`, {
      status: processStatus.value,
      catatan_admin: processNote.value
    })
    alert(res.data.message)
    isDialogVisible.value = false
    fetchItems()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Terjadi kesalahan sistem')
  } finally {
    isProcessing.value = false
  }
}

const formatRupiah = (value: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
}

const formatDate = (dateString: string) => {
  const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit' }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}

const statusColor = (status: string) => {
  switch (status) {
    case 'Pending': return 'warning'
    case 'Approved': return 'success'
    case 'Rejected': return 'error'
    default: return 'grey'
  }
}
</script>

<template>
  <div>
    <h2 class="text-h4 font-weight-bold mb-6 d-flex align-center gap-2">
      <VIcon icon="ri-bank-card-line" color="primary" />
      Kelola Penarikan Dana (Withdrawals)
    </h2>

    <VCard elevation="4" class="rounded-lg">
      <VCardText v-if="loading" class="text-center pa-10">
        <VProgressCircular indeterminate color="primary" size="48" width="4" />
      </VCardText>
      
      <VTable v-else hover class="custom-table text-no-wrap">
        <thead class="bg-grey-lighten-4">
          <tr>
            <th class="text-uppercase text-caption font-weight-bold">Waktu Pengajuan</th>
            <th class="text-uppercase text-caption font-weight-bold">Member</th>
            <th class="text-uppercase text-caption font-weight-bold">Info Bank Tujuan</th>
            <th class="text-uppercase text-caption font-weight-bold">Nominal</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Status</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id" :class="item.status === 'Pending' ? 'bg-warning-lighten-5' : ''">
            <td class="text-body-2">{{ formatDate(item.created_at) }}</td>
            <td>
              <div class="font-weight-bold">{{ item.user?.nama_lengkap }}</div>
              <div class="text-caption text-medium-emphasis">{{ item.user?.email }}</div>
            </td>
            <td>
              <div class="text-body-2 font-weight-medium">{{ item.info_bank }}</div>
            </td>
            <td class="font-weight-bold text-primary">{{ formatRupiah(item.nominal) }}</td>
            <td class="text-center">
              <VChip :color="statusColor(item.status)" size="small" class="font-weight-bold">
                {{ item.status }}
              </VChip>
            </td>
            <td class="text-center">
              <VBtn 
                v-if="item.status === 'Pending'" 
                color="primary" 
                size="small" 
                variant="elevated" 
                @click="openProcessDialog(item)"
              >
                Proses
              </VBtn>
              <span v-else class="text-caption text-medium-emphasis">Selesai</span>
            </td>
          </tr>
          <tr v-if="items.length === 0">
            <td colspan="6" class="text-center pa-8">
              <VIcon icon="ri-check-double-line" size="48" color="grey-lighten-1" class="mb-3" />
              <div class="text-h6 text-medium-emphasis">Belum ada pengajuan penarikan dana</div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Dialog Proses Penarikan -->
    <VDialog v-model="isDialogVisible" max-width="500">
      <VCard class="rounded-lg">
        <VCardTitle class="px-6 pt-6 d-flex justify-space-between align-center text-h5 font-weight-bold bg-primary text-white">
          Proses Penarikan Dana
          <VBtn icon="ri-close-line" variant="text" size="small" color="white" @click="isDialogVisible = false" />
        </VCardTitle>
        <VCardText class="pa-6">
          <div class="mb-4">
            <div class="text-caption text-medium-emphasis mb-1">Transfer sejumlah:</div>
            <div class="text-h4 font-weight-bold text-success">{{ formatRupiah(selectedItem?.nominal || 0) }}</div>
          </div>
          
          <VCard variant="tonal" color="info" class="pa-4 mb-6">
            <div class="text-caption mb-1">Informasi Rekening Tujuan:</div>
            <div class="font-weight-bold text-body-1">{{ selectedItem?.info_bank }}</div>
            <VAlert type="warning" variant="tonal" density="compact" class="mt-3 text-caption">
              Mohon transfer uangnya secara manual (M-Banking/E-Wallet) ke rekening di atas sebelum klik "Setujui". Sistem hanya mengubah status dan tidak mentransfer otomatis.
            </VAlert>
          </VCard>

          <VForm @submit.prevent="submitProcess">
            <VSelect 
              v-model="processStatus"
              :items="['Approved', 'Rejected']"
              label="Keputusan"
              variant="outlined"
              class="mb-4"
            />
            
            <VTextarea
              v-if="processStatus === 'Rejected'"
              v-model="processNote"
              label="Alasan Penolakan (Wajib)"
              hint="Saldo akan otomatis dikembalikan ke wallet member"
              persistent-hint
              variant="outlined"
              rows="3"
              class="mb-4"
              required
            />
            
            <VBtn 
              type="submit" 
              :color="processStatus === 'Approved' ? 'success' : 'error'" 
              block 
              size="large" 
              class="mt-2 font-weight-bold"
              :loading="isProcessing"
            >
              {{ processStatus === 'Approved' ? 'Setujui & Selesaikan' : 'Tolak Pengajuan' }}
            </VBtn>
          </VForm>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.bg-warning-lighten-5 {
  background-color: rgba(var(--v-theme-warning), 0.05);
}
.custom-table tbody tr:hover {
  background-color: rgba(var(--v-theme-primary), 0.03) !important;
}
</style>

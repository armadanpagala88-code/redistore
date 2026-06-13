<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const transactions = ref<any[]>([])
const loading = ref(true)
const router = useRouter()

const fetchTransactions = async () => {
  try {
    const res = await axios.get('/api/admin/transaksi')
    transactions.value = res.data.data
  } catch (error) {
    console.error('Failed to fetch transactions', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  // Check if token exists
  if (!localStorage.getItem('admin_token')) {
    router.push('/login')
    return
  }
  fetchTransactions()
})

const isPreviewOpen = ref(false)
const currentProof = ref('')

const viewProof = (filename: string) => {
  currentProof.value = `/uploads/bukti/${filename}`
  isPreviewOpen.value = true
}

const updateStatus = async (id: string, status: string) => {
  if (!confirm(`Yakin mengubah status transaksi menjadi ${status}?`)) return
  
  try {
    const res = await axios.put(`/api/admin/transaksi/${id}/status`, { status })
    if (res.data.success) {
      alert('Status berhasil diperbarui!')
      fetchTransactions()
    }
  } catch (error) {
    alert('Gagal memperbarui status')
  }
}

const logout = async () => {
  try {
    await axios.post('/api/logout')
  } catch(e) {}
  localStorage.removeItem('admin_token')
  localStorage.removeItem('user_data')
  router.push('/login')
}
</script>

<template>
  <div class="pa-4">
    <div class="d-flex justify-space-between align-center mb-6">
      <h2 class="text-h4 font-weight-bold">Dashboard Admin - Transaksi</h2>
      <VBtn color="error" variant="outlined" @click="logout">Logout</VBtn>
    </div>

    <VCard elevation="3">
      <VCardText v-if="loading" class="text-center pa-6">
        <VProgressCircular indeterminate color="primary" />
      </VCardText>
      
      <VTable v-else hover class="text-no-wrap">
        <thead>
          <tr>
            <th class="text-uppercase">ID Transaksi</th>
            <th class="text-uppercase">Tanggal</th>
            <th class="text-uppercase">Akun / Zona</th>
            <th class="text-uppercase">WhatsApp</th>
            <th class="text-uppercase">Total</th>
            <th class="text-uppercase text-center">Status</th>
            <th class="text-uppercase text-center">Bukti</th>
            <th class="text-uppercase text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="trx in transactions" :key="trx.id">
            <td class="font-weight-medium">{{ trx.id }}</td>
            <td>{{ new Date(trx.created_at).toLocaleDateString('id-ID') }}</td>
            <td>
              <div>{{ trx.user_id_game }}</div>
              <div class="text-caption text-medium-emphasis">{{ trx.zone_id || '-' }}</div>
            </td>
            <td>{{ trx.no_whatsapp }}</td>
            <td class="font-weight-bold">Rp {{ Number(trx.total_bayar).toLocaleString('id-ID') }}</td>
            <td class="text-center">
              <VChip
                :color="trx.status_transaksi === 'Success' ? 'success' : (trx.status_transaksi === 'Pending' ? 'warning' : (trx.status_transaksi === 'Unpaid' ? 'secondary' : 'error'))"
                size="small"
              >
                {{ trx.status_transaksi }}
              </VChip>
            </td>
            <td class="text-center">
              <VBtn
                v-if="trx.bukti_pembayaran"
                color="info"
                variant="tonal"
                size="small"
                @click="viewProof(trx.bukti_pembayaran)"
              >
                Lihat Foto
              </VBtn>
              <span v-else class="text-caption text-medium-emphasis">Belum Ada</span>
            </td>
            <td class="text-center">
              <div class="d-flex justify-center gap-2">
                <VBtn
                  color="success"
                  size="small"
                  :disabled="trx.status_transaksi === 'Success'"
                  @click="updateStatus(trx.id, 'Success')"
                >
                  Terima
                </VBtn>
                <VBtn
                  color="error"
                  size="small"
                  :disabled="trx.status_transaksi === 'Failed' || trx.status_transaksi === 'Cancel'"
                  @click="updateStatus(trx.id, 'Failed')"
                >
                  Tolak
                </VBtn>
              </div>
            </td>
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

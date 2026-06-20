<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

definePage({
  meta: {
    layout: 'frontend',
    public: false,
  },
})

const loading = ref(true)
const saldo = ref(0)
const withdrawals = ref<any[]>([])

const bankForm = ref({
  nama_bank: '',
  nomor_rekening: '',
  atas_nama: ''
})
const isBankSaving = ref(false)

const withdrawAmount = ref(0)
const isWithdrawing = ref(false)

const fetchData = async () => {
  try {
    const res = await axios.get('/api/member/withdrawals')
    saldo.value = res.data.data.saldo
    withdrawals.value = res.data.data.withdrawals
    if (res.data.data.bank_info) {
      bankForm.value.nama_bank = res.data.data.bank_info.nama_bank || ''
      bankForm.value.nomor_rekening = res.data.data.bank_info.nomor_rekening || ''
      bankForm.value.atas_nama = res.data.data.bank_info.atas_nama || ''
    }
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})

const saveBankInfo = async () => {
  if (!bankForm.value.nama_bank || !bankForm.value.nomor_rekening || !bankForm.value.atas_nama) {
    alert('Harap lengkapi semua data bank')
    return
  }

  isBankSaving.value = true
  try {
    const res = await axios.put('/api/member/withdrawals/bank', bankForm.value)
    alert(res.data.message)
    fetchData()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal menyimpan info bank')
  } finally {
    isBankSaving.value = false
  }
}

const submitWithdrawal = async () => {
  if (withdrawAmount.value < 50000) {
    alert('Minimal penarikan adalah Rp 50.000')
    return
  }
  
  if (!bankForm.value.nama_bank) {
    alert('Harap simpan Info Rekening Bank Anda terlebih dahulu!')
    return
  }

  if (!confirm(`Yakin ingin menarik saldo sebesar Rp ${withdrawAmount.value.toLocaleString('id-ID')}?`)) return

  isWithdrawing.value = true
  try {
    const res = await axios.post('/api/member/withdrawals', { nominal: withdrawAmount.value })
    alert(res.data.message)
    withdrawAmount.value = 0
    fetchData() // Refresh data (saldo berkurang, riwayat nambah)
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal mengajukan penarikan')
  } finally {
    isWithdrawing.value = false
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
  <VContainer class="py-8">
    <div class="d-flex flex-column flex-md-row justify-space-between align-md-center mb-6 gap-4">
      <div>
        <h2 class="text-h4 font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-wallet-3-line" color="primary" />
          Tarik Saldo (Withdraw)
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0 mt-1">Cairkan pendapatan jualan Anda ke Rekening Bank atau e-Wallet.</p>
      </div>
      <VBtn color="secondary" variant="tonal" to="/member/dashboard" prepend-icon="ri-arrow-left-line">Kembali ke Dashboard</VBtn>
    </div>

    <VRow v-if="loading">
      <VCol cols="12" class="text-center pa-10">
        <VProgressCircular indeterminate color="primary" size="48" width="4" />
      </VCol>
    </VRow>

    <VRow v-else>
      <!-- Kolom Kiri: Info Saldo & Form Penarikan -->
      <VCol cols="12" md="5">
        <VCard elevation="4" class="mb-6 rounded-lg overflow-hidden border-t-primary">
          <VCardText class="pa-6 text-center bg-primary-lighten-5">
            <div class="text-subtitle-1 mb-1 font-weight-medium">Saldo Tersedia</div>
            <div class="text-h3 font-weight-bold text-primary mb-4">{{ formatRupiah(saldo) }}</div>
          </VCardText>
          <VDivider />
          <VCardText class="pa-6">
            <h4 class="text-h6 font-weight-bold mb-4">Ajukan Penarikan</h4>
            <VForm @submit.prevent="submitWithdrawal">
              <VTextField 
                v-model.number="withdrawAmount" 
                type="number" 
                label="Nominal Penarikan (Rp)" 
                prefix="Rp"
                hint="Minimal penarikan Rp 50.000"
                persistent-hint
                variant="outlined" 
                class="mb-4" 
              />
              <VBtn 
                type="submit" 
                color="primary" 
                block 
                size="large" 
                class="rounded-lg font-weight-bold" 
                :loading="isWithdrawing"
                :disabled="saldo < 50000"
              >
                Tarik Dana Sekarang
              </VBtn>
            </VForm>
          </VCardText>
        </VCard>

        <!-- Form Info Rekening -->
        <VCard elevation="4" class="rounded-lg">
          <VCardTitle class="px-6 pt-6 pb-2 text-h6 font-weight-bold d-flex align-center gap-2">
            <VIcon icon="ri-bank-card-line" />
            Rekening Pencairan
          </VCardTitle>
          <VCardText class="px-6 pb-6">
            <VAlert type="info" variant="tonal" class="mb-4 text-caption">
              Pastikan nama dan nomor rekening valid agar proses pencairan dana tidak terkendala. Mendukung Bank dan e-Wallet (Dana, OVO, GoPay).
            </VAlert>
            <VForm @submit.prevent="saveBankInfo">
              <VTextField 
                v-model="bankForm.nama_bank" 
                label="Nama Bank / e-Wallet" 
                placeholder="Contoh: BCA / Dana / GoPay"
                variant="outlined" 
                density="comfortable"
                class="mb-3" 
                required
              />
              <VTextField 
                v-model="bankForm.nomor_rekening" 
                label="Nomor Rekening / No HP" 
                variant="outlined" 
                density="comfortable"
                class="mb-3" 
                required
              />
              <VTextField 
                v-model="bankForm.atas_nama" 
                label="Nama Pemilik Rekening" 
                variant="outlined" 
                density="comfortable"
                class="mb-4" 
                required
              />
              <VBtn 
                type="submit" 
                color="secondary" 
                variant="tonal" 
                block 
                :loading="isBankSaving"
              >
                Simpan Info Rekening
              </VBtn>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Kolom Kanan: Riwayat Penarikan -->
      <VCol cols="12" md="7">
        <VCard elevation="4" class="rounded-lg h-100">
          <VCardTitle class="px-6 pt-6 pb-2 text-h6 font-weight-bold">Riwayat Penarikan</VCardTitle>
          <VTable hover class="custom-table text-no-wrap">
            <thead class="bg-grey-lighten-4">
              <tr>
                <th class="text-uppercase text-caption font-weight-bold">Tanggal</th>
                <th class="text-uppercase text-caption font-weight-bold">Nominal</th>
                <th class="text-uppercase text-caption font-weight-bold text-center">Status</th>
                <th class="text-uppercase text-caption font-weight-bold">Keterangan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in withdrawals" :key="item.id">
                <td class="text-body-2">{{ formatDate(item.created_at) }}</td>
                <td class="font-weight-bold">{{ formatRupiah(item.nominal) }}</td>
                <td class="text-center">
                  <VChip :color="statusColor(item.status)" size="small" class="font-weight-bold">
                    {{ item.status }}
                  </VChip>
                </td>
                <td class="text-caption">
                  <div v-if="item.status === 'Rejected'" class="text-error font-weight-bold">
                    Ditolak: {{ item.catatan_admin }}
                  </div>
                  <div v-else class="text-medium-emphasis">
                    Tujuan: {{ item.info_bank }}
                  </div>
                </td>
              </tr>
              <tr v-if="withdrawals.length === 0">
                <td colspan="4" class="text-center pa-8">
                  <VIcon icon="ri-history-line" size="48" color="grey-lighten-1" class="mb-3" />
                  <div class="text-h6 text-medium-emphasis">Belum ada riwayat penarikan dana</div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>

<style scoped>
.border-t-primary {
  border-top: 4px solid rgb(var(--v-theme-primary)) !important;
}
.bg-primary-lighten-5 {
  background-color: rgba(var(--v-theme-primary), 0.05);
}
.custom-table tbody tr:hover {
  background-color: rgba(var(--v-theme-primary), 0.03) !important;
}
</style>

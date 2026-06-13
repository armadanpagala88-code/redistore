<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const route = useRoute()
const invoice = ref<any>(null)
const loading = ref(true)
const fileInput = ref<any>(null)
const uploadLoading = ref(false)

const fetchInvoice = async () => {
  try {
    const id = route.params.id
    const res = await axios.get(`/api/checkout/${id}`)
    invoice.value = res.data.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchInvoice)

const handleUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    fileInput.value = target.files[0]
  }
}

const submitBukti = async () => {
  if (!fileInput.value) {
    alert('Pilih file bukti pembayaran terlebih dahulu!')
    return
  }

  uploadLoading.value = true
  const formData = new FormData()
  formData.append('bukti_pembayaran', fileInput.value)

  try {
    const res = await axios.post(`/api/checkout/${invoice.value.id}/upload-bukti`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    if (res.data.success) {
      alert('Bukti berhasil diunggah! Menunggu konfirmasi admin.')
      await fetchInvoice()
    }
  } catch (e) {
    alert('Gagal mengunggah bukti.')
  } finally {
    uploadLoading.value = false
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

    <VRow v-else-if="invoice" justify="center">
      <VCol cols="12" md="8">
        <VCard elevation="4" class="mb-6">
          <VCardItem class="bg-primary text-white">
            <VCardTitle class="text-h5 font-weight-bold">
              Invoice Pemesanan
            </VCardTitle>
            <VCardSubtitle class="text-white">ID Transaksi: {{ invoice.id }}</VCardSubtitle>
          </VCardItem>

          <VCardText class="pt-6">
            <VRow>
              <VCol cols="12" sm="6">
                <div class="text-subtitle-2 text-medium-emphasis">Status Pesanan</div>
                <VChip
                  :color="invoice.status_transaksi === 'Success' ? 'success' : (invoice.status_transaksi === 'Unpaid' ? 'error' : 'warning')"
                  class="mt-1 font-weight-bold text-uppercase"
                >
                  {{ invoice.status_transaksi }}
                </VChip>
              </VCol>
              <VCol cols="12" sm="6" class="text-sm-right">
                <div class="text-subtitle-2 text-medium-emphasis">Total Pembayaran</div>
                <div class="text-h5 font-weight-bold text-primary">Rp {{ Number(invoice.total_bayar).toLocaleString('id-ID') }}</div>
              </VCol>
            </VRow>

            <VDivider class="my-4" />

            <div class="text-h6 font-weight-bold mb-2">Detail Akun</div>
            <VRow>
              <VCol cols="6">
                <div class="text-body-2 text-medium-emphasis">User ID Game</div>
                <div class="text-body-1 font-weight-bold">{{ invoice.user_id_game }}</div>
              </VCol>
              <VCol cols="6">
                <div class="text-body-2 text-medium-emphasis">Zone ID / Server</div>
                <div class="text-body-1 font-weight-bold">{{ invoice.zone_id || '-' }}</div>
              </VCol>
            </VRow>

            <VDivider class="my-4" />

            <div class="text-h6 font-weight-bold mb-2">Rincian Pembelian</div>
            <VTable>
              <thead>
                <tr>
                  <th>Produk</th>
                  <th class="text-right">Harga</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in invoice.details" :key="item.id">
                  <td>
                    <div class="font-weight-bold">{{ item.nama_produk }}</div>
                    <div class="text-caption text-medium-emphasis">{{ item.nama_game }}</div>
                  </td>
                  <td class="text-right">Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}</td>
                </tr>
              </tbody>
            </VTable>
          </VCardText>
        </VCard>

        <VCard elevation="4" v-if="invoice.status_transaksi === 'Unpaid'">
          <VCardItem>
            <VCardTitle class="text-h6 font-weight-bold">Upload Bukti Pembayaran</VCardTitle>
          </VCardItem>
          <VCardText>
            <VAlert type="info" variant="tonal" class="mb-4">
              Silakan transfer ke rekening <strong>BCA 1234567890 a.n Redistore</strong> sebesar <strong>Rp {{ Number(invoice.total_bayar).toLocaleString('id-ID') }}</strong>.
            </VAlert>
            
            <VFileInput
              label="Pilih Foto Bukti Transfer"
              accept="image/*"
              variant="outlined"
              prepend-icon="mdi-camera"
              @change="handleUpload"
            />
            
            <VBtn
              color="primary"
              size="large"
              block
              class="mt-4"
              :loading="uploadLoading"
              @click="submitBukti"
            >
              Kirim Bukti Pembayaran
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

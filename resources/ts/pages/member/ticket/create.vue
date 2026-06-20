<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

definePage({
  meta: {
    layout: 'frontend',
    public: false,
  },
})

const router = useRouter()
const isSubmitting = ref(false)
const transaksis = ref<any[]>([])

const form = ref({
  subjek: '',
  transaksi_id: null,
  prioritas: 'Medium',
  pesan: ''
})

onMounted(async () => {
  // Ambil daftar transaksi user untuk dropdown
  try {
    const res = await axios.get('/api/member/dashboard')
    if (res.data.data && res.data.data.transaksi_terakhir) {
      transaksis.value = res.data.data.transaksi_terakhir
    }
  } catch (e) {
    console.error('Failed to load transaksis', e)
  }
})

const submitTicket = async () => {
  isSubmitting.value = true
  try {
    const res = await axios.post('/api/tickets', form.value)
    if (res.data.success) {
      alert('Tiket berhasil dibuat!')
      router.push(`/member/ticket/${res.data.data.id}`)
    }
  } catch (error: any) {
    alert('Gagal membuat tiket: ' + (error.response?.data?.message || 'Error'))
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <VContainer>
    <VRow justify="center">
      <VCol cols="12" md="8">
        <VBtn variant="text" prepend-icon="ri-arrow-left-line" to="/member/ticket" class="mb-4">
          Kembali ke Riwayat Tiket
        </VBtn>

        <VCard elevation="3" class="rounded-lg">
          <VCardItem class="pa-6 border-b">
            <VCardTitle class="text-h5 font-weight-bold">Buat Tiket Bantuan Baru</VCardTitle>
            <VCardSubtitle>Silakan jelaskan kendala yang Anda alami secara detail.</VCardSubtitle>
          </VCardItem>

          <VCardText class="pa-6">
            <VForm @submit.prevent="submitTicket">
              <VRow>
                <VCol cols="12" md="8">
                  <VTextField
                    v-model="form.subjek"
                    label="Subjek Kendala *"
                    placeholder="Contoh: Akun game belum diterima"
                    variant="outlined"
                    :rules="[v => !!v || 'Subjek wajib diisi']"
                  />
                </VCol>
                
                <VCol cols="12" md="4">
                  <VSelect
                    v-model="form.prioritas"
                    :items="['Low', 'Medium', 'High']"
                    label="Prioritas"
                    variant="outlined"
                  />
                </VCol>

                <VCol cols="12">
                  <VSelect
                    v-model="form.transaksi_id"
                    :items="transaksis"
                    item-title="id"
                    item-value="id"
                    label="Terkait Transaksi (Opsional)"
                    placeholder="Pilih ID Transaksi jika relevan"
                    variant="outlined"
                    clearable
                  >
                    <template #item="{ props, item }">
                      <VListItem v-bind="props" :title="`ID: ${item.raw.id} - Rp ${item.raw.total_bayar}`" :subtitle="new Date(item.raw.created_at).toLocaleDateString('id-ID')" />
                    </template>
                  </VSelect>
                </VCol>

                <VCol cols="12">
                  <VTextarea
                    v-model="form.pesan"
                    label="Jelaskan Detail Kendala *"
                    placeholder="Saya sudah membayar pesanan namun status masih..."
                    variant="outlined"
                    rows="5"
                    :rules="[v => !!v || 'Pesan wajib diisi']"
                  />
                </VCol>

                <VCol cols="12" class="d-flex justify-end mt-4">
                  <VBtn
                    type="submit"
                    color="primary"
                    size="large"
                    prepend-icon="ri-send-plane-fill"
                    :loading="isSubmitting"
                    :disabled="!form.subjek || !form.pesan"
                  >
                    Kirim Tiket
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>

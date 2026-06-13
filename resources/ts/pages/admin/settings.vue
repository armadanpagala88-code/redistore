<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

const form = ref({
  app_name: '',
  app_description: '',
  wa_number: '',
  instagram: '',
})

const loading = ref(true)
const saving = ref(false)
const snackbar = ref({
  show: false,
  message: '',
  color: 'success'
})

onMounted(async () => {
  try {
    const res = await axios.get('/api/settings')
    if (res.data.success && res.data.data) {
      form.value.app_name = res.data.data.app_name || ''
      form.value.app_description = res.data.data.app_description || ''
      form.value.wa_number = res.data.data.wa_number || ''
      form.value.instagram = res.data.data.instagram || ''
    }
  } catch (e) {
    console.error('Error fetching settings', e)
  } finally {
    loading.value = false
  }
})

const saveSettings = async () => {
  saving.value = true
  try {
    const res = await axios.post('/api/admin/settings', form.value)
    if (res.data.success) {
      snackbar.value = {
        show: true,
        message: 'Pengaturan berhasil disimpan!',
        color: 'success'
      }
    }
  } catch (e: any) {
    snackbar.value = {
      show: true,
      message: e.response?.data?.message || 'Gagal menyimpan pengaturan',
      color: 'error'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <h2 class="text-h4 font-weight-bold mb-6">Pengaturan Website</h2>
      </VCol>

      <VCol cols="12" md="8">
        <VCard elevation="3" class="rounded-lg">
          <VCardItem class="bg-surface-variant pa-4">
            <VCardTitle class="text-h6 font-weight-bold d-flex align-center gap-2">
              <VIcon icon="ri-settings-4-line" color="primary" />
              Informasi Umum
            </VCardTitle>
          </VCardItem>
          
          <VCardText class="pa-6" v-if="!loading">
            <VForm @submit.prevent="saveSettings">
              <VRow>
                <VCol cols="12">
                  <VTextField
                    v-model="form.app_name"
                    label="Nama Website"
                    placeholder="Contoh: Redistore"
                    variant="outlined"
                    color="primary"
                    required
                  />
                </VCol>
                
                <VCol cols="12">
                  <VTextarea
                    v-model="form.app_description"
                    label="Deskripsi / SEO"
                    placeholder="Deskripsi website untuk footer dan SEO"
                    variant="outlined"
                    color="primary"
                    rows="3"
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.wa_number"
                    label="Nomor WhatsApp CS"
                    placeholder="Contoh: 08123456789"
                    variant="outlined"
                    color="primary"
                    prepend-inner-icon="ri-whatsapp-line"
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.instagram"
                    label="Username Instagram"
                    placeholder="Contoh: @redistore"
                    variant="outlined"
                    color="primary"
                    prepend-inner-icon="ri-instagram-line"
                  />
                </VCol>

                <VCol cols="12" class="mt-4">
                  <VBtn
                    type="submit"
                    color="primary"
                    :loading="saving"
                    prepend-icon="ri-save-3-line"
                  >
                    Simpan Perubahan
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
          <VCardText v-else class="text-center pa-12">
            <VProgressCircular indeterminate color="primary" />
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="4">
        <VCard elevation="3" class="rounded-lg bg-primary-lighten-1">
          <VCardText class="text-center pa-6 text-white">
            <VIcon icon="ri-information-line" size="48" class="mb-4" />
            <h3 class="text-h6 font-weight-bold mb-2">Informasi</h3>
            <p class="text-body-2 text-white">
              Pengaturan ini akan langsung memengaruhi tampilan utama *Frontend* (halaman depan pelanggan), seperti Nama Web di Navbar dan informasi di Footer.
            </p>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Snackbar / Toast -->
    <VSnackbar v-model="snackbar.show" :color="snackbar.color" location="top right">
      {{ snackbar.message }}
      <template #actions>
        <VBtn variant="text" @click="snackbar.show = false">Tutup</VBtn>
      </template>
    </VSnackbar>
  </div>
</template>

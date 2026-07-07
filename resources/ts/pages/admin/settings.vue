<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = ref({
  app_name: '',
  app_description: '',
  wa_number: '',
  instagram: '',
  biaya_admin_persen: 5,
  fonnte_token: '',
  midtrans_server_key: '',
  midtrans_client_key: '',
  midtrans_is_production: '0',
  midtrans_payment_link: '',
  theme_color_primary: '#666CFF',
})

const loading = ref(true)
const saving = ref(false)
const snackbar = ref({
  show: false,
  message: '',
  color: 'success'
})

onMounted(async () => {
  if (!localStorage.getItem('admin_token')) {
    router.push('/login')
    return
  }
  try {
    const res = await axios.get('/api/admin/settings')
    if (res.data.success && res.data.data) {
      if (res.data.data.app_name) form.value.app_name = res.data.data.app_name
      if (res.data.data.app_description) form.value.app_description = res.data.data.app_description
      if (res.data.data.wa_number) form.value.wa_number = res.data.data.wa_number
      if (res.data.data.instagram) form.value.instagram = res.data.data.instagram
      if (res.data.data.biaya_admin_persen) form.value.biaya_admin_persen = res.data.data.biaya_admin_persen
      if (res.data.data.fonnte_token) form.value.fonnte_token = res.data.data.fonnte_token
      if (res.data.data.midtrans_server_key) form.value.midtrans_server_key = res.data.data.midtrans_server_key
      if (res.data.data.midtrans_client_key) form.value.midtrans_client_key = res.data.data.midtrans_client_key
      if (res.data.data.midtrans_is_production !== undefined) form.value.midtrans_is_production = String(res.data.data.midtrans_is_production)
      if (res.data.data.midtrans_payment_link) form.value.midtrans_payment_link = res.data.data.midtrans_payment_link
      if (res.data.data.theme_color_primary) form.value.theme_color_primary = res.data.data.theme_color_primary
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
  <div class="pa-4">
    <div class="d-flex flex-column flex-md-row justify-space-between align-md-center mb-6 gap-4">
      <div>
        <h2 class="text-h4 font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-settings-3-line" color="primary" />
          Pengaturan Website
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0 mt-1">Konfigurasi informasi umum website seperti nama, deskripsi, dan kontak.</p>
      </div>
    </div>

    <VRow>
      <VCol cols="12" md="8">
        <VCard elevation="10" class="rounded-lg border-t-primary overflow-hidden">
          <VCardTitle class="px-6 pt-6 pb-2 text-h6 font-weight-bold d-flex align-center gap-2">
            <VIcon icon="ri-information-line" color="primary" />
            Informasi Umum
          </VCardTitle>
          
          <VCardText class="pa-6" v-if="!loading">
            <VForm @submit.prevent="saveSettings">
              <VRow>
                <VCol cols="12">
                  <VTextField
                    v-model="form.app_name"
                    label="Nama Website"
                    placeholder="Contoh: Redistore"
                    variant="outlined"
                    density="comfortable"
                    color="primary"
                    prepend-inner-icon="ri-global-line"
                    required
                  />
                </VCol>
                
                <VCol cols="12">
                  <VTextarea
                    v-model="form.app_description"
                    label="Deskripsi / SEO"
                    placeholder="Deskripsi website untuk footer dan SEO"
                    variant="outlined"
                    density="comfortable"
                    color="primary"
                    prepend-inner-icon="ri-file-list-3-line"
                    rows="3"
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.wa_number"
                    label="Nomor WhatsApp CS"
                    placeholder="Contoh: 08123456789"
                    variant="outlined"
                    density="comfortable"
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
                    density="comfortable"
                    color="primary"
                    prepend-inner-icon="ri-instagram-line"
                  />
                </VCol>

                <VCol cols="12">
                  <VTextField
                    v-model.number="form.biaya_admin_persen"
                    type="number"
                    label="Biaya Admin Transaksi Jual Akun (%)"
                    placeholder="Contoh: 5"
                    variant="outlined"
                    density="comfortable"
                    color="primary"
                    prepend-inner-icon="ri-percent-line"
                    hint="Persentase potongan saldo penjual saat akun laku terjual"
                    persistent-hint
                  />
                </VCol>

                <VCol cols="12">
                  <VTextField
                    v-model="form.fonnte_token"
                    label="Token API Fonnte (WhatsApp Gateway)"
                    placeholder="Masukkan Token Fonnte Anda"
                    variant="outlined"
                    density="comfortable"
                    color="primary"
                    prepend-inner-icon="ri-message-3-line"
                    hint="Token untuk pengiriman notifikasi WhatsApp otomatis. Dapatkan dari fonnte.com"
                    persistent-hint
                  />
                </VCol>

                <VCol cols="12">
                  <VDivider class="my-4" />
                  <div class="text-subtitle-1 font-weight-bold mb-4 d-flex align-center gap-2">
                    <VIcon icon="ri-palette-line" color="primary" />
                    Pengaturan Tema & Tampilan
                  </div>
                </VCol>

                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.theme_color_primary"
                    label="Warna Utama Tema (Primary Color)"
                    placeholder="#666CFF"
                    variant="outlined"
                    density="comfortable"
                    prepend-inner-icon="ri-paint-brush-line"
                    hint="Pilih warna dominan yang akan digunakan oleh website"
                    persistent-hint
                  >
                    <template #append-inner>
                      <input 
                        type="color" 
                        v-model="form.theme_color_primary" 
                        style="width: 32px; height: 32px; border: none; cursor: pointer; padding: 0; border-radius: 4px;"
                      />
                    </template>
                  </VTextField>
                </VCol>

                <VCol cols="12">
                  <VDivider class="my-4" />
                  <div class="text-subtitle-1 font-weight-bold mb-4 d-flex align-center gap-2">
                    <VIcon icon="ri-bank-card-line" color="primary" />
                    Pengaturan Payment Gateway (Midtrans)
                  </div>
                </VCol>

                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.midtrans_server_key"
                    label="Server Key"
                    placeholder="SB-Mid-server-xxx"
                    variant="outlined"
                    density="comfortable"
                    color="primary"
                    prepend-inner-icon="ri-key-2-line"
                    hint="Dapatkan dari dashboard Midtrans Anda"
                    persistent-hint
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.midtrans_client_key"
                    label="Client Key"
                    placeholder="SB-Mid-client-xxx"
                    variant="outlined"
                    density="comfortable"
                    color="primary"
                    prepend-inner-icon="ri-key-line"
                  />
                </VCol>

                <VCol cols="12">
                  <VTextField
                    v-model="form.midtrans_payment_link"
                    label="Payment Link (Manual)"
                    placeholder="Contoh: https://app.sandbox.midtrans.com/payment-links/..."
                    variant="outlined"
                    density="comfortable"
                    color="primary"
                    prepend-inner-icon="ri-link"
                    hint="Tautan statis untuk pembayaran manual"
                    persistent-hint
                  />
                </VCol>

                <VCol cols="12">
                  <VSelect
                    v-model="form.midtrans_is_production"
                    :items="[
                      { title: 'Sandbox (Testing)', value: '0' },
                      { title: 'Production (Live)', value: '1' }
                    ]"
                    label="Environment"
                    variant="outlined"
                    density="comfortable"
                    color="primary"
                    prepend-inner-icon="ri-global-line"
                    hint="Gunakan Sandbox saat masa percobaan/development."
                    persistent-hint
                  />
                </VCol>

                <VCol cols="12" class="mt-2 d-flex justify-end">
                  <VBtn
                    type="submit"
                    color="primary"
                    :loading="saving"
                    prepend-icon="ri-save-3-line"
                    class="px-8 rounded-lg font-weight-bold"
                    variant="elevated"
                  >
                    Simpan Perubahan
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
          <VCardText v-else class="text-center pa-12">
            <VProgressCircular indeterminate color="primary" size="48" width="4" />
            <div class="mt-4 text-medium-emphasis font-weight-medium">Memuat pengaturan...</div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="4">
        <VCard elevation="10" class="rounded-lg bg-primary text-white overflow-hidden" style="position: relative;">
          <!-- Decorative circle background -->
          <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; border-radius: 50%; background: rgba(255,255,255,0.1);"></div>
          <div style="position: absolute; bottom: -30px; left: -30px; width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.1);"></div>
          
          <VCardText class="pa-8 position-relative z-1">
            <div class="d-flex justify-center mb-6">
              <VAvatar color="rgba(255,255,255,0.2)" size="80" class="elevation-4">
                <VIcon icon="ri-lightbulb-flash-line" size="40" color="white" />
              </VAvatar>
            </div>
            <h3 class="text-h5 font-weight-bold mb-4 text-center">Pusat Pengaturan</h3>
            <p class="text-body-1 text-center" style="line-height: 1.6; opacity: 0.9;">
              Pengaturan ini akan langsung memengaruhi tampilan utama *Frontend* (halaman depan pelanggan), seperti <strong>Nama Web</strong> di Navbar dan informasi kontak di <strong>Footer</strong>.
            </p>
            <div class="mt-6 d-flex align-center justify-center gap-2 opacity-80 text-caption">
              <VIcon icon="ri-shield-check-line" />
              Perubahan tersimpan otomatis ke database
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Snackbar / Toast -->
    <VSnackbar v-model="snackbar.show" :color="snackbar.color" location="top right" class="font-weight-medium">
      <div class="d-flex align-center gap-2">
        <VIcon :icon="snackbar.color === 'success' ? 'ri-checkbox-circle-line' : 'ri-error-warning-line'" />
        {{ snackbar.message }}
      </div>
      <template #actions>
        <VBtn variant="text" @click="snackbar.show = false" icon="ri-close-line"></VBtn>
      </template>
    </VSnackbar>
  </div>
</template>

<style scoped>
.border-t-primary {
  border-top: 4px solid rgb(var(--v-theme-primary)) !important;
}
.z-1 {
  z-index: 1;
}
</style>

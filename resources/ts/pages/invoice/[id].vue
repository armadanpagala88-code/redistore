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
const paymentLink = ref('')
const bankAccounts = ref<{bank_name: string, bank_account_number: string, bank_account_name: string}[]>([])

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

const payWithMidtrans = () => {
  if (invoice.value && invoice.value.snap_token) {
    // Make sure snap is loaded
    if (typeof (window as any).snap !== 'undefined') {
      (window as any).snap.pay(invoice.value.snap_token, {
        onSuccess: function(result: any) {
          alert("Pembayaran Berhasil!");
          fetchInvoice();
        },
        onPending: function(result: any) {
          alert("Menunggu pembayaran Anda!");
          fetchInvoice();
        },
        onError: function(result: any) {
          alert("Pembayaran Gagal!");
          fetchInvoice();
        },
        onClose: function() {
          alert("Anda menutup popup tanpa menyelesaikan pembayaran");
        }
      });
    } else {
      alert("Sistem pembayaran sedang dimuat, silakan coba beberapa saat lagi.");
    }
  } else if (paymentLink.value) {
    // Fallback ke payment link manual jika snap token tidak ada
    window.location.href = paymentLink.value;
  } else {
    alert("Sistem pembayaran sedang dimuat atau tidak tersedia. Silakan hubungi admin.");
  }
}

const uploadBukti = async (e: Event) => {
  const target = e.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return
  
  const file = target.files[0]
  if (file.size > 2 * 1024 * 1024) {
    alert("Ukuran file maksimal 2MB")
    return
  }

  uploadLoading.value = true
  try {
    const formData = new FormData()
    formData.append('bukti_pembayaran', file)
    
    const res = await axios.post(`/api/checkout/${invoice.value.id}/upload-bukti`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    if (res.data.success) {
      alert("Bukti pembayaran berhasil diunggah! Mohon tunggu konfirmasi admin.")
      fetchInvoice()
    }
  } catch (err: any) {
    alert(err.response?.data?.message || "Gagal mengunggah bukti pembayaran")
  } finally {
    uploadLoading.value = false
    if (fileInput.value) fileInput.value.value = ''
  }
}

onMounted(async () => {
  fetchInvoice();
  
  try {
    const res = await axios.get('/api/settings');
    let isProd = false;
    let clientKey = 'SB-Mid-client-YOUR-CLIENT-KEY';

    if (res.data.success && res.data.data) {
      if (res.data.data.midtrans_is_production === '1') isProd = true;
      if (res.data.data.midtrans_client_key) clientKey = res.data.data.midtrans_client_key;
      if (res.data.data.midtrans_payment_link) paymentLink.value = res.data.data.midtrans_payment_link;
      
      if (res.data.data.bank_accounts) {
        try {
          const parsed = JSON.parse(res.data.data.bank_accounts)
          if (Array.isArray(parsed) && parsed.length > 0) {
            bankAccounts.value = parsed
          }
        } catch(e) {}
      } else {
        // Fallback to legacy single bank account if exists
        if (res.data.data.bank_name || res.data.data.bank_account_number) {
          bankAccounts.value = [{
            bank_name: res.data.data.bank_name || '',
            bank_account_number: res.data.data.bank_account_number || '',
            bank_account_name: res.data.data.bank_account_name || ''
          }]
        }
      }
    }

    // Load Midtrans Snap Script
    const script = document.createElement('script');
    script.src = isProd ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
    script.setAttribute('data-client-key', clientKey);
    document.head.appendChild(script);

  } catch (e) {
    console.error('Failed to load midtrans settings', e);
  }
})
</script>

<template>
  <div class="invoice-container py-8 px-4">
    <VRow v-if="loading">
      <VCol cols="12" class="text-center mt-12">
        <VProgressCircular indeterminate color="primary" size="64" />
      </VCol>
    </VRow>

    <VRow v-else-if="invoice" justify="center">
      <VCol cols="12" md="9" lg="8">
        
        <!-- Header & Branding -->
        <VCard elevation="6" class="rounded-xl overflow-hidden mb-6 border-t-primary border-t-8">
          <VCardText class="pa-sm-10 pa-6">
            <div class="d-flex flex-column flex-sm-row justify-space-between align-sm-start gap-4 mb-8">
              <div>
                <div class="d-flex align-center gap-3 mb-2">
                  <VIcon icon="ri-store-2-fill" color="primary" size="36" />
                  <span class="text-h4 font-weight-black text-primary text-uppercase tracking-wide">Redistore</span>
                </div>
                <div class="text-body-1 text-medium-emphasis mt-2">Solusi Top Up & Jual Beli Akun Terpercaya</div>
              </div>
              <div class="text-sm-right">
                <h1 class="text-h4 font-weight-bold text-high-emphasis mb-1">INVOICE</h1>
                <VChip size="small" :color="invoice.status_transaksi === 'Success' ? 'success' : (invoice.status_transaksi === 'Unpaid' ? 'error' : 'warning')" class="font-weight-bold text-uppercase px-4 mb-2">
                  {{ invoice.status_transaksi }}
                </VChip>
                <div class="text-body-2 text-medium-emphasis">ID: <span class="font-weight-medium text-high-emphasis">{{ invoice.id }}</span></div>
              </div>
            </div>

            <VDivider class="mb-8" />

            <!-- Bill To & Payment Info -->
            <VRow class="mb-8">
              <VCol cols="12" sm="6">
                <h6 class="text-subtitle-2 text-primary font-weight-bold mb-3 text-uppercase">Ditagihkan Kepada:</h6>
                <div class="text-h6 font-weight-bold mb-1">{{ invoice.user ? invoice.user.nama_lengkap : 'Pelanggan' }}</div>
                <div class="text-body-2 text-medium-emphasis mb-1 d-flex align-center gap-2">
                  <VIcon icon="ri-whatsapp-line" size="16"/> {{ invoice.no_whatsapp }}
                </div>
              </VCol>
              <VCol cols="12" sm="6" class="text-sm-right">
                <h6 class="text-subtitle-2 text-primary font-weight-bold mb-3 text-uppercase">Detail Pembayaran:</h6>
                <div class="text-h3 font-weight-black text-primary mb-2">Rp {{ Number(invoice.total_bayar).toLocaleString('id-ID') }}</div>
                <div class="text-body-2 text-medium-emphasis">Tanggal: <span class="font-weight-medium text-high-emphasis">{{ new Date(invoice.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) }}</span></div>
              </VCol>
            </VRow>

            <!-- Data Login Section -->
            <template v-if="invoice.akun_game_login">
              <VCard variant="flat" class="bg-success-lighten-5 border-success mb-8 rounded-lg" style="border: 1px solid rgb(var(--v-theme-success))">
                <VCardText class="pa-5">
                  <div class="d-flex align-center gap-3 mb-4">
                    <VAvatar color="success" variant="tonal" rounded size="40">
                      <VIcon icon="ri-shield-keyhole-line" size="24" />
                    </VAvatar>
                    <h3 class="text-h6 font-weight-bold text-success mb-0">Informasi Data Login Akun</h3>
                  </div>
                  <VRow>
                    <VCol cols="12" sm="4">
                      <div class="text-caption text-success font-weight-medium text-uppercase mb-1">Email / Username</div>
                      <div class="text-body-1 font-weight-bold text-high-emphasis">{{ invoice.akun_game_login.email_akun }}</div>
                    </VCol>
                    <VCol cols="12" sm="4">
                      <div class="text-caption text-success font-weight-medium text-uppercase mb-1">Password</div>
                      <div class="text-body-1 font-weight-bold text-high-emphasis">{{ invoice.akun_game_login.password_akun }}</div>
                    </VCol>
                    <VCol cols="12" sm="4">
                      <div class="text-caption text-success font-weight-medium text-uppercase mb-1">Login Via</div>
                      <div class="text-body-1 font-weight-bold text-high-emphasis">{{ invoice.akun_game_login.login_via }}</div>
                    </VCol>
                  </VRow>
                  
                  <div v-if="invoice.akun_game_login.catatan_akun" class="mt-4 pt-4 border-t">
                    <div class="text-caption text-warning font-weight-bold text-uppercase mb-1 d-flex align-center gap-1">
                      <VIcon icon="ri-alert-line" size="14"/> Catatan Khusus
                    </div>
                    <div class="text-body-2 font-weight-medium text-high-emphasis">
                      {{ invoice.akun_game_login.catatan_akun }}
                    </div>
                  </div>
                </VCardText>
              </VCard>
            </template>
            <template v-else-if="invoice.tipe_transaksi === 'BeliAkun'">
              <VAlert type="info" variant="tonal" class="mb-8 font-weight-medium border" style="border: 1px solid rgb(var(--v-theme-info))" icon="ri-whatsapp-line">
                Data login akun akan otomatis dikirim ke nomor WhatsApp <strong>{{ invoice.no_whatsapp }}</strong> setelah pembayaran diverifikasi oleh Admin.
              </VAlert>
            </template>

            <!-- Table -->
            <VTable class="invoice-table border rounded-lg overflow-hidden mb-6">
              <thead class="bg-grey-lighten-4">
                <tr>
                  <th class="text-uppercase text-caption font-weight-bold py-3 pl-4">Deskripsi Produk</th>
                  <th class="text-uppercase text-caption font-weight-bold py-3 text-right pr-4">Total Harga</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in invoice.details" :key="item.id" class="border-b">
                  <td class="py-4 pl-4">
                    <div class="text-subtitle-1 font-weight-bold text-high-emphasis mb-1">{{ item.nama_produk }}</div>
                    <div class="text-body-2 text-medium-emphasis d-flex align-center gap-1">
                      <VIcon icon="ri-gamepad-line" size="14"/> {{ item.nama_game }}
                    </div>
                  </td>
                  <td class="text-right py-4 pr-4 text-subtitle-1 font-weight-bold">
                    Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}
                  </td>
                </tr>
              </tbody>
            </VTable>

            <!-- Diskon & Footer -->
            <div class="d-flex justify-end mb-8" v-if="invoice.nominal_diskon > 0">
               <div style="width: 300px">
                 <div class="d-flex justify-space-between text-success font-weight-bold border-b pb-2 mb-2">
                   <span>Diskon Promo:</span>
                   <span>- Rp {{ Number(invoice.nominal_diskon).toLocaleString('id-ID') }}</span>
                 </div>
               </div>
            </div>

            <!-- Pesan -->
            <div class="text-center pt-6 border-t">
              <p class="text-body-2 text-medium-emphasis mb-0">
                Terima kasih telah berbelanja di Redistore. Kami harap Anda puas dengan layanan kami!
              </p>
            </div>
          </VCardText>
        </VCard>

        <!-- Payment Actions -->
        <VCard elevation="6" class="rounded-xl overflow-hidden mb-6 border-t-warning border-t-8" v-if="invoice.status_transaksi === 'Pending'">
          <VCardText class="pa-6 text-center">
            <VAvatar color="warning" variant="tonal" size="64" class="mb-4">
              <VIcon icon="ri-time-line" size="32" />
            </VAvatar>
            <h3 class="text-h5 font-weight-bold text-high-emphasis mb-2">Menunggu Verifikasi</h3>
            <p class="text-body-1 text-medium-emphasis mb-0">
              Bukti pembayaran Anda sedang kami periksa. Kami akan segera memproses pesanan Anda dalam waktu maksimal 1x24 jam.
            </p>
          </VCardText>
        </VCard>

        <VRow v-if="invoice.status_transaksi === 'Unpaid'">
          <VCol cols="12" md="6" v-if="bankAccounts.length > 0">
            <VCard elevation="6" class="h-100 rounded-xl overflow-hidden border-t-primary border-t-8">
              <VCardText class="pa-6 d-flex flex-column h-100">
                <div class="d-flex align-center gap-3 mb-6">
                  <VAvatar color="primary" variant="tonal" rounded size="48">
                    <VIcon icon="ri-bank-card-line" size="24" />
                  </VAvatar>
                  <div>
                    <h3 class="text-h6 font-weight-bold mb-0">Transfer Manual</h3>
                    <div class="text-caption text-medium-emphasis">Upload bukti transfer bank</div>
                  </div>
                </div>

                <div v-for="(bank, index) in bankAccounts" :key="index" class="bg-grey-lighten-4 pa-4 rounded-lg mb-4 text-center border">
                  <div class="text-subtitle-2 text-uppercase font-weight-bold text-medium-emphasis mb-1">{{ bank.bank_name }}</div>
                  <div class="text-h5 font-weight-black text-primary mb-1 tracking-wide">{{ bank.bank_account_number }}</div>
                  <div class="text-body-2 font-weight-medium">A/N {{ bank.bank_account_name }}</div>
                </div>

                <VBtn
                  color="primary"
                  size="large"
                  block
                  class="font-weight-bold rounded-lg mt-auto"
                  prepend-icon="ri-upload-cloud-2-line"
                  @click="fileInput.click()"
                  :loading="uploadLoading"
                  elevation="2"
                >
                  Upload Bukti Transfer
                </VBtn>
                <input type="file" ref="fileInput" accept="image/*" style="display: none" @change="uploadBukti">
              </VCardText>
            </VCard>
          </VCol>
          
          <VCol cols="12" :md="bankAccounts.length > 0 ? 6 : 12">
            <VCard elevation="6" class="h-100 rounded-xl overflow-hidden border-t-success border-t-8">
              <VCardText class="pa-6 d-flex flex-column h-100">
                <div class="d-flex align-center gap-3 mb-6">
                  <VAvatar color="success" variant="tonal" rounded size="48">
                    <VIcon icon="ri-qr-code-line" size="24" />
                  </VAvatar>
                  <div>
                    <h3 class="text-h6 font-weight-bold mb-0">Pembayaran Otomatis</h3>
                    <div class="text-caption text-medium-emphasis">QRIS, e-Wallet, Virtual Account</div>
                  </div>
                </div>
                
                <div class="text-body-1 text-medium-emphasis mb-6 flex-grow-1 text-center d-flex align-center justify-center">
                  Nikmati kemudahan pembayaran instan tanpa perlu upload bukti transfer. Pesanan otomatis diproses dalam hitungan detik!
                </div>

                <VBtn
                  color="success"
                  size="large"
                  block
                  class="font-weight-bold rounded-lg mt-auto"
                  @click="payWithMidtrans"
                  prepend-icon="ri-secure-payment-line"
                  elevation="2"
                >
                  Bayar Instan Sekarang
                </VBtn>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>

      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.border-t-8 {
  border-top-width: 8px !important;
  border-top-style: solid !important;
}
.border-t-primary {
  border-top-color: rgb(var(--v-theme-primary)) !important;
}
.border-t-success {
  border-top-color: rgb(var(--v-theme-success)) !important;
}
.border-t-warning {
  border-top-color: rgb(var(--v-theme-warning)) !important;
}
.bg-success-lighten-5 {
  background-color: rgba(var(--v-theme-success), 0.05) !important;
}
.tracking-wide {
  letter-spacing: 0.05em !important;
}
</style>

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

            <div class="text-h6 font-weight-bold mb-2">Informasi Pengiriman / Data Login</div>
            <VRow>
              <VCol cols="12">
                <template v-if="invoice.akun_game_login">
                  <VCard variant="tonal" color="success" class="mb-4">
                    <VCardText>
                      <div class="d-flex align-center gap-2 mb-3">
                        <VIcon icon="ri-shield-check-line" color="success" size="24" />
                        <span class="text-h6 font-weight-bold text-success">Data Login Akun Anda</span>
                      </div>
                      
                      <VRow>
                        <VCol cols="12" sm="6" class="mb-2">
                          <div class="text-caption text-medium-emphasis">Email / Username / Nomor HP</div>
                          <div class="text-body-1 font-weight-bold">{{ invoice.akun_game_login.email_akun }}</div>
                        </VCol>
                        <VCol cols="12" sm="6" class="mb-2">
                          <div class="text-caption text-medium-emphasis">Password</div>
                          <div class="text-body-1 font-weight-bold">{{ invoice.akun_game_login.password_akun }}</div>
                        </VCol>
                        <VCol cols="12" sm="6" class="mb-2">
                          <div class="text-caption text-medium-emphasis">Login Via</div>
                          <div class="text-body-1 font-weight-bold">{{ invoice.akun_game_login.login_via }}</div>
                        </VCol>
                      </VRow>
                      
                      <div v-if="invoice.akun_game_login.catatan_akun" class="mt-4">
                        <div class="text-caption text-medium-emphasis">Catatan dari Penjual:</div>
                        <VAlert type="warning" variant="tonal" class="mt-1 mb-0 py-2 text-body-2">
                          {{ invoice.akun_game_login.catatan_akun }}
                        </VAlert>
                      </div>
                    </VCardText>
                  </VCard>
                </template>
                <template v-else>
                  <VAlert type="info" variant="tonal" class="mb-0 text-body-2" icon="ri-whatsapp-line">
                    Jika pesanan telah sukses dan data login tidak muncul di sini, penjual akan mengirimkannya secara manual ke nomor WhatsApp Anda: <strong>{{ invoice.no_whatsapp }}</strong>
                  </VAlert>
                </template>
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

            <div v-if="invoice.nominal_diskon > 0" class="d-flex justify-space-between mt-4 text-success">
              <span class="text-subtitle-1">Diskon Promo</span>
              <span class="text-subtitle-1 font-weight-bold">- Rp {{ Number(invoice.nominal_diskon).toLocaleString('id-ID') }}</span>
            </div>
          </VCardText>
        </VCard>

        <VCard elevation="4" v-if="invoice.status_transaksi === 'Pending'" class="mb-6">
          <VCardItem class="bg-warning text-white">
            <VCardTitle class="text-h6 font-weight-bold d-flex align-center gap-2">
              <VIcon icon="ri-time-line" /> Menunggu Verifikasi Admin
            </VCardTitle>
          </VCardItem>
          <VCardText class="pt-4">
            <p class="mb-0">
              Terima kasih! Bukti transfer Anda sedang kami proses. Mohon bersabar menunggu admin untuk memverifikasi pembayaran Anda.
            </p>
          </VCardText>
        </VCard>

        <VCard elevation="4" v-if="invoice.status_transaksi === 'Unpaid'">
          <VCardItem>
            <VCardTitle class="text-h6 font-weight-bold">Selesaikan Pembayaran</VCardTitle>
          </VCardItem>
          <VCardText>
            <VAlert type="info" variant="tonal" class="mb-4">
              Total tagihan Anda adalah <strong>Rp {{ Number(invoice.total_bayar).toLocaleString('id-ID') }}</strong>.
            </VAlert>
            
            <VRow>
              <VCol cols="12" md="6" v-if="bankAccounts.length > 0">
                <VCard variant="outlined" color="primary" class="h-100">
                  <VCardText>
                    <div class="d-flex align-center gap-2 mb-3">
                      <VIcon icon="ri-bank-line" color="primary" size="24" />
                      <span class="font-weight-bold">Transfer Manual</span>
                    </div>
                    <div class="mb-2">Silakan transfer tepat sejumlah total tagihan ke salah satu rekening berikut:</div>
                    
                    <div v-for="(bank, index) in bankAccounts" :key="index" class="bg-grey-lighten-4 pa-3 rounded text-center mb-3">
                      <div class="text-subtitle-2">{{ bank.bank_name }}</div>
                      <div class="text-h5 font-weight-bold text-primary">{{ bank.bank_account_number }}</div>
                      <div class="text-caption">A/N {{ bank.bank_account_name }}</div>
                    </div>
                    
                    <VBtn
                      color="primary"
                      variant="tonal"
                      block
                      prepend-icon="ri-upload-cloud-2-line"
                      @click="fileInput.click()"
                      :loading="uploadLoading"
                    >
                      Upload Bukti Transfer
                    </VBtn>
                    <input 
                      type="file" 
                      ref="fileInput" 
                      accept="image/*" 
                      style="display: none" 
                      @change="uploadBukti"
                    >
                  </VCardText>
                </VCard>
              </VCol>
              
              <VCol cols="12" :md="bankAccounts.length > 0 ? 6 : 12">
                <VCard variant="outlined" color="success" class="h-100">
                  <VCardText class="d-flex flex-column justify-center h-100">
                    <div class="d-flex align-center gap-2 mb-3">
                      <VIcon icon="ri-qr-code-line" color="success" size="24" />
                      <span class="font-weight-bold">Pembayaran Otomatis</span>
                    </div>
                    <div class="mb-4">Bayar lebih mudah menggunakan QRIS, e-Wallet, atau Virtual Account. Verifikasi instan.</div>
                    <VBtn
                      color="success"
                      size="large"
                      block
                      @click="payWithMidtrans"
                      prepend-icon="ri-secure-payment-line"
                      class="mt-auto"
                    >
                      Bayar Sekarang
                    </VBtn>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

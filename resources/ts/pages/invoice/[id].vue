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

const payWithMidtrans = () => {
  if (!invoice.value || !invoice.value.snap_token) return;

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

            <div v-if="invoice.nominal_diskon > 0" class="d-flex justify-space-between mt-4 text-success">
              <span class="text-subtitle-1">Diskon Promo</span>
              <span class="text-subtitle-1 font-weight-bold">- Rp {{ Number(invoice.nominal_diskon).toLocaleString('id-ID') }}</span>
            </div>
          </VCardText>
        </VCard>

        <VCard elevation="4" v-if="invoice.status_transaksi === 'Unpaid'">
          <VCardItem>
            <VCardTitle class="text-h6 font-weight-bold">Selesaikan Pembayaran</VCardTitle>
          </VCardItem>
          <VCardText>
            <VAlert type="info" variant="tonal" class="mb-4">
              Total tagihan Anda adalah <strong>Rp {{ Number(invoice.total_bayar).toLocaleString('id-ID') }}</strong>. Klik tombol di bawah ini untuk memilih metode pembayaran (QRIS, e-Wallet, Virtual Account, dll).
            </VAlert>
            
            <VBtn
              color="primary"
              size="large"
              block
              class="mt-4"
              @click="payWithMidtrans"
              prepend-icon="ri-secure-payment-line"
            >
              Bayar Sekarang
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

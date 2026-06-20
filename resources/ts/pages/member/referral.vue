<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useClipboard } from '@vueuse/core'

definePage({
  meta: {
    layout: 'frontend',
    public: false,
  },
})

const { copy, copied } = useClipboard()

const loading = ref(true)
const referralData = ref<any>(null)
const userData = ref<any>(null)

onMounted(async () => {
  const userStr = localStorage.getItem('user_data')
  if (userStr) {
    userData.value = JSON.parse(userStr)
  }

  try {
    const res = await axios.get('/api/member/referrals')
    referralData.value = res.data.data
  } catch (error) {
    console.error('Error fetching referrals:', error)
  } finally {
    loading.value = false
  }
})

const copyReferralLink = () => {
  if (referralData.value) {
    // Simulasi link register (sesuaikan dengan domain asli)
    const link = `${window.location.origin}/register?ref=${referralData.value.kode_referral}`
    copy(link)
  }
}
</script>

<template>
  <VContainer>
    <VRow>
      <VCol cols="12" md="4">
        <h2 class="text-h4 font-weight-bold mb-6">Program Afiliasi</h2>
        
        <VCard elevation="3" class="rounded-lg text-center pa-6 mb-6">
          <VIcon icon="ri-share-forward-fill" size="48" color="primary" class="mb-4" />
          <h3 class="text-h6 font-weight-bold mb-2">Kode Referral Anda</h3>
          
          <div v-if="loading">
            <VProgressCircular indeterminate color="primary" class="my-4" />
          </div>
          <div v-else-if="referralData">
            <VChip
              size="x-large"
              color="primary"
              variant="tonal"
              class="font-weight-black text-h5 mt-2 mb-4 pa-6"
            >
              {{ referralData.kode_referral || 'BELUM ADA' }}
            </VChip>
            
            <VBtn
              block
              color="primary"
              :prepend-icon="copied ? 'ri-check-line' : 'ri-file-copy-line'"
              @click="copyReferralLink"
            >
              {{ copied ? 'Berhasil Disalin!' : 'Salin Link Pendaftaran' }}
            </VBtn>
          </div>

          <VDivider class="my-6" />

          <div class="text-left" v-if="referralData">
            <h4 class="font-weight-bold mb-2">Keuntungan Mengundang:</h4>
            <div class="d-flex align-center gap-2 mb-2">
              <VIcon icon="ri-coin-line" color="warning" />
              <span>Dapat <strong>{{ referralData.reward_info.points_per_trx }} Poin</strong> tiap downline belanja</span>
            </div>
            <div class="d-flex align-center gap-2" v-if="referralData.reward_info.balance_per_trx > 0">
              <VIcon icon="ri-wallet-3-line" color="success" />
              <span>Dapat Saldo <strong>Rp {{ referralData.reward_info.balance_per_trx }}</strong> tiap belanja</span>
            </div>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" md="8">
        <h2 class="text-h5 font-weight-bold mb-6 mt-1">Daftar Downline (Teman yang Diundang)</h2>
        
        <VCard elevation="3" class="rounded-lg">
          <VCardText v-if="loading" class="text-center pa-8">
            <VProgressCircular indeterminate color="primary" />
          </VCardText>

          <VTable v-else-if="referralData && referralData.referrals.length > 0" class="text-no-wrap">
            <thead>
              <tr>
                <th class="text-uppercase">Nama Teman</th>
                <th class="text-uppercase">Username</th>
                <th class="text-uppercase">Bergabung Pada</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="ref in referralData.referrals" :key="ref.id">
                <td class="font-weight-medium">
                  <div class="d-flex align-center gap-3">
                    <VAvatar color="primary" variant="tonal" size="32">
                      <span class="text-caption font-weight-bold">{{ ref.nama_lengkap.charAt(0) }}</span>
                    </VAvatar>
                    {{ ref.nama_lengkap }}
                  </div>
                </td>
                <td>{{ ref.username }}</td>
                <td>{{ new Date(ref.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</td>
              </tr>
            </tbody>
          </VTable>

          <VCardText v-else class="text-center pa-10 text-medium-emphasis">
            <VIcon icon="ri-group-line" size="64" class="mb-4 opacity-50" />
            <h3 class="text-h6">Belum ada downline</h3>
            <p>Bagikan kode referral Anda ke teman-teman untuk mulai mendapatkan komisi!</p>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>

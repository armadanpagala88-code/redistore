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
const conversations = ref<any[]>([])
const loading = ref(true)
const userData = ref<any>(null)

onMounted(async () => {
  const userStr = localStorage.getItem('user_data')
  if (userStr) {
    userData.value = JSON.parse(userStr)
  }

  try {
    const res = await axios.get('/api/chat/conversations')
    conversations.value = res.data.data
  } catch (error) {
    console.error('Error fetching conversations:', error)
  } finally {
    loading.value = false
  }
})

const openChat = (id: number) => {
  router.push(`/member/chat/${id}`)
}

const getOtherParty = (conv: any) => {
  if (!userData.value) return 'Unknown'
  return conv.buyer_id === userData.value.id ? conv.seller?.username : conv.buyer?.username
}

const getRole = (conv: any) => {
  if (!userData.value) return ''
  return conv.buyer_id === userData.value.id ? 'Penjual' : 'Pembeli'
}
</script>

<template>
  <VContainer>
    <VRow>
      <VCol cols="12">
        <h2 class="text-h4 font-weight-bold mb-6">Inbox Obrolan</h2>
        
        <VCard elevation="3" class="rounded-lg">
          <VCardText v-if="loading" class="text-center pa-8">
            <VProgressCircular indeterminate color="primary" />
          </VCardText>
          
          <VList v-else-if="conversations.length > 0" lines="two">
            <template v-for="(conv, index) in conversations" :key="conv.id">
              <VListItem @click="openChat(conv.id)" class="py-4">
                <template #prepend>
                  <VAvatar color="primary" variant="tonal" size="50">
                    <VIcon icon="ri-user-smile-line" size="24" />
                  </VAvatar>
                </template>

                <VListItemTitle class="font-weight-bold text-subtitle-1">
                  {{ getOtherParty(conv) }}
                  <VChip size="x-small" color="secondary" class="ml-2">{{ getRole(conv) }}</VChip>
                </VListItemTitle>
                
                <VListItemSubtitle class="mt-1 text-truncate">
                  Akun: {{ conv.akun_game?.judul_akun || 'Akun Game' }}
                </VListItemSubtitle>

                <template #append>
                  <div class="text-caption text-medium-emphasis">
                    {{ new Date(conv.updated_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) }}
                  </div>
                  <VBtn icon variant="text" size="small" color="primary">
                    <VIcon icon="ri-arrow-right-s-line" />
                  </VBtn>
                </template>
              </VListItem>
              <VDivider v-if="index < conversations.length - 1" />
            </template>
          </VList>
          
          <VCardText v-else class="text-center pa-10 text-medium-emphasis">
            <VIcon icon="ri-chat-3-line" size="64" class="mb-4 opacity-50" />
            <h3 class="text-h6">Belum ada obrolan</h3>
            <p>Anda belum memiliki riwayat obrolan dengan penjual atau pembeli.</p>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import axios from 'axios'

definePage({
  meta: {
    layout: 'default', // Admin layout
  },
})

const conversations = ref<any[]>([])
const loading = ref(true)

// Dialog State
const isDialogVisible = ref(false)
const selectedConversation = ref<any>(null)
const selectedMessages = ref<any[]>([])
const isLoadingMessages = ref(false)
const messagesContainer = ref<any>(null)

const fetchConversations = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/admin/chats/conversations', {
      headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` }
    })
    conversations.value = res.data.data
  } catch (error) {
    console.error('Error fetching conversations:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchConversations()
})

const openConversationDetails = async (conv: any) => {
  selectedConversation.value = conv
  isDialogVisible.value = true
  isLoadingMessages.value = true
  selectedMessages.value = []
  
  try {
    const res = await axios.get(`/api/admin/chats/${conv.id}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` }
    })
    selectedMessages.value = res.data.data
    scrollToBottom()
  } catch (error) {
    console.error('Error fetching messages', error)
  } finally {
    isLoadingMessages.value = false
  }
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      const container = messagesContainer.value.$el || messagesContainer.value
      container.scrollTop = container.scrollHeight
    }
  })
}
</script>

<template>
  <div>
    <VCard title="Monitoring Chat (Administrator)">
      <VCardText>
        Halaman ini digunakan oleh Admin untuk memantau aktivitas obrolan antara Penjual dan Pembeli untuk mencegah penipuan atau penyalahgunaan.
      </VCardText>

      <VCardText>
        <VTable class="text-no-wrap border rounded">
          <thead>
            <tr>
              <th class="text-uppercase">ID Chat</th>
              <th class="text-uppercase">Akun Game</th>
              <th class="text-uppercase">Pembeli</th>
              <th class="text-uppercase">Penjual</th>
              <th class="text-uppercase">Dibuat Pada</th>
              <th class="text-uppercase">Aksi</th>
            </tr>
          </thead>
          
          <tbody v-if="loading">
            <tr>
              <td colspan="6" class="text-center pa-4">
                <VProgressCircular indeterminate color="primary" />
              </td>
            </tr>
          </tbody>
          
          <tbody v-else-if="conversations.length > 0">
            <tr v-for="conv in conversations" :key="conv.id">
              <td class="font-weight-medium">#{{ conv.id }}</td>
              <td>
                <div class="font-weight-medium text-truncate" style="max-width: 200px;">{{ conv.akun_game?.judul_akun || 'Akun Game Terhapus' }}</div>
                <div class="text-caption text-medium-emphasis">{{ conv.akun_game?.kategori?.nama_game }}</div>
              </td>
              <td>
                <div class="d-flex align-center gap-2">
                  <VAvatar color="info" variant="tonal" size="30">
                    <span class="text-caption font-weight-bold">{{ conv.buyer?.nama_lengkap?.charAt(0) }}</span>
                  </VAvatar>
                  <div class="font-weight-medium">{{ conv.buyer?.username }}</div>
                </div>
              </td>
              <td>
                <div class="d-flex align-center gap-2">
                  <VAvatar color="warning" variant="tonal" size="30">
                    <span class="text-caption font-weight-bold">{{ conv.seller?.nama_lengkap?.charAt(0) }}</span>
                  </VAvatar>
                  <div class="font-weight-medium">{{ conv.seller?.username }}</div>
                </div>
              </td>
              <td>{{ new Date(conv.created_at).toLocaleDateString('id-ID') }}</td>
              <td>
                <VBtn size="small" color="primary" variant="tonal" @click="openConversationDetails(conv)" prepend-icon="ri-eye-line">
                  Lihat Obrolan
                </VBtn>
              </td>
            </tr>
          </tbody>
          
          <tbody v-else>
            <tr>
              <td colspan="6" class="text-center pa-8 text-medium-emphasis">
                Belum ada aktivitas obrolan.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCardText>
    </VCard>

    <!-- Dialog View Chat -->
    <VDialog v-model="isDialogVisible" max-width="800">
      <VCard :loading="isLoadingMessages" class="d-flex flex-column" height="85vh">
        <VToolbar color="surface" class="border-b" density="compact">
          <VToolbarTitle class="font-weight-bold text-h6">
            Log Obrolan: {{ selectedConversation?.buyer?.username }} vs {{ selectedConversation?.seller?.username }}
          </VToolbarTitle>
          <VSpacer />
          <VBtn icon="ri-close-line" variant="text" @click="isDialogVisible = false" />
        </VToolbar>

        <VCardText 
          ref="messagesContainer" 
          class="flex-grow-1 overflow-y-auto bg-grey-lighten-4 pa-6"
        >
          <div v-if="isLoadingMessages" class="text-center mt-10">
            <VProgressCircular indeterminate color="primary" />
          </div>

          <div v-else-if="selectedMessages.length > 0" class="d-flex flex-column gap-4">
            <div 
              v-for="msg in selectedMessages" 
              :key="msg.id"
              class="d-flex justify-start"
            >
              <div class="d-flex gap-3 flex-row" style="max-width: 80%;">
                <VAvatar :color="msg.sender_id === selectedConversation?.buyer_id ? 'info' : 'warning'" variant="tonal" size="40">
                  <span class="text-caption font-weight-bold">{{ msg.sender?.nama_lengkap?.charAt(0) || 'U' }}</span>
                </VAvatar>

                <div 
                  class="rounded-lg pa-4 elevation-1 bg-white border"
                  :class="[
                    msg.sender_id === selectedConversation?.buyer_id ? 'border-info' : 'border-warning'
                  ]"
                >
                  <div class="d-flex justify-space-between align-center mb-2 gap-4">
                    <div class="text-caption font-weight-bold opacity-90 d-flex align-center gap-2">
                      {{ msg.sender?.username }}
                      <VChip size="x-small" :color="msg.sender_id === selectedConversation?.buyer_id ? 'info' : 'warning'">
                        {{ msg.sender_id === selectedConversation?.buyer_id ? 'Pembeli' : 'Penjual' }}
                      </VChip>
                    </div>
                    <div class="text-caption opacity-70" style="font-size: 0.7rem !important;">
                      {{ new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'short' }) }}
                    </div>
                  </div>
                  
                  <div class="text-body-1" style="white-space: pre-wrap;">{{ msg.pesan }}</div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="text-center mt-10 text-medium-emphasis">
            Belum ada pesan di ruang obrolan ini.
          </div>
        </VCardText>
        
        <VCardText class="bg-grey-lighten-3 text-center py-4 text-caption text-medium-emphasis">
          <VIcon icon="ri-eye-line" class="mr-1 mb-1" size="small" />
          Mode Pantau (Read-Only). Admin tidak dapat membalas obrolan antar pengguna.
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

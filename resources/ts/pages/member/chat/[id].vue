<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

definePage({
  meta: {
    layout: 'frontend',
    public: false,
  },
})

const route = useRoute()
const conversationId = route.params.id

const messages = ref<any[]>([])
const loading = ref(true)
const newMessage = ref('')
const isSending = ref(false)
const userData = ref<any>(null)
const messagesContainer = ref<any>(null)

onMounted(async () => {
  const userStr = localStorage.getItem('user_data')
  if (userStr) {
    userData.value = JSON.parse(userStr)
  }

  await fetchMessages()
})

const fetchMessages = async () => {
  try {
    const res = await axios.get(`/api/chat/${conversationId}`)
    messages.value = res.data.data
    scrollToBottom()
  } catch (error) {
    console.error('Error fetching messages:', error)
  } finally {
    loading.value = false
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim()) return

  isSending.value = true
  try {
    const res = await axios.post(`/api/chat/${conversationId}`, {
      pesan: newMessage.value
    })
    messages.value.push(res.data.data)
    newMessage.value = ''
    scrollToBottom()
  } catch (error) {
    console.error('Error sending message:', error)
  } finally {
    isSending.value = false
  }
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      // For Vuetify, scroll to the bottom of the container
      const container = messagesContainer.value.$el || messagesContainer.value
      container.scrollTop = container.scrollHeight
    }
  })
}

const isMe = (senderId: number) => {
  return userData.value && userData.value.id === senderId
}
</script>

<template>
  <VContainer>
    <VRow justify="center">
      <VCol cols="12" md="8">
        <VCard elevation="4" class="rounded-lg d-flex flex-column" height="80vh">
          <VToolbar color="primary" class="rounded-t-lg">
            <VBtn icon="ri-arrow-left-line" variant="text" to="/member/chat" class="mr-2" />
            <VToolbarTitle class="font-weight-bold">Ruang Obrolan</VToolbarTitle>
            <VSpacer />
            <VBtn icon="ri-refresh-line" variant="text" @click="fetchMessages" />
          </VToolbar>

          <VCardText 
            ref="messagesContainer" 
            class="flex-grow-1 overflow-y-auto bg-grey-lighten-4 pa-4"
          >
            <div v-if="loading" class="text-center mt-10">
              <VProgressCircular indeterminate color="primary" />
            </div>

            <div v-else class="d-flex flex-column gap-3">
              <div 
                v-for="msg in messages" 
                :key="msg.id"
                class="d-flex"
                :class="isMe(msg.sender_id) ? 'justify-end' : 'justify-start'"
              >
                <div 
                  class="message-bubble rounded-lg pa-3 elevation-1"
                  :class="isMe(msg.sender_id) ? 'bg-primary text-white' : 'bg-white text-high-emphasis'"
                  style="max-width: 75%;"
                >
                  <div class="text-caption font-weight-bold mb-1 opacity-80" v-if="!isMe(msg.sender_id)">
                    {{ msg.sender?.username || 'User' }}
                  </div>
                  <div class="text-body-1" style="white-space: pre-wrap;">{{ msg.pesan }}</div>
                  <div class="text-caption text-right opacity-70 mt-1" style="font-size: 0.7rem !important;">
                    {{ new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                  </div>
                </div>
              </div>
            </div>
          </VCardText>

          <VDivider />

          <VCardActions class="pa-4 bg-white rounded-b-lg">
            <VForm @submit.prevent="sendMessage" class="w-100 d-flex align-center gap-2">
              <VTextField
                v-model="newMessage"
                placeholder="Ketik pesan..."
                variant="outlined"
                density="compact"
                hide-details
                bg-color="grey-lighten-4"
                class="flex-grow-1 rounded-pill"
              />
              <VBtn 
                type="submit" 
                color="primary" 
                variant="elevated" 
                icon="ri-send-plane-fill"
                :loading="isSending"
                :disabled="!newMessage.trim()"
              />
            </VForm>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>

<style scoped>
.message-bubble {
  word-wrap: break-word;
  position: relative;
}
</style>

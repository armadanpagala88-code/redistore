<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const categories = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const response = await axios.get('/api/kategori-game')
    categories.value = response.data.data
  } catch (error) {
    console.error('Error fetching categories:', error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <h2 class="text-h4 mb-6 font-weight-bold">Katalog Game</h2>
    
    <VRow v-if="loading">
      <VCol cols="12" class="text-center mt-12">
        <VProgressCircular indeterminate color="primary" size="64" />
      </VCol>
    </VRow>

    <VRow v-else>
      <VCol
        v-for="cat in categories"
        :key="cat.id"
        cols="12"
        md="4"
        sm="6"
      >
        <VCard elevation="4" class="h-100 d-flex flex-column transition-swing hover-elevate">
          <VImg
            :src="cat.gambar_logo && cat.gambar_logo !== '' ? `/images/${cat.gambar_logo}` : 'https://placehold.co/400x200?text=Game'"
            height="200"
            cover
            class="bg-grey-lighten-2"
          />
          <VCardItem class="flex-grow-1">
            <VCardTitle class="text-h6 font-weight-bold">{{ cat.nama_game }}</VCardTitle>
            <VCardSubtitle class="text-body-2 mt-2">{{ cat.deskripsi }}</VCardSubtitle>
          </VCardItem>
          
          <VCardActions class="pa-4 pt-0 mt-auto">
            <VBtn
              color="primary"
              variant="elevated"
              block
              size="large"
              :to="`/game/${cat.slug}`"
            >
              Top Up Sekarang
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.hover-elevate:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
</style>

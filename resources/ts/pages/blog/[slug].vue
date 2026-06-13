<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const route = useRoute()
const router = useRouter()
const artikel = ref<any>(null)
const loading = ref(true)

const fetchArtikel = async () => {
  try {
    const res = await axios.get(`/api/artikels/${route.params.slug}`)
    artikel.value = res.data.data
  } catch (e) {
    console.error(e)
    router.push('/blog') // Redirect if not found
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchArtikel()
})

const formatDate = (dateString: string) => {
  const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute:'2-digit' }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}
</script>

<template>
  <div>
    <VRow v-if="loading" justify="center">
      <VCol cols="12" class="text-center py-12">
        <VProgressCircular indeterminate color="primary" size="64" />
      </VCol>
    </VRow>

    <VRow v-else-if="artikel" justify="center">
      <VCol cols="12" md="10" lg="8">
        <VBtn variant="text" prepend-icon="ri-arrow-left-line" to="/blog" class="mb-6 px-0 text-medium-emphasis">
          Kembali ke Blog
        </VBtn>

        <VCard elevation="0" class="bg-transparent">
          <div class="mb-4 d-flex align-center gap-4 text-medium-emphasis text-body-2">
            <VChip color="primary" size="small" variant="tonal">News & Tips</VChip>
            <span><VIcon icon="ri-calendar-line" size="small" class="mr-1" />{{ formatDate(artikel.created_at) }}</span>
            <span><VIcon icon="ri-eye-line" size="small" class="mr-1" />{{ artikel.views }} kali dibaca</span>
          </div>

          <h1 class="text-h3 font-weight-black mb-6 text-primary">{{ artikel.judul }}</h1>

          <!-- Gambar -->
          <VImg
            v-if="artikel.gambar"
            :src="artikel.gambar"
            cover
            class="rounded-lg mb-8"
            max-height="400"
          ></VImg>
          <div v-else class="bg-primary-lighten-4 rounded-lg mb-8 d-flex align-center justify-center" style="height: 300px">
            <VIcon icon="ri-image-line" size="100" color="primary" opacity="0.5" />
          </div>

          <!-- Konten Artikel -->
          <!-- NOTE: render HTML karena artikel.konten berasal dari WYSIWYG editor admin -->
          <div class="artikel-content text-body-1" v-html="artikel.konten"></div>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style lang="scss">
/* Styling internal content dari v-html */
.artikel-content {
  line-height: 1.8;
  color: #334155;

  p {
    margin-bottom: 1.5rem;
  }

  h2, h3, h4 {
    color: #0f172a;
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 700;
  }

  img {
    max-width: 100%;
    border-radius: 8px;
    margin: 1rem 0;
  }

  ul, ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
  }
}
</style>

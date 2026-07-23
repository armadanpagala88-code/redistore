<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const akunGames = ref<any[]>([])
const banners = ref<any[]>([])
const flashSales = ref<any[]>([])
const loading = ref(true)
const currentTime = ref(new Date().getTime())
const selectedCategory = ref<string>('Semua')

onMounted(async () => {
  // Update time for countdown every second
  setInterval(() => {
    currentTime.value = new Date().getTime()
  }, 1000)

  try {
    const [akunRes, bannerRes, fsRes] = await Promise.all([
      axios.get('/api/akun-games'),
      axios.get('/api/banners'),
      axios.get('/api/public/flash-sales')
    ])
    akunGames.value = akunRes.data.data
    banners.value = bannerRes.data.data
    flashSales.value = fsRes.data.data
  } catch (error) {
    console.error('Error fetching data:', error)
  } finally {
    loading.value = false
  }
})

const getImageUrl = (path: string) => {
  if (!path) return 'https://placehold.co/400x400/f1f5f9/94a3b8.png?text=Icon'
  return path.startsWith('http') ? path : `/img/${path}`
}

const getAkunImage = (path: string) => {
  if (!path) return 'https://placehold.co/400x400/f1f5f9/94a3b8.png?text=Image'
  return `/img/akun/${path}`
}

const formatRupiah = (angka: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka)
}

const getCountdown = (endTimeStr: string) => {
  const endTime = new Date(endTimeStr).getTime()
  const distance = endTime - currentTime.value

  if (distance < 0) return 'Berakhir'

  const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
  const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))
  const seconds = Math.floor((distance % (1000 * 60)) / 1000)

  return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
}

const getGameColor = (nama_game: string) => {
  const name = (nama_game || '').toLowerCase()
  if (name.includes('mobile legends') || name.includes('mlbb')) return 'info'
  if (name.includes('free fire') || name.includes('ff')) return 'warning'
  if (name.includes('genshin')) return 'success'
  if (name.includes('pubg')) return 'secondary'
  if (name.includes('valorant')) return 'error'
  return 'primary'
}

const uniqueCategories = computed(() => {
  const cats = new Set<string>()
  akunGames.value.forEach(akun => {
    if (akun.kategori?.nama_game) {
      cats.add(akun.kategori.nama_game)
    }
  })
  return ['Semua', ...Array.from(cats)]
})

const filteredAkunGames = computed(() => {
  if (selectedCategory.value === 'Semua') return akunGames.value
  return akunGames.value.filter(akun => akun.kategori?.nama_game === selectedCategory.value)
})
</script>

<template>
  <div class="unipin-style-container">
    <VRow v-if="loading">
      <VCol cols="12" class="text-center mt-12">
        <VProgressCircular indeterminate color="primary" size="64" />
      </VCol>
    </VRow>

    <template v-else>
      <!-- Hero Carousel with Banners -->
      <VCarousel
        v-if="banners.length > 0"
        height="450"
        hide-delimiter-background
        show-arrows="hover"
        class="rounded-lg overflow-hidden mb-12 elevation-5 unipin-carousel"
        cycle
      >
        <VCarouselItem
          v-for="banner in banners"
          :key="banner.id"
        >
          <div class="carousel-bg" :style="`background-image: url('${getImageUrl(banner.gambar_banner)}')`">
            <div class="carousel-overlay d-flex align-end pb-8 pl-8">
              <div>
                <VChip color="primary" size="small" class="mb-2 font-weight-bold">PROMO</VChip>
                <h2 class="text-h4 font-weight-bold text-white mb-1 text-shadow">{{ banner.judul }}</h2>
                <p class="text-body-2 text-white text-shadow-sm mb-4 max-w-500">{{ banner.deskripsi }}</p>
                <VBtn
                  v-if="banner.link_tujuan"
                  color="primary"
                  class="font-weight-bold px-6"
                  :href="banner.link_tujuan"
                  target="_blank"
                >
                  Lihat Detail
                </VBtn>
              </div>
            </div>
          </div>
        </VCarouselItem>
      </VCarousel>
      
      <!-- Empty State for Banner -->
      <div v-else class="mb-12 rounded-lg d-flex align-center justify-center empty-banner-box">
        <div class="text-center pa-8">
          <VIcon icon="ri-image-add-line" size="48" color="primary" class="mb-3 opacity-50" />
          <h3 class="text-h6 font-weight-bold text-primary mb-1">Space Iklan Tersedia</h3>
          <p class="text-caption text-medium-emphasis">Login sebagai admin untuk mengupload banner promo di sini.</p>
        </div>
      </div>

      <!-- Flash Sale Section -->
      <div v-for="fs in flashSales" :key="fs.id" class="mb-12">
        <div class="d-flex align-center mb-6 bg-error rounded-lg pa-4 elevation-3 justify-space-between">
          <h2 class="text-h5 font-weight-bold text-white d-flex align-center gap-2 mb-0">
            <VIcon icon="ri-flashlight-fill" color="warning" size="28" class="animate-pulse" />
            {{ fs.nama_promo }}
          </h2>
          <div class="d-flex align-center gap-3">
            <span class="text-white font-weight-medium">Berakhir Dalam:</span>
            <VChip color="white" variant="elevated" class="text-error font-weight-bold text-h6 px-4" size="large">
              {{ getCountdown(fs.waktu_selesai) }}
            </VChip>
          </div>
        </div>

        <VRow dense class="match-unipin-grid">
          <VCol
            v-for="item in fs.items"
            :key="item.id"
            cols="6"
            sm="4"
            md="3"
            lg="3"
          >
            <VCard elevation="2" class="h-100 d-flex flex-column unipin-card rounded-lg border-error position-relative">
              <VChip 
                color="error" 
                size="small" 
                class="position-absolute font-weight-bold" 
                style="top: 10px; right: 10px; z-index: 2;"
              >
                Diskon!
              </VChip>
              <div class="text-center pt-4">
                <VIcon icon="ri-ticket-2-line" size="64" color="primary" class="opacity-80" />
              </div>
              <VCardItem class="pa-4 text-left flex-grow-1 d-flex flex-column justify-end">
                <VCardTitle class="text-subtitle-1 font-weight-bold text-high-emphasis mb-1 line-clamp-2">
                  {{ item.produk?.nama_produk || 'Voucher Game' }}
                </VCardTitle>
                <div class="d-flex flex-column mt-2">
                  <span class="text-decoration-line-through text-caption text-medium-emphasis">{{ formatRupiah(item.produk?.harga || 0) }}</span>
                  <div class="text-error font-weight-black text-h6">{{ formatRupiah(item.harga_flash_sale) }}</div>
                </div>
                <div class="d-flex justify-space-between align-center mt-4">
                  <VProgressLinear
                    :model-value="(item.stok_promo / 100) * 100"
                    color="error"
                    height="8"
                    class="rounded-pill mr-3"
                  />
                  <span class="text-caption font-weight-bold text-error whitespace-nowrap">Sisa {{ item.stok_promo }}</span>
                </div>
                <VBtn size="small" color="error" class="w-100 rounded-pill font-weight-bold mt-4" :to="`/produk/${item.produk?.id}`">Beli Sekarang</VBtn>
              </VCardItem>
            </VCard>
          </VCol>
        </VRow>
      </div>


      <!-- Section Title ala UniPin -->
      <div id="marketplace" class="d-flex flex-column flex-md-row align-md-center mb-6 justify-space-between mt-8 gap-4">
        <h2 class="text-h5 font-weight-bold text-high-emphasis d-flex align-center gap-2">
          <VIcon icon="ri-store-2-fill" color="primary" size="28" />
          Marketplace Akun Game
        </h2>
        
        <!-- Category Filter -->
        <VChipGroup
          v-model="selectedCategory"
          mandatory
          class="custom-chip-group"
        >
          <VChip
            v-for="cat in uniqueCategories"
            :key="cat"
            :value="cat"
            variant="elevated"
            :class="['font-weight-medium px-4 mx-1', selectedCategory === cat ? 'text-white' : 'text-high-emphasis']"
            :color="selectedCategory === cat ? 'primary' : 'surface'"
          >
            {{ cat }}
          </VChip>
        </VChipGroup>
      </div>

      <!-- Game Grid ala UniPin -->
      <VRow dense class="match-unipin-grid">
        <VCol
          v-for="akun in filteredAkunGames"
          :key="akun.id"
          cols="6"
          sm="4"
          md="3"
          lg="3"
        >
          <VCard elevation="2" class="h-100 d-flex flex-column unipin-card rounded-lg" :to="`/akun/${akun.id}`">
            <div class="img-wrapper bg-surface" style="position: relative;">
              <VImg
                :src="akun.gambar_utama ? getAkunImage(akun.gambar_utama) : getImageUrl(akun.kategori?.gambar_logo)"
                height="180"
                cover
                class="game-img"
              />
              <VChip 
                :color="getGameColor(akun.kategori?.nama_game)" 
                size="small" 
                variant="elevated"
                class="font-weight-bold position-absolute" 
                style="bottom: 8px; left: 8px; z-index: 2;"
              >
                {{ akun.kategori?.nama_game }}
              </VChip>
            </div>
            
            <VCardItem class="pa-4 text-left card-content flex-grow-1 d-flex flex-column justify-space-between">
              <div>
                <VCardTitle class="text-subtitle-1 font-weight-bold text-high-emphasis mb-1 line-clamp-2" style="line-height: 1.3;">{{ akun.judul_akun }}</VCardTitle>
                <div class="text-success font-weight-black text-h6 mt-2">{{ formatRupiah(akun.harga) }}</div>
              </div>
              <div class="d-flex justify-space-between align-center mt-4">
                <span class="text-caption text-medium-emphasis">Via: {{ akun.login_via }}</span>
                <VBtn size="small" color="primary" variant="tonal" class="rounded-pill font-weight-bold px-4">Beli</VBtn>
              </div>
            </VCardItem>
          </VCard>
        </VCol>
        
        <VCol cols="12" v-if="filteredAkunGames.length === 0">
          <div class="text-center pa-12">
            <VIcon icon="ri-store-3-line" size="64" color="grey-lighten-1" class="mb-4" />
            <h3 class="text-h5 font-weight-bold text-medium-emphasis">Belum ada akun game yang dijual</h3>
          </div>
        </VCol>
      </VRow>
    </template>
  </div>
</template>

<style scoped>
/* UniPin Style Tweaks */
.unipin-carousel {
  border-radius: 12px !important;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}

.carousel-bg {
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
}

.carousel-overlay {
  width: 100%;
  height: 100%;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.2) 60%, rgba(0,0,0,0) 100%);
}

.empty-banner-box {
  background: rgba(var(--v-theme-primary), 0.05);
  border: 1px dashed rgba(var(--v-theme-primary), 0.3);
  min-height: 200px;
}

.text-shadow {
  text-shadow: 0 2px 8px rgba(0,0,0,0.8);
}

.text-shadow-sm {
  text-shadow: 0 1px 4px rgba(0,0,0,0.8);
}

.max-w-500 {
  max-width: 500px;
}

/* UniPin Game Card */
.match-unipin-grid {
  margin: -8px;
}
.match-unipin-grid > .v-col {
  padding: 8px;
}

.unipin-card {
  background: #ffffff !important; /* Latar warna putih */
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  cursor: pointer;
  overflow: hidden;
}

.unipin-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
  border-color: rgba(var(--v-theme-primary), 0.5);
}

.img-wrapper {
  position: relative;
  overflow: hidden;
  border-bottom: 2px solid rgba(var(--v-theme-primary), 0.8);
}

.game-img {
  transition: transform 0.3s ease;
}

.unipin-card:hover .game-img {
  transform: scale(1.05);
}

.card-content {
  background: #ffffff;
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: normal;
}

.opacity-50 {
  opacity: 0.5;
}
</style>

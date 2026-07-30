<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const route = useRoute()
const router = useRouter()

// ── State ────────────────────────────────────────────────────────────────────
const kategoris      = ref<any[]>([])
const products       = ref<any[]>([])
const paginationMeta = ref<any>(null)
const loadingKat     = ref(true)
const loadingProd    = ref(true)

const searchQuery       = ref((route.query.search as string) || '')
const selectedKategori  = ref((route.query.kategori as string) || '')
const selectedSort      = ref((route.query.sort as string) || 'terbaru')
const currentPage       = ref(Number(route.query.page) || 1)

const sortOptions = [
  { title: 'Terbaru', value: 'terbaru' },
  { title: 'Termurah', value: 'harga_asc' },
  { title: 'Termahal', value: 'harga_desc' },
]

// ── Computed ─────────────────────────────────────────────────────────────────
const activeKategori = computed(() =>
  kategoris.value.find(k => k.slug === selectedKategori.value) ?? null
)

const pageTitle = computed(() => {
  if (activeKategori.value) return activeKategori.value.nama_game
  if (searchQuery.value)    return `Hasil: "${searchQuery.value}"`
  return 'Semua Produk'
})

const totalPages = computed(() =>
  paginationMeta.value?.last_page ?? 1
)

// ── Methods ───────────────────────────────────────────────────────────────────
const getImageUrl = (path: string) => {
  if (!path) return 'https://placehold.co/400x400/f1f5f9/94a3b8.png?text=Game'
  return path.startsWith('http') ? path : `/img/kategori/${path}`
}

const getAkunImage = (path: string) => {
  if (!path) return 'https://placehold.co/400x400/f1f5f9/94a3b8.png?text=Akun'
  return `/img/akun/${path}`
}

const formatRupiah = (angka: number) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka)

const getGameColor = (nama_game: string) => {
  const name = (nama_game || '').toLowerCase()
  if (name.includes('mobile legends') || name.includes('mlbb')) return 'info'
  if (name.includes('free fire') || name.includes('ff'))         return 'warning'
  if (name.includes('genshin'))                                  return 'success'
  if (name.includes('pubg'))                                     return 'secondary'
  if (name.includes('valorant'))                                 return 'error'
  return 'primary'
}

const syncUrlToState = () => {
  searchQuery.value      = (route.query.search as string) || ''
  selectedKategori.value = (route.query.kategori as string) || ''
  selectedSort.value     = (route.query.sort as string) || 'terbaru'
  currentPage.value      = Number(route.query.page) || 1
}

const pushUrl = () => {
  const query: Record<string, string> = {}
  if (searchQuery.value)      query.search   = searchQuery.value
  if (selectedKategori.value) query.kategori = selectedKategori.value
  if (selectedSort.value !== 'terbaru') query.sort = selectedSort.value
  if (currentPage.value > 1)  query.page     = String(currentPage.value)
  router.replace({ query })
}

const fetchKategoris = async () => {
  loadingKat.value = true
  try {
    const res = await axios.get('/api/kategori-game')
    kategoris.value = res.data.data
  } catch (e) {
    console.error('Gagal load kategori:', e)
  } finally {
    loadingKat.value = false
  }
}

const fetchProducts = async () => {
  loadingProd.value = true
  try {
    const params: Record<string, any> = {
      page:    currentPage.value,
      sort:    selectedSort.value,
    }
    if (selectedKategori.value) params.kategori_slug = selectedKategori.value
    if (searchQuery.value)      params.search        = searchQuery.value

    const res = await axios.get('/api/akun-games', { params })

    // Support both paginated (data.data + meta) and plain array
    if (res.data.data?.data) {
      products.value       = res.data.data.data
      paginationMeta.value = res.data.data
    } else {
      products.value       = res.data.data
      paginationMeta.value = null
    }
  } catch (e) {
    console.error('Gagal load produk:', e)
  } finally {
    loadingProd.value = false
  }
}

// ── Actions ───────────────────────────────────────────────────────────────────
const selectKategori = (slug: string) => {
  selectedKategori.value = slug
  currentPage.value      = 1
  pushUrl()
}

const onSearch = () => {
  currentPage.value = 1
  pushUrl()
}

const onSortChange = () => {
  currentPage.value = 1
  pushUrl()
}

const onPageChange = (page: number) => {
  currentPage.value = page
  pushUrl()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const clearFilters = () => {
  searchQuery.value      = ''
  selectedKategori.value = ''
  selectedSort.value     = 'terbaru'
  currentPage.value      = 1
  pushUrl()
}

// ── Watchers ──────────────────────────────────────────────────────────────────
// Setiap kali URL query berubah, re-fetch produk
watch(() => route.query, () => {
  syncUrlToState()
  fetchProducts()
}, { deep: true })

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(async () => {
  syncUrlToState()
  await Promise.all([fetchKategoris(), fetchProducts()])
})
</script>

<template>
  <div class="marketplace-page">
    <!-- ── Page Header ─────────────────────────────────────────────────────── -->
    <div class="mp-header mb-8">
      <div class="d-flex align-center gap-3 mb-2">
        <VIcon icon="ri-store-2-fill" color="primary" size="36" />
        <div>
          <h1 class="text-h4 font-weight-black text-high-emphasis mb-0">
            Marketplace Akun Game
          </h1>
          <p class="text-body-2 text-medium-emphasis mb-0">
            Temukan akun game impianmu — harga terbaik, transaksi aman
          </p>
        </div>
      </div>
      <!-- Breadcrumb -->
      <VBreadcrumbs
        :items="[
          { title: 'Home', to: '/' },
          { title: 'Marketplace', disabled: !activeKategori },
          ...(activeKategori ? [{ title: activeKategori.nama_game, disabled: true }] : [])
        ]"
        class="pa-0 mt-2"
        density="compact"
      />
    </div>

    <!-- ── Main Layout: Sidebar + Content ────────────────────────────────── -->
    <VRow>
      <!-- Sidebar Kategori -->
      <VCol cols="12" md="3" lg="2">
        <VCard class="kategori-sidebar rounded-xl elevation-1 mb-4 mb-md-0" style="position: sticky; top: 90px;">
          <div class="sidebar-header pa-4 pb-2 d-flex align-center gap-2">
            <VIcon icon="ri-layout-grid-line" color="primary" size="20" />
            <span class="text-subtitle-2 font-weight-bold text-high-emphasis">Kategori Game</span>
          </div>
          <VDivider />
          <VList density="compact" nav class="pa-2">
            <!-- Semua Produk -->
            <VListItem
              :active="selectedKategori === ''"
              active-color="primary"
              rounded="lg"
              class="mb-1 kategori-item"
              @click="selectKategori('')"
            >
              <template #prepend>
                <VAvatar size="32" color="primary" variant="tonal" class="mr-2">
                  <VIcon icon="ri-apps-2-fill" size="16" />
                </VAvatar>
              </template>
              <VListItemTitle class="text-body-2 font-weight-medium">Semua</VListItemTitle>
              <template #append>
                <VChip size="x-small" color="primary" variant="tonal">
                  {{ kategoris.reduce((s, k) => s + (k.akun_games_count ?? 0), 0) }}
                </VChip>
              </template>
            </VListItem>

            <!-- Loading skeleton -->
            <template v-if="loadingKat">
              <VListItem v-for="n in 5" :key="n" class="mb-1">
                <VSkeleton-Loader type="list-item-avatar" />
              </VListItem>
            </template>

            <!-- Kategori list -->
            <VListItem
              v-for="kat in kategoris"
              :key="kat.id"
              :active="selectedKategori === kat.slug"
              active-color="primary"
              rounded="lg"
              class="mb-1 kategori-item"
              @click="selectKategori(kat.slug)"
            >
              <template #prepend>
                <VAvatar size="32" class="mr-2" :color="getGameColor(kat.nama_game)" variant="tonal">
                  <VImg
                    v-if="kat.gambar_logo"
                    :src="getImageUrl(kat.gambar_logo)"
                    cover
                  />
                  <VIcon v-else icon="ri-gamepad-line" size="16" />
                </VAvatar>
              </template>
              <VListItemTitle class="text-body-2 font-weight-medium text-truncate">
                {{ kat.nama_game }}
              </VListItemTitle>
              <template #append>
                <VChip size="x-small" :color="getGameColor(kat.nama_game)" variant="tonal">
                  {{ kat.akun_games_count ?? 0 }}
                </VChip>
              </template>
            </VListItem>
          </VList>
        </VCard>
      </VCol>

      <!-- Main Content -->
      <VCol cols="12" md="9" lg="10">

        <!-- ── Toolbar: Search + Sort ──────────────────────────────────── -->
        <VCard class="rounded-xl elevation-1 mb-6 pa-4">
          <VRow align="center" no-gutters class="gap-3">
            <!-- Search -->
            <VCol cols="12" sm="7" md="8">
              <VTextField
                v-model="searchQuery"
                placeholder="Cari nama akun, game..."
                prepend-inner-icon="ri-search-line"
                variant="outlined"
                density="compact"
                hide-details
                clearable
                rounded="lg"
                @keyup.enter="onSearch"
                @click:clear="() => { searchQuery = ''; onSearch() }"
              />
            </VCol>
            <!-- Sort -->
            <VCol cols="12" sm="5" md="4">
              <VSelect
                v-model="selectedSort"
                :items="sortOptions"
                item-title="title"
                item-value="value"
                variant="outlined"
                density="compact"
                hide-details
                rounded="lg"
                prepend-inner-icon="ri-sort-desc"
                @update:model-value="onSortChange"
              />
            </VCol>
          </VRow>

          <!-- Active filter chips -->
          <div v-if="selectedKategori || searchQuery" class="d-flex align-center gap-2 mt-3 flex-wrap">
            <span class="text-caption text-medium-emphasis">Filter aktif:</span>
            <VChip
              v-if="activeKategori"
              closable
              size="small"
              color="primary"
              @click:close="selectKategori('')"
            >
              {{ activeKategori.nama_game }}
            </VChip>
            <VChip
              v-if="searchQuery"
              closable
              size="small"
              color="secondary"
              @click:close="() => { searchQuery = ''; onSearch() }"
            >
              Pencarian: "{{ searchQuery }}"
            </VChip>
            <VBtn
              size="x-small"
              variant="text"
              color="error"
              @click="clearFilters"
            >
              Reset Semua
            </VBtn>
          </div>
        </VCard>

        <!-- ── Section Title ─────────────────────────────────────────── -->
        <div class="d-flex align-center justify-space-between mb-4">
          <div>
            <h2 class="text-h6 font-weight-bold text-high-emphasis mb-0">
              {{ pageTitle }}
            </h2>
            <span v-if="paginationMeta" class="text-caption text-medium-emphasis">
              {{ paginationMeta.total }} produk ditemukan
            </span>
          </div>
          <VBtn
            v-if="searchQuery"
            size="small"
            color="primary"
            variant="tonal"
            prepend-icon="ri-search-line"
            @click="onSearch"
          >
            Cari
          </VBtn>
        </div>

        <!-- ── Product Grid ──────────────────────────────────────────── -->
        <template v-if="loadingProd">
          <VRow dense>
            <VCol v-for="n in 12" :key="n" cols="6" sm="4" md="3">
              <VCard class="rounded-xl" elevation="1">
                <VSkeletonLoader type="image, article" />
              </VCard>
            </VCol>
          </VRow>
        </template>

        <template v-else-if="products.length > 0">
          <VRow dense class="mp-grid">
            <VCol
              v-for="akun in products"
              :key="akun.id"
              cols="6"
              sm="4"
              md="4"
              lg="3"
            >
              <VCard
                elevation="2"
                class="h-100 d-flex flex-column mp-card rounded-xl"
                :to="`/akun/${akun.id}`"
              >
                <!-- Gambar -->
                <div class="img-wrapper" style="position: relative;">
                  <VImg
                    :src="akun.gambar_utama ? getAkunImage(akun.gambar_utama) : getImageUrl(akun.kategori?.gambar_logo)"
                    height="170"
                    cover
                    class="game-img"
                  />
                  <!-- Badge game -->
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

                <VCardItem class="pa-3 flex-grow-1 d-flex flex-column justify-space-between card-content">
                  <div>
                    <VCardTitle class="text-subtitle-2 font-weight-bold text-high-emphasis line-clamp-2 pa-0 mb-1">
                      {{ akun.judul_akun }}
                    </VCardTitle>
                    <div class="text-success font-weight-black text-h6">
                      {{ formatRupiah(akun.harga) }}
                    </div>
                  </div>
                  <div class="d-flex justify-space-between align-center mt-3">
                    <span class="text-caption text-medium-emphasis">Via: {{ akun.login_via }}</span>
                    <VBtn size="x-small" color="primary" variant="tonal" class="rounded-pill font-weight-bold px-3">
                      Beli
                    </VBtn>
                  </div>
                </VCardItem>
              </VCard>
            </VCol>
          </VRow>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="d-flex justify-center mt-8">
            <VPagination
              v-model="currentPage"
              :length="totalPages"
              :total-visible="7"
              rounded="circle"
              active-color="primary"
              @update:model-value="onPageChange"
            />
          </div>
        </template>

        <!-- Empty State -->
        <template v-else>
          <VCard class="rounded-xl elevation-0 border pa-12 text-center" style="border-style: dashed !important;">
            <VIcon icon="ri-store-3-line" size="72" color="grey-lighten-1" class="mb-4" />
            <h3 class="text-h5 font-weight-bold text-medium-emphasis mb-2">
              Tidak ada produk ditemukan
            </h3>
            <p class="text-body-2 text-medium-emphasis mb-6">
              Coba ubah filter atau kata kunci pencarian Anda.
            </p>
            <VBtn color="primary" variant="tonal" @click="clearFilters" prepend-icon="ri-refresh-line">
              Reset Filter
            </VBtn>
          </VCard>
        </template>

      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.marketplace-page {
  min-height: 70vh;
}

/* ── Sidebar ───────────────────────────────────────────────── */
.kategori-sidebar {
  background: #ffffff;
  border: 1px solid rgba(0, 0, 0, 0.06);
}

.sidebar-header {
  background: rgba(var(--v-theme-primary), 0.04);
}

.kategori-item {
  transition: all 0.2s ease;
}

.kategori-item:hover {
  background: rgba(var(--v-theme-primary), 0.06) !important;
}

/* ── Cards ─────────────────────────────────────────────────── */
.mp-grid {
  margin: -6px;
}
.mp-grid > .v-col {
  padding: 6px;
}

.mp-card {
  background: #ffffff !important;
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
  cursor: pointer;
  overflow: hidden;
}

.mp-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12) !important;
  border-color: rgba(var(--v-theme-primary), 0.4) !important;
}

.img-wrapper {
  overflow: hidden;
  border-bottom: 2px solid rgba(var(--v-theme-primary), 0.7);
}

.game-img {
  transition: transform 0.35s ease;
}

.mp-card:hover .game-img {
  transform: scale(1.07);
}

.card-content {
  background: #ffffff;
}

/* ── Typography ─────────────────────────────────────────────── */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: normal;
  line-height: 1.35 !important;
}
</style>

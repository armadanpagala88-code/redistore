<script lang="ts" setup>
import { ref, watch, onMounted, computed } from 'vue'
import axios from 'axios'
import navItems from '@/navigation/vertical'
import { useConfigStore } from '@core/stores/config'
import { themeConfig } from '@themeConfig'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import NavBarI18n from '@core/components/I18n.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'

const configStore = useConfigStore()

// ℹ️ Provide animation name for vertical nav collapse icon.
const verticalNavHeaderActionAnimationName = ref<'rotate-180' | 'rotate-back-180' | null>(null)

watch([
  () => configStore.isVerticalNavCollapsed,
  () => configStore.isAppRTL,
], val => {
  if (configStore.isAppRTL)
    verticalNavHeaderActionAnimationName.value = val[0] ? 'rotate-back-180' : 'rotate-180'
  else
    verticalNavHeaderActionAnimationName.value = val[0] ? 'rotate-180' : 'rotate-back-180'
}, { immediate: true })

const pendingAkunCount = ref(0)

const fetchPendingAkun = async () => {
  try {
    const res = await axios.get('/api/admin/akun-game/stats')
    if (res.data.success && res.data.data.pending > 0) {
      pendingAkunCount.value = res.data.data.pending
    } else {
      pendingAkunCount.value = 0
    }
  } catch (error) {
    console.error('Failed to fetch pending akun stats', error)
  }
}

onMounted(() => {
  fetchPendingAkun()
  // Optional: setInterval(fetchPendingAkun, 30000)
})

const dynamicNavItems = computed(() => {
  return navItems.map(item => {
    if (item.title === 'Persetujuan Akun') {
      return {
        ...item,
        badgeContent: pendingAkunCount.value > 0 ? pendingAkunCount.value.toString() : undefined,
        badgeClass: 'bg-error text-white'
      }
    }
    return item
  })
})
</script>

<template>
  <VerticalNavLayout :nav-items="dynamicNavItems">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <IconBtn
          id="vertical-nav-toggle-btn"
          class="ms-n2 d-lg-none"
          @click="toggleVerticalOverlayNavActive(true)"
        >
          <VIcon icon="ri-menu-line" />
        </IconBtn>



        <NavbarThemeSwitcher />

        <VSpacer />

        <NavBarI18n
          v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
          :languages="themeConfig.app.i18n.langConfig"
        />
        <UserProfile />
      </div>
    </template>

    <!-- 👉 Pages -->
    <slot />

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Customizer -->
    <TheCustomizer />
  </VerticalNavLayout>
</template>

<style lang="scss">
@keyframes rotate-180 {
  from { transform: rotate(0deg); }
  to { transform: rotate(180deg); }
}

@keyframes rotate-back-180 {
  from { transform: rotate(180deg); }
  to { transform: rotate(0deg); }
}

.layout-vertical-nav {
  .nav-header {
    .header-action {
      animation-duration: 0.35s;
      animation-fill-mode: forwards;
      animation-name: v-bind(verticalNavHeaderActionAnimationName);
      transform: rotate(0deg);
    }
  }
}
</style>

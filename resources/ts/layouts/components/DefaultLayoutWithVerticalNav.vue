<script lang="ts" setup>
import { ref, watch, onMounted, reactive } from 'vue'
import navItems from '@/navigation/vertical'
import axios from 'axios'
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

// Reactive nav items to avoid infinite render loops with computed arrays
const dynamicNavItems = reactive([...navItems])

const fetchStats = async () => {
  try {
    const [akunRes, transRes, wdRes] = await Promise.all([
      axios.get('/api/admin/akun-game/stats'),
      axios.get('/api/admin/transaksi/stats'),
      axios.get('/api/admin/withdrawals/stats')
    ])
    
    if (akunRes.data.success) {
      const pending = akunRes.data.data.pending
      const item = dynamicNavItems.find(i => i.title === 'Persetujuan Akun')
      if (item) {
        item.badgeContent = pending > 0 ? pending.toString() : undefined
        item.badgeClass = 'bg-error text-white'
      }
    }
    
    if (transRes.data.success) {
      const unpaid = transRes.data.data.unpaid
      const item = dynamicNavItems.find(i => i.title === 'Data Transaksi')
      if (item) {
        item.badgeContent = unpaid > 0 ? unpaid.toString() : undefined
        item.badgeClass = 'bg-warning text-white'
      }
    }
    
    if (wdRes.data.success) {
      const pendingWd = wdRes.data.data.pending
      const item = dynamicNavItems.find(i => i.title === 'Penarikan Dana')
      if (item) {
        item.badgeContent = pendingWd > 0 ? pendingWd.toString() : undefined
        item.badgeClass = 'bg-info text-white'
      }
    }
  } catch (error) {
    console.error('Failed to fetch admin stats', error)
  }
}

onMounted(() => {
  fetchStats()
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

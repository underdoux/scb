<template>
  <div class="min-h-screen" :class="{ 'bg-gray-100': !isHomePage, 'bg-white': isHomePage }">
    <nav v-if="!isHomePage" class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <router-link to="/" class="flex-shrink-0 flex items-center">
              <h1 class="text-xl font-bold">Social Content Generator</h1>
            </router-link>
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <router-link 
                to="/dashboard" 
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
              >
                Dashboard
              </router-link>
              <router-link 
                to="/schedule" 
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
              >
                Schedule Posts
              </router-link>
              <router-link 
                to="/accounts" 
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
              >
                Connected Accounts
              </router-link>
              <router-link 
                to="/generator" 
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
              >
                Content Generator
              </router-link>
            </div>
          </div>
          <div class="flex items-center">
            <NotificationCenter />
          </div>
        </div>
      </div>
    </nav>

    <div :class="{ 'py-10': !isHomePage }">
      <main>
        <div :class="{ 'max-w-7xl mx-auto sm:px-6 lg:px-8': !isHomePage }">
          <router-view></router-view>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { computed } from 'vue'
import NotificationCenter from './components/NotificationCenter.vue'

const route = useRoute()
const isHomePage = computed(() => route.name === 'Home')
</script>

<style>
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';
</style>

<template>
  <main class="min-h-screen bg-white">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-emerald-50 to-white">
      <div class="container mx-auto px-6 py-16 md:py-24">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
          <div>
            <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
              Discover Modern SaaS Solutions with SCB
            </h1>
            <p class="text-xl text-gray-600 mb-8">
              Empower your business with modern cloud solutions
            </p>
            <router-link 
              to="/dashboard" 
              class="bg-emerald-700 hover:bg-emerald-800 text-white px-8 py-3 rounded-lg text-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl inline-block"
            >
              Get Started
            </router-link>
          </div>
          <div class="hidden md:block relative h-[400px]">
            <img
              src="https://images.pexels.com/photos/3861964/pexels-photo-3861964.jpeg"
              alt="Woman working on laptop"
              class="rounded-lg shadow-xl object-cover w-full h-full"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 md:py-24 bg-white">
      <div class="container mx-auto px-6">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-16">Features</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Feature 1 -->
          <div class="p-8 rounded-xl border border-gray-100 hover:shadow-lg transition-all duration-200">
            <div class="flex items-center mb-4">
              <svg class="w-8 h-8 text-emerald-600 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
              <h3 class="text-xl font-semibold">Flexible Architecture</h3>
            </div>
            <p class="text-gray-600">
              Scale your applications seamlessly with our adaptable cloud infrastructure
            </p>
          </div>

          <!-- Feature 2 -->
          <div class="p-8 rounded-xl border border-gray-100 hover:shadow-lg transition-all duration-200">
            <div class="flex items-center mb-4">
              <svg class="w-8 h-8 text-emerald-600 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
              <h3 class="text-xl font-semibold">Real-Time Analytics</h3>
            </div>
            <p class="text-gray-600">
              Make informed decisions with instant data insights and reporting
            </p>
          </div>

          <!-- Feature 3 -->
          <div class="p-8 rounded-xl border border-gray-100 hover:shadow-lg transition-all duration-200">
            <div class="flex items-center mb-4">
              <svg class="w-8 h-8 text-emerald-600 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
              </svg>
              <h3 class="text-xl font-semibold">Secure & Reliable</h3>
            </div>
            <p class="text-gray-600">
              Enterprise-grade security with 99.9% guaranteed uptime
            </p>
          </div>

          <!-- Feature 4 -->
          <div class="p-8 rounded-xl border border-gray-100 hover:shadow-lg transition-all duration-200">
            <div class="flex items-center mb-4">
              <svg class="w-8 h-8 text-emerald-600 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
              </svg>
              <h3 class="text-xl font-semibold">Customizable Solutions</h3>
            </div>
            <p class="text-gray-600">
              Tailor the platform to your specific business requirements
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gray-50">
      <div class="container mx-auto px-6 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Join the SCB Platform</h2>
        <p class="text-xl text-gray-600 mb-8">Start your digital transformation journey today</p>
        <router-link 
          to="/dashboard" 
          class="bg-emerald-700 hover:bg-emerald-800 text-white px-8 py-3 rounded-lg text-lg font-semibold transition-all duration-200 inline-flex items-center"
        >
          Get Started
          <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
          </svg>
        </router-link>
      </div>
    </section>
  </main>
</template>

<script setup>
// No additional script needed for this component
</script>

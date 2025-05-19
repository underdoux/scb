import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import './style.css'

// Import components
import Home from './components/Home.vue'
import Dashboard from './components/Dashboard.vue'
import ScheduleForm from './components/ScheduleForm.vue'
import ConnectedAccounts from './components/ConnectedAccounts.vue'
import ContentGenerator from './components/ContentGenerator.vue'
import NotificationCenter from './components/NotificationCenter.vue'

// Create router instance
const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'Home',
      component: Home
    },
    {
      path: '/dashboard',
      name: 'Dashboard',
      component: Dashboard
    },
    {
      path: '/schedule',
      name: 'Schedule',
      component: ScheduleForm
    },
    {
      path: '/accounts',
      name: 'Accounts',
      component: ConnectedAccounts
    },
    {
      path: '/generator',
      name: 'Generator',
      component: ContentGenerator
    },
    {
      path: '/notifications',
      name: 'Notifications',
      component: NotificationCenter
    }
  ]
})

// Create and mount the Vue application
const app = createApp(App)
app.use(router)
app.mount('#app')

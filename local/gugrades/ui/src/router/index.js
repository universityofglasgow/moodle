import { createRouter, createMemoryHistory} from 'vue-router'
import CaptureAggregation from '../views/CaptureAggregation.vue';

const routes = [
  {
    path: '/',
    name: 'captureaggregation',
    component: CaptureAggregation
  },
  {
    path: '/about',
    name: 'about',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "about" */ '../views/AboutView.vue')
  }
]

const router = createRouter({
  history: createMemoryHistory(),
  //history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router

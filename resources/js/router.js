import Vue from 'vue'
import VueRouter from 'vue-router'

// ページコンポーネントをインポートする
import showScene from './pages/Show_Scene.vue'
import showProp from './pages/Show_Prop.vue'
import registerScene from './pages/Register_Scene.vue'
import registerProp from './pages/Register_Prop.vue'
import Setting from './pages/Setting.vue'

// VueRouterプラグインを使用する
// これによって<RouterView />コンポーネントなどを使うことができる
Vue.use(VueRouter)

// パスとコンポーネントのマッピング
const routes = [
  {
    path: '/',
    component: showScene
  },
  {
    path: '/show_prop',
    component: showProp
  },
  {
    path: '/register_scene',
    component: registerScene
  },
  {
    path: '/register_prop',
    component: registerProp
  },
  {
    path: '/setting',
    component: Setting
  }
]

// VueRouterインスタンスを作成する
const router = new VueRouter({
  mode: 'history',
  routes
})

// VueRouterインスタンスをエクスポートする
// app.jsでインポートするため
export default router
<template>
  <div id="overlay">
    <div id="content" class="panel">
      <ul>
        <li v-for="prop in props">
          <div type="button" @click="openModal_propDetail(prop.id)">{{ prop.name }}</div>
        </li>
        <detailProp :val="postProp" v-show="showContent" @close="closeModal_propDetail" />
      </ul>
      <button type="button" @click="$emit('close')" class="button button--inverse">閉じる</button>
    </div>
  </div>
  
</template>

<script>
import { OK } from '../util'

import detailProp from './Detail_Prop.vue'

export default {
  // モーダルとしてこのページを表示
  name: 'listProps',
  // このページの上で表示するコンポーネント
  components: {
    detailProp
  },
  // データ
  data() {
    return {
      // 小道具リスト
      props: [],
      // 小道具詳細
      showContent: false,
      postProp: "",
    }
  },
  methods: {
    // 小道具一覧を取得
    async fetchProps () {
      const response = await axios.get('/api/props')

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.props = response.data
    },

    // 小道具詳細のモーダル表示 
    openModal_propDetail (id) {
      this.showContent = true
      this.postProp = id;
    },
    // 小道具詳細のモーダル非表示
    closeModal_propDetail() {
      this.showContent = false
    },
  },
  watch: {
    $route: {
      async handler () {
        await this.fetchProps()
      },
      immediate: true
    }
  }
}
</script>

<style>
#overlay{
  overflow-y: scroll;
  z-index: 9999;
  position:fixed;
  top:0;
  left:0;
  width:100%;
  height:100%;
  background-color:rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}

#content{
  z-index: 2;
  width: 50%;
  background-color: white;
}
</style>

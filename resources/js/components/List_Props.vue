<template>
  <div id="overlay">
    <div id="content" class="panel">
      <ul>
        <li v-for="prop in props_list">
          <div type="button" @click="openModal_propDetail(prop.id)">{{ prop.name }}</div>
        </li>
        <detailProp :postProp="postProp" v-show="showContent" @close="closeModal_propDetail" />
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
  props: {
    postFlag: {
      type: Number,
      required: true
    }
  },
  // データ
  data() {
    return {
      // 小道具リスト
      props_list: [],
      // 小道具詳細
      showContent: false,
      postProp: ""
    }
  },
  watch: {
    postFlag: {
      async handler (postFlag) {
        await this.fetchProps()
      },
      immediate: true
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

      this.props_list = response.data
    },

    // 小道具詳細のモーダル表示 
    openModal_propDetail (id) {
      this.showContent = true
      this.postProp = id;
    },
    // 小道具詳細のモーダル非表示
    async closeModal_propDetail() {
      this.showContent = false
      await this.fetchProps()
    },
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

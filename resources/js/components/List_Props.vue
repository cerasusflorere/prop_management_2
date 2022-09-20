<template>
  <div v-bind:class="[overlay_class === 1 ? 'overlay' : 'overlay overlay-custom']"  @click.self="$emit('close')">
    <div class="content content-confirm-dialog panel" ref="content_list_props">
      <ul v-if="props_list.length">
        <li v-for="prop in props_list">
          <div type="button" class="list-button" @click="openModal_propDetail(prop.id)">{{ prop.name }}</div>
        </li>
        <detailProp :postProp="postProp" v-show="showContent" @close="closeModal_propDetail" />
      </ul>
      <div v-else>
        小道具は登録されていません。
      </div>
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
      // overlayのクラス
      overlay_class: 1, // 1の時はつかない
      // 小道具詳細
      showContent: false,
      postProp: ""
    }
  },
  watch: {
    postFlag: {
      async handler (postFlag) {
        if(this.postFlag){
          const props = await this.fetchProps();

          const content_dom = this.$refs.content_list_props;
          const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得
  
          if(content_rect.top < 0){
            this.overlay_class = 0;
          }else{
            this.overlay_class = 1;
          }
        }
      },
      immediate: true
    }
  },
  methods: {
    // 小道具一覧を取得
    async fetchProps () {
      const response = await axios.get('/api/props');

      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }
      
      this.props_list = response.data;
    },

    // 小道具詳細のモーダル表示 
    openModal_propDetail (id) {
      this.showContent = true;
      this.postProp = id;
    },
    // 小道具詳細のモーダル非表示
    async closeModal_propDetail() {
      this.showContent = false;
      await this.fetchProps();
    },
  }
}
</script>
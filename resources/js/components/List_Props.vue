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

          this.sort_Standard();

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

    

    sort_Standard(){
      const regex_str = /[^ぁ-んー]/g; // ひらがな以外
      const regex_number = /[^0-9]/g; // 数字以外
      const regex_alf = /[^A-Z]/g; // アルファベット
      this.props_list.sort((a, b) => {
        // kanaで並び替え
        if(a.kana !== b.kana){
          const a_str = a.kana.replace(regex_str, "");
          const b_str = b.kana.replace(regex_str, "");
          let a_number = a.kana.replace(regex_number, "");
          let b_number = b.kana.replace(regex_number, "");
          const a_alf = a.kana.replace(regex_alf, "");
          const b_alf = b.kana.replace(regex_alf, "");

          if(a_str !== b_str){
            let sort_str = a_str;
            if([...b_str].length < [...a_str].length){
              sort_str = b_str;
            } 
            for(let i=0; i < [...sort_str].length; i++){
              if(a_str.codePointAt(i) !== b_str.codePointAt(i)){
                if(a_str.codePointAt(i) > b_str.codePointAt(i)){
                  return 1;
                }else if(a_str.codePointAt(i) < b_str.codePointAt(i)){
                  return -1;
                }
              }          
            }
          }

          if(a_number !== b_number){
            if(!a_number){
              a_number = 0;
            }
            if(!b_number){
              b_number = 0;
            }

            if(parseInt(a_number) > parseInt(b_number)){
              return 1;
            }else if(parseInt(a_number) < parseInt(b_number)){
              return -1;
            }
          }

          if(a_alf !== b_alf){
            if(a_alf.codePointAt(0) > b_alf.codePointAt(0)){
              return 1;
            }else if(a_alf.codePointAt(0) < b_alf.codePointAt(0)){
              return -1;
            }else{
              return 0;
            }
          }
        }
        return 0;
      });
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
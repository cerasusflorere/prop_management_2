<template>
  <div class="overlay">
    <div class="content content-confirm-dialog panel">
      <form class="form"  @submit.prevent="confirm_section">
        <!-- 区分 -->
        <label for="section_edit">区分</label>
        <input type="text" id="section_edit" class="form__item" v-model="editForm_section.section" required>

        <!--- 変更ボタン -->
        <div class="form__button">
          <button type="submit" class="button button--inverse"><i class="fas fa-edit fa-fw"></i>変更</button>
        </div>
      </form>
      <!--- 削除ボタン -->
      <div class="form__button">
        <button type="button" class="button button--inverse" @click="openModal_confirmDelete"><i class="fas fa-eraser fa-fw"></i>削除</button>
      </div>
      <confirmDialog_Delete :confirm_dialog_delete_message="postMessage_Delete" v-show="showContent_confirmDelete" @Cancel_Delete="closeModal_confirmDelete_Cancel" @OK_Delete="closeModal_confirmDelete_OK"/>
        
      <button type="button" @click="$emit('close')" class="button button--inverse">閉じる</button>
    </div>
  </div>
</template>

<script>
import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util'

import confirmDialog_Delete from './Confirm_Dialog_Delete.vue'

export default {
  // モーダルとして表示
  name: 'editSection',
  components: {
    confirmDialog_Delete
  },
  props: {
    getSection: {
      type: Number,
      required: true
    }
  },
  // データ
  data() {
    return {
      section_edit: [],
      editForm_section: {
        id: null,
        section: null
      },
      // 削除confirm
      showContent_confirmDelete: false,
      postMessage_Delete: ""
    }
  },
  watch: {
    getSection: {
      async handler(getSection) {
        if(this.getSection){
          await this.fetchSection_edit();
        }
      },
      immediate: true,
    }
  },
  methods: {
    // 区分の詳細を取得
    async fetchSection_edit () {
      const response = await axios.get('/api/informations/sections/'+ this.getSection);
       
      this.section_edit = response.data;
      this.editForm_section.id = this.section_edit.id;
      this.editForm_section.section = this.section_edit.section;

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status);
        return false;
      }
    },

    // 編集エラー
    confirm_section () {
      if(this.section_edit.id === this.editForm_section.id && this.section_edit.section !== this.editForm_section.section){
        this.edit_section();
      }else{
        // メッセージ登録
        alert('元の区分名と同じです！変更するなら違う区分名にしてください！');
      }
    },

    // 編集する
    async edit_section () {
      const promise = new Promise(async (resolve) => {
        const response = await axios.post('/api/informations/sections/'+ this.section_edit.id, {
          section: this.editForm_section.section
        });
        resolve(response);
      })
      .then((response) => {
        if (response.statusText === 'Unprocessable Entity') {
          this.errors.error = response.data.errors;
          return false;
        }

        if (response.statusText !== 'Created') {
          this.$store.commit('error/setCode', response.status);
          return false;
        }else if(response.statusText === 'Created') {
          return response;
        }        
      })
      .then((response) => {
        this.section_edit.section = this.editForm_section.section;
        return response;
      })
      .then((response) => {
        // メッセージ登録
        this.$store.commit('message/setContent', {
          content: '区分が変更されました！',
          timeout: 6000
        });

        this.$emit('close');
      });
    },

    // 削除confirmのモーダル表示 
    openModal_confirmDelete (id) {
      this.showContent_confirmDelete = true;
      this.postMessage_Delete = 'これを行うと、紐づけられてた登場人物、その登場人物が小道具を使用するシーンも全て削除されます。本当に削除しますか？';
    },
    // 削除confirmのモーダル非表示_OKの場合
    async closeModal_confirmDelete_OK() {
      await this.deletSection();
      this.showContent_confirmDelete = false;
    },
    // 削除confirmのモーダル非表示_Cancelの場合
    closeModal_confirmDelete_Cancel() {
      this.showContent_confirmDelete = false;
    },

    // 削除する
    async deletSection() {
      const promise = new Promise(async (resolve) => {
        const response = await axios.delete('/api/informations/sections/'+ this.section_edit.id);
        resolve(response);
      })
      .then((response) => {
        if (response.statusText === 'Unprocessable Entity') {
          this.errors.error = response.data.errors;
          return false;
        }

        this.section_edit.id = null;
        this.section_edit.section = null;
        this.editForm_section.id = null;
        this.editForm_section.section = null;

        if (response.statusText !== 'Created') {
          this.$store.commit('error/setCode', response.status);
          return false;
        }

        return response.statusText;
      })
      .then((status) => {
        // メッセージ登録
        this.$store.commit('message/setContent', {
          content: '区分が1つ削除されました！',
          timeout: 6000
        });

        this.$emit('close');
      });
    }
  }
}
</script>
<template>
  <div class="overlay" @click.self="$emit('close')">
    <div class="content content-confirm-dialog panel">
        <form class="form"  @submit.prevent="confirm_character">
        <!-- 登場人物 -->
        <label for="character_edit_area">登場人物</label>
        <!-- 区分-->
        <label for="character_attr_edit">区分</label>
        <select id="character_attr_edit" class="form__item" v-model="editForm_character.section_id">
          <option disabled value="">区分</option>
          <option v-for="section in optionSections" v-bind:value="section.id">
            {{ section.section }}
          </option>
        </select>
        <!-- 名前 -->
        <input type="text" id="section_character" class="form__item" v-model="editForm_character.name" required>

        <!--- 変更ボタン -->
        <div class="form__button">
          <button type="submit" class="button button--inverse"><i class="fas fa-edit fa-fw"></i>変更</button>
        </div>
      </form>
      <confirmDialog_Edit :confirm_dialog_edit_message="postMessage_Edit" v-show="showContent_confirmEdit" @Cancel_Edit="closeModal_confirmEdit_Cancel" @OK_Edit="closeModal_confirmEdit_OK"/>

      <!--- 削除ボタン -->
      <div class="form__button">
        <button type="button" class="button button--inverse"  @click="openModal_confirmDelete"><i class="fas fa-trash fa-fw"></i>削除</button>
      </div>
      <confirmDialog_Delete :confirm_dialog_delete_message="postMessage_Delete" v-show="showContent_confirmDelete" @Cancel_Delete="closeModal_confirmDelete_Cancel" @OK_Delete="closeModal_confirmDelete_OK"/>
      
        <button type="button" @click="$emit('close')" class="button button--inverse">閉じる</button>
    </div>
  </div>
</template>

<script>
import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util'

import confirmDialog_Edit from './Confirm_Dialog_Edit.vue'
import confirmDialog_Delete from './Confirm_Dialog_Delete.vue'

export default {
  // モーダルとして表示
  name: 'editCharacter',
  components: {
    confirmDialog_Edit,
    confirmDialog_Delete
  },
  props: {
    getCharacter: {
      type: Number,
      required: true
    }
  },
  // データ
  data() {
    return {
      character_edit: [],
      optionSections: [],
      characters: [],
      editForm_character: {
        id: null,
        section_id: null,
        section: null,
        name: null
      },
      // 変更confirm
      showContent_confirmEdit: false,
      postMessage_Edit: "",
      // 削除confirm
      showContent_confirmDelete: false,
      postMessage_Delete: ""
    }
  },
  watch: {
    getCharacter: {
      async handler(getCharacter) {
        if(this.getCharacter){
          await this.fetchSections();
          await this.fetchCharacter_edit();
          await this.fetchCharacters();
        }        
      },
      immediate: true,
    }
  },
  methods: {
    // 区分を取得
    async fetchSections () {
      const response = await axios.get('/api/informations/sections');

      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.optionSections = response.data;
    },

    // 登場人物を取得
    async fetchCharacters () {
      const response = await axios.get('/api/informations/characters');
      
      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.characters = response.data;
    },

    // 登場人物の詳細を取得
    async fetchCharacter_edit () {
      const response = await axios.get('/api/informations/characters/'+ this.getCharacter);

      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.character_edit = response.data;
      this.editForm_character.id = this.character_edit.id;
      this.editForm_character.section_id = this.character_edit.section.id;
      this.editForm_character.section = this.character_edit.section.section;
      this.editForm_character.name = this.character_edit.name;
    },

    // 登場人物回す
    turnCharacters () {
      
    },

    // 確認する
    confirm_character () {
      let character = 0;
      this.characters.forEach((names) => {
        names.characters.forEach((name) => {
          if(name.name === this.editForm_character.name && name.id !== this.editForm_character.id) {
            character = 1;
            return false;
          }
        }, this);
      }, this);

      if(this.character_edit.id === this.editForm_character.id && (this.character_edit.section.id !== this.editForm_character.section_id || this.character_edit.name !== this.editForm_character.name) && !character){
        this.openModal_confirmEdit();
      }else if(character){
        alert('同じ名前は登録できません。');
      }else{
        alert('元の名前と同じです！変更するなら違う名前にしてください！');
      }
    },

    // 編集confirmのモーダル表示 
    openModal_confirmEdit () {
      this.showContent_confirmEdit = true;
      // セクション名はリアクティブじゃない
      this.optionSections.forEach((section) => {
        if(section.id === this.editForm_character.section_id) { 
          this.editForm_character.section = section.section;
          return false;
        }
      }, this);
      this.postMessage_Edit = '以下のように編集します。\n属性：' + this.editForm_character.section + '\n名前：' + this.editForm_character.name;
    },
    // 編集confirmのモーダル非表示_OKの場合
    async closeModal_confirmEdit_OK() {
      this.showContent_confirmEdit = false;
      await this.editCharacter();
    },
    // 編集confirmのモーダル非表示_Cancelの場合
    closeModal_confirmEdit_Cancel() {
      this.showContent_confirmEdit= false;
    },

    // 編集する
    async editCharacter () {
      const response = await axios.post('/api/informations/characters/'+ this.character_edit.id, {
        section_id: this.editForm_character.section_id,
        name: this.editForm_character.name
      });

      if (response.status === 422) {
        this.errors.error = response.data.errors;
        return false;
      }      

      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.character_edit.section = this.editForm_character.section;
      this.character_edit.section_id = this.editForm_character.section_id;
      this.character_edit.name = this.editForm_character.name;

      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '登場人物の区分または名前が変更されました！',
        timeout: 6000
      });

      this. $emit('close');
    },

    // 削除confirmのモーダル表示 
    openModal_confirmDelete () {
      this.showContent_confirmDelete = true;
      this.postMessage_Delete = 'この登場人物を削除すると、この登場人物が小道具を使用シーンが全て削除されます。\n本当に削除しますか？';
    },
    // 削除confirmのモーダル非表示_OKの場合
    async closeModal_confirmDelete_OK() {
      await this.deletCharacter();
      this.showContent_confirmDelete = false;
    },
    // 削除confirmのモーダル非表示_Cancelの場合
    closeModal_confirmDelete_Cancel() {
      this.showContent_confirmDelete = false;
    },

    // 削除する
    async deletCharacter() {
      const response = await axios.delete('/api/informations/characters/'+ this.character_edit.id);

      if (response.status === 422) {
        this.errors.error = response.data.errors;
        return false;
      }

      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.character_edit = null;
      this.editForm_character.id = null;
      this.editForm_character.name = null;
      this.editForm_character.section_id = null;
      this.editForm_character.section = null;
  
      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '登場人物が1人削除されました！',
        timeout: 6000
      });

      this.$emit('close');
    }
  }
}
</script>
<template>
  <div id="overlay">
    <div id="content" class="panel">
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

        <!--- 送信ボタン -->
        <div class="form__button">
          <button type="submit" class="button button--inverse" @click="$emit('close')">変更</button>
        </div>
      </form>
        
        <button type="button" @click="$emit('close')" class="button button--inverse">閉じる</button>
    </div>
  </div>
</template>

<script>
import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util'

import VueTypes from 'vue-types';

export default {
  // モーダルとして表示
  name: 'editCharacter',
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
      editForm_character: {
        id: null,
        section_id: null,
        name: null
      }
    }
  },
  watch: {
    getCharacter: {
      async handler(getCharacter) {
        await this.fetchSections()
        await this.fetchCharacter_edit()       
      },
      immediate: true,
    }
  },
  methods: {
    // 区分を取得
    async fetchSections () {
      const response = await axios.get('/api/informations/sections')

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.optionSections = response.data
    },

    // 登場人物の詳細を取得
    async fetchCharacter_edit () {
      const response = await axios.get('/api/informations/characters/'+ this.getCharacter)

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.character_edit = response.data
      this.editForm_character.id = this.character_edit.id
      this.editForm_character.section_id = this.character_edit.section.id
      this.editForm_character.name = this.character_edit.name
    },

    // 確認する
    confirm_character () {
      console.log(this.character_edit.section.id);
      console.log(this.editForm_character.section_id);
      console.log(this.character_edit.name);
      console.log(this.editForm_character.name);
      if(this.character_edit.id === this.editForm_character.id && (this.character_edit.section.id !== this.editForm_character.section_id || this.character_edit.name !== this.editForm_character.name)){
        this.editCharacter()
      }else{
        // メッセージ登録
        this.$store.commit('message/setContent', {
          content: '元の区分も名前も同じです！変更するなら違う区分または名前にしてください！',
          timeout: 6000
        })
      }
    },

    // 編集する
    async editCharacter () {      
      const response = await axios.post('/api/informations/characters/'+ this.character_edit.id, {
        section_id: this.editForm_character.section_id,
        name: this.editForm_character.name
      })

      if (response.statusText === 'Unprocessable Entity') {
        this.errors.error = response.data.errors
        return false
      }

      this.character_edit.section = null
      this.character_edit.id = null
      this.character_edit.name = null
      this.editForm_character.section_id = null
      this.editForm_character.id = null
      this.editForm_character.name = null

      if (response.statusText !== 'Created') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '登場人物の区分または名前が変更されました！',
        timeout: 6000
      })
    },
  }
}
</script>

<style scoped>
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

#content {
  z-index: 2;
  background-color: white;
  width: 80%;
  aspect-ratio: 2 / 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
</style>
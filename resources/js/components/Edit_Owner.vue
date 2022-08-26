<template>
  <div id="overlay">
    <div id="content" class="panel">
        <form class="form"  @submit.prevent="confirm_owner">
        <!-- 持ち主 -->
        <label for="owner_edit">持ち主</label>
        <input type="text" id="owner_edit" class="form__item" v-model="editForm_owner.name" required>

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

export default {
  // モーダルとして表示
  name: 'editOwner',
  props: {
    getOwner: {
      type: Number,
      required: true
    }
  },
  // データ
  data() {
    return {
      owner_edit: [],
      editForm_owner: {
        id: null,
        name: null
      }
    }
  },
  watch: {
    getOwner: {
      async handler(getOwner) {
        await this.fetchOwner_edit()
      },
      immediate: true,
    }
  },
  methods: {
    // 持ち主の詳細を取得
    async fetchOwner_edit () {
      const response = await axios.get('/api/informations/owners/'+ this.getOwner)

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.owner_edit = response.data
      this.editForm_owner.id = this.owner_edit.id
      this.editForm_owner.name = this.owner_edit.name
    },

    // 確認する
    confirm_owner () {
      if(this.owner_edit.id === this.editForm_owner.id && this.owner_edit.name !== this.editForm_owner.name){
        this.editOwner()
      }else{
        // メッセージ登録
        this.$store.commit('message/setContent', {
          content: '元の名前と同じです！変更するなら違う名前にしてください！',
          timeout: 6000
        })
      }
    },

    // 編集する
    async editOwner () {      
      const response = await axios.post('/api/informations/owners/'+ this.owner_edit.id, {
        name: this.editForm_owner.name
      })

      if (response.statusText === 'Unprocessable Entity') {
        this.errors.error = response.data.errors
        return false
      }

      this.editForm_owner.id = null
      this.editForm_owner.name = null

      if (response.statusText !== 'Created') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '持ち主の名前が変更されました！',
        timeout: 6000
      })
    }
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
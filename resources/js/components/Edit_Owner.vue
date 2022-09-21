<template>
  <div class="overlay" @click.self="$emit('close')">
    <div class="content content-confirm-dialog panel">
        <form class="form"  @submit.prevent="confirm_owner">
        <!-- 持ち主 -->
        <label for="owner_edit">持ち主</label>
        <input type="text" id="owner_edit" class="form__item" v-model="editForm_owner.name" required>

        <!--- 変更ボタン -->
        <div class="form__button">
          <button type="submit" class="button button--inverse"><i class="fas fa-edit fa-fw"></i>変更</button>
        </div>
      </form>
      <confirmDialog_Edit :confirm_dialog_edit_message="postMessage_Edit" v-show="showContent_confirmEdit" @Cancel_Edit="closeModal_confirmEdit_Cancel" @OK_Edit="closeModal_confirmEdit_OK"/>

      <!--- 削除ボタン -->
      <div class="form__button">
        <button type="button" class="button button--inverse" @click="openModal_confirmDelete"><i class="fas fa-trash fa-fw"></i>削除</button>
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
  name: 'editOwner',
  components: {
    confirmDialog_Edit,
    confirmDialog_Delete
  },
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
      },
      owners: [],
      // 変更confirm
      showContent_confirmEdit: false,
      postMessage_Edit: "",
      // 削除confirm
      showContent_confirmDelete: false,
      postMessage_Delete: ""
    }
  },
  watch: {
    getOwner: {
      async handler(getOwner) {
        if(this.getOwner){
          await this.fetchOwner_edit();
          await this.fetchOwners();
        }        
      },
      immediate: true,
    }
  },
  methods: {
    // 持ち主の詳細を取得
    async fetchOwner_edit () {
      const response = await axios.get('/api/informations/owners/'+ this.getOwner);

      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.owner_edit = response.data;
      this.editForm_owner.id = this.owner_edit.id;
      this.editForm_owner.name = this.owner_edit.name;
    },

    // 持ち主を取得
    async fetchOwners () {
      const response = await axios.get('/api/informations/owners');

      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.owners = response.data;
    },

    // 確認する
    confirm_owner () {
      let owner = 0;
      this.owners.forEach((name) => {
        if(name.name === this.editForm_owner.name && name.id !== this.editForm_owner.id) {
          owner = 1;
          return false;
        }
      }, this);

      if(this.owner_edit.id === this.editForm_owner.id && this.owner_edit.name !== this.editForm_owner.name && !owner){
        this.openModal_confirmEdit();
      }else if(owner){
        alert('同じ名前は登録できません。');
      }else{
        alert('元の名前と同じです！変更するなら違う名前にしてください！');
      }
    },

    // 編集confirmのモーダル表示 
    openModal_confirmEdit () {
      this.showContent_confirmEdit = true;
      this.postMessage_Edit = '以下のように編集します。\n持ち主：' + this.editForm_owner.name;
    },
    // 編集confirmのモーダル非表示_OKの場合
    async closeModal_confirmEdit_OK() {
      this.showContent_confirmEdit = false;
      await this.editOwner();
    },
    // 編集confirmのモーダル非表示_Cancelの場合
    closeModal_confirmEdit_Cancel() {
      this.showContent_confirmEdit= false;
    },

    // 編集する
    async editOwner () {
      const response = await axios.post('/api/informations/owners/'+ this.owner_edit.id, {
        name: this.editForm_owner.name
      });
      
      if (response.status === 422) {
        this.errors.error = response.data.errors;
        return false;
      }

      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.owner_edit.name = this.editForm_owner.name;

      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '持ち主の名前が変更されました！',
        timeout: 6000
      });

      this. $emit('close');
    },

    // 削除confirmのモーダル表示 
    openModal_confirmDelete (id) {
      this.showContent_confirmDelete = true;
      this.postMessage_Delete = 'この持ち主を削除すると、紐づけられてたこの方が所有するする小道具も全て削除されます。\n本当に削除しますか？';
    },
    // 削除confirmのモーダル非表示_OKの場合
    async closeModal_confirmDelete_OK() {
      await this.deletOwner();
      this.showContent_confirmDelete = false;
      this.$emit('close');
    },
    // 削除confirmのモーダル非表示_Cancelの場合
    closeModal_confirmDelete_Cancel() {
      this.showContent_confirmDelete = false;
    },

    // 削除する
    async deletOwner() {
      const response = await axios.delete('/api/informations/owners/'+ this.owner_edit.id);

      if (response.status === 422) {
        this.errors.error = response.data.errors;
        return false;
      }

      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.owner_edit.id = null;
      this.owner_edit.name= null;
      this.editForm_owner.id = null;
      this.editForm_owner.name = null;

      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '持ち主が1人削除されました！',
        timeout: 6000
      });

      this.$emit('close');
    }
  }
}
</script>
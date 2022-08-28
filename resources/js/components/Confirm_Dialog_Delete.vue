<template>
  <div id="overlay">
    <div id="content" class="panel">
      <div id="confirm_dialog_delete_title">
        最終確認
      </div>
      <div id="confirm_dialog_delete_message">
        {{ confirm_dialog_delete_message }}
      </div>

      <button type="button" @click="$emit('Cancel_Delete')" class="button button--inverse">キャンセル</button>
        
      <button type="button" @click="$emit('OK_Delete')" class="button button--inverse">OK</button>
    </div>
  </div>
</template>

<script>
export default {
  // モーダルとして表示
  name: 'confirmDialog_Delete',
  props: {
    confirm_dialog_delete_message: {
        type: String,
        required: true
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
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
</style>
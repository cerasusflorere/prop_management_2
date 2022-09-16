<template>
  <div class="overlay" @click.self="$emit('close')">
    <div class="content content-confirm-dialog panel">
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
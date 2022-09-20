<template>
  <div class="overlay" @click.self="$emit('close')">
    <div class="content content-confirm-dialog panel">
      <div id="confirm_dialog_delete_title">
        最終確認
      </div>
      <div id="confirm_dialog_delete_message">
        {{ confirm_dialog_delete_message }}
      </div>

      <div class="button-area--together">
        <button type="button" @click="$emit('Cancel_Delete')" class="button button--inverse button--confirm"><i class="fas fa-ban fa-fw"></i>キャンセル</button>
        
        <button type="button" @click="$emit('OK_Delete')" class="button button--inverse button--confirm button--danger"><i class="fas fa-trash fa-fw"></i>削除</button>
      </div>

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
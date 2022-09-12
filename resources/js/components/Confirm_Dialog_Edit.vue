<template>
  <div v-bind:class="[overlay_class === 1 ? 'overlay' : 'overlay overlay-custom']">
    <div class="content content-confirm-dialog panel"  ref="content_confirm_dialog_edit">
      <div id="confirm_dialog_edit_title">
        編集
      </div>
      <div id="confirm_dialog_edit_message">
        {{ confirm_dialog_edit_message }}
      </div>
  
      <button type="button" @click="$emit('Cancel_Edit')" class="button button--inverse">キャンセル</button>
        
      <button type="button" @click="$emit('OK_Edit')" class="button button--inverse">OK</button>
    </div>
  </div>
</template>
  
<script>
  export default {
    // モーダルとして表示
    name: 'confirmDialog_Edit',
    props: {
      confirm_dialog_edit_message: {
          type: String,
          required: true
      }
    },
    data() {
      return {
        // overlayのクラス
        overlay_class: 1
      }
    },
    watch: {
      confirm_dialog_edit_message: {
        async handler(confirm_dialog_edit_message) {
          const content_dom = this.$refs.content_confirm_dialog_edit;
          const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得

          if(content_rect.top < 0){
            this.overlay_class = 0;
          }else{
            this.overlay_class = 1;
          }
        },
        immediate: true,
      },
    }
  }
</script>
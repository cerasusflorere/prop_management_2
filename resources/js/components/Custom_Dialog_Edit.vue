<template>
  <div v-bind:class="[overlay_class === 1 ? 'overlay' : 'overlay overlay-custom']" @click.self="$emit('Cancel_CustomEdit')">
    <div class="content content-confirm-dialog panel"  ref="content_custom_dialog_edit">
      <div id="confirm_dialog_edit_title">
        編集項目選択
      </div>
      <div id="custom_dialog_edit_message" class="dialog-message">
{{ custom_dialog_edit_message }}
      </div>

      <div v-show="prop_scene_flag === 1">
        <div class="checkbox-area--together">
          <!-- ピッコロに持ってきたか -->
          <label for="prop_location">ピッコロに持ってきたか</label>

          <input type="radio" id="prop_location_yes" value="location_yes" v-model="editCustomProp">
          <label for="prop_location_yes">持ってきてる</label>
          <input type="radio" id="prop_location_no" value="location_no" v-model="editCustomProp">
          <label for="prop_location_no">持ってきてない</label>
        </div>

        <div class="checkbox-area--together">
          <!-- 作るかどうか -->
          <label for="prop_handmade">作るかどうか</label>
          <div class="checkbox-area--column">
            <div class="checkbox-area--together">
              <input type="radio" id="prop_handmade_comolete" value="handmade_comolete" v-model="editCustomProp">
              <label for="prop_handmade_comolete">完成</label>
              <input type="radio" id="prop_handmade_progress" value="handmade_progress" v-model="editCustomProp">
              <label for="prop_handmade_progress">仕掛中</label>
              <input type="radio" id="prop_handmade_unfinished" value="handmade_unfinished" v-model="editCustomProp">
              <label for="prop_handmade_unfinished">未着手</label>
            </div>
            <div class="checkbox-area--together">
              <input type="radio" id="prop_handmade_no" value="handmade_no" v-model="editCustomProp">
              <label for="prop_handmade_no">作らない</label>
            </div>
          </div>          
        </div>

        <div class="checkbox-area--together">
          <!-- これで決定か -->
          <label for="prop_decision">これで決定か</label>

          <input type="radio" id="prop_decision_yes" value="decision_yes" v-model="editCustomProp">
          <label for="prop_decision_yes">決定してる</label>
          <input type="radio" id="prop_decision_no" value="decision_no" v-model="editCustomProp">
          <label for="prop_decision_no">決定してない</label>
        </div>

        <div class="checkbox-area--together">
          <!-- 中間発表で使用するか -->
          <label for="prop_usage">中間発表</label>

          <input type="radio" id="prop_usage_yes" value="usage_yes" v-model="editCustomProp">
          <label for="prop_usage_yes">使う</label>
          <input type="radio" id="prop_usage_no" value="usage_no" v-model="editCustomProp">
          <label for="prop_usage_no">使わない</label>
        </div>

        <div class="checkbox-area--together">
          <!-- 卒業公演で使用するか -->
          <label for="prop_usage_guraduation">卒業公演</label>

          <input type="radio" id="prop_usage_guraduation_yes" value="usage_guraduation_yes" v-model="editCustomProp">
          <label for="prop_usage_guraduation_yes">使う</label>
          <input type="radio" id="prop_usage_guraduation_no" value="usage_guraduation_no" v-model="editCustomProp">
          <label for="prop_usage_guraduation_no">使わない</label>
        </div>

        <div class="checkbox-area--together">
          <!-- 上手で使用するか -->
          <label for="prop_usage_left">上手</label>

          <input type="radio" id="prop_usage_left_yes" value="usage_left_yes" v-model="editCustomProp">
          <label for="prop_usage_left_yes">使う</label>
          <input type="radio" id="prop_usage_left_no" value="usage_left_no" v-model="editCustomProp">
          <label for="prop_usage_left_no">使わない</label>
        </div>

        <div class="checkbox-area--together">
          <!-- 下手で使用するか -->
          <label for="prop_usage_right">下手</label>

          <input type="radio" id="prop_usage_right_yes" value="usage_right_yes" v-model="editCustomProp">
          <label for="prop_usage_right_yes">使う</label>
          <input type="radio" id="prop_usage_right_no" value="usage_right_no" v-model="editCustomProp">
          <label for="prop_usage_right_no">使わない</label>
        </div>
      </div>

      <!-- 使用シーン -->
      <div v-show="prop_scene_flag === 2">
        <div class="checkbox-area--together">
          <!-- これで決定か -->
          <label for="scene_decision">これで決定か</label>

          <input type="radio" id="scene_decision_yes" value="decision_yes" v-model="editCustomScene">
          <label for="scene_decision_yes">決定してる</label>
          <input type="radio" id="scene_decision_no" value="decision_no" v-model="editCustomScene">
          <label for="scene_decision_no">決定してない</label>
        </div>

        <div class="checkbox-area--together">
          <!-- 中間発表で使用するか -->
          <label for="scene_usage">中間発表</label>

          <input type="radio" id="scene_usage_yes" value="usage_yes" v-model="editCustomScene">
          <label for="scene_usage_yes">使う</label>
          <input type="radio" id="scene_usage_no" value="usage_no" v-model="editCustomScene">
          <label for="scene_usage_no">使わない</label>
        </div>

        <div class="checkbox-area--together">
          <!-- 卒業公演で使用するか -->
          <label for="scene_usage_guraduation">卒業公演</label>

          <input type="radio" id="scene_usage_guraduation_yes" value="usage_guraduation_yes" v-model="editCustomScene">
          <label for="scene_usage_guraduation_yes">使う</label>
          <input type="radio" id="scene_usage_guraduation_no" value="usage_guraduation_no" v-model="editCustomScene">
          <label for="scene_usage_guraduation_no">使わない</label>
        </div>

        <div class="checkbox-area--together">
          <!-- 上手で使用するか -->
          <label for="scene_usage_left">上手</label>

          <input type="radio" id="scene_usage_left_yes" value="usage_left_yes" v-model="editCustomScene">
          <label for="scene_usage_left_yes">使う</label>
          <input type="radio" id="scene_usage_left_no" value="usage_left_no" v-model="editCustomScene">
          <label for="scene_usage_left_no">使わない</label>
        </div>

        <div class="checkbox-area--together">
          <!-- 下手で使用するか -->
          <label for="scene_usage_right">下手</label>

          <input type="radio" id="scene_usage_right_yes" value="usage_right_yes" v-model="editCustomScene">
          <label for="scene_usage_right_yes">使う</label>
          <input type="radio" id="scene_usage_right_no" value="usage_right_no" v-model="editCustomScene">
          <label for="scene_usage_right_no">使わない</label>
        </div>

        <div class="checkbox-area--together">
          <!-- セットする人を削除するか -->
          <label for="scene_setting">セットする人</label>

          <input type="radio" id="scene_setting_no" value="setting_no" v-model="editCustomScene">
          <label for="scene_setting_no">未設定にする</label>
        </div>
      </div>

      <div v-show="prop_scene_flag === 1" class="button-area--together">
        <button type="button" @click="$emit('Cancel_CustomEdit')" class="button button--inverse button--confirm"><i class="fas fa-ban fa-fw"></i>キャンセル</button>
        
        <button type="button" @click="$emit('OK_CustomEdit', editCustomProp)" class="button button--inverse button--confirm button--danger"><i class="fas fa-edit fa-fw"></i>決定</button>
      </div>
      <div v-show="prop_scene_flag === 2" class="button-area--together">
        <button type="button" @click="$emit('Cancel_CustomEdit')" class="button button--inverse button--confirm"><i class="fas fa-ban fa-fw"></i>キャンセル</button>

        <button type="button" @click="$emit('OK_CustomEdit', editCustomScene)" class="button button--inverse button--confirm button--danger"><i class="fas fa-edit fa-fw"></i>決定</button>   
      </div>  
    </div>
  </div>
</template>
  
<script>
  export default {
    // モーダルとして表示
    name: 'custmDialog_Edit',
    props: {
      custom_dialog_edit_message: {
          type: String,
          required: true
      }
    },
    data() {
      return {
        // overlayのクラス
        overlay_class: 1,
        // 小道具か使用シーンか
        prop_scene_flag: 1, // 1:小道具、2:使用シーン
        // 選択項目(小道具)
        editCustomProp: null,
        // 選択項目(使用シーン)
        editCustomScene: null
      }
    },
    watch: {
      custom_dialog_edit_message: {
        async handler(custom_dialog_edit_message) {
          const content_dom = this.$refs.content_custom_dialog_edit;
          const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得

          if(content_rect.top < 0){
            this.overlay_class = 0;
          }else{
            this.overlay_class = 1;
          }

          if(this.custom_dialog_edit_message.indexOf('小道具') !== -1){
            this.prop_scene_flag = 1;
          }else{
            this.prop_scene_flag = 2;
          }
        },
        immediate: true,
      },
    }
  }
</script>
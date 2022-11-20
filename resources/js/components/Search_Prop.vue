<template>
    <div v-bind:class="[overlay_class === 1 ? 'overlay' : 'overlay overlay-custom']" @click.self="$emit('close', null, null, null)">
      <div class="content cotent-search content-confirm-dialog panel" ref="content_search_prop">
        <div class="button-search--area">
          <!-- 閉じるボタン -->
          <div class="button-search--close">
            <button type="button" @click.self="$emit('close', null, null, null)">×</button>
          </div>

          <!-- リセットボタン -->
          <div>
            <button type="button" class="button button--reset" @click="resetSearchProp"><i class="fas fa-undo-alt fa-fw"></i>リセット</button>
          </div>
        </div>

        <form class="form form-search"  @submit.prevent="searchProp">
          <div class="search-sort-area">
            <span class="search-span"><i class="fas fa-sort fa-fw"></i></i>並び替え</span>
            <div class="checkbox-area--together checkbox-area--search">
              <input type="radio" id="sort_prop_name" v-model="search_prop.prop_sort" value="kana">
              <label for="sort_prop_name">名前順</label>       
  
              <input type="radio" id="sort_prop_owner" v-model="search_prop.prop_sort" value="owner">
              <label for="sort_prop_owner">持ち主順</label>
  
              <input type="radio" id="sort_prop_created_at" v-model="search_prop.prop_sort" value="created_at">
              <label for="sort_prop_created_at">登録日順</label>
  
              <input type="radio" id="sort_prop_updated_at" v-model="search_prop.prop_sort" value="updated_at">
              <label for="sort_prop_updated_at">更新日順</label>
            </div>
          </div>
  
          <div class="search-sort-area">
            <span class="search-span"><i class="fas fa-filter fa-fw"></i>絞り込み</span>
  
            <!-- 入力検索 -->
            <div class="search--select-area--box">
              <label for="search_prop_name" class="search--label">名前</label>
              <input type="text" class="form__item search--input" id="search_prop_name" v-model="search_prop.prop_search.name.input"></input>
              <span class="checkbox-area--together">
                <input type="radio" id="search_prop_name_only" v-model="search_prop.prop_search.name.scope" value="name_only">
                <label for="search_prop_name_only">名前のみ</label>       
  
                <input type="radio" id="search_prop_memo_toghether" v-model="search_prop.prop_search.name.scope" value="memo_together">
                <label for="search_prop_memo_toghether">メモ含む</label>
              </span>
            </div>
  
            <!-- 色々分類 -->
            <div class="search--select-area--box">              
              <!-- 持ち主 -->
              <div class="search--select-area">
                <label for="search_prop_owner" class="search--label">持ち主</label>
                <select id="search_prop_owner" class="form__item"  v-model="search_prop.prop_search.owner">
                  <option value=0>選択なし</option>
                  <option v-for="owner in optionOwners" v-bind:value="owner.id">
                    {{ owner.name }}
                  </option>
                </select>
              </div>
              
              <!-- ピッコロに持ってきたか -->
              <div class="search-search--select-area checkbox-area--together">
                <label>ピッコロに</label>
                <span class="checkbox-area--together">
                  <input type="checkbox" id="search_prop_location" class="form__check__input" v-model="search_prop.prop_search.location">
                  <label for="search_prop_location" class="form__check__label">持ってきた</label>
                </span>
                <span class="checkbox-area--together">
                  <input type="checkbox" id="search_prop_location_no" class="form__check__input" v-model="search_prop.prop_search.location_no">
                  <label for="search_prop_location_no" class="form__check__label">持ってきてない</label>
                </span>
              </div>

              <!-- 作るかどうか -->
              <div class="search-search--select-area checkbox-area--together">
                <span class="checkbox-area--together">
                  <input type="checkbox" id="search_prop_handmade" class="form__check__input" v-model="search_prop.prop_search.handmade">
                  <label for="search_prop_handmade" class="form__check__label">作る</label>
                </span>
                <span class="checkbox-area--together">
                  <input type="checkbox" id="search_prop_handmade_no" class="form__check__input" v-model="search_prop.prop_search.handmade_no">
                  <label for="search_prop_handmade_no" class="form__check__label">作らない</label>
                </span>
              </div>
              <div class="search-search--select-area checkbox-area--together">
                <span class="checkbox-area--together">
                  <input type="checkbox" id="search_prop_handmade_complete" class="form__check__input" :disabled="!search_prop.prop_search.handmade" v-model="search_prop.prop_search.handmade_complete">
                  <label for="search_prop_handmade_complete" class="form__check__label">完成</label>
                </span>
                <span class="checkbox-area--together">
                  <input type="checkbox" id="search_prop_handmade_proguress" class="form__check__input" :disabled="!search_prop.prop_search.handmade" v-model="search_prop.prop_search.handmade_progress">
                  <label for="search_prop_handmade_proguress" class="form__check__label">仕掛中</label>
                </span>
                <span class="checkbox-area--together">
                  <input type="checkbox" id="search_prop_handmade_unfinished" class="form__check__input" :disabled="!search_prop.prop_search.handmade" v-model="search_prop.prop_search.handmade_unfinished">
                  <label for="search_prop_handmade_unfinished" class="form__check__label">未着手</label>
                </span>
              </div>

              <!-- これで決定か -->
              <div class="search-search--select-area checkbox-area--together">
                <label>決定</label>
                <span class="checkbox-area--together">
                  <input type="checkbox" id="search_prop_decision" class="form__check__input" v-model="search_prop.prop_search.decision">
                  <label for="search_prop_decision" class="form__check__label">してる</label>
                </span>
                <span class="checkbox-area--together">
                  <input type="checkbox" id="search_prop_decision_no" class="form__check__input" v-model="search_prop.prop_search.decision_no">
                  <label for="search_prop_decision_no" class="form__check__label">してない</label>
                </span>
              </div>
  
              <!-- 使用するか -->
              <div class="search--select-area checkbox-area--together">
                <span class="checkbox-area--together search--select-area--performance">
                  <input type="checkbox" id="search_prop_usage" class="form__check__input" v-model="search_prop.prop_search.usage">
                  <label for="search_prop_usage" class="form__check__label">中間発表</label>
                </span>
  
                <span class="search--select-area--performance">
                  <span class="checkbox-area--together">
                    <input type="checkbox" id="search_prop_usage_guraduation" class="form__check__input" v-model="search_prop.prop_search.usage_guraduation">
                    <label for="search_prop_usage_guraduation" class="form__check__label">卒業公演</label>
                  </span>
  
                  <span class="checkbox-area--together">
                    <input type="checkbox" id="search_prop_usage_left" class="form__check__input" value="left" v-model="search_prop.prop_search.usage_left">
                    <label for="search_prop_usage_left" class="form__check__label">上手</label>
                    
                    <input type="checkbox" id="search_prop_usage_right" class="form__check__input" value="right" v-model="search_prop.prop_search.usage_right"></input>
                    <label for="search_prop_usage_right" class="form__check__label">下手</label>
                  </span>
                </span>            
              </div>
            </div>
  
          </div>
        
          <!--- 送信ボタン -->
          <div class="form__button">
            <button type="submit" class="button button--inverse"><i class="fas fa-search fa-fw"></i>検索</button>
          </div>
          
        </form>
      </div>
    </div>
  </template>
  
  <script>
    import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util';
  
    export default {
      // モーダルとして表示
      name: 'searchProp',
      props: {
        postSearch: {
          type: Number,
          required: true
        }
      },
      data() {
        return{
          // overlayのクラス
          overlay_class: 1, // 1の時はつかない
          // 持ち主リスト
          optionOwners: [],
          // 検索
          search_prop: {
            prop_sort: "kana",
            prop_search: {
              name: {
                input: null,
                scope: "name_only"
              },
              owner: 0,
              location: false,
              location_no: false,
              handmade: false,
              handmade_no: false,
              handmade_complete: true,
              handmade_progress: true,
              handmade_unfinished: true,
              decision: false,
              decision_no: false,
              usage: false,
              usage_guraduation: false,
              usage_left: false,
              usage_right: false
            }
          }
        }
      },
      watch: {
        postSearch: {
          async handler(postSearch) {
            if(this.postSearch){
              await this.fetchOwners();
  
              const content_dom = this.$refs.content_search_prop;
              const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得
  
              if(content_rect.top < 0){
                this.overlay_class = 0;
              }else{
                this.overlay_class = 1;
              }
            }        
          },
          immediate: true,
        }
      },
      methods: {  
        // 持ち主を取得
        async fetchOwners () {
          const response = await axios.get('/api/informations/owners');
  
          if (response.status !== 200) {
            this.$store.commit('error/setCode', response.status);
            return false;
          }
  
          this.optionOwners = response.data;
        },
  
        // エスケープ処理
        h(unsafeText){
          if(typeof unsafeText !== 'string'){
              return unsafeText;
          }
          return unsafeText.replace(
            /[&'`"<>]/g, 
            function(match) {
              return {
                '&': '&amp;',
                "'": '&#x27;',
                '`': '&#x60;',
                '"': '&quot;',
                '<': '&lt;',
                '>': '&gt;',
              }[match]
            } 
          );
        },
  
        // 並び替えか絞り込みか // 全部一致か一部一致か
        searchProp() {
          let name_input = '!=' + 100;
          let name_scope = '!=' + 100;
          let owner_id = '!=' + 0;
          let location = '!=' + 100;
          let handmade = '(a.handmade !=' + 100;
          let decision = '!=' + 100;
          let usage = '!=' + 100;
          let usage_guraduation = '!=' + 100;
          let usage_left = '!=' + 100;
          let usage_right = '!=' + 100;
          
          if(this.search_prop.prop_search.name.input){
            name_input = '==' + this.h(this.search_prop.prop_search.name.input);
            if(this.search_prop.prop_search.name.scope === "memo_together"){
              name_scope = '==' + this.h(this.search_prop.prop_search.name.input);
            }else{
              name_scope = '!= 100';
            }
          }
  
          if(this.search_prop.prop_search.owner != 0){
            owner_id = '===' + this.search_prop.prop_search.owner;
          }
  
          if(this.search_prop.prop_search.location && !this.search_prop.prop_search.location_no){
            location = '===' + 1;
          }else if(!this.search_prop.prop_search.location && this.search_prop.prop_search.location_no){
            location = '===' + 0;
          }

          if(this.search_prop.prop_search.handmade && !this.search_prop.prop_search.handmade_no){
            handmade = '(a.handmade !==' + 0;
            if(this.search_prop.prop_search.handmade_complete) {
              handmade = '(a.handmade ===' + 1;
              if(this.search_prop.prop_search.handmade_progress){
                handmade = handmade + '|| a.handmade === ' + 2;
              }
              if(this.search_prop.prop_search.handmade_unfinished) {
                handmade = handmade + '|| a.handmade === ' + 3;
              }
            }else if(this.search_prop.prop_search.handmade_progress){
              handmade = '(a.handmade ===' + 2;
              if(this.search_prop.prop_search.handmade_unfinished) {
                handmade = handmade + '|| a.handmade === ' + 3;
              }
            }else{
              handmade ='(a.handmade ===' +  3;
            }
          }else if(!this.search_prop.prop_search.handmade && this.search_prop.prop_search.handmade_no){
            handmade = '(a.handmade ===' + 0;
          }
          handmade = handmade + ')';
          console.log(handmade);

          if(this.search_prop.prop_search.decision && !this.search_prop.prop_search.decision_no){
            decision = '===' + 1;
          }else if(!this.search_prop.prop_search.decision && this.search_prop.prop_search.decision_no){
            decision = '===' + 0;
          }
  
          if(this.search_prop.prop_search.usage){
            usage = '===' + 1;
          }
  
          if(this.search_prop.prop_search.usage_guraduation){
            usage_guraduation = '===' + 1;
          }
  
          if(this.search_prop.prop_search.usage_left){
            usage_left = '===' + 1;
          }
  
          if(this.search_prop.prop_search.usage_right){
            usage_right = '===' + 1;
          }
  
          const refine = 'a.owner_id' + owner_id +  '&& a.location' + location + '&&' + handmade +'&& a.decision' + decision + '&& a.usage' + usage + '&& a.usage_guraduation' + usage_guraduation + '&& a.usage_left' + usage_left + '&& a.usage_right' + usage_right;
  
          console.log(refine);
          this.$emit('close', this.search_prop.prop_sort, this.search_prop.prop_search.name, refine);
        },

        // リセット
        resetSearchProp() {
          this.search_prop.prop_sort = 'kana';
          this.search_prop.prop_search.name.input = null;
          this.search_prop.prop_search.name.scope = 'name_only';
          this.search_prop.prop_search.owner = 0;
          this.search_prop.prop_search.location = false;
          this.search_prop.prop_search.location_no = false;
          this.search_prop.prop_search.handmade = false;
          this.search_prop.prop_search.handmade_no = false;
          this.search_prop.prop_search.handmade_complete = false;
          this.search_prop.prop_search.handmade_progress = false;
          this.search_prop.prop_search.handmade_unfinished = false;
          this.search_prop.prop_search.decision = false;
          this.search_prop.prop_search.decision_no = false;
          this.search_prop.prop_search.usage = false;
          this.search_prop.prop_search.usage = false;
          this.search_prop.prop_search.usage_guraduation = false;
          this.search_prop.prop_search.usage_left = false;
          this.search_prop.prop_search.usage_right = false;
        }
      }
    }
  </script>
<template>
    <div v-bind:class="[overlay_class === 1 ? 'overlay' : 'overlay overlay-custom']" @click.self="$emit('close', null, null, null)">
      <div class="content cotent-search content-confirm-dialog panel" ref="content_search_scene">
        <!-- 閉じるボタン -->
        <div class="button-search--close">
          <button type="button" @click.self="$emit('close', null, null, null)">×</button>
        </div>
        <form class="form form-search"  @submit.prevent="searchScene">
          <div class="search-sort-area">
            <span class="search-span"><i class="fas fa-sort fa-fw"></i></i>並び替え</span>
            <div class="checkbox-area--together checkbox-area--search">
              <input type="radio" id="sort_scene_page" v-model="search_scene.scene_sort" value="page">
              <label for="sort_scene_page">ページ順</label>
  
              <input type="radio" id="sort_scene_character" v-model="search_scene.scene_sort" value="character">
              <label for="sort_scene_character">登場人物順</label>
    
              <input type="radio" id="sort_scene_created_at" v-model="search_scene.scene_sort" value="created_at">
              <label for="sort_scene_created_at">作成日順</label>
    
              <input type="radio" id="sort_scene_updated_at" v-model="search_scene.scene_sort" value="updated_at">
              <label for="sort_scene_updated_at">更新日順</label>
            </div>
          </div>
    
          <div class="search-sort-area">
            <span class="search-span"><i class="fas fa-filter fa-fw"></i>絞り込み</span>
            <!-- シーン -->
            <div class="search--select-area--box">
              <!-- ページ -->
              <div class="search--select-area">
                <label for="search_scene_page" class="search--label">ページ</label>
                <div class="serach--select-area-colors">
                  <label for="search_scene_first_page" class="search--label">何ページから</label>
                  <input type="number" min="1" max="100" class="form__item search--input" id="search_scene_first_page" v-model="search_scene.scene_search.page.first"></input>
                  <label for="search_scene_page_final" class="search--label">何ページまで</label>
                  <input type="number" min="2" max="100" class="form__item search--input" id="search_scene_page_finals" v-model="search_scene.scene_search.page.final"></input>
                </div>    
              </div>
  
              <!-- 登場人物 -->
              <div class="search--select-area serach--select-area-box-colors">
                <label class="search--label">登場人物</label>
                <div class="serach--select-area-colors">
                  <div>
                    <label for="search_scene_section" class="search--label">区分</label>
                    <select id="search_scene_section" class="form__item" v-model="selectedAttr" v-on:change="selectedCharacter">
                      <option value=0>選択なし</option>
                      <option v-for="(value, key) in optionCharacters">
                        {{ key }}
                      </option>
                    </select>
                  </div>
    
                  <div>
                    <label for="search_scene_character" class="search--label">人物名</label>
                    <select id="search_scene_character" class="form__item" v-model="search_scene.scene_search.character">
                      <option value=0>選択なし</option>
                      <option v-if="selectedCharacters" v-for="character in selectedCharacters"
                          v-bind:value="character.id">
                        {{ character.name }}
                      </option>
                    </select>
                  </div>
                </div>
                  
              </div>
            </div>
  
            <!-- 小道具 -->
            <!-- 入力検索 -->
            <div class="search--select-area--box">
              <label for="search_scene_name" class="search--label">小道具名</label>
              <input type="text" class="form__item search--input" id="search_scene_name" v-model="search_scene.scene_search.name.input"></input>
              <span class="checkbox-area--together">
                <input type="radio" id="search_scene_name_only" v-model="search_scene.scene_search.name.scope" value="name_only">
                <label for="search_scene_name_only">小道具名のみ</label>       
    
                <input type="radio" id="search_scene_memo_toghether" v-model="search_scene.scene_search.name.scope" value="memo_together">
                <label for="search_scene_memo_toghether">メモ含む</label>
              </span>
            </div>
    
            <!-- 色々分類 -->
            <div class="search--select-area--box">    
              <!-- 使用するか -->
              <div class="search--select-area checkbox-area--together">
                <span class="checkbox-area--together search--select-area--performance">
                  <input type="checkbox" id="search_scene_usage" class="form__check__input" v-model="search_scene.scene_search.usage">
                  <label for="search_scene_usage" class="form__check__label">中間発表</label>
                </span>
  
                <span class="search--select-area--performance">
                  <span class="checkbox-area--together">
                    <input type="checkbox" id="search_scene_usage_guraduation" class="form__check__input" v-model="search_scene.scene_search.usage_guraduation">
                    <label for="search_scene_usage_guraduation" class="form__check__label">卒業公演</label>
                  </span>
  
                  <span class="checkbox-area--together">
                    <input type="checkbox" id="search_scene_usage_left" class="form__check__input" value="left" v-model="search_scene.scene_search.usage_left">
                    <label for="search_scene_usage_left" class="form__check__label">上手</label>
                    
                    <input type="checkbox" id="search_scene_usage_right" class="form__check__input" value="right" v-model="search_scene.scene_search.usage_right"></input>
                    <label for="search_scene_usage_right" class="form__check__label">下手</label>
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
      name: 'searchScene',
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
            // 小道具 一覧
            props: [],
            // 登場人物
            characters: [],
            selectedAttr: '',
            selectedCharacters: '',
            optionCharacters: null,
            // 持ち主リスト
            optionOwners: [],
            // 検索
            search_scene: {
              scene_sort: "page",
              scene_search: {
                page: {
                  first: null,
                  final: null
                },
                section: 0,
                character: 0,
                name: {
                  input: null,
                  scope: "name_only"
                },
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
                await this.fetchCharacters();
                await this.fetchOwners();
    
                const content_dom = this.$refs.content_search_scene;
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
          // 小道具一覧を取得
          async fetchProps () {
            const response = await axios.get('/api/props_all');
  
            if (response.status !== 200) {
              this.$store.commit('error/setCode', response.status);
              return false;
            }
  
            this.props = response.data; // オリジナルデータ
            this.showProps = JSON.parse(JSON.stringify(this.props));
          },
  
          // 登場人物を取得
          async fetchCharacters () {
            const response = await axios.get('/api/informations/characters');
  
            if (response.status !== 200) {
              this.$store.commit('error/setCode', response.status);
              return false;
            }
  
            this.characters = response.data;
  
            // 区分と登場人物をオブジェクトに変換する
            let sections = new Object();
            this.characters.forEach(function(section){
              sections[section.section] = section.characters
            });
            this.optionCharacters = sections;      
          },
  
          // 連動プルダウン
          selectedCharacter: function(){
            this.selectedCharacters = this.optionCharacters[this.selectedAttr];
            this.search_scene.scene_search.section = this.selectedAttr;
          },

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
          async searchScene() {
            let page = 'a.first_page != ' + 0;
            let character_id = 'a.character_id !=' + 0;
            let prop_id = 'a.prop_id != ' + 0;
            let name_input = '!=' + 100;
            let name_scope = '!=' + 100;
            let usage = '!=' + 100;
            let usage_guraduation = '!=' + 100;
            let usage_left = '!=' + 100;
            let usage_right = '!=' + 100;
  
            // 小道具 id検索
            if(this.search_scene.scene_search.name.input){
              await this.fetchProps();            
  
              let prop_ids = [];
  
              let array_original = JSON.parse(JSON.stringify(this.props));
              let array = [];
  
              if(this.h(this.search_scene.scene_search.name.input)){
                if(this.search_scene.scene_search.name.scope === "memo_together"){
                  // メモを含めて検索
                  array = array_original.filter((a) => {
                    if(a.name.toLocaleLowerCase().indexOf(this.h(this.search_scene.scene_search.name.input).toLocaleLowerCase()) !== -1) {
                      return a;
                    }else if(a.kana.toLocaleLowerCase().indexOf(this.h(this.search_scene.scene_search.name.input).toLocaleLowerCase()) !== -1) {
                      return a;
                    }else if(a.prop_comments[0]){
                      if(a.prop_comments[0].memo.toLocaleLowerCase().indexOf(this.h(this.search_scene.scene_search.name.input).toLocaleLowerCase()) !== -1){
                        return a;
                      }                  
                    }
                  });
                }else{
                  // 小道具名のみで検索
                  array = array_original.filter((a) => {
                    if(a.name.toLocaleLowerCase().indexOf(this.h(this.search_scene.scene_search.name.input).toLocaleLowerCase()) !== -1) {
                      return a;
                    }else if(a.kana.toLocaleLowerCase().indexOf(this.h(this.search_scene.scene_search.name.input).toLocaleLowerCase()) !== -1) {
                      return a;
                    }
                  });
                }
              }else{
                array = array_original;
              }
  
              prop_ids = array.map(a => a.id);
  
              prop_id = '('
              prop_ids.forEach((prop, index) => {
                prop_id = prop_id + 'a.prop_id === ' + prop;
                if(index !== prop_ids.length-1){
                    prop_id = prop_id + '||';
                }else{
                    prop_id = prop_id + ')';
                }
              });
            }     
  
            if(this.search_scene.scene_search.page.first){
              page = 'a.first_page >= ' + this.search_scene.scene_search.page.first;
              if(this.search_scene.scene_search.page.final) {
                page = page + '&& a.first_page <=' + this.search_scene.scene_search.page.final + '&& a.final_page >= ' + this.search_scene.scene_search.page.first +  '&& a.final_page <= ' + this.search_scene.scene_search.page.final;
              }
            }
  
            if(this.search_scene.scene_search.section != 0 && this.search_scene.scene_search.character == 0){
              // 登場人物のid配列
              let character_ids = this.optionCharacters[this.search_scene.scene_search.section].map((character) => character.id);
              // 登場人物のidで検索文字列
              character_id = '('
              character_ids.forEach((character, index) => {
                character_id = character_id + 'a.character_id === ' + character;
                if(index !== character_ids.length-1){
                  character_id = character_id + '||';
                }else{
                  character_id = character_id + ')';
                }
              });
            }
  
            if(this.search_scene.scene_search.character != 0){
              character_id = 'a.character_id === ' + this.search_scene.scene_search.character;
            }
    
            if(this.search_scene.scene_search.usage){
              usage = '===' + 1;
            }
    
            if(this.search_scene.scene_search.usage_guraduation){
              usage_guraduation = '===' + 1;
            }
    
            if(this.search_scene.scene_search.usage_left){
              usage_left = '===' + 1;
            }
    
            if(this.search_scene.scene_search.usage_right){
              usage_right = '===' + 1;
            }
    
            const refine = prop_id + '&&' + page + '&&' + character_id  + '&& a.usage' + usage + '&& a.usage_guraduation' + usage_guraduation + '&& a.usage_left' + usage_left + '&& a.usage_right' + usage_right;
    
            this.$emit('close', this.search_scene.scene_sort, this.search_scene.scene_search.name, refine);
          },
        }
      }
    </script>
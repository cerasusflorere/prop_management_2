<template>
  <div>
    <!-- ボタンエリア -->
    <div>
      <!--表示切替ボタン-->
      <div v-show="tabProp === 1">
       <button type="button" @click="switchDisplay_prop"><i class="fas fa-th fa-fw"></i>写真ブロック</button>
      </div>
      <div v-show="tabProp === 2">
       <button type="button" @click="switchDisplay_prop"><i class="fas fa-list-ul fa-fw"></i>リスト</button>
      </div>
    </div>


    <!-- 表示エリア -->
    <div v-show="tabProp === 1">
      <!-- ダウンロードボタン -->
      <div>
        <button type="button" @click="downloadProps">ダウンロード</button>
      </div>
      <table>
        <thead>
          <tr>
            <th></th>
            <th>小道具名</th>
            <th>持ち主</th>
            <th>使用状況</th>
            <th>メモ</th>
            <th>登録日時</th>
          </tr>
        </thead>
        <tbody v-if="showProps.length">
          <tr v-for="(prop, index) in showProps">
            <td>{{ index + 1 }}</td>
            <!-- 小道具名 -->
            <td type="button" @click="openModal_propDetail(prop.id)">{{ prop.name }}</td>
            <!-- 持ち主 -->
            <td v-if="prop.owner">{{ prop.owner.name }}</td>
            <td v-else></td>
            <!-- 使用状況 -->
            <td v-if="prop.usage"><i class="fas fa-check fa-fw"></i></td>
            <td v-else></td>
            <!-- メモ -->
            <td v-if="prop.prop_comments.length">
              <div v-for="memo in prop.prop_comments"> {{ memo.memo }}</div>
            </td>
            <td v-else></td>
            <!-- 登録日時 -->
            <td>{{ prop.created_at }}</td>
          </tr>
        </tbody>      
      </table>
    </div>

    <div v-show="tabProp === 2" class="photo-list">
      <div class="grid" v-if="showProps.length">
        <div class="grid__item" v-for="prop in showProps">
          <div class="photo">
            <figure class="photo__wrapper" type="button" @click="openModal_propDetail(prop.id)">
              <img
                class="photo__image"
                :src="prop.url"
                :alt="prop.name"
              >
            </figure>
            <div>
              <div>
                {{ prop.name}}
              </div>
              <div v-if="prop.owner">
                {{ prop.owner.name }}
              </div>
              <div v-if="prop.usage">
                <i class="fas fa-tag"></i>
              </div>
              <div v-if="prop.prop_comments.length">
                <div v-for="memo in prop.prop_comments">
                  {{ memo.memo }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div> 
    <detailProp :postProp="postProp" v-show="showContent" @close="closeModal_propDetail" /> 
  </div>
</template>

<script>
  import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util'

  import detailProp from '../components/Detail_Prop.vue'

  export default {
    // このページの上で表示するコンポーネント
    components: {
      detailProp
    },
    data() {
      return{
        // タブ切り替え
        tabProp: 1,
        // 取得するデータ
        props: [],
        // 表示するデータ
        showProps: [],
        // 小道具詳細
        showContent: false,
        postProp: ""
      }
    },
    watch: {
      $route: {
        async handler () {
          await this.fetchProps()
        },
        immediate: true
      }
    },
    methods: {
      // 小道具一覧を取得
      async fetchProps () {
        const response = await axios.get('/api/props_all')
  
        if (response.statusText !== 'OK') {
          this.$store.commit('error/setCode', response.status)
          return false
        }

        this.props = response.data // オリジナルデータ
        this.showProps = JSON.parse(JSON.stringify(this.props));
      },
      
      // 表示切替
      switchDisplay_prop() {
        if(this.tabProp === 1){
          this.tabProp = 2;
        }else{
          this.tabProp = 1;
        }
      },

      // 小道具詳細のモーダル表示 
      openModal_propDetail (id) {
        this.showContent = true
        this.postProp = id;
      },
      // 小道具詳細のモーダル非表示
      async closeModal_propDetail() {
        this.showContent = false
        await this.fetchProps()
      },

      // ダウンロード
      downloadProps() {
        const response = axios.post('/api/props_list', this.showProps);
      }
    }
  }  
</script>

<style>
  table {
    margin: auto;
    width: 95%;
    border-collapse: collapse;    
  }

  table th, table td {
    border: solid 1px black; /*実線 1px 黒*/
    text-align: center;
  }

  table th {/*table内のthに対して*/
    position: sticky;
    top: 3.9rem;
    padding: 0.5em;/*上下左右10pxずつ*/
    color: #169b62;/*文字色 緑*/
    background: #ddefe3;/*背景色*/
  }

  table td {/*table内のtdに対して*/
    padding: 0.3em 0.5em;/*上下3pxで左右10px*/
  }
</style>
<template>
  
  <!-- 表示エリア -->
  <div v-show="tab === 1">
    <!-- ダウンロードボタン -->
    <div>
      <button type="button" @click="downloadProps">ダウンロード</button>
    </div>
    <table>
      <thead>
        <tr>
          <th>小道具名</th>
          <th>持ち主</th>
          <th>使用状況</th>
          <th>メモ</th>
          <th>登録日時</th>
        </tr>
      </thead>
      <tbody v-if="props.length">
        <tr v-for="prop in props">
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
        tab: 1,
        // 取得するデータ
        props: [],
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

        this.props = response.data
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
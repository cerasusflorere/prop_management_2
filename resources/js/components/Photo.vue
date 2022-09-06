<template>
  <div class="photo">
    <figure class="photo__wrapper" type="button" @click="openModal_propDetail(prop_photo.id)">
      <img
        class="photo__image"
        :src="prop_photo.url"
        :alt="prop_photo.name"
      >
      <div>
        {{ prop_photo.name}}
      </div>
      <div v-if="prop_photo.owner">
        {{ prop_photo.owner.name }}
      </div>
      <div v-if="prop_photo.usage">
        <i class="fas fa-tag"></i>
      </div>
      <div v-if="prop_photo.prop_comments.length">
        <div v-for="memo in prop_photo.prop_comments">
          {{ memo.memo }}
        </div>
      </div>
    </figure>
  </div>
</template>

<script>
import detailProp from '../components/Detail_Prop.vue'

export default {
  // このページの上で表示するコンポーネント
  components: {
    detailProp
  },
  props: {
    prop_photo: {
      type: Object,
      required: true
    }
  },
  data() {
    return{
      // 小道具詳細
      showContent: false,
      postProp: ""
    }
  },
  methods: {
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
  }
}
</script>
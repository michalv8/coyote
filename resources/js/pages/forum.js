// import 'jquery-color-animation/jquery.animate-colors';
// import 'jquery-prettytextdiff/jquery.pretty-text-diff';
// import '../plugins/tags';
// import '../pages/forum/draft';
// import '../pages/forum/posting';
// import '../pages/forum/sidebar';
// import '../pages/forum/tags';
// import 'bootstrap/js/src/popover';
import VueTimeago from '../plugins/timeago';
import VueSection from '../components/forum/section.vue';
import VueTopic from '../components/forum/topic.vue';
import VuePost from '../components/forum/post.vue';
import VueForm from '../components/forum/form.vue';
import VueModal from '../components/modal.vue';
import VueButton from '../components/forms/button.vue';
import VueSelect from '../components/forms/select.vue';
import Vue from "vue";
import store from '../store';
import { default as mixin } from '../components/mixins/user';
import { mapState, mapGetters } from "vuex";

Vue.use(VueTimeago);

new Vue({
  el: '#js-forum',
  delimiters: ['${', '}'],
  store,
  data: {
    collapse: window.collapse || {},
    postsPerPage: window.postsPerPage || null,
    flags: window.flags || [],
    showCategoryName: window.showCategoryName || false,
    groupStickyTopics: window.groupStickyTopics || false,
    tags: window.tags || {}
  },
  components: {
    'vue-section': VueSection,
    'vue-topic': VueTopic
  },
  created() {
    store.commit('forums/init', window.forums || []);
    store.commit('topics/init', (window.topics?.data) || []);
  },
  methods: {
    changeCollapse(id) {
      this.$set(this.collapse, id, !(!!(this.collapse[id])));
    },

    getFlag(topicId) {
      return this.flags[topicId];
    }
  },
  computed: {
    forums() {
      return store.state.forums.categories;
    },

    sections() {
      return Object.values(
        this
          .forums
          .sort((a, b) => a.order < b.order ? -1 : 1)
          .reduce((acc, forum) => {
            if (!acc[forum.section]) {
              acc[forum.section] = {name: forum.section, order: forum.order, categories: [], isCollapse: !!(this.collapse[forum.id])};
            }

            acc[forum.section].categories.push(forum);

            return acc;
          }, {})
        ).sort((a, b) => a.order < b.order ? -1 : 1); // sort sections
    },

    groups() {
      return this.topics.reduce((acc, item) => {
        let index = this.groupStickyTopics ? (+!item.is_sticky) : 0;

        if (!acc[index]) {
          acc[index] = [];
        }

        acc[index].push(item);

        return acc;
      }, {});
    },

    ...mapState('topics', ['topics'])
  }
});

new Vue({
  el: '#js-sidebar',
  delimiters: ['${', '}'],
  mixins: [ mixin ],
  components: { 'vue-modal': VueModal, 'vue-button': VueButton, 'vue-select': VueSelect },
  store,
  data() {
    return {
      topic: window.topic,
      forum: window.forum,
      allForums: window.allForums,
      reasons: window.reasons,
      isProcessing: false,
      forumId: null,
      reasonId: null
    }
  },
  methods: {
    markForums() {
      store.dispatch('forums/markAll');
      store.commit('topics/markAll');
    },

    markTopics() {
      store.dispatch('topics/markAll');
    },

    lock() {
      store.dispatch('topics/lock', this.topic);
    },

    subscribe() {
      store.dispatch('topics/subscribe', this.topic);
    },

    move() {
      this.isProcessing = true;

      store.dispatch('topics/move', { topic: this.topic, forumId: this.forumId, reasonId: this.reasonId })
        .then(result => window.location.href = result.data.url)
        .finally(() => this.isProcessing = false);
    }
  }
});

new Vue({
  el: '#js-post',
  delimiters: ['${', '}'],
  components: { 'vue-post': VuePost, 'vue-form': VueForm },
  store,
  created() {
    store.commit('posts/init', { pagination: window.pagination });
  },
  computed: mapGetters('posts', ['posts'])
})

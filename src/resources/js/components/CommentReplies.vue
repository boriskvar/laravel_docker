<template>
    <ul>
      <li v-for="comment in comments" :key="comment.id">
        <strong>{{ comment.user_name }}</strong> ({{ comment.email }})
        <p>{{ comment.text }}</p>
        <small>{{ formatDate(comment.created_at) }}</small>

        <!-- Рекурсивное отображение ответов -->
        <CommentReplies
          v-if="comment.replies && comment.replies.length"
          :comments="comment.replies"
          class="comment-reply"
        />
      </li>
    </ul>
  </template>

  <script setup>
  import { computed } from 'vue';
  import CommentReplies from './CommentReplies.vue';

  const props = defineProps({
    comments: {
      type: Array,
      required: true,
    },
  });

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString();
  };
  </script>

  <style scoped>
  .comment-reply {
    margin-left: 20px;
    border-left: 2px solid #ccc;
    padding-left: 10px;
  }
  </style>

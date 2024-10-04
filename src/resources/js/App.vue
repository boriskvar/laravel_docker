<template>
    <div id="app">
      <h2>Таблица комментариев</h2>
      <CommentTable :comments="comments" />

      <h2>Дерево комментариев</h2>
      <div class="comments">
        <CommentTree
          v-for="comment in comments"
          :key="comment.id"
          v-bind="comment"
        />
      </div>

      <h2>Форма для комментариев</h2>
      <CommentForm :comments="comments" :isFormVisible="isFormVisible" @submitComment="addComment" />
    </div>
  </template>

  <script>
  import { ref } from 'vue';
  import CommentTable from './components/CommentTable.vue';
  import CommentTree from './components/CommentTree.vue';
  import CommentForm from './components/CommentForm.vue';

  export default {
    name: 'App',
    components: {
      CommentTable,
      CommentTree,
      CommentForm,
    },
    setup() {
      const comments = ref([]);
      const isFormVisible = ref(true);

      const fetchComments = async () => {
        try {
          const response = await fetch('http://localhost/api/comments');
          if (!response.ok) {
            throw new Error(`Error fetching comments: ${response.status} ${response.statusText}`);
          }
          const data = await response.json();
          console.log(data); // Проверяем данные в консоли
          comments.value = transformComments(data.data); // Используем data.data для обработки комментариев
        } catch (error) {
          console.error('Error fetching comments:', error);
        }
      };

      const transformComments = (data) => {
        if (!Array.isArray(data)) {
          console.error('Data is not an array:', data);
          return [];
        }
        return data.map(comment => ({
          id: comment.id,
          author: comment.user_name,
          email: comment.email,
          homePage: comment.home_page,
          captcha: comment.captcha,
          body: comment.text,
          timestamp: comment.created_at,
          replies: comment.replies ? transformComments(comment.replies) : [],
          avatar: comment.avatar ? `${comment.avatar}` : 'default-avatar.png',
        }));
      };

      const addComment = (newComment) => {
        comments.value.push(newComment); // Добавляем новый комментарий в список
      };

      fetchComments(); // Вызываем fetchComments при монтировании компонента

      return {
        comments,
        isFormVisible,
        fetchComments,
        transformComments,
        addComment,
      };
    },
  };
  </script>


    <style lang="css">
    html {
      box-sizing: border-box;
    }

    *,
    *:before,
    *:after {
      box-sizing: inherit;
    }

    body {
      font-family: sans-serif;
    }
    </style>

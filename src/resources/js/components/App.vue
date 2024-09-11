<template>
    <div id="app">
      <h1>Comments</h1>

      <!-- Форма для добавления комментария -->
      <div v-if="isFormVisible">
        <form id="commentForm" @submit.prevent="submitComment">
          <div>
            <label for="user_name">User Name:</label>
            <input type="text" id="user_name" v-model="user_name" required />
          </div>

          <div>
            <label for="avatar">Avatar URL:</label>
            <input
              type="text"
              id="avatar"
              v-model="avatar"
              placeholder="images/avatars/Your Name.png"
            />
          </div>

          <div>
            <label for="email">E-mail:</label>
            <input type="email" id="email" v-model="email" required />
          </div>

          <div>
            <label for="home_page">Home page (optional):</label>
            <input
              type="url"
              id="home_page"
              v-model="home_page"
              placeholder="https://example.com"
            />
          </div>

          <div>
            <label for="text">Text:</label>
            <textarea id="text" v-model="text" required></textarea>
          </div>

          <div>
            <label for="fileInput">Attach file (optional):</label>
            <input
              type="file"
              id="fileInput"
              @change="handleFileChange"
              accept=".jpg, .jpeg, .png, .gif, .txt"
            />
          </div>

          <div class="captcha">
            <input v-model="captchaInput" placeholder="Enter captcha" required />
          </div>

          <button type="submit">Submit</button>
        </form>
      </div>

      <!-- Таблица комментариев -->
      <div v-if="comments.length > 0">
        <h2>Comments Table</h2>
        <table>
          <thead>
            <tr>
              <th @click="sortBy('user_name')">User Name</th>
              <th @click="sortBy('email')">Email</th>
              <th @click="sortBy('created_at')">Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="comment in paginatedComments" :key="comment.id">
              <td>{{ comment.user_name }}</td>
              <td>{{ comment.email }}</td>
              <td>{{ formatDate(comment.created_at) }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Пагинация -->
        <div class="pagination">
          <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1">
            Previous
          </button>
          <span>Page {{ currentPage }} of {{ totalPages }}</span>
          <button
            @click="changePage(currentPage + 1)"
            :disabled="currentPage === totalPages"
          >
            Next
          </button>
        </div>
      </div>

      <!-- Дерево комментариев -->
      <div>
        <h2>Comments Tree</h2>
        <CommentTree :comments="comments" @reply-added="handleReplyAdded" />
      </div>
    </div>
  </template>

  <script>
  import { ref, onMounted, computed } from "vue";
  import CommentTree from "./CommentTree.vue";

  export default {
    name: "App",
    components: {
      CommentTree,
    },
    props: {
      comments: Array
    },
    setup() {
      const comments = ref([]);
      const user_name = ref("");
      const avatar = ref("");
      const email = ref("");
      const text = ref("");
      const home_page = ref("");
      const captchaInput = ref("");
      const isFormVisible = ref(true);
      const file_path = ref(null);
      const currentPage = ref(1);
      const itemsPerPage = 25;
      const sortKey = ref("created_at");
      const sortOrder = ref("desc");

      const loadComments = async () => {
        try {
          const response = await fetch("/api/comments");
          if (!response.ok) throw new Error("Failed to fetch comments");
          const data = await response.json();
          comments.value = data.sort(
            (a, b) => new Date(b.created_at) - new Date(a.created_at)
          );
        } catch (error) {
          console.error("Error loading comments:", error);
        }
      };

      const handleFileChange = (event) => {
        const selectedFile = event.target.files[0];
        if (
          selectedFile &&
          selectedFile.size <= 100 * 1024 &&
          /\.(jpg|jpeg|png|gif|txt)$/i.test(selectedFile.name)
        ) {
          file_path.value = selectedFile;
        } else {
          alert("File is too large or has unsupported format.");
          event.target.value = null;
        }
      };

      const submitComment = async () => {
        // Проверка на наличие запрещенных HTML-тегов
        const forbiddenTags = /<\/?(script|iframe|object|embed|link|style)[\s>]/i;
        if (forbiddenTags.test(text.value)) {
          alert("Запрещенные теги обнаружены в тексте комментария.");
          return;
        }

        // Проверка на заполнение всех необходимых полей
        if (user_name.value && email.value && text.value && captchaInput.value) {
          const formData = new FormData();
          formData.append("user_name", user_name.value);
          formData.append("email", email.value);
          formData.append("text", text.value);
          formData.append("captcha", captchaInput.value);
          if (home_page.value) formData.append("home_page", home_page.value);
          if (file_path.value) formData.append("file_path", file_path.value); // Обновлено на 'file_path'

          try {
            const response = await fetch("/api/comments", {
              method: "POST",
              body: formData,
            });
            if (!response.ok) {
              // Отображение детальной информации об ошибке
              const errorData = await response.json();
              throw new Error(
                `Failed to add comment: ${errorData.message || "Unknown error"}`
              );
            }

            const data = await response.json();
            comments.value.unshift(data); // Добавляем новый комментарий в начало списка

            // Очистка формы
            user_name.value = "";
            email.value = "";
            text.value = "";
            home_page.value = "";
            captchaInput.value = "";
            file_path.value = null;
          } catch (error) {
            console.error("Error adding comment:", error);
          }
        } else {
          console.error("Please enter all fields and captcha");
        }
      };

      const formatDate = (dateString) => {
        return (
          new Date(dateString).toLocaleDateString("ru-RU") +
          " в " +
          new Date(dateString).toLocaleTimeString("ru-RU", {
            hour: "2-digit",
            minute: "2-digit",
          })
        );
      };

      const sortBy = (key) => {
        sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
        comments.value.sort((a, b) => {
          if (sortOrder.value === "asc") return a[key] > b[key] ? 1 : -1;
          return a[key] < b[key] ? 1 : -1;
        });
      };

      const paginatedComments = computed(() => {
        const start = (currentPage.value - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        return comments.value.slice(start, end);
      });

      const totalPages = computed(() => {
        return Math.ceil(comments.value.length / itemsPerPage);
      });

      const changePage = (page) => {
        if (page > 0 && page <= totalPages.value) {
          currentPage.value = page;
        }
      };

      const handleReplyAdded = ({ commentId, newReply }) => {
        const findComment = (commentList) => {
          for (const comment of commentList) {
            if (comment.id === commentId) {
              comment.replies = comment.replies || [];
              comment.replies.push(newReply);
              return true;
            }
            if (comment.replies && findComment(comment.replies)) {
              return true;
            }
          }
          return false;
        };

        if (findComment(comments.value)) {
          comments.value = [...comments.value];
        }
      };

      onMounted(() => {
        loadComments();
      });

      return {
        comments,
        user_name,
        avatar,
        email,
        text,
        home_page,
        captchaInput,
        isFormVisible,
        submitComment,
        handleFileChange,
        formatDate,
        sortBy,
        paginatedComments,
        currentPage,
        totalPages,
        changePage,
        handleReplyAdded,
      };
    },
  };
  </script>

  <style scoped>
  /* Стили */
  form {
    display: flex;
    flex-direction: column;
    max-width: 400px;
    margin: 0 auto;
  }

  label {
    margin-bottom: 0.5em;
  }

  input[type="text"],
  input[type="email"],
  input[type="url"],
  textarea {
    width: 100%;
    padding: 0.5em;
    margin-bottom: 1em;
    border: 1px solid #ccc;
  }

  button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 0.5em 1em;
    cursor: pointer;
  }

  button:hover {
    background-color: #0056b3;
  }
  </style>

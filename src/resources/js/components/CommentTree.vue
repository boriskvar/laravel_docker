<template>
    <div>
      <div v-for="comment in comments" :key="comment.id" :style="{ marginLeft: calculateIndentLevel(comment) + 'px' }">
        <div>
          <img :src="getAvatarUrl(comment.user_name)" @error="handleAvatarError" class="avatar"/>
          <strong>{{ comment.user_name }}</strong>
          <p v-html="comment.text"></p>
          <small>{{ formatDate(comment.created_at) }}</small>
          <button @click="toggleReplyForm(comment.id)">Reply</button>
        </div>

        <!-- Форма ответа -->
        <div v-if="activeReplyForm === comment.id">
          <form @submit.prevent="submitReply(comment)">
            <!-- Поля формы -->
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
              <textarea id="text" v-model="replyText" required></textarea>
            </div>

            <!-- Поле для загрузки файла -->
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
            <button @click="toggleReplyForm(null)">Cancel</button>
          </form>
        </div>

        <!-- Рекурсивное отображение дочерних комментариев -->
        <comment-tree
          v-if="comment.replies"
          :comments="comment.replies"
          :level="level + 1"
          @reply-added="$emit('reply-added', $event)"
        ></comment-tree>
      </div>
    </div>
  </template>

  <script>
  export default {
    props: {
      comments: Array,
      level: {
        type: Number,
        default: 0,
      },
    },
    data() {
      return {
        activeReplyForm: null,
        user_name: "",
        avatar: "",
        email: "",
        home_page: "",
        replyText: "",
        captchaInput: "",
        file: null, // Сохраняем файл
      };
    },
    methods: {
      calculateIndentLevel(comment) {
        return (this.level || 0) * 20;
      },
      getAvatarUrl(userName) {
        return `/images/avatars/${userName}.png`;
      },
      handleAvatarError(event) {
        event.target.src = "/images/avatars/default.png";
      },
      formatDate(date) {
        return new Date(date).toLocaleString();
      },
      toggleReplyForm(commentId) {
        this.activeReplyForm = this.activeReplyForm === commentId ? null : commentId;
      },
      handleFileChange(event) {
        this.file = event.target.files[0]; // Сохраняем файл в data
      },
      async submitReply(parentComment) {
        if (this.validateReply()) {
          const formData = new FormData();
          formData.append("user_name", this.user_name);
          formData.append("avatar", this.avatar);
          formData.append("email", this.email);
          formData.append("home_page", this.home_page);
          formData.append("text", this.replyText);
          formData.append("captcha", this.captchaInput);
          if (this.file) {
            formData.append("file", this.file); // Добавляем файл в formData
          }

          try {
            const response = await fetch(`/api/comments/${parentComment.id}/replies`, {
              method: "POST",
              body: formData,
            });

            if (response.ok) {
              const data = await response.json();
              this.$emit('reply-added', { parentId: parentComment.id, newReply: data });
              this.resetReplyForm();
            } else {
              const errorData = await response.json();
              throw new Error(errorData.message || response.statusText);
            }
          } catch (error) {
            console.error("Error submitting reply:", error);
          }
        } else {
          alert("Please fill in all required fields");
        }
      },
      validateReply() {
        return (
          this.user_name.trim() !== "" &&
          this.email.trim() !== "" &&
          this.replyText.trim() !== "" &&
          this.captchaInput.trim() !== ""
        );
      },
      resetReplyForm() {
        this.user_name = "";
        this.avatar = "";
        this.email = "";
        this.home_page = "";
        this.replyText = "";
        this.captchaInput = "";
        this.file = null;
        this.activeReplyForm = null;
      },
    },
  };
  </script>

<style scoped>
/* Стили для панели инструментов */
.toolbar {
  margin-bottom: 10px;
}

.toolbar button {
  margin-right: 5px;
}

.reply-form textarea {
  width: 100%;
  height: 100px;
}

.comment-tree {
  /* Основные стили для дерева комментариев */
  font-family: Arial, sans-serif;
  line-height: 1.5;
}

.comment-item {
  padding: 10px;
  border-bottom: 1px solid #ddd;
  margin-bottom: 10px;
}

.comment-header {
  display: flex;
  align-items: center;
  margin-bottom: 5px;
}

.avatar {
  width: 40px; /* Размер аватара */
  height: 40px; /* Высота аватара */
  border-radius: 50%; /* Округление аватара для круглой формы */
  object-fit: cover; /* Обрезка изображения, чтобы оно не искажалось */
  margin-right: 10px; /* Отступ между аватаром и текстом */
}

.author {
  font-weight: bold;
  margin-right: 5px;
}

.date-time {
  color: #888;
  font-size: 0.9em;
}

.comment-text {
  margin: 10px 0;
}

.reply-form,
.reply-to-reply-form {
  margin-top: 10px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.reply-button,
.reply-to-reply-button {
  margin-top: 10px;
}

.replies {
  margin-top: 10px;
}

/* Стили для вложенных комментариев */
.comment-item.nested {
  border-left: 2px solid #ccc;
}

.comment-item:not(.nested) {
  padding-left: 0;
}
</style>

<template>
  <div>
    <div class="comment" :class="{ reply: type === 'reply' }">
      <header>

        <img :src="avatarUrl" alt="Avatar" class="comment-avatar" />

        <h3 class="comment-name">{{ author }}</h3>
        <span class="comment-date">{{ formattedDate }}</span>
        <span v-html="icon" class="comment-icon"></span>
      </header>
      <div v-html="body" class="comment-body" />
      <button @click="showReplyInput = !showReplyInput" class="reply-button">
        Reply
      </button>
      <div v-if="showReplyInput" class="reply-input">
        <img :src="avatarPreview || avatarUrl" alt="Avatar" class="reply-avatar" />
        <input type="file" @change="onFileChange" />
        <textarea v-model="replyName" placeholder="Enter your name..."></textarea>
        <textarea v-model="replyBody" placeholder="Enter your reply..."></textarea>
        <button @click="submitReply">Submit</button>
      </div>
    </div>
    <div v-if="replies.length" class="comment-replies">
      <Comment
        v-for="reply in replies"
        :key="reply.id"
        v-bind="reply"
        type="reply"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: "Comment",
  props: {
    author: { type: String, required: true },
    body: { type: String, required: true },
    timestamp: { type: String, required: true },
    replies: { type: Array, required: true, default: () => [] }, // Добавлено значение по умолчанию
    type: { type: String, required: false, default: "comment" },
    id: { type: Number, required: true }, // Убедитесь, что ID обязательный
    avatar: { type: String, required: true }, // Добавлено свойство для аватара
  },
  data() {
    return {
      showReplyInput: false,
      replyBody: "",
      replyName: "",
      selectedFile: null, // Для хранения выбранного файла
      avatarPreview: null, // Для предварительного просмотра аватара

    };
  },
  methods: {
    onFileChange(event) {
      this.selectedFile = event.target.files[0];
      if (this.selectedFile) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.avatarPreview = e.target.result; // Предварительный просмотр
        };
        reader.readAsDataURL(this.selectedFile);
      }
    },
    async submitReply() {
      if (!this.replyBody.trim()) {
        console.warn("Reply cannot be empty.");
        return;
      }

      try {
        const formData = new FormData();
        formData.append("text", this.replyBody);
        formData.append("user_name", this.replyName);

        if (this.selectedFile) {
          formData.append("avatar", this.selectedFile); // Добавляем аватар, если загружен
        }

        const response = await fetch(`/api/comments/${this.id}/replies`, {
          method: "POST",
          body: formData, // Используем FormData для отправки формы
        });

        if (!response.ok) {
          const errorText = await response.text();
          console.error("Error response:", errorText);
          throw new Error("Failed to submit reply");
        }

        const newReply = await response.json(); // Получаем JSON-ответ
        this.replies.push(newReply);
        this.replyBody = "";
        this.replyName = ""; // очищаем поле имени
        this.selectedFile = null;
        this.avatarPreview = null;
        this.showReplyInput = false;
      } catch (error) {
        console.error("Error submitting reply:", error);
      }
    },
  },
  computed: {
    avatarUrl() {
      // Формируем правильный путь к аватару
      // Проверяем, существует ли avatar, чтобы избежать ошибок
      return this.avatar ? `${this.avatar}` : 'default-avatar.png';
    },
    formattedDate() {
      const date = new Date(this.timestamp);
      return (
        date.toLocaleDateString("ru-RU") +
        " в " +
        date.toLocaleTimeString("ru-RU", { hour: "2-digit", minute: "2-digit" })
      );
    },
    icon() {
      const commentIcon = `<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18zM18 14H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg>`;
      const replyIcon = `<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M10 9V5l-7 7 7 7v-4.1c5 0 8.5 1.6 11 5.1-1-5-4-10-11-11z"/></svg>`;

      return this.type === "reply" ? replyIcon : commentIcon;
    },
  },
};
</script>

<style lang="css" scoped>
.reply-avatar {
  width: 30px; /* Установи нужный размер для аватара в ответе */
  height: 30px;
  border-radius: 50%; /* Чтобы сделать его круглым */
  margin-right: 10px; /* Отступ справа */
}

.comment-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 10px;
}

button {
  background-color: DodgerBlue;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  border-radius: 0.25rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

button:hover {
  background-color: RoyalBlue;
}

.comment {
  border: 1px solid DodgerBlue;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  padding: 1.5rem;
}

.comment.reply {
  position: relative;
}

.comment.reply:before {
  background-color: Silver;
  content: "";
  height: 1px;
  left: -2.5rem;
  position: absolute;
  top: 50%;
  width: 0.75rem;
}

h3,
p {
  margin: 0;
}

header {
  align-items: center;
  display: flex;
  margin-bottom: 0.75rem;
}

svg {
  fill: SlateGray;
}

.comment-body {
  margin-bottom: 0.375rem;
}

.timestamp {
  color: DimGray;
  font-size: 0.8rem;
  margin-left: 30px;
}

.comment-replies {
  padding-left: 3.5rem;
  position: relative;
}

.comment-replies:before {
  background-color: SlateGray;
  content: "";
  height: calc(100% + 1rem);
  left: 1rem;
  position: absolute;
  top: 0;
  width: 1px;
}

.comment-replies:last-child:before {
  height: calc(100% - 1rem);
}

.comment-name {
  margin-right: 30px;
}

.comment-date {
  color: DimGray;
  font-size: 0.8rem;
  margin-right: 30px;
}

.comment-icon {
  margin-left: auto;
}
</style>

<template>
    <div v-if="isFormVisible">
      <form id="commentForm" @submit.prevent="submitComment" method="post">
        <input type="hidden" name="rating" value="0">
        <div>
          <label for="user_name">User Name:</label>
          <input
            type="text"
            id="user_name"
            v-model="user_name"
            required
          />
        </div>

        <div>
            <label for="avatar">Avatar (optional):</label>
            <input
              type="file"
              id="avatar"
              @change="handleAvatarChange"
              accept="image/*"
            />
            <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar Preview" class="avatar-preview" />
        </div>

        <div>
          <label for="email">E-mail:</label>
          <input
            type="email"
            id="email"
            v-model="email"
            required
          />
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
          <textarea
            id="text"
            v-model="text"
            required
          >
          </textarea>
        </div>

        <div>
          <label for="file_path">Attach file (optional):</label>
          <input
            type="file"
            id="file_path"
            @change="handleFileChange"
            accept=".jpg, .jpeg, .png, .gif, .txt"
          />
        </div>

        <div class="captcha">
          <input
            v-model="captcha"
            placeholder="Enter captcha"
            required
          />
        </div>

        <button type="submit">Submit</button>
      </form>
    </div>
  </template>

  <script>
  import { ref } from 'vue';

  export default {
    name: 'CommentForm',
    props: {
      comments: {
        type: Array,
        required: true,
      },
      isFormVisible: {
        type: Boolean,
        default: true,
      },
    },
    setup() {
      const user_name = ref("");
      const email = ref("");
      const text = ref("");
      const home_page = ref("");
      const captcha = ref("");
      const isFormVisible = ref(true);
      const avatar = ref("");
      const avatarPreview = ref(null); // Для предварительного просмотра аватара
      const file_path = ref(null);


      const handleAvatarChange = (event) => {
      const file = event.target.files[0];
      avatar.value = file; // Сохраняем выбранный файл
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          avatarPreview.value = e.target.result; // Отображаем предварительный просмотр
        };
        reader.readAsDataURL(file);
      }
    };

    const handleFileChange = (event) => {
      file_path.value = event.target.files[0];
    };


      const submitComment = async () => {
        // Проверка на наличие запрещенных HTML-тегов
        const forbiddenTags = /<\/?(script|iframe|object|embed|link|style)[\s>]/i;
        if (forbiddenTags.test(text.value)) {
          alert("Запрещенные теги обнаружены в тексте комментария.");
          return;
        }

        // Проверка на заполнение всех необходимых полей
        if (user_name.value && email.value && text.value && captcha.value) {
          const formData = new FormData();
          formData.append("user_name", user_name.value);
          formData.append("email", email.value);
          formData.append("text", text.value);
          formData.append("captcha", captcha.value);
          if (home_page.value) formData.append("home_page", home_page.value);
          if (avatar.value) formData.append("avatar", avatar.value); // Добавляем аватар
          if (file_path.value) formData.append("file_path", file_path.value); // Обновлено на 'file_path'

          try {
            const response = await fetch("http://localhost/api/comments", {
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
            // Если запрос успешен, парсим и логируем ответ
            const data = await response.json();
            console.log("Комментарий успешно добавлен:", data);

            // Очистка формы
            user_name.value = "";
            email.value = "";
            text.value = "";
            home_page.value = "";
            captcha.value = "";
            avatar.value = "";
            avatarPreview.value = null; // Очищаем превью
            file_path.value = null;
          } catch (error) {
            console.error("Error adding comment:", error);
          }
        } else {
          console.error("Please enter all fields and captcha");
        }
      };

      return {
        user_name,
        avatar,
        email,
        text,
        home_page,
        captcha,
        isFormVisible,
        avatarPreview, // Добавлено для возвращения
        handleAvatarChange,
        handleFileChange,
        submitComment,
      };
    },
  };
  </script>



<style lang="css" scoped>
.avatar-preview {
    max-width: 100px;
    max-height: 100px;
    margin-top: 10px;
  }

form {
    background-color: #f9f9f9;
    border: 1px solid DodgerBlue;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }

  label {
    display: block;
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: SlateGray;
  }

  input,
  textarea {
    width: 100%;
    padding: 0.5rem;
    margin-bottom: 1rem;
    border: 1px solid #ccc;
    border-radius: 0.25rem;
    font-size: 1rem;
  }

  input[type="file"] {
    padding: 0;
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

  .captcha input {
    width: calc(100% - 1rem);
  }

  .captcha {
    margin-bottom: 1rem;
  }

  input:focus,
  textarea:focus {
    border-color: DodgerBlue;
    outline: none;
  }

  textarea {
    resize: vertical;
    min-height: 120px;
  }

</style>


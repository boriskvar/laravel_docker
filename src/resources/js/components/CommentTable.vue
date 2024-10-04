<template>
    <div>
      <!-- Таблица комментариев -->
      <table v-if="comments.length">
        <thead>
          <tr>
            <th @click="sortBy('user_name')">
              User Name <i :class="sortIcon('user_name')"></i>
            </th>
            <th @click="sortBy('email')">
              E-mail <i :class="sortIcon('email')"></i>
            </th>
            <th @click="sortBy('created_at')">
              Date Added <i :class="sortIcon('created_at')"></i>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="comment in comments" :key="comment.id">
            <td>{{ comment.user_name }}</td>
            <td>{{ comment.email }}</td>
            <td>{{ formatDate(comment.created_at) }}</td>
          </tr>
        </tbody>
      </table>

      <div v-else>
        <p>No comments yet.</p>
      </div>

      <!-- Пагинация -->
      <Pagination
        v-if="totalPages > 1"
        :total-pages="totalPages"
        :current-page="page"
        @page-changed="fetchComments"
      />
    </div>
  </template>

  <script setup>
  import { ref, onMounted } from 'vue';
  import Pagination from './Pagination.vue'; // Импортируем компонент пагинации

  const API_URL = 'http://localhost/api/comments'; // URL API

  // Состояние для комментариев, сортировки и пагинации
  const comments = ref([]);
  const sortKey = ref('created_at');
  const sortOrder = ref('desc');
  const page = ref(1);
  const totalPages = ref(1);

  // Функция для получения комментариев с сортировкой и пагинацией
  const fetchComments = async (pageNum = 1) => {
    page.value = pageNum;
    try {
      const response = await fetch(`${API_URL}?page=${pageNum}&per_page=1&sort=${sortKey.value}&order=${sortOrder.value}`);
      if (!response.ok) {
        throw new Error('Network response was not ok.');
      }

      const data = await response.json();
      console.log('API Response:', data); // Проверяем формат данных

      comments.value = transformComments(data.data); // Обрабатываем данные
      totalPages.value = data.last_page; // Общее количество страниц
    } catch (error) {
      console.error('Error fetching comments:', error);
    }
  };

  // Функция для преобразования комментариев
  const transformComments = (data) => {
    if (!Array.isArray(data)) {
      console.error('Data is not an array:', data);
      return [];
    }
    return data.map(comment => ({
      id: comment.id,
      user_name: comment.user_name,
      email: comment.email,
      home_page: comment.home_page,
      captcha: comment.captcha,
      text: comment.text,
      created_at: comment.created_at,
      replies: comment.replies ? transformComments(comment.replies) : [], // Рекурсивно обрабатываем вложенные комментарии, если они есть
      avatar: comment.avatar ? `/storage/${comment.avatar}` : 'default-avatar.png', // Убедитесь, что путь правильный
    }));
  };

  // Функция для смены сортировки
  const sortBy = (key) => {
    if (sortKey.value === key) {
      sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
      sortKey.value = key;
      sortOrder.value = 'asc';
    }
    fetchComments(page.value); // Перезагружаем комментарии с новой сортировкой
  };

  // Определение иконки для сортировки
  const sortIcon = (key) => {
    return sortKey.value === key
      ? sortOrder.value === 'asc'
        ? 'fas fa-sort-up'
        : 'fas fa-sort-down'
      : 'fas fa-sort';
  };

  // Форматирование даты
  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString(); // Вы можете настроить форматирование даты
  };

  // Загрузка комментариев при монтировании компонента
  onMounted(() => {
    fetchComments();
  });
  </script>





  <style scoped>
  table {
    width: 100%;
  }

  th,
  td {
    padding: 10px;
    border: 1px solid DodgerBlue;
    border-radius: 0.5rem;
  }

  th {
    cursor: pointer;
  }

  i {
    margin-left: 5px;
  }
  </style>

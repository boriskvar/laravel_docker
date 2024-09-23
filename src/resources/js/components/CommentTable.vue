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
          <tr v-for="comment in sortedComments" :key="comment.id">
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
        :total-pages="totalPages"
        :current-page="page"
        @page-changed="fetchComments"
      />
    </div>
  </template>

  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import Pagination from './Pagination.vue';

  const API_URL = 'http://localhost/api/comments'; // URL API

  // Состояние для комментариев и сортировки
  const comments = ref([]);
  const sortKey = ref('created_at');
  const sortOrder = ref('desc');
  const page = ref(1);
  const totalPages = ref(1);

  // Функция для получения комментариев
  const fetchComments = async (pageNum = 1) => {
    page.value = pageNum;
    try {
      const response = await fetch(`${API_URL}?page=${pageNum}&sort=${sortKey.value}&order=${sortOrder.value}`);
      if (!response.ok) {
        throw new Error('Network response was not ok.');
      }

      const data = await response.json();
      console.log('API Response:', data); // Проверяем формат данных

      // Ожидаем, что ответ содержит массив комментариев
      if (Array.isArray(data)) {
        comments.value = data;
        totalPages.value = calculateTotalPages(data.length); // Пример вычисления общего числа страниц
      } else {
        console.error('Unexpected response format:', data);
      }
    } catch (error) {
      console.error('Error fetching comments:', error);
    }
  };

  // Пример функции для вычисления общего числа страниц
  const calculateTotalPages = (totalItems) => {
    // Замените это значением по умолчанию или логикой расчета
    return Math.ceil(totalItems / 10); // Если, например, 10 комментариев на страницу
  };

  // Функция для сортировки комментариев
  const sortBy = (key) => {
    if (sortKey.value === key) {
      sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
      sortKey.value = key;
      sortOrder.value = 'asc';
    }
    fetchComments(page.value);
  };

  // Сортировка комментариев по ключу и порядку
  const sortedComments = computed(() => {
    return [...comments.value].sort((a, b) => {
      const modifier = sortOrder.value === 'asc' ? 1 : -1;
      if (a[sortKey.value] < b[sortKey.value]) return -1 * modifier;
      if (a[sortKey.value] > b[sortKey.value]) return 1 * modifier;
      return 0;
    });
  });

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

<template>
    <div class="pagination">
      <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1">
        Previous
      </button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">
        Next
      </button>
    </div>
</template>

<script setup>
  import { defineEmits, defineProps } from 'vue';

  // Определяем свойства, которые компонент принимает
  const props = defineProps({
    totalPages: {
      type: Number,
      required: true,
    },
    currentPage: {
      type: Number,
      required: true,
    },
  });

  // Определяем события, которые компонент излучает
  const emit = defineEmits(['page-changed']);

  // Функция для изменения страницы
  const changePage = (page) => {
    if (page < 1 || page > props.totalPages) return;
    emit('page-changed', page); // Излучаем событие с новым номером страницы
  };
</script>


  <style scoped>
  .pagination {
    display: flex;
    justify-content: center; /* Центрируем элементы пагинации */
    align-items: center;
    margin-top: 20px;
  }

  button {
    padding: 5px 10px;
    margin: 0 5px; /* Добавляем отступ между кнопками */
    cursor: pointer; /* Изменяем курсор при наведении */
  }

  button:disabled {
    cursor: not-allowed; /* Изменяем курсор для отключенных кнопок */
    opacity: 0.5; /* Уменьшаем непрозрачность отключенных кнопок */
  }
  </style>

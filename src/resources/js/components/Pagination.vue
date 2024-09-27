<template>
    <div class="pagination">
      <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1">Previous</button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">Next</button>
    </div>
  </template>

  <script setup>
  import { ref, watch, defineEmits, defineProps } from 'vue';

  const props = defineProps({
    totalPages: {
      type: Number,
      required: true
    },
    currentPage: {
      type: Number,
      required: true
    }
  });

  const emit = defineEmits(['page-changed']);

  const changePage = (page) => {
    if (page < 1 || page > props.totalPages) return;
    emit('page-changed', page);
  };
  </script>

  <style scoped>
  .pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
  }

  button {
    padding: 5px 10px;
  }
  </style>

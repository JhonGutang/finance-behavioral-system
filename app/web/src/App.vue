<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { apiService, type MessageResponse } from './services/api.service';

const message = ref<string>('');
const description = ref<string>('');
const loading = ref<boolean>(true);
const error = ref<string>('');

onMounted(async () => {
  try {
    loading.value = true;
    const response: MessageResponse = await apiService.getMessage();
    message.value = response.message;
    description.value = response.description;
  } catch (err: any) {
    error.value = err.message || 'Failed to connect to backend';
    console.error('Error fetching message from backend:', err);
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="container">
    <div class="card">
      <h1>Finance Behavioral System</h1>
      
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Connecting to backend...</p>
      </div>

      <div v-else-if="error" class="error">
        <h2>❌ Connection Error</h2>
        <p>{{ error }}</p>
        <p class="hint">Make sure the Laravel backend is running on http://localhost:8000</p>
      </div>

      <div v-else class="success">
        <h2>✅ Backend Connected Successfully!</h2>
        <div class="message-box">
          <p class="message">{{ message }}</p>
          <p class="description">{{ description }}</p>
        </div>
        <div class="info">
          <p><strong>Frontend:</strong> Vue 3 + TypeScript (Vite)</p>
          <p><strong>Backend:</strong> Laravel API</p>
          <p><strong>CORS:</strong> Configured ✓</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
}

.card {
  background: white;
  border-radius: 1rem;
  padding: 3rem;
  max-width: 600px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

h1 {
  color: #667eea;
  margin-bottom: 2rem;
  text-align: center;
  font-size: 2rem;
}

h2 {
  margin-bottom: 1rem;
  text-align: center;
}

.loading {
  text-align: center;
  padding: 2rem;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error {
  text-align: center;
  color: #e53e3e;
}

.hint {
  font-size: 0.9rem;
  color: #718096;
  margin-top: 1rem;
}

.success {
  text-align: center;
}

.message-box {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  border-radius: 0.5rem;
  margin: 1.5rem 0;
}

.message {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.description {
  font-size: 1rem;
  opacity: 0.9;
}

.info {
  background: #f7fafc;
  padding: 1.5rem;
  border-radius: 0.5rem;
  text-align: left;
}

.info p {
  margin: 0.5rem 0;
  color: #2d3748;
}

.info strong {
  color: #667eea;
}
</style>

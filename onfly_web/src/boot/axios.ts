import { boot } from 'quasar/wrappers';
import axios from 'axios';
import { useAuthStore } from 'src/stores/auth';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost',
});

export default boot(() => {
  const authStore = useAuthStore();

  api.interceptors.request.use((config) => {
    if (authStore.token) {
      config.headers = config.headers ?? {};
      config.headers.Authorization = `Bearer ${authStore.token}`;
    }
    return config;
  });
});

export { api };

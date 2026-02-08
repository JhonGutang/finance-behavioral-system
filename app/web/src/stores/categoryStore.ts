import { defineStore } from 'pinia';
import { computed } from 'vue';
import {
  useCategories,
  useCreateCategory,
  useUpdateCategory,
  useDeleteCategory,
} from '../composables/useCategories';
import type { CategoryFormData } from '../types';

/**
 * Category Store
 * 
 * Manages category state using TanStack Vue Query for server state.
 * Provides computed properties and helper methods for component usage.
 */
export const useCategoryStore = defineStore('category', () => {
  // Use Vue Query composables for server state
  const categoriesQuery = useCategories();
  const createMutation = useCreateCategory();
  const updateMutation = useUpdateCategory();
  const deleteMutation = useDeleteCategory();

  // Computed properties for filtered categories
  const incomeCategories = computed(() =>
    categoriesQuery.data.value?.filter((c) => c.type === 'income') || []
  );

  const expenseCategories = computed(() =>
    categoriesQuery.data.value?.filter((c) => c.type === 'expense') || []
  );

  // Backward compatible getters
  const categories = computed(() => categoriesQuery.data.value || []);
  const loading = computed(() => categoriesQuery.isPending.value);
  const error = computed(() => categoriesQuery.error.value?.message || null);

  /**
   * Fetch categories (triggers refetch)
   * Maintained for backward compatibility
   */
  async function fetchCategories() {
    await categoriesQuery.refetch();
  }

  /**
   * Create a new category
   */
  async function createCategory(data: CategoryFormData) {
    return new Promise((resolve, reject) => {
      createMutation.mutate(data, {
        onSuccess: (newCategory) => resolve(newCategory),
        onError: (error) => reject(error),
      });
    });
  }

  /**
   * Update an existing category
   */
  async function updateCategory(id: number, data: Partial<CategoryFormData>) {
    return new Promise((resolve, reject) => {
      updateMutation.mutate(
        { id, data },
        {
          onSuccess: (updated) => resolve(updated),
          onError: (error) => reject(error),
        }
      );
    });
  }

  /**
   * Delete a category
   */
  async function deleteCategory(id: number) {
    return new Promise<void>((resolve, reject) => {
      deleteMutation.mutate(id, {
        onSuccess: () => resolve(),
        onError: (error) => reject(error),
      });
    });
  }

  const isCategoriesFetched = computed(() => !!categoriesQuery.data.value);

  return {
    // Data
    categories,
    incomeCategories,
    expenseCategories,
    loading,
    error,
    isCategoriesFetched,
    
    // Actions
    fetchCategories,
    createCategory,
    updateCategory,
    deleteCategory,
  };
});


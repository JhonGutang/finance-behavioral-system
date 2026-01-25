export type TransactionType = 'income' | 'expense';

export interface Category {
  id: number;
  name: string;
  type: TransactionType;
  color?: string;
  icon?: string;
  is_default: boolean;
  created_at: string;
  updated_at: string;
}

export interface Transaction {
  id: number;
  type: TransactionType;
  amount: number;
  date: string;
  category_id: number;
  category?: Category;
  description?: string;
  created_at: string;
  updated_at: string;
}

export interface Summary {
  total_income: number;
  total_expenses: number;
  net_balance: number;
  transaction_count: number;
}

export interface ExpenseByCategory {
  category_id: number;
  category: Category;
  total: number;
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
  errors?: Record<string, string[]>;
}

export interface TransactionFormData {
  type: TransactionType;
  amount: number | string;
  date: string;
  category_id: number | null | '';
  description?: string;
}

export interface CategoryFormData {
  name: string;
  type: TransactionType;
  color?: string;
  icon?: string;
}

export interface ChartDataPoint {
  label: string;
  income: number;
  expenses: number;
}

export interface AnalyticsData {
  labels: string[];
  income: number[];
  expenses: number[];
}

export type TimeRange = 'daily' | 'monthly' | 'yearly';

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

export interface LoginCredentials {
  email: string;
  password?: string;
}

export interface RegisterCredentials {
  name: string;
  email: string;
  password?: string;
  password_confirmation?: string;
}

export interface AuthResponse {
  user: User;
  access_token: string;
  token_type: string;
}

export interface RuleTrigger {
  rule_id: string;
  triggered: boolean;
  data: Record<string, any>;
}

export interface RuleEvaluation {
  user_id: number;
  evaluation_date: string;
  weeks: {
    current: { start: string; end: string };
    previous: { start: string; end: string };
  };
  triggered_rules: RuleTrigger[];
}

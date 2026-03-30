import type { Metadata } from 'next';
import { LoginForm } from '@/components/auth/LoginForm';

export const metadata: Metadata = {
  title: 'Sign In — Billoria Adpoint',
  description: 'Sign in to your Billoria account to discover, compare and book billboard advertising across Bangladesh.',
};

export default function LoginPage() {
  return <LoginForm />;
}

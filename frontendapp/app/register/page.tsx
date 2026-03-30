import type { Metadata } from 'next';
import { RegisterForm } from '@/components/auth/RegisterForm';

export const metadata: Metadata = {
  title: 'Create Account — Billoria Adpoint',
  description: 'Register a Brand, Agency or Billboard Owner account on Billoria — Bangladesh\'s premier outdoor advertising marketplace.',
};

export default function RegisterPage() {
  return <RegisterForm />;
}

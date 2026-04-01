'use client';

import { useEffect } from 'react';
import { authAPI } from '@/lib/api/auth';

/**
 * Syncs authentication state between cookie and localStorage.
 * 
 * Problem: localStorage persists indefinitely, but the auth cookie expires.
 * This causes UI to show "logged in" while middleware blocks protected routes.
 * 
 * Solution: On mount, verify the auth cookie exists. If missing but localStorage
 * has session data, clear localStorage to force re-login.
 */
export function AuthSync() {
    useEffect(() => {
        // Only run in browser
        if (typeof window === 'undefined') return;

        const hasCookie = document.cookie.includes('billoria_logged_in=1');
        const hasLocalStorage = authAPI.getCurrentUser() !== null;

        // Mismatch: localStorage says logged in, but cookie is gone
        if (!hasCookie && hasLocalStorage) {
            console.warn('[AuthSync] Cookie expired but localStorage persists. Clearing session.');
            authAPI.clearLocalSession();

            // Optional: Show a toast notification
            // toast.info('Your session has expired. Please log in again.');
        }
    }, []);

    return null; // No UI
}

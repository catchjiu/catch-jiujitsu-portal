/**
 * API client for Laravel portal backend.
 * Uses relative URLs when served from Laravel (same origin). For Vite dev,
 * ensure you access via Laravel (e.g. http://localhost:8000/portal) so session works.
 */

const API_BASE = '/api/portal';

async function request<T>(
  path: string,
  options: RequestInit = {}
): Promise<T> {
  const url = path.startsWith('http') ? path : `${API_BASE}${path}`;
  const res = await fetch(url, {
    ...options,
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...options.headers,
    },
  });

  if (res.status === 401) {
    window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
    throw new Error('Unauthenticated');
  }

  if (!res.ok) {
    const err = await res.json().catch(() => ({ error: res.statusText }));
    throw new Error(err.error || err.message || 'Request failed');
  }

  return res.json();
}

export async function getMe() {
  return request<{
    id: number;
    name: string;
    email: string;
    rank: string;
    stripes: number;
    matHours: number;
    isAdmin: boolean;
    avatarUrl?: string;
  }>('/me');
}

export async function getClasses(params?: { date?: string; filter?: string }) {
  const qs = params ? '?' + new URLSearchParams(params as Record<string, string>).toString() : '';
  return request<Array<{
    id: number;
    title: string;
    type: string;
    startTime: string;
    durationMinutes: number;
    instructor: string;
    capacity: number;
    bookedCount: number;
    isBookedByUser: boolean;
  }>>('/classes' + qs);
}

export async function getClassesUpcoming(params?: { days?: number; filter?: string }) {
  const qs = params ? '?' + new URLSearchParams(params as Record<string, string>).toString() : '';
  return request<Array<{
    id: number;
    title: string;
    type: string;
    startTime: string;
    durationMinutes: number;
    instructor: string;
    capacity: number;
    bookedCount: number;
    isBookedByUser: boolean;
  }>>('/classes/upcoming' + qs);
}

export async function bookClass(classId: number) {
  return request<{ success: boolean }>(`/book/${classId}`, { method: 'POST' });
}

export async function cancelBooking(classId: number) {
  return request<{ success: boolean }>(`/book/${classId}`, { method: 'DELETE' });
}

export async function getPayments() {
  return request<Array<{
    id: number;
    amount: number;
    month: string;
    status: string;
    proofImageUrl?: string;
    submittedAt?: string;
  }>>('/payments');
}

export async function uploadPaymentProof(paymentId: number, file: File) {
  const form = new FormData();
  form.append('proof', file);
  const res = await fetch(`${API_BASE}/payments/${paymentId}/upload-proof`, {
    method: 'POST',
    credentials: 'include',
    body: form,
    headers: { Accept: 'application/json' },
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.error || 'Upload failed');
  }
  return res.json();
}

export async function getAdminPayments() {
  return request<Array<{
    id: number;
    amount: number;
    month: string;
    status: string;
    proofImageUrl?: string;
    submittedAt?: string;
    userName?: string;
  }>>('/admin/payments');
}

export async function adminApprovePayment(id: number) {
  return request<{ success: boolean }>(`/admin/payments/${id}/approve`, { method: 'POST' });
}

export async function adminRejectPayment(id: number) {
  return request<{ success: boolean }>(`/admin/payments/${id}/reject`, { method: 'POST' });
}

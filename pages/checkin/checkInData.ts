import { CheckInMember } from '../../types';

/**
 * API base URL for check-in lookup. Empty = same origin (use when deployed with Laravel).
 * Set VITE_CHECKIN_API in .env to override (e.g. "https://yoursite.com" for CORS/proxy).
 */
const API_BASE = (typeof import.meta !== 'undefined' && (import.meta as unknown as { env?: { VITE_CHECKIN_API?: string } }).env?.VITE_CHECKIN_API) ?? '';

export type LookupResult = { member: CheckInMember } | { error: 'not_found' } | { error: 'server_error' };

/**
 * Look up member by QR code. Calls live API when deployed with Laravel.
 * Code can be numeric id (e.g. "1") or prefixed (e.g. "CATCH-1").
 */
export async function lookupMemberByCode(code: string): Promise<LookupResult> {
  const trimmed = code.trim();
  if (!trimmed) return { error: 'not_found' };

  const url = `${API_BASE}/api/checkin?code=${encodeURIComponent(trimmed)}`;

  try {
    const res = await fetch(url);
    if (res.status === 404) return { error: 'not_found' };
    if (!res.ok) return { error: 'server_error' };
    const data = await res.json();

    return {
      member: {
        id: data.id,
        name: data.name ?? '',
        rank: data.rank ?? 'White',
        stripes: Number(data.stripes) ?? 0,
        avatarUrl: data.avatarUrl ?? undefined,
        hoursThisYear: Number(data.hoursThisYear) ?? 0,
        classesThisMonth: Number(data.classesThisMonth) ?? 0,
        membershipExpiresAt: data.membershipExpiresAt ?? null,
        isActive: Boolean(data.isActive),
      },
    };
  } catch {
    return { error: 'server_error' };
  }
}
